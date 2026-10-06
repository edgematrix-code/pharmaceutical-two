<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/api-boot.php';

/* POST /api/subscribe.php  { email }  -> newsletter signup */
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_out(['ok' => false, 'error' => 'Method not allowed'], 405);
}

try {
    $src   = json_input();
    $email = strtolower(req_str($src, 'email'));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_out(['ok' => false, 'errors' => ['Please enter a valid email address.']], 422);
    }

    $stmt = db()->prepare(
        "INSERT INTO subscribers (email, status) VALUES (?, 'subscribed')
         ON DUPLICATE KEY UPDATE status = 'subscribed'"
    );
    $stmt->execute([$email]);

    json_out(['ok' => true, 'message' => 'You are subscribed! Watch your inbox for updates and discounts.']);
} catch (Throwable $e) {
    json_out(['ok' => false, 'error' => 'Server error'], 500);
}
