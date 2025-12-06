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

<aside class="sidebar bg-white border-end h-100">
    <div class="sidebar-header px-3 py-3 border-bottom">
        <strong><?= esc(setting('Site.siteName') ?? 'BSS Software') ?></strong>
        <div class="text-muted small"><?= ucfirst(ENVIRONMENT) ?></div>
    </div>

    <nav class="sidebar-nav px-2 py-3">
        <ul class="nav flex-column gap-1">

            <?php if (! empty($sidebarTree)): ?>
                <?php foreach ($sidebarTree as $block): ?>
                    <?php
                        $categoryName = $block['category']['menu_category'] ?? 'Uncategorized';
                        $menus = $block['menus'] ?? [];
                    ?>
                    <!-- Category header -->
                    <li class="nav-item mt-3">
                        <span class="text-uppercase small text-muted ps-2 fw-semibold"><?= esc($categoryName) ?></span>
                    </li>

                    <?php foreach ($menus as $menu): ?>
                        <?php
                            $hasChildren = ! empty($menu['children']);
                            $isActive = ! empty($menu['route']) && str_starts_with($currentUri, trim($menu['route'], '/'));
                            $canView = empty($menu['permission']) || can($menu['permission']);
                            if (! $canView) continue;

                            // Check if any child is active
                            $childActive = false;
                            foreach ($menu['children'] as $child) {
                                if (! empty($child['route']) && str_starts_with($currentUri, trim($child['route'], '/'))) {
                                    $childActive = true;
                                    break;
                                }
                            }
                            $isExpanded = $isActive || $childActive;
                        ?>
                        <li class="nav-item">
                            <a href="<?= $menu['route'] ? site_url($menu['route']) : '#' ?>"
                               class="nav-link d-flex justify-content-between align-items-center <?= ($isActive || $childActive) ? 'active text-primary fw-semibold' : 'text-dark' ?>"
                               data-bs-toggle="<?= $hasChildren ? 'collapse' : '' ?>"
                               data-target="#submenu-<?= $menu['id'] ?>"
                               role="button"
                               aria-expanded="<?= $isExpanded ? 'true' : 'false' ?>"
                               aria-controls="submenu-<?= $menu['id'] ?>">
                                <span class="d-flex align-items-center">
                                    <?php if (! empty($menu['icon'])): ?>
                                        <i class="<?= esc($menu['icon']) ?> me-2"></i>
                                    <?php endif; ?>
                                    <?= esc($menu['title']) ?>
                                </span>
                                <?php if ($hasChildren): ?>
                                    <i class="bi bi-caret-down-fill small opacity-50 ms-1"></i>
                                <?php endif; ?>
                            </a>

                            <?php if ($hasChildren): ?>
                                <ul id="submenu-<?= $menu['id'] ?>"
                                    class="nav flex-column collapse <?= $isExpanded ? 'show' : '' ?> ms-4 mt-1 border-start ps-2">
                                    <?php foreach ($menu['children'] as $child): ?>
                                        <?php
                                            $childActive = ! empty($child['route']) && str_starts_with($currentUri, trim($child['route'], '/'));
                                            $canViewChild = empty($child['permission']) || can($child['permission']);
                                            if (! $canViewChild) continue;
                                        ?>
                                        <li class="nav-item">
                                            <a href="<?= $child['route'] ? site_url($child['route']) : '#' ?>"
                                               class="nav-link d-flex align-items-center <?= $childActive ? 'active text-primary fw-semibold' : 'text-muted' ?>">
                                                <?php if (! empty($child['icon'])): ?>
                                                    <i class="<?= esc($child['icon']) ?> me-2"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-dot me-2 small opacity-50"></i>
                                                <?php endif; ?>
                                                <?= esc($child['title']) ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="nav-item ps-2"><span class="text-muted small fst-italic">No menu items</span></li>
            <?php endif; ?>
        </ul>
    </nav>
</aside>

<!-- Optional JS (auto-collapse + active highlight) -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const active = document.querySelector('.nav-link.active');
    if (active) {
        active.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Collapse/expand behavior for child menus
    document.querySelectorAll('.nav-link[data-target]').forEach(link => {
        link.addEventListener('click', e => {
            const target = document.querySelector(link.dataset.target);
            if (target) {
                target.classList.toggle('show');
                e.preventDefault();
            }
        });
    });
});
</script>

<style>
.nav-link.active {
    background-color: rgba(13, 110, 253, 0.1);
    border-radius: .25rem;
}
.nav-link:hover {
    background-color: rgba(0,0,0,0.03);
}
.nav .collapse.show > .nav-item > .nav-link.active {
    font-weight: 600;
}
</style>
