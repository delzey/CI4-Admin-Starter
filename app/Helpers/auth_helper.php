<?php

use CodeIgniter\Shield\Entities\User;

if (! function_exists('has_permission')) {
    /**
     * Checks whether the currently authenticated user
     * has a specific permission.
     *
     * @param string $permission Permission name (e.g. "menu.manage")
     */
    function has_permission(string $permission): bool
    {
        if ($permission === '') {
            return true; // treat empty permission as unrestricted
        }

        $auth = auth();
        $user = $auth?->user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->can($permission);
    }
}
