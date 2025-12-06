<?php

namespace App\Services;

use App\Models\GroupPermissionModel;
use Config\AuthGroups;

class PermissionService
{
    protected GroupPermissionModel $groupPermModel;
    protected AuthGroups $authConfig;

    public function __construct(?GroupPermissionModel $model = null, ?AuthGroups $config = null)
    {
        $this->groupPermModel = $model ?? new GroupPermissionModel();
        $this->authConfig     = $config ?? config(AuthGroups::class);
    }

    /**
     * Get all groups with their combined (static + DB) permissions.
     */
    public function getAllGroupsWithPermissions(): array
    {
        $groups = $this->authConfig->groups;
        $matrix = $this->authConfig->matrix ?? [];

        foreach ($groups as $alias => &$group) {
            $dbRow = $this->groupPermModel->getByGroup($alias);
            $dbPerms = $dbRow['permissions'] ?? [];

            $merged = array_unique(array_merge(
                $matrix[$alias] ?? [],
                $dbPerms
            ));

            $group['permissions'] = $merged;
        }

        return $groups;
    }

    /**
     * Get all permissions (from config)
     */
    public function getAllPermissions(): array
    {
        return $this->authConfig->permissions;
    }

    /**
     * Get a group's effective permissions.
     */
    public function getGroupPermissions(string $group): array
    {
        $base = $this->authConfig->matrix[$group] ?? [];
        $db   = $this->groupPermModel->getByGroup($group);
        $extra = $db['permissions'] ?? [];

        return array_unique(array_merge($base, $extra));
    }

    /**
     * Save or update a group's permissions.
     */
    public function setGroupPermissions(string $group, array $permissions): bool
    {
        $existing = $this->groupPermModel->getByGroup($group);
        $data = [
            'group_name'  => $group,
            'permissions' => array_values(array_unique($permissions)),
        ];

        return $existing
            ? $this->groupPermModel->update($existing['id'], $data)
            : $this->groupPermModel->insert($data) !== false;
    }
}
