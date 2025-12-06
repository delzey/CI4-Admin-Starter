<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Dashboard extends BaseController
{
    protected $app;

    public function __construct()
    {
        $this->app = service('applicationService');
    }

    /**
     * Main dashboard view.
     */
    public function index(): string
    {
        $user = $this->app->authUser();
        $title = 'Dashboard';

        return view('pages/dashboard', [
            'title' => $title,
            'authUser' => $user,
        ]);
    }

    /**
     * AJAX endpoint for dashboard widget data.
     */
    public function widgets(): ResponseInterface
    {
        $widgets = $this->app->getDashboardWidgets();

        $data = [];
        foreach ($widgets as $key => $widget) {
            $data[$key] = $widget->data();
        }

        return $this->response->setJSON($data['summary'] ?? []);
    }
}
