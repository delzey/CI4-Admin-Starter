<?php

namespace App\Services;

use App\Models\MenuModel;
use App\Models\MenuCategoryModel;
use CodeIgniter\Cache\CacheInterface;

/**
 * MenuService
 *
 * Central menu management:
 *  - Low-level CRUD wrappers
 *  - Tree building for sidebar + admin
 *  - Category CRUD + reordering
 */
class MenuService
{
    protected MenuModel $menuModel;
    protected MenuCategoryModel $categoryModel;
    protected CacheInterface $cache;
    protected string $cacheKey = 'sidebar_menu_tree';

    public function __construct(
        ?MenuModel $menuModel = null,
        ?MenuCategoryModel $categoryModel = null,
        ?CacheInterface $cache = null
    ) {
        $this->menuModel     = $menuModel     ?? new MenuModel();
        $this->categoryModel = $categoryModel ?? new MenuCategoryModel();
        $this->cache         = $cache         ?? cache();
    }

    /**
     * Flat list of all menus (admin).
     */
    public function getAllMenus(): array
    {
        return $this->menuModel
            ->orderBy('position', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * Single menu by ID.
     */
    public function getMenu(int $id): ?array
    {
        return $this->menuModel->find($id) ?: null;
    }

    /**
     * Create menu and return ID.
     */
    public function createMenu(array $data): int
    {
        $data = $this->normalizeMenuData($data);

        if (! isset($data['position']) || $data['position'] === null) {
            $data['position'] = $this->getNextPosition($data['parent_id'] ?? null);
        }

        if ($this->menuModel->insert($data, true) === false) {
            throw new \RuntimeException('Failed to create menu: ' . json_encode($this->menuModel->errors()));
        }

        $this->clearSidebarCache();

        return (int) $this->menuModel->getInsertID();
    }

    /**
     * Update menu.
     */
    public function updateMenu(int $id, array $data): bool
    {
        $data = $this->normalizeMenuData($data);

        if (! $this->menuModel->update($id, $data)) {
            throw new \RuntimeException('Failed to update menu: ' . json_encode($this->menuModel->errors()));
        }

        $this->clearSidebarCache();

        return true;
    }

    /**
     * Delete menu.
     * (FK can cascade children if configured.)
     */
    public function deleteMenu(int $id): bool
    {
        $this->clearSidebarCache();

        return (bool) $this->menuModel->delete($id);
    }

    /**
     * Generic menu tree (no categories).
     * Includes active + inactive.
     *
     * Structure:
     * [
     *   [
     *     'id'       => 1,
     *     'title'    => 'Dashboard',
     *     'children' => [...],
     *   ],
     *   ...
     * ]
     */
    public function getMenuTree(?callable $filter = null): array
    {
        $rows = $this->menuModel
            ->orderBy('position', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        if ($filter !== null) {
            $rows = array_values(array_filter($rows, $filter));
        }

        // Index by ID and prepare children
        $items = [];
        foreach ($rows as $row) {
            $row['children'] = [];
            $items[$row['id']] = $row;
        }

        $tree = [];

        foreach ($items as $id => &$item) {
            $parentId = $item['parent_id'] ?? null;

            if (! empty($parentId) && isset($items[$parentId])) {
                $items[$parentId]['children'][] = &$item;
            } else {
                $tree[] = &$item;
            }
        }
        unset($item);

        return $tree;
    }

    /**
     * Sidebar-ready menu tree.
     *  - Only active menus
     *  - Grouped by category
     *  - Drops empty categories
     */
    public function getSidebarTree(bool $useCache = true): array
    {
        if ($useCache && ($cached = $this->cache->get($this->cacheKey))) {
            return $cached;
        }

        $categories = $this->categoryModel
            ->orderBy('menu_category', 'ASC')
            ->findAll();

        $menus = $this->menuModel
            ->where('is_active', 1)
            ->orderBy('position', 'ASC')
            ->orderBy('title', 'ASC')
            ->findAll();

        // Always include Uncategorized bucket
        $tree = [
            0 => [
                'category' => ['id' => 0, 'menu_category' => 'Uncategorized'],
                'menus'    => [],
            ],
        ];

        foreach ($categories as $category) {
            $tree[$category['id']] = [
                'category' => $category,
                'menus'    => [],
            ];
        }

        // Build parent/child
        $menuMap = [];
        foreach ($menus as $menu) {
            $menu['children'] = [];
            $menuMap[$menu['id']] = $menu;
        }

        foreach ($menuMap as $id => &$menu) {
            if (!empty($menu['parent_id']) && isset($menuMap[$menu['parent_id']])) {
                $menuMap[$menu['parent_id']]['children'][] = &$menu;
            }
        }
        unset($menu);

        // Assign top-level menus to categories
        foreach ($menuMap as $menu) {
            if (!empty($menu['parent_id'])) {
                continue;
            }
            $catId = $menu['category_id'] ?? 0;
            if (! isset($tree[$catId])) {
                $tree[$catId] = [
                    'category' => ['id' => $catId, 'menu_category' => 'Unknown'],
                    'menus'    => [],
                ];
            }
            $tree[$catId]['menus'][] = $menu;
        }

        // Drop empty categories
        foreach ($tree as $id => $block) {
            if (empty($block['menus'])) {
                unset($tree[$id]);
            }
        }

        $result = array_values($tree);

        // Cache for 10 min
        $this->cache->save($this->cacheKey, $result, 600);

        return $result;
    }

    /**
     * Admin tree (Mode A):
     *  - Shows ALL menus (active + inactive)
     *  - Includes empty categories
     *  - Includes "Uncategorized"
     */
    public function getAdminTree(): array
    {
        // Categories ordered by position then name
        $categories = $this->categoryModel
            ->orderBy('position', 'ASC')
            ->orderBy('menu_category', 'ASC')
            ->findAll();

        // Base blocks with categories
        $blocks = [];

        // Synthetic "Uncategorized"
        $blocks[0] = [
            'category' => [
                'id'            => 0,
                'menu_category' => 'Uncategorized',
                'position'      => -999,
            ],
            'menus' => [],
        ];

        foreach ($categories as $cat) {
            $blocks[$cat['id']] = [
                'category' => $cat,
                'menus'    => [],
            ];
        }

        // Full hierarchical tree (all menus)
        $tree = $this->getMenuTree();

        // Assign root menus to categories
        foreach ($tree as $root) {
            $catId = $root['category_id'] ?? 0;

            if (! isset($blocks[$catId])) {
                $blocks[$catId] = [
                    'category' => [
                        'id'            => $catId,
                        'menu_category' => 'Unknown',
                        'position'      => 9999,
                    ],
                    'menus' => [],
                ];
            }

            $blocks[$catId]['menus'][] = $root;
        }

        // Preserve ordering by category position
        usort($blocks, static function ($a, $b) {
            return ($a['category']['position'] ?? 0) <=> ($b['category']['position'] ?? 0);
        });

        return $blocks;
    }

    /**
     * Clear sidebar cache.
     */
    public function clearSidebarCache(): void
    {
        $this->cache->delete($this->cacheKey);
    }

    /**
     * Categories (for Admin UI)
     */

    public function listCategories(): array
    {
        return $this->categoryModel
            ->orderBy('position', 'ASC')
            ->orderBy('menu_category', 'ASC')
            ->findAll();
    }

    public function getCategory(int $id): ?array
    {
        return $this->categoryModel->find($id) ?: null;
    }

    public function createCategory(string $name, ?string $permissionName = null): bool
    {
        $name = trim($name);
        if ($name === '') {
            return false;
        }

        $this->clearSidebarCache();

        return (bool) $this->categoryModel->insert([
            'menu_category'   => $name,
            'permission_name' => $permissionName ?: null,
        ]);
    }

    public function updateCategory(int $id, array $data): bool
    {
        $data['menu_category'] = trim($data['menu_category'] ?? '');
        if ($data['menu_category'] === '') {
            return false;
        }

        $this->clearSidebarCache();

        return (bool) $this->categoryModel->update($id, $data);
    }

    /**
     * Delete category only if it has no menus.
     */
    public function deleteCategory(int $id): bool
    {
        // Check for menus using this category
        $count = $this->menuModel
            ->where('category_id', $id)
            ->countAllResults();

        if ($count > 0) {
            return false;
        }

        $this->clearSidebarCache();

        return (bool) $this->categoryModel->delete($id);
    }

    /**
     * Move category up/down by swapping position.
     */
    public function moveCategory(int $id, string $direction = 'up'): bool
    {
        $current = $this->categoryModel->find($id);
        if (! $current) {
            return false;
        }

        $pos     = (int) ($current['position'] ?? 0);
        $offset  = $direction === 'up' ? -1 : 1;
        $targetPos = $pos + $offset;

        $swap = $this->categoryModel
            ->where('position', $targetPos)
            ->first();

        if (! $swap) {
            return false;
        }

        $db = $this->categoryModel->db;
        $db->transStart();

        $this->categoryModel->update($current['id'], ['position' => $swap['position']]);
        $this->categoryModel->update($swap['id'], ['position' => $current['position']]);

        $db->transComplete();

        return $db->transStatus();
    }

    /**
     * Next position within same parent.
     */
    protected function getNextPosition(?int $parentId): int
    {
        $builder = $this->menuModel->builder();
        $builder->select('MAX(position) AS max_pos');

        if ($parentId !== null) {
            $builder->where('parent_id', $parentId);
        } else {
            $builder->where('parent_id IS NULL', null, false);
        }

        $row = $builder->get()->getRowArray();

        $max = isset($row['max_pos']) ? (int) $row['max_pos'] : 0;

        return $max + 1;
    }

    /**
     * Normalize CRUD data for menus.
     *
     * - Normalizes category_id, parent_id, is_active, position
     * - Inherits category_id from parent if parent is set
     * - Trims strings
     * - Normalizes permission to Shield format (scope.action)
     */
    protected function normalizeMenuData(array $data): array
    {
        // Sensible defaults
        $data = array_merge([
            'category_id' => null,
            'parent_id'   => null,
            'title'       => '',
            'icon'        => '',
            'route'       => '',
            'permission'  => null,
            'position'    => 0,
            'is_active'   => 1,
        ], $data);

        // --- category_id ---
        $cat = $data['category_id'];
        if (
            $cat === '' || $cat === '0' || $cat === 0 ||
            $cat === 'null' || $cat === 'undefined' || $cat === null
        ) {
            $cat = null;
        } else {
            $cat = (int) $cat;
        }

        // --- parent_id ---
        $parent = $data['parent_id'];
        if (
            $parent === '' || $parent === '0' || $parent === 0 ||
            $parent === 'null' || $parent === 'undefined' || $parent === null
        ) {
            $parent = null;
        } else {
            $parent = (int) $parent;
        }

        // Inherit category from parent if parent is set
        if ($parent !== null) {
            $parentRow = $this->menuModel->find($parent);
            if ($parentRow && isset($parentRow['category_id'])) {
                $cat = $parentRow['category_id'];
            }
        }

        // --- is_active (checkbox / tinyint) ---
        $data['is_active'] = ! empty($data['is_active']) ? 1 : 0;

        // --- position (int, default 0) ---
        $data['position'] = is_numeric($data['position']) ? (int) $data['position'] : 0;

        // --- basic string trims ---
        $data['title'] = trim((string) $data['title']);
        $data['icon']  = trim((string) $data['icon']);
        $data['route'] = trim((string) $data['route']);

        // --- permission (Shield: scope.action) ---
        $perm = trim((string) ($data['permission'] ?? ''));

        if ($perm === '') {
            // No permission => public menu item
            $data['permission'] = null;
        } elseif (! preg_match('~^[a-z0-9_-]+\.[a-z0-9_.-]+$~i', $perm)) {
            // Invalid like "dashboard" (no dot) -> auto-upgrade to "dashboard.view"
            $base = strtolower(str_replace(' ', '_', $perm));
            $fixed = $base . '.view';

            log_message(
                'debug',
                'MenuService::normalizeMenuData auto-upgraded permission "' . $perm . '" to "' . $fixed . '"'
            );

            $data['permission'] = $fixed;

        } else {
            // Already a valid Shield permission
            $data['permission'] = $perm;
        }

        $data['category_id'] = $cat;
        $data['parent_id']   = $parent;

        return $data;
    }

    /**
     * Swap menu positions.
     */
    public function swapPosition(int $idA, int $idB): bool
    {
        $menuA = $this->menuModel->find($idA);
        $menuB = $this->menuModel->find($idB);

        if (! $menuA || ! $menuB) {
            return false;
        }

        $posA = (int) ($menuA['position'] ?? 0);
        $posB = (int) ($menuB['position'] ?? 0);

        $db = $this->menuModel->db;
        $db->transStart();

        $this->menuModel->update($idA, ['position' => $posB]);
        $this->menuModel->update($idB, ['position' => $posA]);

        $db->transComplete();

        return $db->transStatus();
    }

    /**
     * Move menu up or down within same parent.
     */
    public function moveMenu(int $id, string $direction = 'up'): bool
    {
        $item = $this->menuModel->find($id);
        if (! $item) {
            return false;
        }

        $parentId   = $item['parent_id'] ?? null;
        $currentPos = (int) ($item['position'] ?? 0);

        $operator = $direction === 'up' ? '<' : '>';
        $order    = $direction === 'up' ? 'DESC' : 'ASC';

        $neighbor = $this->menuModel
            ->where('parent_id', $parentId)
            ->where("position {$operator}", $currentPos)
            ->orderBy('position', $order)
            ->first();

        if (! $neighbor) {
            return false;
        }

        $ok = $this->swapPosition($id, $neighbor['id']);
        if ($ok) {
            $this->clearSidebarCache();
        }

        return $ok;
    }
}