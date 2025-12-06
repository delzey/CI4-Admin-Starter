<?php $user = auth()->user(); ?>

<nav class="navbar navbar-top navbar-expand navbar-dark bg-primary border-bottom shadow-sm">
    <div class="container-fluid px-3 px-md-4">

        <!-- Sidebar toggle (mobile) -->
        <button class="navbar-toggler d-md-none me-2" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Brand -->
        <a class="navbar-brand fw-semibold d-flex align-items-center" href="<?= site_url('/') ?>">
            <i class="fa fa-bolt me-2"></i>
            <?= esc(setting('Site.siteName') ?? 'CI4 Starter') ?>
        </a>

        <!-- Right side actions -->
        <div class="d-flex align-items-center ms-auto">

            <!-- Page-level actions -->
            <div class="d-none d-md-flex align-items-center me-3">
                <?= $this->renderSection('navbarActions') ?>
            </div>

            <!-- Messages Dropdown -->
            <div class="dropdown me-3">
                <button class="btn btn-outline-light text-secondary btn-sm position-relative"
                        id="navbarMessagesDropdown"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">

                    <i class="fas fa-inbox"></i>

                    <!-- Unread badge -->
                    <span id="navbarMsgBadge"
                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                        style="display:none; font-size: 0.65rem;">
                    </span>
                </button>

                <div class="dropdown-menu dropdown-menu-end dropdown-menu-messages shadow-sm"
                    style="min-width: 320px; max-height: 420px; overflow-y: auto;">

                    <div id="navbarMsgContainer" class="list-group list-group-flush small">
                        <div class="text-center text-muted py-3">Loading…</div>
                    </div>

                    <div class="dropdown-divider"></div>

                    <a href="<?= site_url('messages') ?>"
                    class="dropdown-item text-center small text-primary fw-semibold">
                        View All Messages
                    </a>
                </div>
            </div>

            <!-- User Dropdown -->
            <div class="dropdown">
                <button class="btn btn-outline-light text-secondary btn-sm d-flex align-items-center" id="navbarUserDropdown" data-bs-toggle="dropdown" aria-expanded="false">

                    <!-- User Icon -->
                    <span class="avatar-sm rounded-circle bg-white text-primary d-flex align-items-center justify-content-center me-2">
                        <i class="fas fa-user"></i>
                    </span>

                    <span class="d-none text-secondary d-sm-inline"><?= esc($user?->username ?? 'Guest') ?></span>
                    <i class="fas fa-chevron-down ms-1 small d-none d-sm-inline"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end mt-2 shadow-sm" aria-labelledby="navbarUserDropdown">
                    <li><a class="dropdown-item" href="<?= site_url('profile') ?>"><i class="fas fa-user me-2"></i> Profile</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-danger" href="<?= site_url('logout') ?>"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </div>

        </div>

    </div>
</nav>