<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/api-boot.php';

/* POST /api/contact.php  { name, email, subject?, message } */
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_out(['ok' => false, 'error' => 'Method not allowed'], 405);
}

try {
    $src = json_input();

    // Honeypot: bots fill hidden fields, humans do not.
    if (req_str($src, 'website') !== '') {
        json_out(['ok' => true, 'message' => 'Thanks for reaching out!']);
    }

    $name    = req_str($src, 'name');
    $email   = req_str($src, 'email');
    $subject = req_str($src, 'subject');
    $message = req_str($src, 'message') !== '' ? req_str($src, 'message') : req_str($src, 'body');

    $errors = [];
    if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
        $errors[] = 'Please enter your name (2-120 characters).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (mb_strlen($message) < 5 || mb_strlen($message) > 5000) {
        $errors[] = 'The message must be between 5 and 5000 characters.';
    }
    if (mb_strlen($subject) > 200) {
        $subject = mb_substr($subject, 0, 200);
    }
    if ($errors) {
        json_out(['ok' => false, 'errors' => $errors], 422);
    }

    $stmt = db()->prepare('INSERT INTO messages (name, email, subject, body) VALUES (?, ?, ?, ?)');
    $stmt->execute([$name, $email, $subject !== '' ? $subject : null, $message]);

    json_out(['ok' => true, 'message' => 'Thanks for reaching out! We will get back to you shortly.']);
} catch (Throwable $e) {
    json_out(['ok' => false, 'error' => 'Server error'], 500);
}
