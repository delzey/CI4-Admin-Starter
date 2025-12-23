<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;

class Settings extends BaseController
{
    public function index()
    {
        $tzList = \DateTimeZone::listIdentifiers();
        $currentTZ  = (string) (setting('App.appTimezone') ?? 'America/Chicago');

        $dateFormat = (string) (setting('App.dateFormat') ?? 'M j, Y');
        $timeFormat = (string) (setting('App.timeFormat') ?? 'g:i A');

        $groups = config('AuthGroups')->groups ?? [];
        $defaultGroup = (string) (setting('AuthGroups.defaultGroup') ?? 'user');

        $rememberOptions = [
            '1 hour'   => 3600,
            '1 day'    => 86400,
            '1 week'   => 604800,
            '2 weeks'  => 1209600,
            '30 days'  => 2592000,
        ];

        // --- Server Info ----------------------------------------------------
        $ciVersion = defined('\CodeIgniter\CodeIgniter::CI_VERSION')
            ? \CodeIgniter\CodeIgniter::CI_VERSION
            : (defined('CI_VERSION') ? CI_VERSION : 'Unknown');

        $db = db_connect();
        $dbDriver  = $db->DBDriver ?? 'Unknown';
        $dbVersion = method_exists($db, 'getVersion') ? ($db->getVersion() ?: 'Unknown') : 'Unknown';

        $serverLoad = null;
        if (function_exists('sys_getloadavg')) {
            $loads = sys_getloadavg();
            $serverLoad = is_array($loads) && isset($loads[0]) ? $loads[0] : null;
        }

        $envFile = null;
        if (is_file(ROOTPATH . '.env')) {
            $path = ROOTPATH . '.env';
            $envFile = [
                'size' => filesize($path) ?: 0,
                'permission' => substr(sprintf('%o', fileperms($path)), -4),
            ];
        }

        return view('pages/settings', [
            'title'           => 'Application Settings',
            'tzList'          => $tzList,
            'currentTZ'       => $currentTZ,
            'dateFormat'      => $dateFormat,
            'timeFormat'      => $timeFormat,
            'groups'          => $groups,
            'defaultGroup'    => $defaultGroup,
            'rememberOptions' => $rememberOptions,

            // server info
            'ciVersion'  => $ciVersion,
            'dbDriver'   => $dbDriver,
            'dbVersion'  => $dbVersion,
            'serverLoad' => $serverLoad,
            'envFile'    => $envFile,
        ]);
    }

    public function saveGeneral(): ResponseInterface
    {
        $settings = service('settings');

        try {
            $siteName        = trim((string) $this->request->getPost('siteName'));
            $siteDescription = trim((string) $this->request->getPost('siteDescription'));
            $siteKeyWords    = trim((string) $this->request->getPost('siteKeyWords'));
            $siteOnline      = $this->request->getPost('siteOnline') === '1';

            $appTimezone     = trim((string) $this->request->getPost('appTimezone'));
            $dateFormat      = trim((string) $this->request->getPost('dateFormat'));
            $timeFormat      = trim((string) $this->request->getPost('timeFormat'));

            $idleTimeout     = (int) $this->request->getPost('idleTimeoutMinutes');

            if ($siteName === '') {
                return $this->response->setJSON(['success' => false, 'message' => 'Site name is required.'])
                    ->setStatusCode(400);
            }
            if ($idleTimeout < 1 || $idleTimeout > 240) {
                return $this->response->setJSON(['success' => false, 'message' => 'Idle timeout must be between 1 and 240 minutes.'])
                    ->setStatusCode(400);
            }

            // Save settings
            $settings->set('Site.siteName', $siteName);
            $settings->set('Site.siteDescription', $siteDescription);
            $settings->set('Site.siteKeyWords', $siteKeyWords);
            $settings->set('Site.siteOnline', $siteOnline);
            $settings->set('App.appTimezone', $appTimezone);
            $settings->set('App.dateFormat', $dateFormat);
            $settings->set('App.timeFormat', $timeFormat);

            $settings->set('Site.idleTimeoutMinutes', $idleTimeout);

            return $this->response->setJSON(['success' => true, 'message' => 'General settings updated.']);

        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()])
                ->setStatusCode(500);
        }
    }

    public function saveCompany(): ResponseInterface
    {
        $settings = service('settings');

        try {
            $allowRegistration = $this->request->getPost('allowRegistration') === '1';
            $minLen            = (int) $this->request->getPost('minimumPasswordLength');
            $defaultGroup      = (string) $this->request->getPost('defaultGroup');
            $rememberLength    = (int) $this->request->getPost('rememberLength');

            $allowGoogleLogins = $this->request->getPost('allowGoogleLogins') === '1';

            // Actions (register/login)
            $emailActivation = $this->request->getPost('emailActivation') === '1';
            $email2FA        = $this->request->getPost('email2FA') === '1';

            // Validators[]
            $validators = $this->request->getPost('validators');
            if (!is_array($validators)) {
                $validators = [];
            }

            if ($minLen < 6 || $minLen > 128) {
                return $this->response->setJSON(['success' => false, 'message' => 'Minimum password length must be between 6 and 128.'])
                    ->setStatusCode(400);
            }

            // Save simple settings
            $settings->set('Auth.allowRegistration', $allowRegistration);
            $settings->set('Auth.minimumPasswordLength', $minLen);
            $settings->set('AuthGroups.defaultGroup', $defaultGroup);
            $settings->set('Auth.allowGoogleLogins', $allowGoogleLogins);

            // Save sessionConfig rememberLength (keep other keys intact!)
            $sessionConfig = (array) (setting('Auth.sessionConfig') ?? []);
            $sessionConfig['rememberLength'] = (string) $rememberLength;
            $settings->set('Auth.sessionConfig', $sessionConfig);

            // Save actions[] (keep the structure)
            $actions = (array) (setting('Auth.actions') ?? []);
            $actions['register'] = $emailActivation ? 'CodeIgniter\\Shield\\Authentication\\Actions\\EmailActivator' : null;
            $actions['login']    = $email2FA ? 'CodeIgniter\\Shield\\Authentication\\Actions\\Email2FA' : null;
            $settings->set('Auth.actions', $actions);

            // Save password validators array
            $settings->set('Auth.passwordValidators', array_values($validators));

            return $this->response->setJSON(['success' => true, 'message' => 'Company/Auth settings updated.']);

        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()])
                ->setStatusCode(500);
        }
    }

    // Optional single-key reset endpoint
    public function forget(): ResponseInterface
    {
        $settings = service('settings');
        $key = (string) $this->request->getPost('key');

        if ($key === '') {
            return $this->response->setJSON(['success' => false, 'message' => 'Missing key.'])->setStatusCode(400);
        }

        try {
            $settings->forget($key);
            return $this->response->setJSON(['success' => true, 'message' => 'Setting reset to default.']);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()])->setStatusCode(500);
        }
    }

    /**
     * Secure PHP Info endpoint for modal iframe.
     */
    public function phpInfo(): ResponseInterface
    {
        // Guard: only superadmin (or add a permission check if you prefer)
        $user = auth()->user();
        if (!$user || !$user->inGroup('superadmin')) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }

        // Capture phpinfo output
        ob_start();
        phpinfo();
        $html = ob_get_clean();

        // phpinfo() returns a full HTML document; that's perfect for an iframe.
        return $this->response
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setBody($html);
    }
}
