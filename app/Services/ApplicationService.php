<?php

namespace App\Services;

use CodeIgniter\Shield\Authentication\Authenticators\Session as ShieldSession;

/**
 * ApplicationService
 * Central hub for runtime application state.
 *  - Cached menu tree
 *  - Settings
 *  - Current user info
 */
class ApplicationService
{
    protected SettingsService $settingsService;
    protected MenuService $menuService;
    protected ?array $cachedMenus = null;
    protected ?array $settings = null;
    protected ?array $user = null;

    public function __construct(?SettingsService $settingsService = null, ?MenuService $menuService = null)
    {
        $this->settingsService = $settingsService ?? service('settingsService');
        $this->menuService     = $menuService     ?? service('menuService');

        $this->boot();
    }

    protected function boot(): void
    {
        $this->loadSettings();
        $this->loadUser();
        $this->loadMenus();
    }

    protected function loadSettings(): void
    {
        $this->settings = [
            'app.theme'             => $this->settingsService->get('app.theme', 'light'),
            'app.sidebar_collapsed' => $this->settingsService->get('app.sidebar_collapsed', false),
        ];
    }

    protected function loadMenus(): void
    {
        $this->cachedMenus = cache('sidebar_tree');
        if (! $this->cachedMenus) {
            $this->cachedMenus = $this->menuService->getSidebarTree();
            cache()->save('sidebar_tree', $this->cachedMenus, 300);
        }
    }

    protected function loadUser(): void
    {
        try {
            // Get the current authenticator instance (defaults to 'session')
            $authenticator = auth('session')->getAuthenticator();

            // Use loggedIn() for status, not check()
            if ($authenticator->loggedIn()) {
                $user = $authenticator->getUser();

                $this->user = [
                    'id'       => $user->id,
                    'email'    => $user->email ?? '',
                    'username' => $user->username ?? '',
                    'roles'    => method_exists($user, 'getRoleNames')
                                    ? $user->getRoleNames()
                                    : [],
                ];
            } else {
                $this->user = null;
            }
        } catch (\Throwable $e) {
            log_message('error', 'ApplicationService->loadUser() failed: ' . $e->getMessage());
            $this->user = null;
        }
    }

    public function authUser(): ?\CodeIgniter\Shield\Entities\User
    {
        try {
            $auth = auth();
            if (! $auth->loggedIn()) {
                return null;
            }

            return $auth->user();
        } catch (\Throwable $e) {
            log_message('error', 'ApplicationService->authUser() failed: ' . $e->getMessage());
            return null;
        }
    }

    // -------------------- Accessors --------------------

    public function getMenus(): array
    {
        return $this->cachedMenus ?? [];
    }

    public function getSettings(): array
    {
        return $this->settings ?? [];
    }

    public function getUser(): ?array
    {
        return $this->user;
    }

    public function refreshMenus(): void
    {
        cache()->delete('sidebar_tree');
        $this->loadMenus();
    }

    public function refreshSettings(): void
    {
        cache()->delete('settings_app');
        $this->loadSettings();
    }

    // -------------------- Widgets --------------------

    protected array $widgetRegistry = [
        'summary' => [
            'class'       => \App\Widgets\SummaryWidget::class,
            'permission'  => 'dashboard.view',
        ],
    ];

    public function getDashboardWidgets(): array
    {
        $widgets = [];

        foreach ($this->widgetRegistry as $key => $info) {
            if (!class_exists($info['class'])) continue;

            $widget = new $info['class']();

            // Permission check
            $user = $this->authUser();
            if ($info['permission'] && $user && !$user->can($info['permission'])) {
                continue;
            }

            $widgets[$key] = $widget;
        }

        return $widgets;
    }
}
