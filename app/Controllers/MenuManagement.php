<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use InvalidArgumentException;
use App\Services\MenuService;

class MenuManagement extends BaseController
{
    protected MenuService $menuService;
    protected $validation;

    public function __construct()
    {
        $this->menuService = service('menuService');
        $this->validation  = \Config\Services::validation();
    }

    /**
     * Main UI
     */
    public function index(): string
    {
        return view('pages/menu_management', [
            'title'       => 'Menu Management',
            'categories'  => $this->menuService->listCategories(),
            'menus'       => $this->menuService->getAllMenus(),
            // For initial server-rendered tree if you still use it:
            'sidebarTree' => $this->menuService->getAdminTree(),
            'validation'  => $this->validation,
        ]);
    }

    /**
     * Flat menus list (legacy AJAX).
     */
    public function view(): ResponseInterface
    {
        return $this->response->setJSON([
            'data' => $this->menuService->getAllMenus(),
        ]);
    }

    /**
     * Create menu.
     */
    public function create(): ResponseInterface
    {
        $data = $this->request->getPost([
            'category_id',
            'parent_id',
            'title',
            'icon',
            'route',
            'permission',
            'position',
            'is_active',
        ]);

        $rules = [
            'title'       => 'required|min_length[2]',
            'icon'        => 'permit_empty|string',
            'route'       => 'permit_empty|string',
            'permission'  => 'permit_empty|string',
            'position'    => 'permit_empty|integer',
            'parent_id'   => 'permit_empty|integer',
            'category_id' => 'permit_empty|integer',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'success'  => false,
                'messages' => $this->validator->getErrors(),
            ]);
        }

        try {
            $id = $this->menuService->createMenu($data);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Menu created successfully.',
                'id'      => $id,
            ]);
        } catch (InvalidArgumentException $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid data: ' . $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Menu creation failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create menu item.',
            ]);
        }
    }

    /**
     * Update menu.
     */
    public function update(): ResponseInterface
    {
        $id = (int) $this->request->getPost('id');

        $data = $this->request->getPost([
            'category_id',
            'parent_id',
            'title',
            'icon',
            'route',
            'permission',
            'position',
            'is_active',
        ]);

        $rules = [
            'title'       => 'required|min_length[2]',
            'icon'        => 'permit_empty|string',
            'route'       => 'permit_empty|string',
            'permission'  => 'permit_empty|string',
            'position'    => 'permit_empty|integer',
            'parent_id'   => 'permit_empty|integer',
            'category_id' => 'permit_empty|integer',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'success'  => false,
                'messages' => $this->validator->getErrors(),
            ]);
        }

        try {
            $this->menuService->updateMenu($id, $data);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Menu updated successfully.',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Menu update failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update menu item.',
            ]);
        }
    }

    /**
     * Delete menu.
     */
    public function delete(): ResponseInterface
    {
        $id = (int) $this->request->getPost('id');

        try {
            $ok = $this->menuService->deleteMenu($id);
            return $this->response->setJSON([
                'success' => $ok,
                'message' => $ok ? 'Menu deleted successfully.' : 'Failed to delete menu.',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Menu deletion failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unexpected error deleting menu.',
            ]);
        }
    }

    /**
     * Reorder menu (up/down).
     */
    public function reorder(): ResponseInterface
    {
        $id        = (int) $this->request->getPost('id');
        $direction = $this->request->getPost('direction') ?? 'up';

        try {
            $ok = $this->menuService->moveMenu($id, $direction);
            return $this->response->setJSON([
                'success' => $ok,
                'message' => $ok ? 'Menu reordered.' : 'Unable to reorder menu.',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Menu reorder failed: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to reorder menu.',
            ]);
        }
    }

    /**
     * Admin tree endpoint used by JS:
     * GET /menu-management/tree
     *
     * Uses MenuService::getAdminTree()
     * (Mode A: ALL menus, active + inactive.)
     */
    public function tree(): ResponseInterface
    {
        $blocks = $this->menuService->getAdminTree();

        return $this->response->setJSON([
            'success' => true,
            'data'    => $blocks,
        ]);
    }

    /**
     * Get a single menu for edit.
     * GET /menu-management/get/{id}
     */
    public function get($id): ResponseInterface
    {
        $item = $this->menuService->getMenu((int) $id);

        if (! $item) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Menu not found.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $item,
        ]);
    }

    // --------------------------------------------------------------------
    // CATEGORIES via MenuService
    // --------------------------------------------------------------------

    public function createCategory(): ResponseInterface
    {
        $name = (string) $this->request->getPost('menu_category');
        $perm = (string) $this->request->getPost('permission_name') ?: null;

        $ok = $this->menuService->createCategory($name, $perm);

        return $this->response->setJSON([
            'success' => $ok,
            'message' => $ok ? 'Category created successfully' : 'Unable to create category',
        ]);
    }

    public function listCategories(): ResponseInterface
    {
        return $this->response->setJSON([
            'success' => true,
            'data'    => $this->menuService->listCategories(),
        ]);
    }

    public function getCategory($id): ResponseInterface
    {
        $cat = $this->menuService->getCategory((int) $id);

        if (! $cat) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Category not found',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data'    => $cat,
        ]);
    }

    public function updateCategory(): ResponseInterface
    {
        $id   = (int) $this->request->getPost('id');
        $data = $this->request->getPost();

        $ok = $this->menuService->updateCategory($id, $data);

        return $this->response->setJSON([
            'success' => $ok,
            'message' => $ok ? 'Category updated successfully' : 'Unable to update category',
        ]);
    }

    public function deleteCategory(): ResponseInterface
    {
        $id = (int) $this->request->getPost('id');

        $ok = $this->menuService->deleteCategory($id);

        return $this->response->setJSON([
            'success' => $ok,
            'message' => $ok
                ? 'Category removed'
                : 'Category cannot be removed (menus still assigned)',
        ]);
    }

    public function reorderCategory(): ResponseInterface
    {
        $id        = (int) $this->request->getPost('id');
        $direction = $this->request->getPost('direction') ?? 'up';

        $ok = $this->menuService->moveCategory($id, $direction);

        return $this->response->setJSON([
            'success' => $ok,
            'message' => $ok ? 'Category reordered' : 'Unable to reorder category',
        ]);
    }

    /**
     * Dev debug
     */
    public function debug(): ResponseInterface
    {
        return $this->response->setJSON([
            'flat'    => $this->menuService->getAllMenus(),
            'tree'    => $this->menuService->getMenuTree(),
            'sidebar' => $this->menuService->getSidebarTree(false),
            'admin'   => $this->menuService->getAdminTree(),
        ]);
    }
}