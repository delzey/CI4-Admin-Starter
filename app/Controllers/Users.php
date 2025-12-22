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
            log_message(
                'error',
                'Users::createUser failed: {message} in {file}:{line}',
                [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]
            );
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
            log_message(
                'error',
                'Users::updateUser failed: {message} in {file}:{line}',
                [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]
            );
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
            log_message(
                'error',
                'Users::deleteUser failed: {message} in {file}:{line}',
                [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]
            );
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
            log_message(
                'error',
                'Users::setUserGroups failed: {message} in {file}:{line}',
                [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]
            );
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}



We are starting a new project conversation.

This project is a full rewrite of my existing “BSS-Aviation Framework” using my
CodeIgniter 4 Starter (CI4 Starter #2) as the foundation.

You must treat the following as the permanent baseline for ALL future answers
unless I explicitly say otherwise:

PROJECT BASELINE
----------------
• Backend: CodeIgniter 4.6.3
• PHP: 8.3
• Database: MySQL
• Auth: CodeIgniter Shield 1.2.0
• Settings: CodeIgniter Settings 2.2.0
• App style: Modular ERP (Quotes, Invoices, Purchase Orders, Repair Orders, etc.)

FRONTEND / UI
-------------
• UI theme: Volt (Bootstrap-based, not AdminLTE)
• Bootstrap: 4.x (compiled assets, not CDN)
• Icons: Font Awesome 5.15.3
• JS stack: jQuery + DataTables + SweetAlert/Toast
• Layout: Top Navbar + Sidebar
• Light theme only

TEMPLATING RULES (VERY IMPORTANT)
---------------------------------
• Volt layout and partials are the authoritative structure
• Views extend a master Volt layout (no inline JS in views)
• Page-specific JavaScript lives in per-page JS files
• Shared JS utilities live in common assets
• All modals, confirmations, and toasts follow the existing Volt patterns
• No AdminLTE markup, helpers, or assumptions are allowed

ARCHITECTURE RULES
------------------
• Controllers are thin; logic lives in Services or Models
• Use BaseModel extensions consistently
• Use Query Builder (no raw SQL unless justified)
• All routes are named and permission-aware
• Shield permissions control menu visibility and access
• Sidebar menus are dynamic, role-based, and cacheable

SCaffold / DEV TOOLS
--------------------
• This starter includes a custom scaffold engine
• Generated code must match this project’s standards
• Generated controllers, models, views, and JS must follow Volt + CI4 conventions

EXPECTATIONS
------------
• All code must be production-grade and drop-in ready
• Prefer refactors over rewrites when possible
• Be explicit about assumptions
• If something conflicts with this baseline, ask before proceeding

If I suggest code that violates any of the above standards,
consider that a bug and correct it immediately.

Acknowledge this baseline, then we will begin migrating modules
one-by-one from the legacy BSS-Aviation Framework into this starter.
