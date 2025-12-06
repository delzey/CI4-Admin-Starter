<?php
$menuService = service('menuService');
$sidebarTree = $menuService->getSidebarTree();
$currentUri  = trim(service('uri')->getPath(), '/');

if (!function_exists('can')) {
    function can(string $permission): bool
    {
        return function_exists('auth') && auth()->user()?->can($permission);
    }
}
?>

<!-- Volt Lite Sidebar -->
<nav id="sidebarMenu"
     class="sidebar bg-primary text-white collapse d-md-block"
     data-simplebar>

    <div class="sidebar-inner px-3 pt-3 pb-4">

        <!-- Header -->
        <div class="pb-3 mb-3 border-bottom border-primary">
            <h5 class="mb-0 fw-semibold"><?= esc(setting('Site.siteName') ?? 'BSS Software') ?></h5>
            <div class="text-white-50 small"><?= ucfirst(ENVIRONMENT) ?></div>
        </div>

        <ul class="nav flex-column">

            <?php if (! empty($sidebarTree)): ?>
                <?php foreach ($sidebarTree as $block): ?>
                    <?php
                        $categoryName = $block['category']['menu_category'] ?? 'Uncategorized';
                        $menus        = $block['menus'] ?? [];
                    ?>

                    <!-- Category label -->
                    <li class="nav-item mt-3 mb-1">
                        <span class="sidebar-heading text-uppercase text-white-50 small fw-semibold">
                            <?= esc($categoryName) ?>
                        </span>
                    </li>

                    <?php foreach ($menus as $menu): ?>
                        <?php
                            $hasChildren = ! empty($menu['children']);
                            $route       = trim($menu['route'] ?? '', '/');
                            $isActive    = ! empty($route) && str_starts_with($currentUri, $route);
                            $canView     = empty($menu['permission']) || can($menu['permission']);
                            if (! $canView) continue;

                            // Active child?
                            $childActive = false;
                            foreach ($menu['children'] as $child) {
                                if (! empty($child['route']) &&
                                    str_starts_with($currentUri, trim($child['route'], '/'))) {
                                    $childActive = true;
                                    break;
                                }
                            }

                            $isExpanded = $isActive || $childActive;
                            $menuId     = 'submenu-' . ($menu['id'] ?? uniqid());
                        ?>

                        <li class="nav-item">

                            <!-- Main item -->
                            <button
                                class="nav-link btn btn-link w-100 text-start d-flex align-items-center py-2 px-2 rounded
                                       <?= ($isActive || $childActive) ? 'active text-white fw-semibold' : 'text-white-75' ?>"
                                <?php if ($hasChildren): ?>
                                    data-bs-toggle="collapse"
                                    data-bs-target="#<?= $menuId ?>"
                                    aria-expanded="<?= $isExpanded ? 'true' : 'false' ?>"
                                <?php else: ?>
                                    onclick="window.location='<?= site_url($menu['route']) ?>'"
                                <?php endif; ?>
                            >
                                <span class="sidebar-icon me-2">
                                    <i class="<?= esc($menu['icon'] ?? 'fas fa-circle') ?>"></i>
                                </span>

                                <span class="sidebar-text flex-grow-1"><?= esc($menu['title']) ?></span>

                                <?php if ($hasChildren): ?>
                                    <span class="sidebar-chevron small ms-auto">
                                        <i class="fas fa-chevron-right"></i>
                                    </span>
                                <?php endif; ?>
                            </button>

                            <!-- Children -->
                            <?php if ($hasChildren): ?>
                                <div class="multi-level collapse <?= $isExpanded ? 'show' : '' ?>"
                                     id="<?= $menuId ?>">
                                    <ul class="nav flex-column ms-3 border-start border-primary border-opacity-25 ps-2 mt-1">
                                        <?php foreach ($menu['children'] as $child): ?>
                                            <?php
                                                $childRoute  = trim($child['route'] ?? '', '/');
                                                $childActive = ! empty($childRoute)
                                                    && str_starts_with($currentUri, $childRoute);

                                                $canChild = empty($child['permission']) || can($child['permission']);
                                                if (! $canChild) continue;
                                            ?>

                                            <li class="nav-item">
                                                <a href="<?= $child['route'] ? site_url($child['route']) : '#' ?>"
                                                   class="nav-link d-flex align-items-center py-1 px-2 rounded small
                                                          <?= $childActive ? 'active text-white fw-semibold' : 'text-white-50' ?>">
                                                    <span class="sidebar-icon me-2">
                                                        <?php if (! empty($child['icon'])): ?>
                                                            <i class="<?= esc($child['icon']) ?>"></i>
                                                        <?php else: ?>
                                                            <i class="fas fa-circle small"></i>
                                                        <?php endif; ?>
                                                    </span>
                                                    <span class="sidebar-text"><?= esc($child['title']) ?></span>
                                                </a>
                                            </li>

                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                        </li>

                    <?php endforeach; ?>
                <?php endforeach; ?>

            <?php else: ?>
                <li class="nav-item ps-2 text-white-50 small fst-italic">No menu items</li>
            <?php endif; ?>

        </ul>
    </div>
</nav>

<style>
/* Volt compatibility tweaks */
.sidebar .nav-link.active {
    background: rgba(255, 255, 255, 0.15);
    border-radius: .35rem;
}
.sidebar .nav-link:hover {
    background: rgba(255, 255, 255, 0.10);
}
.sidebar .multi-level .nav-link.active {
    font-weight: 600;
}
</style>
