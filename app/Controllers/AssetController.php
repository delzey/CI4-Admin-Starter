<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\AssetService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Serve private assets from ROOT/assets/* via /assets/<bucket>/...
 *
 * Route example:
 *   $routes->get('assets/(:any)', 'AssetController::serve/$1');
 *
 * URL example:
 *   /assets/admin/volt/css/volt.1766418318.css  -> ROOT/assets/Admin/volt/css/volt.css
 *   /assets/app/vendor/fontawesome/css/all.min.1766418318.css -> ROOT/assets/App/vendor/fontawesome/css/all.min.css
 */

class AssetController extends BaseController
{
    public function serve(...$segments): ResponseInterface
    {
        $service = service('assetService');

        $asset = $service->resolve($segments);

        if ($asset === null) {
            return $this->response->setStatusCode(404);
        }

        // --- Conditional caching (ETag / 304) -------------------------
        $etag = $asset['etag'];

        if ($this->request->getHeaderLine('If-None-Match') === $etag) {
            return $this->response->setStatusCode(304)->setHeader('ETag', $etag);
        }

        // --- Build response ------------------------------------------
        return $this->response
            ->setHeader('Content-Type', $asset['mime'])
            ->setHeader('Last-Modified', $asset['lastModified'])
            ->setHeader('ETag', $etag)
            ->setHeader('Cache-Control', $asset['cacheControl'])
            ->setBody($asset['body']);
    }
}
