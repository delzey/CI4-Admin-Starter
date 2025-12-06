<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    /** @var CLIRequest|IncomingRequest */
    protected $request;

    protected $helpers = ['url', 'form'];

    protected $session;

    protected array $data = [];

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        $this->session = service('session');

        $auth = service('auth');
        $userEntity = null;

        try {
            $userEntity = $auth->user();
        } catch (\Throwable $e) {
            $userEntity = null;
        }

        $user = null;

        if ($userEntity) {
            $user = [
                'id'       => $userEntity->id,
                'email'    => $userEntity->email,
                'username' => $userEntity->username,
            ];
        }

        $this->data = [
            'authUser' => $user,
        ];
    }
}
