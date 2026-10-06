<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/api-boot.php';

/*
 * POST /api/payment.php
 * JSON body: { "order_number": "ARL-20261006-ABCDE" }
 *
 * Called from the dedicated payment page when the customer clicks "I have
 * completed payment". It flips payment_status from unpaid/pending to paid so
 * the order shows up as paid in the admin. The store still verifies the
 * transfer on-chain before dispatching; this only records the customer's claim.
 */
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_out(['ok' => false, 'error' => 'Method not allowed'], 405);
}

try {
    $src    = json_input();
    $number = strtoupper(trim((string)($src['order_number'] ?? $src['number'] ?? '')));

    if ($number === '' || !preg_match('/^[A-Z]{2,4}-[0-9]{8}-[A-Z0-9]{4,8}$/', $number)) {
        json_out(['ok' => false, 'error' => 'Invalid order number.'], 422);
    }

    $stmt = db()->prepare('SELECT id, payment_status FROM orders WHERE order_number = ? LIMIT 1');
    $stmt->execute([$number]);
    $order = $stmt->fetch();
    if (!$order) {
        json_out(['ok' => false, 'error' => 'We could not find that order.'], 404);
    }

    // Idempotent: only move an unpaid/pending order to paid.
    if ((string)$order['payment_status'] !== 'paid') {
        db()->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ?")->execute([(int)$order['id']]);
    }

    json_out([
        'ok'             => true,
        'payment_status' => 'paid',
        'message'        => 'Thank you. We have recorded your payment and will confirm it shortly.',
    ]);
} catch (Throwable $e) {
    json_out(['ok' => false, 'error' => 'Server error'], 500);
}
