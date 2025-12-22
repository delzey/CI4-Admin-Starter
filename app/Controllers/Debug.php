<?php

namespace App\Controllers;

use App\Services\PermissionService;
use CodeIgniter\HTTP\ResponseInterface;

class Debug extends BaseController
{
    public function groupPerms(): ResponseInterface
    {
        $permService = new PermissionService();
        return $this->response->setJSON($permService->getAllGroupsWithPermissions());
    }
}
