<?php

use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Entities\User;

if (! function_exists('buildPermissionsTable')) {
    /**
     * Renders a permissions table for a given user ID.
     *
     * @param int|null $userId
     * @return string HTML markup
     */
    function buildPermissionsTable(?int $userId = null): string
    {
        if (! $userId) {
            return '<div class="alert alert-info text-center">Select a user to view permissions.</div>';
        }

        $userModel = model(UserModel::class);
        $user = $userModel->find($userId);

        if (! $user instanceof User) {
            return '<div class="alert alert-warning text-center">User not found.</div>';
        }

        $permissions = setting('AuthGroups.permissions') ?? [];

        if (empty($permissions)) {
            return '<div class="alert alert-info text-center">No permissions defined in AuthGroups config.</div>';
        }

        // Group permissions like "users.create", "users.delete" => users => [create, delete]
        $grouped = [];
        foreach ($permissions as $key => $desc) {
            [$module, $action] = explode('.', $key) + [null, null];
            if ($module && $action) {
                $grouped[$module][] = [
                    'key'         => $key,
                    'action'      => $action,
                    'description' => $desc,
                ];
            }
        }

        ksort($grouped);

        $html = '<div class="permissions-table">';
        foreach ($grouped as $module => $actions) {
            $html .= '<div class="card mb-3">';
            $html .= '<div class="card-header bg-light"><strong>' . esc(ucwords($module)) . '</strong></div>';
            $html .= '<div class="card-body"><div class="row">';

            foreach ($actions as $a) {
                $checked = $user->can($a['key']) ? 'checked' : '';
                $id = uniqid('perm_');

                $html .= <<<HTML
                <div class="col-md-3 mb-2">
                    <div class="form-check form-switch">
                        <input type="checkbox" 
                               class="form-check-input tglPerm"
                               id="{$id}"
                               data-user="{$userId}"
                               data-module="{$module}"
                               data-operation="{$a['action']}"
                               {$checked}>
                        <label class="form-check-label" for="{$id}">
                            {$a['action']}
                        </label>
                    </div>
                </div>
                HTML;
            }

            $html .= '</div></div></div>';
        }

        $html .= '</div>';
        return $html;
    }
}
