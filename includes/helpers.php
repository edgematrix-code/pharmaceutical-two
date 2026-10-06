<?php
declare(strict_types=1);

/**
 * Small shared helpers (output escaping, JSON responses, CSRF, uploads).
 */

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function json_out($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Security headers for every public response.
 *
 * These used to live in .htaccess, where they never actually ran: nginx/Herd
 * ignores .htaccess, and phpix (the PHP runtime behind Wasmer Edge) rejects
 * the `Header always set ...` form by answering every request with
 * "500 Htaccess evaluation failed". Sending them from PHP means they are
 * really sent, on every host.
 */
function arail_send_security_headers(): void
{
    if (headers_sent()) {
        return;
    }

    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

    // 'unsafe-inline' is required while the inline critical-CSS/age-gate
    // snippets remain; tighten once they are externalised. The Google hosts are
    // listed so GA4 keeps working once app.ga4_id is set. HSTS is left to the
    // hosting platform.
    header(
        "Content-Security-Policy: default-src 'self'; "
        . "img-src 'self' data: https://www.google-analytics.com https://www.googletagmanager.com; "
        . "style-src 'self' 'unsafe-inline'; "
        . "script-src 'self' 'unsafe-inline' https://www.googletagmanager.com; "
        . "font-src 'self'; "
        . "connect-src 'self' https://www.google-analytics.com https://region1.google-analytics.com; "
        . "base-uri 'self'; form-action 'self'; frame-ancestors 'self'"
    );
}

/** Accepts JSON body or classic form POST. */
function json_input(): array
{
    $raw = file_get_contents('php://input');
    if ($raw !== false && $raw !== '') {
        $data = json_decode($raw, true);
        if (is_array($data)) {
            return $data;
        }
    }
    return $_POST;
}

function req_str(array $src, string $key, string $default = ''): string
{
    return isset($src[$key]) && is_scalar($src[$key]) ? trim((string)$src[$key]) : $default;
}

/**
 * Lenient email check, used by the checkout form only.
 *
 * `FILTER_VALIDATE_EMAIL` rejects anything without a dot in the domain, so a
 * customer who typed "name@gmail" (or "name@localhost") could not place an
 * order at all - the whole checkout failed on a contact field. The customer
 * email is not a credential, so this only requires what we actually need: one
 * "@", a non-empty local part and a non-empty domain, no whitespace, and no
 * dot at the very start or end of either side.
 *
 * Account authentication (api/auth.php) keeps using the strict filter.
 */
function plausible_email(string $email): bool
{
    if ($email === '' || strlen($email) > 254) {
        return false;
    }
    if (preg_match('/[[:space:]]/', $email)) {
        return false;
    }
    $parts = explode('@', $email);
    if (count($parts) !== 2) {
        return false;
    }
    [$local, $domain] = $parts;

    return $local !== '' && $domain !== ''
        && !str_starts_with($local, '.') && !str_ends_with($local, '.')
        && !str_starts_with($domain, '.') && !str_ends_with($domain, '.');
}

function req_int(array $src, string $key, int $default = 0): int
{
    return isset($src[$key]) && is_numeric($src[$key]) ? (int)$src[$key] : $default;
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-');
}

function money(float $amount): string
{
    return arail_config()['app']['currency'] . number_format($amount, 2);
}

/**
 * Minimum order value, in the store currency.
 *
 * Orders below this cannot be placed (the "Is there a minimum order?" FAQ on
 * contact.php promises the rule). The value is configured as app.min_order in
 * includes/config.php, handed to the cart/checkout JS through a data attribute,
 * and enforced again in api/order.php so nothing can bypass it.
 */
function min_order_value(): float
{
    return (float)(arail_config()['app']['min_order'] ?? 0);
}

/**
 * "Add $X more to reach the $Y minimum order." for a merchandise subtotal, or
 * an empty string when the subtotal already qualifies.
 *
 * Kept in PHP as well as in cart.js so the server response and the on-page
 * notices say exactly the same thing.
 */
function min_order_notice(float $subtotal): string
{
    $minimum = min_order_value();
    if ($minimum <= 0 || round($subtotal, 2) >= $minimum) {
        return '';
    }
    return 'Add ' . money(round($minimum - $subtotal, 2)) . ' more to reach the ' . money($minimum) . ' minimum order.';
}

/**
 * Payment providers offered at checkout, in display order.
 *
 * The keys are the identifiers stored in orders.payment_method. The legacy
 * values (bitcoin / bank / cash) are kept so that orders placed before the
 * checkout redesign still resolve to a readable label.
 *
 * @return array<string, string>
 */
function payment_methods(): array
{
    return [
        'btcpaygf_default' => 'Bitcoin (BTCPay)',
        'cryptapi'         => 'Other cryptocurrency',
        'bitcoin'          => 'Bitcoin',
        'bank'             => 'Bank transfer',
        'cash'             => 'Cash / other',
        'other'            => 'Other',
    ];
}

function payment_method_label(?string $value): string
{
    return payment_methods()[(string)$value] ?? 'Other';
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function flash(string $type, string $message): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): void
{
    echo '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $sent = $_POST['_token'] ?? '';
    if (!is_string($sent) || $sent === '' || !hash_equals(csrf_token(), $sent)) {
        http_response_code(419);
        exit('Security token expired. Go back, reload the page and try again.');
    }
}

function random_code(int $length = 8): string
{
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $code;
}

/**
 * Validates and stores an uploaded image.
 *
 * @return array{0: ?string, 1: ?string} [relative web path, error message]
 */
function handle_image_upload(string $field, string $prefix = 'img'): array
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Upload failed (error code ' . (int)$file['error'] . ').'];
    }
    $app = arail_config()['app'];
    if ($file['size'] > $app['max_upload_mb'] * 1024 * 1024) {
        return [null, 'File is larger than ' . $app['max_upload_mb'] . ' MB.'];
    }
    $info = @getimagesize($file['tmp_name']);
    if ($info === false) {
        return [null, 'The uploaded file is not a valid image.'];
    }
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    $mime = $info['mime'] ?? '';
    if (!isset($allowed[$mime])) {
        return [null, 'Only JPG, PNG, WEBP or GIF images are allowed.'];
    }
    $dir = $app['uploads_dir'];
    if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
        return [null, 'Uploads directory is not writable.'];
    }
    $name = $prefix . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return [null, 'Could not save the uploaded file.'];
    }
    return ['uploads/' . $name, null];
}
