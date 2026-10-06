<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/api-boot.php';

/*
 * Customer accounts (storefront only - separate from admin_users).
 *
 *   POST ?action=register  { email, password, first_name, last_name, phone? }
 *   POST ?action=login     { email, password }
 *   POST ?action=logout
 *   POST ?action=update    { first_name, last_name, phone, address }   (logged in)
 *   GET        ?action=me  / ?action=orders
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = req_str($_GET, 'action', $method === 'POST' ? '' : 'me');
$src    = $method === 'POST' ? (json_input() + $_GET) : $_GET;
$action = req_str($src, 'action', $action);

function current_customer(): ?array
{
    if (empty($_SESSION['customer_id'])) {
        return null;
    }
    $stmt = db()->prepare('SELECT id, email, first_name, last_name, phone, address, created_at FROM customers WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$_SESSION['customer_id']]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function public_customer(array $row): array
{
    unset($row['password_hash']);
    return $row;
}

try {
    switch ($action) {
        case 'register':
            $email = strtolower(req_str($src, 'email'));
            $pass  = (string)($src['password'] ?? '');
            $first = req_str($src, 'first_name');
            $last  = req_str($src, 'last_name');
            $phone = req_str($src, 'phone');

            $errors = [];
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Please enter a valid email address.';
            }
            if (strlen($pass) < 8) {
                $errors[] = 'Your password must be at least 8 characters.';
            }
            if ($errors) {
                json_out(['ok' => false, 'errors' => $errors], 422);
            }

            $stmt = db()->prepare('SELECT id FROM customers WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                json_out(['ok' => false, 'errors' => ['An account with that email already exists.']], 409);
            }

            $stmt = db()->prepare(
                'INSERT INTO customers (email, password_hash, first_name, last_name, phone) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$email, password_hash($pass, PASSWORD_DEFAULT), $first, $last, $phone !== '' ? $phone : null]);
            $_SESSION['customer_id'] = (int)db()->lastInsertId();
            json_out(['ok' => true, 'message' => 'Welcome! Your account is ready.', 'customer' => current_customer()], 201);
            break;

        case 'login':
            $email = strtolower(req_str($src, 'email'));
            $pass  = (string)($src['password'] ?? '');
            $stmt  = db()->prepare('SELECT * FROM customers WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $row = $stmt->fetch();
            if (!$row || !password_verify($pass, (string)$row['password_hash'])) {
                json_out(['ok' => false, 'errors' => ['Email or password is incorrect.']], 401);
            }
            $_SESSION['customer_id'] = (int)$row['id'];
            db()->prepare('UPDATE customers SET last_login_at = NOW() WHERE id = ?')->execute([(int)$row['id']]);
            json_out(['ok' => true, 'message' => 'Signed in.', 'customer' => public_customer($row)]);
            break;

        case 'logout':
            unset($_SESSION['customer_id']);
            json_out(['ok' => true, 'message' => 'Signed out.']);
            break;

        case 'update':
            $me = current_customer();
            if (!$me) {
                json_out(['ok' => false, 'error' => 'Not signed in.'], 401);
            }
            $stmt = db()->prepare('UPDATE customers SET first_name = ?, last_name = ?, phone = ?, address = ? WHERE id = ?');
            $stmt->execute([
                req_str($src, 'first_name'),
                req_str($src, 'last_name'),
                req_str($src, 'phone') !== '' ? req_str($src, 'phone') : null,
                req_str($src, 'address') !== '' ? req_str($src, 'address') : null,
                (int)$me['id'],
            ]);
            json_out(['ok' => true, 'message' => 'Profile updated.', 'customer' => current_customer()]);
            break;

        case 'orders':
            $me = current_customer();
            if (!$me) {
                json_out(['ok' => false, 'error' => 'Not signed in.'], 401);
            }
            $stmt = db()->prepare(
                'SELECT order_number, total, status, payment_status, created_at
                 FROM orders WHERE customer_email = ? ORDER BY created_at DESC LIMIT 50'
            );
            $stmt->execute([$me['email']]);
            json_out(['ok' => true, 'orders' => $stmt->fetchAll()]);
            break;

        case 'me':
        default:
            json_out(['ok' => true, 'customer' => current_customer()]);
            break;
    }
} catch (Throwable $e) {
    json_out(['ok' => false, 'error' => 'Server error'], 500);
}
