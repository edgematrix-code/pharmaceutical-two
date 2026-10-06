<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

// seo.php provides url_path(), catalog.php provides image_url() - the same
// stored-path -> URL mapping the storefront uses for "Shop_files/..." rows.
require_once __DIR__ . '/../includes/seo.php';
require_once __DIR__ . '/../includes/catalog.php';

/**
 * Shared admin bootstrap: auth + status vocabularies.
 */

/**
 * URL for a product/review image inside the admin.
 *
 * image_url() maps a stored value (e.g. the legacy "Shop_files/x.png" rows)
 * onto /assets/img, but it anchors that to base_path(), which is derived from
 * dirname(SCRIPT_NAME) - inside /admin/ that is "/admin", so the URL would come
 * back as /admin/assets/... and 404. The admin directory always sits one level
 * below the app root, so "../" + the mapped path is correct wherever the app is
 * installed, and keeps image_url() as the single source of the path mapping.
 */
function admin_image_url(?string $stored): string
{
    $url  = image_url($stored);
    $base = base_path();
    if ($base !== '' && str_starts_with($url, $base . '/')) {
        $url = substr($url, strlen($base));
    }
    return '../' . ltrim($url, '/');
}

function order_statuses(): array
{
    return ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
}

function validate_order_status(string $status): bool
{
    return in_array($status, order_statuses(), true);
}

function payment_statuses(): array
{
    return ['unpaid', 'pending', 'paid', 'refunded'];
}
