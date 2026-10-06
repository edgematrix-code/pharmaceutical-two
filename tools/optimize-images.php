<?php
declare(strict_types=1);

/*
 * CLI-only. nginx does not read .htaccess, so these utilities must refuse to
 * run over HTTP themselves rather than relying on server configuration.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit('This script can only be run from the command line.');
}

/**
 * Image optimisation (run once, or after uploading new images).
 *
 *   php tools/optimize-images.php            # convert every raster asset
 *   php tools/optimize-images.php --force    # re-convert even if up to date
 *
 * For every PNG/JPEG in /assets/img it writes a sibling .webp file at the same
 * dimensions. The templates emit a <picture> element so browsers that support
 * WebP (all modern browsers) download the smaller file, and everything else
 * falls back to the original format. Requires ext-gd with WebP support.
 */
$root  = dirname(__DIR__);
$dir   = $root . '/assets/img';
$force = in_array('--force', $argv, true);

if (!function_exists('imagewebp')) {
    fwrite(STDERR, "GD with WebP support is required (ext-gd).\n");
    exit(1);
}

$converted = 0;
$skipped   = 0;
$failed    = [];
$before    = 0;
$after     = 0;

foreach (glob($dir . '/*') as $path) {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (!in_array($ext, ['png', 'jpg', 'jpeg'], true)) {
        continue;
    }

    $target = preg_replace('~\.(png|jpe?g)$~i', '.webp', $path);
    $srcSize = filesize($path);

    if (!$force && is_file($target) && filemtime($target) >= filemtime($path)) {
        $skipped++;
        $before += $srcSize;
        $after  += filesize($target);
        continue;
    }

    $image = $ext === 'png' ? @imagecreatefrompng($path) : @imagecreatefromjpeg($path);
    if (!$image) {
        $failed[] = basename($path);
        continue;
    }

    if ($ext === 'png') {
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
    }

    if (imagewebp($image, $target, 82)) {
        $converted++;
        $before += $srcSize;
        $after  += filesize($target);
    } else {
        $failed[] = basename($path);
    }
}

$saved = $before > 0 ? round((1 - $after / $before) * 100, 1) : 0;
echo "converted: $converted  already current: $skipped  failed: " . count($failed) . "\n";
printf("payload: %s -> %s (%.1f%% smaller)\n", round($before / 1024) . ' KB', round($after / 1024) . ' KB', $saved);
foreach ($failed as $f) {
    echo "  FAILED: $f\n";
}
