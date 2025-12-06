<?php
// app/Controllers/Users.php
namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use App\Services\UserService;

class Users extends BaseController
{
    protected UserService $userService;

    public function __construct()
    {
        // Use service() to allow CI to inject dependencies if configured
        $this->userService = service(UserService::class) ?? new UserService();
    }

    public function index()
    {
        return view('pages/users', [
            'title' => 'User Management',
        ]);
    }

    // --- AJAX: Users ---------------------------------------------------------

    public function listUsers(): ResponseInterface
    {
        return $this->response->setJSON([
            'data' => $this->userService->getUsers(),
        ]);
    }

    public function createUser(): ResponseInterface
    {
        $authUser = auth()->user();

        if (! $authUser || ! $authUser->can('users.create')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You do not have permission to create users.',
            ]);
        }

        try {
            $res = $this->userService->createUser($this->request->getPost());
            return $this->response->setJSON([
                'success' => true,
                'id'      => $res['id'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function updateUser(): ResponseInterface
    {
        $authUser = auth()->user();

        if (! $authUser || ! $authUser->can('users.edit')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You do not have permission to edit users.',
            ]);
        }

        try {
            $ok = $this->userService->updateUser($this->request->getPost());
            return $this->response->setJSON(['success' => $ok]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function deleteUser(): ResponseInterface
    {
        $authUser = auth()->user();

        if (! $authUser || ! $authUser->can('users.delete')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You do not have permission to delete users.',
            ]);
        }

        $id = (int) $this->request->getPost('id');

        try {
            $ok = $this->userService->deleteUser($id);

            return $this->response->setJSON([
                'success' => $ok,
                'message' => $ok ? 'User deleted' : 'Failed to delete user',
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    // --- AJAX: Groups (from config) -----------------------------------------

    public function listConfigGroups(): ResponseInterface
    {
        return $this->response->setJSON($this->userService->getConfigGroups());
    }

    // --- AJAX: User ↔ Groups -------------------------------------------------

    public function getUserGroups(): ResponseInterface
    {
        $id = (int) $this->request->getGet('id');

        return $this->response->setJSON([
            'groups' => $this->userService->getUserGroupAliases($id),
        ]);
    }

    public function setUserGroups(): ResponseInterface
    {
        $id     = $this->request->getPost('id');
        $groups = $this->request->getPost('groups') ?? [];

        $authUser = auth()->user();

        if (! $authUser || ! $authUser->can('users.manage-admins')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You do not have permission to assign groups.',
            ]);
        }

        // Prevent editing superadmin unless you're superadmin
        $provider = auth()->getProvider();
        $target   = $provider->findById($id);

        if ($target && $target->inGroup('superadmin') && ! $authUser->inGroup('superadmin')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You cannot modify a superadmin.',
            ]);
        }

        try {
            $this->userService->setGroups((int) $id, $groups);

            return $this->response->setJSON([
                'success' => true,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
