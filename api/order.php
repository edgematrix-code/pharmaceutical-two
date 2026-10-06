<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/api-boot.php';

/* POST /api/order.php
   JSON body:  { "customer": {"first_name","last_name","name","email","phone",
                               "address","city","state","postal_code","country","notes"},
                 "payment_method": "bitcoin",
                 "coupon": "",
                 "items": [ {"slug": "test-cyp-200", "qty": 1} ] }
   Or form POST with items as a JSON string in the "items" field.
   Prices are always recalculated from the database. */
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_out(['ok' => false, 'error' => 'Method not allowed'], 405);
}

try {
    $src      = json_input();
    $customer = is_array($src['customer'] ?? null) ? $src['customer'] : $src;

    // The checkout form collects a first and last name; "name" is still
    // accepted so older clients and the mobile menu keep working.
    $first   = req_str($customer, 'first_name');
    $last    = req_str($customer, 'last_name');
    $name    = trim($first . ' ' . $last);
    if ($name === '') {
        $name = req_str($customer, 'name');
    }
    $email   = req_str($customer, 'email');
    $phone   = req_str($customer, 'phone');
    $notes   = req_str($customer, 'notes');
    $coupon  = req_str($src, 'coupon');

    // Shipping is collected as separate fields and stored as one text block.
    $street  = req_str($customer, 'address');
    $city    = req_str($customer, 'city');
    $region  = req_str($customer, 'state');
    $postal  = req_str($customer, 'postal_code');
    $country = req_str($customer, 'country');

    $notEmpty = static fn (string $part): bool => $part !== '';

    $lines      = [];
    $regionLine = implode(' ', array_filter([$region, $postal], $notEmpty));
    $cityLine   = implode(', ', array_filter([$city, $regionLine], $notEmpty));
    if ($street !== '') {
        $lines[] = $street;
    }
    if ($cityLine !== '') {
        $lines[] = $cityLine;
    }
    if ($country !== '') {
        $lines[] = $country;
    }
    $address = implode("\n", $lines);

    // A coupon code has no pricing engine yet: keep it on the order so the
    // code the customer typed is never lost between checkout and dispatch.
    if ($coupon !== '') {
        $couponLine = 'Coupon requested: ' . $coupon;
        $notes = $notes === '' ? $couponLine : $notes . "\n" . $couponLine;
    }

    $payment = req_str($src, 'payment_method', 'bitcoin');
    if (!array_key_exists($payment, payment_methods())) {
        $payment = 'other';
    }

    $items = $src['items'] ?? [];
    if (is_string($items)) {
        $decoded = json_decode($items, true);
        $items   = is_array($decoded) ? $decoded : [];
    }

    $errors = [];
    if (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
        $errors[] = 'Please enter your first and last name.';
    }
    // Deliberately lenient (see plausible_email()): a missing TLD such as
    // "name@gmail" must not block an order. Account login stays strict.
    if (!plausible_email($email)) {
        $errors[] = 'Please add an email address so we can send your payment instructions.';
    }
    if (!is_array($items) || count($items) === 0) {
        $errors[] = 'The order needs at least one item.';
    }
    if ($errors) {
        json_out(['ok' => false, 'errors' => $errors], 422);
    }

    $pdo = db();
    $pdo->beginTransaction();

    $prepared = [];
    $subtotal = 0.0;

    $stmtBySlug = $pdo->prepare('SELECT id, slug, name, price, sale_price, stock FROM products WHERE slug = ? AND is_active = 1 LIMIT 1');
    $stmtById   = $pdo->prepare('SELECT id, slug, name, price, sale_price, stock FROM products WHERE id = ? AND is_active = 1 LIMIT 1');

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $qty = max(1, min(999, (int)($item['qty'] ?? $item['quantity'] ?? 1)));
        $slug = trim((string)($item['slug'] ?? ''));
        $id   = (int)($item['id'] ?? 0);

        $stmt = $slug !== '' ? $stmtBySlug : $stmtById;
        $stmt->execute([$slug !== '' ? $slug : $id]);
        $product = $stmt->fetch();
        if (!$product) {
            continue; // unknown or inactive product is skipped
        }

        $unit      = (float)($product['sale_price'] ?? $product['price']);
        $lineTotal = round($unit * $qty, 2);
        $subtotal += $lineTotal;

        $prepared[] = [
            'product_id' => (int)$product['id'],
            'name'       => (string)$product['name'],
            'unit'       => $unit,
            'qty'        => $qty,
            'line_total' => $lineTotal,
        ];
    }

    if (count($prepared) === 0) {
        $pdo->rollBack();
        json_out(['ok' => false, 'errors' => ['None of the submitted items could be found.']], 422);
    }

    // Authoritative minimum-order check. The cart and checkout pages also
    // disable their buttons, but the rule has to hold for a hand-made request
    // too, so it is enforced here against the recalculated subtotal.
    $minNotice = min_order_notice($subtotal);
    if ($minNotice !== '') {
        $pdo->rollBack();
        json_out(['ok' => false, 'errors' => [$minNotice]], 422);
    }

    $shipping = (float)arail_config()['app']['shipping_flat'];
    $total    = round($subtotal + $shipping, 2);
    $orderNumber = 'ARL-' . date('Ymd') . '-' . random_code(5);

    $stmt = $pdo->prepare(
        'INSERT INTO orders
           (order_number, customer_name, customer_email, customer_phone, shipping_address, customer_notes,
            payment_method, subtotal, shipping, total, status, payment_status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \'pending\', \'unpaid\')'
    );
    $stmt->execute([
        $orderNumber, $name, $email,
        $phone !== '' ? $phone : null,
        $address !== '' ? $address : null,
        $notes !== '' ? $notes : null,
        $payment, $subtotal, $shipping, $total,
    ]);
    $orderId = (int)$pdo->lastInsertId();

    $stmtItem = $pdo->prepare(
        'INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, line_total)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    foreach ($prepared as $row) {
        $stmtItem->execute([$orderId, $row['product_id'], $row['name'], $row['unit'], $row['qty'], $row['line_total']]);
    }

    $pdo->commit();

    json_out([
        'ok'           => true,
        'order_id'     => $orderId,
        'order_number' => $orderNumber,
        'subtotal'     => $subtotal,
        'shipping'     => $shipping,
        'total'        => $total,
        'message'      => 'Order received. We will email payment instructions shortly.',
    ], 201);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_out(['ok' => false, 'error' => 'Server error'], 500);
}
