<?php

namespace App\Controllers;

use App\Services\PermissionService;
use CodeIgniter\HTTP\ResponseInterface;
use Config\AuthGroups;

class GroupPermissions extends BaseController
{
    protected PermissionService $permService;
    protected AuthGroups $authConfig;

    public function __construct()
    {
        $this->permService = service('permissionService') ?? new PermissionService();
        $this->authConfig  = config(AuthGroups::class);
    }

    // Page shell
    public function index()
    {
        return view('pages/group_permissions', [
            'title' => 'Group Permissions',
        ]);
    }

    // All groups with merged permissions (config + DB)
    public function groups(): ResponseInterface
    {
        return $this->response->setJSON($this->permService->getAllGroupsWithPermissions());
    }

    // All available permissions from config, grouped by "module" (prefix)
    public function perms(): ResponseInterface
    {
        $all = $this->permService->getAllPermissions(); // ['users.create' => '...', ...]

        // Group by left side of "module.action"
        $grouped = [];
        foreach ($all as $key => $desc) {
            [$module, $action] = explode('.', $key, 2) + [null, null];
            $module = $module ?? '_misc';
            $grouped[$module][$key] = $desc;
        }

        ksort($grouped);

        return $this->response->setJSON($grouped);
    }

    // Save selected permissions for a group (stores as overrides)
    public function save(): ResponseInterface
    {
        $groupAlias  = (string) $this->request->getPost('group');
        $permissions = $this->request->getPost('permissions') ?? [];

        // Validate: group must exist in config
        if (! array_key_exists($groupAlias, $this->authConfig->groups)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unknown group alias.',
            ]);
        }

        // Validate: all submitted perms must be defined in config
        $allowed = array_keys($this->authConfig->permissions);
        $invalid = array_diff($permissions, $allowed);
        if (! empty($invalid)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid permission(s): ' . implode(', ', $invalid),
            ]);
        }

        try {
            $ok = $this->permService->setGroupPermissions($groupAlias, $permissions);
            return $this->response->setJSON([
                'success' => (bool) $ok,
                'message' => $ok ? 'Permissions saved.' : 'Nothing changed.',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
