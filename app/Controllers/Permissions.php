<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\HTTP\ResponseInterface;

class Permissions extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        helper(['auth', 'permissions']);
        $this->userModel = model(UserModel::class);
    }

    public function index(): string
    {
        $users = $this->userModel
            ->select('id, username')
            ->orderBy('username', 'ASC')
            ->findAll();

        return view('pages/permissions', [
            'title' => 'Permissions Manager',
            'users' => $users,
        ]);
    }

    /**
     * Returns a rendered permission table partial for the given user.
     */
    public function table(int $userId): string
    {
        helper('permissions');
        return buildPermissionsTable($userId);
    }

    /**
     * AJAX endpoint for toggling a permission for a user.
     */
    public function chgPerm(): ResponseInterface
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request.']);
        }

        // Require "users.manage-admins" or another admin permission
        if (! has_permission('users.manage-admins')) {
            return $this->response->setJSON(['success' => false, 'message' => lang('App.invalid_permission')]);
        }

        $userId   = (int) $this->request->getPost('user_id');
        $module   = trim((string) $this->request->getPost('module'));
        $operation= trim((string) $this->request->getPost('operation'));
        $active   = $this->request->getPost('active');

        $user = $this->userModel->find($userId);
        if (! $user instanceof User) {
            return $this->response->setJSON(['success' => false, 'message' => 'User not found.']);
        }

        $permKey = strtolower("$module.$operation");

        // validate permission exists in AuthGroups
        $configPerms = setting('AuthGroups.permissions');
        if (! array_key_exists($permKey, $configPerms)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid permission key.']);
        }

        if ($active === '1') {
            $user->addPermission($permKey);
            $ok = $user->can($permKey);
        } else {
            $user->removePermission($permKey);
            $ok = ! $user->can($permKey);
        }

        return $this->response->setJSON([
            'success' => $ok,
            'message' => $ok ? 'Permission updated' : 'Failed to update permission',
        ]);
    }
}
