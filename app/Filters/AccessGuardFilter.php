<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AccessGuardFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $requiredPermission = $arguments[0] ?? null;

        if (! $requiredPermission) {
            return;
        }

        if ($user->can($requiredPermission)) {
            return;
        }

        // Build a human-readable context (route alias if available, else URI)
        $router = service('router');
        $route  = $router->getMatchedRoute();
        $alias  = $route[1]['as'] ?? null;

        $contextSlug = $alias
            ? "route:{$alias}"
            : 'uri:' . $request->getPath();

        service('accessGuard')->onDenied($user, $contextSlug);

        // AJAX vs normal response
        if ($request->isAJAX()) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON([
                    'success' => false,
                    'message' => 'You do not have permission to perform this action.',
                ]);
        }

        // Important part: set flash + redirect to dashboard
        session()->setFlashdata('accessDenied', 'You do not have permission to view that page.');

        return redirect()->route('dashboard.index'); // or your preferred dashboard route alias
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
