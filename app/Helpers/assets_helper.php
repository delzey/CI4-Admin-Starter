<?php

use Config\Assets;

/**
 * Assets Helper
 *
 * Generates HTML tags or URLs for private assets stored
 * under /app/assets, automatically adding version fingerprints.
 *
 * Usage:
 *   <?= asset_url('admin/css/style.css', 'css'); ?>
 *   <?= asset_url('admin/js/app.js', 'js'); ?>
 *   <?= asset('admin/img/logo.png', 'img'); ?>
 */

if (! function_exists('asset_url')) {
    /**
     * Returns a full HTML tag for an asset.
     */
    function asset_url(string $location, string $type): string
    {
        $url = asset($location, $type);

        return match ($type) {
            'css' => "<link rel='stylesheet' href='{$url}'>",
            'js'  => "<script src='{$url}' type='text/javascript'></script>",
            'img' => "<img src='{$url}' alt=''>",
            default => $url,
        };
    }
}

if (! function_exists('asset')) {
    /**
     * Builds a versioned asset URL that maps to AssetController::serve().
     *
     * Example:
     *   asset('admin/js/app.js')
     *   → /assets/admin/js/app.1730419200.js
     */
    function asset(string $location, string $type = ''): string
    {
        $config   = config(Assets::class);
        $location = trim($location, '/');
        $segments = explode('/', $location);
        $folderKey = strtolower($segments[0] ?? '');
        $ext = pathinfo($location, PATHINFO_EXTENSION);

        if (! isset($config->folders[$folderKey])) {
            throw new \RuntimeException("Unknown asset group: {$folderKey}");
        }

        $basePath = rtrim($config->folders[$folderKey], '/');
        $localPath = "{$basePath}/" . implode('/', array_slice($segments, 1));

        // Determine fingerprint
        if ($config->bustingType === 'file') {
            $fingerprint = file_exists($localPath)
                ? filemtime($localPath)
                : time();
        } else {
            $fingerprint = (ENVIRONMENT === 'production')
                ? ($config->versions[$ext] ?? '1.0')
                : time();
        }

        // Insert fingerprint before file extension
        $fingerprinted = preg_replace(
            '/\.' . $ext . '$/',
            '.' . $fingerprint . '.' . $ext,
            basename($location)
        );

        $segments[count($segments) - 1] = $fingerprinted;
        $assetPath = implode('/', $segments);

        // Point to AssetController route
        return base_url('assets/' . $assetPath);
    }
}
