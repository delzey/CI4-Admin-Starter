<?php

namespace App\Services;

use CodeIgniter\Settings\Settings;
use Config\Services;

class SettingsService
{
    protected Settings $settings;

    public function __construct(?Settings $settings = null)
    {
        $this->settings = $settings ?? Services::settings();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->settings->get($key) ?? $default;
    }

public function set(string $key, mixed $value): bool
{
    try {
        $result = $this->settings->set($key, $value);
        return $result !== null; // return true if call succeeded
    } catch (\Throwable $e) {
        log_message('error', 'SettingsService->set() failed: ' . $e->getMessage());
        return false;
    }
}

    /**
     * CI Settings 2.2.0 has no public “get all”.
     * We’ll maintain an empty array for now to avoid errors.
     */
    public function all(string $group = 'app'): array
    {
        return [];   // placeholder until Settings adds an official API
    }

    public function clear(string $group = 'app'): void
    {
        cache()->delete("settings_{$group}");
    }
}
