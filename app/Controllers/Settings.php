<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Settings extends BaseController
{
    protected $settingsService;

    public function __construct()
    {
        $this->settingsService = service('settingsService');
    }

    public function index()
    {
        return view('pages/settings', [
            'title' => 'Application Settings',
        ]);
    }

    public function get(): ResponseInterface
    {
        $data = [
            'app.theme'             => $this->settingsService->get('app.theme', 'light'),
            'app.sidebar_collapsed' => $this->settingsService->get('app.sidebar_collapsed', false),
        ];
        return $this->response->setJSON(['data' => $data]);
    }

    public function save(): ResponseInterface
    {
        $theme    = $this->request->getPost('app_theme') ?? 'light';
        $collapsed = $this->request->getPost('app_sidebar_collapsed') === '1';

        $this->settingsService->set('app.theme', $theme);
        $this->settingsService->set('app.sidebar_collapsed', $collapsed);

        // refresh cache for ApplicationService
        service('applicationService')->refreshSettings();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Settings saved successfully',
        ]);
    }
}
