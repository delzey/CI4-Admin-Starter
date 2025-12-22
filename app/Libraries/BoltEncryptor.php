<?php

namespace App\Libraries;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

class BoltEncryptor
{
    protected string $key;
    protected string $outputBase;
    protected array $exclude = ['vendor', '.git', 'node_modules'];

    public function __construct(?string $key = null, ?string $outputBase = null)
    {
        // You can define PHP_BOLT_KEY in .env or a config file
        $this->key        = $key ?? (defined('PHP_BOLT_KEY') ? PHP_BOLT_KEY : '');
        // Output encrypted files under project root: /writable/encrypted/...
        $this->outputBase = rtrim($outputBase ?? (WRITEPATH . 'encrypted'), DIRECTORY_SEPARATOR);
    }

    /**
     * Encrypt a directory tree into $outputBase/<label>/...
     *
     * @param string $sourceDir Full path to source directory (e.g. APPPATH . 'Controllers')
     * @param string $label     Used for output subfolder name
     */
    public function encryptDirectory(string $sourceDir, string $label = ''): string
    {
        if (! $this->key) {
            throw new \RuntimeException('PHP_BOLT_KEY is not defined. Cannot run Bolt encryption.');
        }

        if (! function_exists('bolt_encrypt') || ! function_exists('bolt_decrypt')) {
            throw new \RuntimeException('Bolt extension functions are not available. Is bolt.so loaded?');
        }

        $sourceDir = rtrim($sourceDir, DIRECTORY_SEPARATOR);
        if (! is_dir($sourceDir)) {
            throw new \InvalidArgumentException("Source directory does not exist: {$sourceDir}");
        }

        $label = $label ?: basename($sourceDir);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            $fullPath = $file->getPathname();

            // Skip excluded directories (vendor, node_modules, etc)
            foreach ($this->exclude as $ex) {
                if (strpos($fullPath, DIRECTORY_SEPARATOR . $ex . DIRECTORY_SEPARATOR) !== false) {
                    // Still mirror directory, but copy plain if it's a file
                    if ($file->isFile()) {
                        $this->copyPlain($sourceDir, $fullPath, $label);
                    }
                    continue 2; // continue outer foreach
                }
            }

            if ($file->isDir()) {
                $this->ensureTargetDir($sourceDir, $fullPath, $label);
                continue;
            }

            // Build destination path
            $relative   = substr($fullPath, strlen($sourceDir) + 1);
            $targetPath = $this->outputBase . DIRECTORY_SEPARATOR . $label . DIRECTORY_SEPARATOR . $relative;

            // Remove previous encrypted folder for clean rebuild
            if (is_dir($targetPath)) {
                $this->deleteDirectory($targetPath);
            }

            $extension = pathinfo($fullPath, PATHINFO_EXTENSION);

            // Non-PHP files: just copy
            if ($extension !== 'php') {
                $this->copyFile($fullPath, $targetPath);
                continue;
            }

            // PHP files: encrypt
            $this->encryptFile($fullPath, $targetPath);
        }

        return "Successfully secured '{$label}' files. Output: {$this->outputBase}/{$label}";
    }

    protected function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    protected function ensureTargetDir(string $sourceDir, string $fullPath, string $label): void
    {
        $relative = substr($fullPath, strlen($sourceDir) + 1);
        $target   = $this->outputBase . DIRECTORY_SEPARATOR . $label . DIRECTORY_SEPARATOR . $relative;

        if (! is_dir($target)) {
            mkdir($target, 0755, true);
        }
    }

    protected function copyPlain(string $sourceDir, string $fullPath, string $label): void
    {
        $relative   = substr($fullPath, strlen($sourceDir) + 1);
        $targetPath = $this->outputBase . DIRECTORY_SEPARATOR . $label . DIRECTORY_SEPARATOR . $relative;
        $this->copyFile($fullPath, $targetPath);
    }

    protected function copyFile(string $src, string $dst): void
    {
        $dir = dirname($dst);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        copy($src, $dst);
    }

    protected function encryptFile(string $src, string $dst): void
    {
        $contents = file_get_contents($src);
        if ($contents === false) {
            throw new \RuntimeException("Unable to read file: {$src}");
        }

        // Strip opening PHP tag
        $contents = preg_replace('/^\s*<\?php\s*/', '', $contents);

        $cipher = bolt_encrypt($contents, $this->key);

        // Create decrypt stub + inline cipher
        $bootstrap = '<?php bolt_decrypt(__FILE__, PHP_BOLT_KEY); return 0;' . PHP_EOL .
                    '##!!!##';

        // Ensure directory exists
        $dir = dirname($dst);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Delete previous file(s) before writing
        if (file_exists($dst)) {
            unlink($dst);
        }

        file_put_contents($dst, $bootstrap . $cipher);
    }
}
