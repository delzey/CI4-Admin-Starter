<?php

namespace App\Services;

use Config\Assets;

class AssetService
{
    protected Assets $config;

    protected array $mimeMap = [
        'css'   => 'text/css; charset=utf-8',
        'js'    => 'application/javascript; charset=utf-8',
        'map'   => 'application/json; charset=utf-8',
        'svg'   => 'image/svg+xml',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'webp'  => 'image/webp',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'eot'   => 'application/vnd.ms-fontobject',
    ];

    public function __construct()
    {
        $this->config = config(Assets::class);
    }

    public function resolve(array $segments): ?array
    {
        // ---- security ------------------------------------------------
        foreach ($segments as $seg) {
            if ($seg === '' || $seg === '.' || $seg === '..' || strpbrk($seg, "\\/")) {
                return null;
            }
        }

        if (count($segments) < 2) {
            return null;
        }

        $bucket = array_shift($segments);

        if (! isset($this->config->folders[$bucket])) {
            return null;
        }

        $baseDir  = realpath($this->config->folders[$bucket]);
        if ($baseDir === false) {
            return null;
        }

        $requested = array_pop($segments);
        $filename  = $this->deBustFilename($requested);

        $path = $baseDir
            . '/' . implode('/', $segments)
            . '/' . $filename;

        $realPath = realpath($path);

        if ($realPath === false || strpos($realPath, $baseDir) !== 0) {
            return null;
        }

        $body = file_get_contents($realPath);
        if ($body === false) {
            return null;
        }

        $mtime = filemtime($realPath) ?: time();
        $size  = filesize($realPath) ?: strlen($body);

        $ext  = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
        $mime = $this->mimeMap[$ext] ?? 'application/octet-stream';

        return [
            'body'         => $body,
            'mime'         => $mime,
            'etag'         => '"' . sha1($realPath . '|' . $mtime . '|' . $size) . '"',
            'lastModified' => gmdate('D, d M Y H:i:s', $mtime) . ' GMT',
            'cacheControl' => ENVIRONMENT === 'production'
                ? 'public, max-age=31536000, immutable'
                : 'no-store, no-cache, must-revalidate',
        ];
    }

    protected function deBustFilename(string $name): string
    {
        $parts = explode('.', $name);
        if (count($parts) < 3) {
            return $name;
        }

        $ext = array_pop($parts);
        $last = end($parts);

        if (preg_match('/^\d{8,}$/', $last) || preg_match('/^\d+\.\d+(\.\d+)?$/', $last)) {
            array_pop($parts);
        }

        return implode('.', $parts) . '.' . $ext;
    }
}

