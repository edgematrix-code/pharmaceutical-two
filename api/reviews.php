<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/api-boot.php';

/* GET  /api/reviews.php?product_id=1&limit=20  -> approved reviews
   POST /api/reviews.php                        -> submit a review (goes to "pending")
   Accepts JSON or multipart/form-data (optional photo). */
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method === 'GET') {
        $where  = "status = 'approved'";
        $params = [];
        $productId = req_int($_GET, 'product_id', 0);
        if ($productId > 0) {
            $where   .= ' AND product_id = ?';
            $params[] = $productId;
        }
        $limit = min(max(req_int($_GET, 'limit', 20), 1), 100);
        $stmt = db()->prepare(
            "SELECT id, product_id, author_name, rating, title, body, forum_url, photo, created_at
             FROM reviews WHERE $where ORDER BY created_at DESC LIMIT $limit"
        );
        $stmt->execute($params);
        json_out(['ok' => true, 'reviews' => $stmt->fetchAll()]);
    }

    $src = json_input();

    $name  = req_str($src, 'name');
    $email = req_str($src, 'email');
    $body  = req_str($src, 'quote') !== '' ? req_str($src, 'quote') : req_str($src, 'body');
    $title = req_str($src, 'title');
    $forum = req_str($src, 'forumUrl') !== '' ? req_str($src, 'forumUrl') : req_str($src, 'forum_url');
    $rating    = req_int($src, 'rating', 5);
    $productId = req_int($src, 'product_id', 0);
    $slug      = req_str($src, 'product_slug');

    $errors = [];
    if (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
        $errors[] = 'Please enter a display name (2-80 characters).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (mb_strlen($body) < 20 || mb_strlen($body) > 2000) {
        $errors[] = 'The review text must be between 20 and 2000 characters.';
    }
    if ($rating < 1 || $rating > 5) {
        $rating = 5;
    }
    if ($forum !== '' && !filter_var($forum, FILTER_VALIDATE_URL)) {
        $errors[] = 'The forum link must be a valid URL.';
    }

    if ($productId === 0 && $slug !== '') {
        $stmt = db()->prepare('SELECT id FROM products WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $productId = (int)($stmt->fetchColumn() ?: 0);
    }
    if ($productId > 0) {
        $stmt = db()->prepare('SELECT COUNT(*) FROM products WHERE id = ?');
        $stmt->execute([$productId]);
        if ((int)$stmt->fetchColumn() === 0) {
            $productId = 0;
        }
    }

    [$photo, $uploadError] = handle_image_upload('photo', 'review');
    if ($uploadError !== null) {
        $errors[] = $uploadError;
    }

    if ($errors) {
        json_out(['ok' => false, 'errors' => $errors], 422);
    }

    $stmt = db()->prepare(
        "INSERT INTO reviews (product_id, author_name, author_email, rating, title, body, forum_url, photo, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
    );
    $stmt->execute([
        $productId > 0 ? $productId : null,
        $name,
        $email,
        $rating,
        $title !== '' ? $title : null,
        $body,
        $forum !== '' ? $forum : null,
        $photo,
    ]);

    json_out(['ok' => true, 'message' => 'Thank you! Your review was submitted and will appear once approved.']);
} catch (Throwable $e) {
    json_out(['ok' => false, 'error' => 'Server error'], 500);
}
