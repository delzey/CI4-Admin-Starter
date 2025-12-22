<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class IdleController extends BaseController
{
    /**
     * Simple "ping" to check if the session is still valid.
     * Returns 200 if logged in, 401 if not.
     */
    public function ping(): ResponseInterface
    {
        if (! auth()->loggedIn()) {
            return $this->response->setStatusCode(401)
                ->setJSON(['ok' => false, 'reason' => 'not_logged_in']);
        }

        return $this->response->setJSON(['ok' => true]);
    }

    /**
     * Force logout due to idle timeout.
     * Destroys Shield session and returns JSON with redirect URL.
     */
    public function forceLogout(): ResponseInterface
    {
        if (auth()->loggedIn()) {
            auth()->logout(); // Shield logout + session cleanup
        }

        // You can change this route if your login URI is different
        $loginUrl = site_url('login'); 

        return $this->response->setJSON([
            'success'  => true,
            'redirect' => $loginUrl,
            'reason'   => 'idle_timeout',
        ]);
    }
}
