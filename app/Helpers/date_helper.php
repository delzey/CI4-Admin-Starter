<?php

use CodeIgniter\I18n\Time;

if (! function_exists('formatDateBySetting')) {
    /**
     * Format a date using the Site.dateFormat setting.
     *
     * @param string|\DateTimeInterface|null $value
     * @param string|null $formatOverride Optional PHP date format override
     * @return string
     */
    function formatDateBySetting($value, ?string $formatOverride = null): string
    {
        if (empty($value)) {
            return '';
        }

        try {
            $format   = $formatOverride
                ?? setting('Site.dateFormat')
                ?? 'Y-m-d';

            $timezone = setting('Site.timezone')
                ?? config('App')->appTimezone
                ?? 'UTC';

            // Normalize into CodeIgniter Time
            if ($value instanceof \DateTimeInterface) {
                $time = Time::instance($value, $timezone);
            } else {
                $time = Time::parse((string) $value, $timezone);
            }

            return $time->format($format);

        } catch (\Throwable $e) {
            // Fail soft — never break a view
            return (string) $value;
        }
    }
}
