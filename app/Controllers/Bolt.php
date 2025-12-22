<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\BoltEncryptor;
use CodeIgniter\HTTP\ResponseInterface;

class Bolt extends BaseController
{
    protected BoltEncryptor $bolt;

    public function __construct()
    {
        $this->bolt = new BoltEncryptor();
    }

    public function index()
    {
        // TODO: add Shield/permission check here if desired
        return view('pages/bolt', [
            'title' => 'Secure Files & Directories',
        ]);
    }

    public function encrypt(string $target = ''): ResponseInterface
    {
        // Optional: log what CI *thinks* the method is, for curiosity
        log_message('debug', 'Bolt::encrypt called. Method: {method}', [
            'method' => $this->request->getMethod(),
        ]);

        // Remove the manual method guard. Routing already restricts to POST.
        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setStatusCode(405)
                ->setJSON(['status' => 'error', 'message' => 'Method not allowed.']);
        }

        $map = [
            'controllers' => APPPATH . 'Controllers',
            'services'    => APPPATH . 'Services',
            'helpers'     => APPPATH . 'Helpers',
            'libraries'   => APPPATH . 'Libraries',
            'models'      => APPPATH . 'Models',
            'commands'    => APPPATH . 'Commands',
        ];

        if (! array_key_exists($target, $map)) {
            return $this->response->setStatusCode(404)
                ->setJSON(['status' => 'error', 'message' => 'Invalid target.']);
        }

        try {
            $msg = $this->bolt->encryptDirectory($map[$target], $target);

            return $this->response->setJSON([
                'status'  => 'ok',
                'message' => $msg,
            ]);
        } catch (\Throwable $e) {
            log_message(
                'error',
                'Bolt encryption failed for target {target}: {message} in {file}:{line}',
                [
                    'target'  => $target,
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]
            );

            return $this->response->setStatusCode(500)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Encryption failed: ' . $e->getMessage(),
                ]);
        }
    }
}
