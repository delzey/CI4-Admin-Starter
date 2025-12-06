<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserDetailsModel;
use CodeIgniter\HTTP\ResponseInterface;

class ProfileController extends BaseController
{
    protected UserDetailsModel $detailsModel;

    public function __construct()
    {
        $this->detailsModel = new UserDetailsModel();
    }

    /**
     * Show profile page for the currently logged-in user.
     */
    public function index()
    {
        $user = auth()->user();
        if (! $user) {
            return redirect()->to(site_url('auth/login'));
        }

        $userId = (int) $user->id;

        // Pull existing details
        $details = $this->detailsModel->forUser($userId);

        // Pull defaults from Settings (user-defaults) if not set
        // Example: setting('UserDefaults.profile') returns an array
        $defaults = setting('UserDefaults.profile') ?? [];

        if (! $details) {
            // Merge defaults (if any) and ensure user_id is set
            $details = (object) array_merge($defaults, [
                'user_id'          => $userId,
                'firstname'        => $defaults['firstname'] ?? '',
                'middlename'       => $defaults['middlename'] ?? '',
                'lastname'         => $defaults['lastname'] ?? '',
                'phone'            => $defaults['phone'] ?? '',
                'address1'         => $defaults['address1'] ?? '',
                'address2'         => $defaults['address2'] ?? '',
                'city'             => $defaults['city'] ?? '',
                'state'            => $defaults['state'] ?? '',
                'zip'              => $defaults['zip'] ?? '',
                'profile_complete' => 0,
            ]);
        }

        // Compute profile_complete boolean (simple rule: firstname + lastname + phone)
        $profileComplete = ! empty($details->firstname) && ! empty($details->lastname);

        return view('profile/index', [
            'authUser'        => $user,
            'details'         => $details,
            'profileComplete' => $profileComplete,
        ]);
    }

    /**
     * AJAX endpoint: update profile details for current user.
     */
    public function updateProfile(): ResponseInterface
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'  => false,
                'messages' => ['general' => 'Invalid request.'],
            ]);
        }

        $user = auth()->user();
        if (! $user) {
            return $this->response->setStatusCode(401)->setJSON([
                'success'  => false,
                'messages' => ['general' => 'Not authenticated.'],
            ]);
        }

        $userId = (int) $user->id;

        $data = [
            'firstname'  => (string) $this->request->getPost('firstname'),
            'middlename' => (string) $this->request->getPost('middlename'),
            'lastname'   => (string) $this->request->getPost('lastname'),
            'phone'      => (string) $this->request->getPost('phone'),
            'address1'   => (string) $this->request->getPost('address1'),
            'address2'   => (string) $this->request->getPost('address2'),
            'city'       => (string) $this->request->getPost('city'),
            'state'      => (string) $this->request->getPost('state'),
            'zip'        => (string) $this->request->getPost('zip'),
        ];

        $rules = [
            'firstname'  => 'permit_empty|max_length[255]',
            'middlename' => 'permit_empty|max_length[50]',
            'lastname'   => 'permit_empty|max_length[255]',
            'phone'      => 'permit_empty|max_length[255]',
            'address1'   => 'permit_empty|max_length[255]',
            'address2'   => 'permit_empty|max_length[255]',
            'city'       => 'permit_empty|max_length[255]',
            'state'      => 'permit_empty|max_length[3]',
            'zip'        => 'permit_empty|max_length[10]',
        ];

        if (! $this->validateData($data, $rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success'  => false,
                'messages' => $this->validator->getErrors(),
            ]);
        }

        // Compute profile_complete boolean:
        $profileComplete = ! empty($data['firstname']) && ! empty($data['lastname']);

        $existing = $this->detailsModel->forUser($userId);

        if ($existing) {
            $saveData              = $data;
            $saveData['id']        = $existing->id;
            $saveData['user_id']   = $userId;
            $saveData['profile_complete'] = (int) $profileComplete;

            if (! $this->detailsModel->save($saveData)) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success'  => false,
                    'messages' => ['general' => 'Unable to update profile details.'],
                ]);
            }
        } else {
            $saveData                    = $data;
            $saveData['user_id']         = $userId;
            $saveData['profile_complete'] = (int) $profileComplete;

            if (! $this->detailsModel->insert($saveData)) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success'  => false,
                    'messages' => ['general' => 'Unable to create profile details.'],
                ]);
            }
        }

        return $this->response->setJSON([
            'success'  => true,
            'messages' => ['general' => 'Profile updated successfully.'],
        ]);
    }

    /**
     * Change password for current user.
     * Non-AJAX: after success, logs the user out.
     */
    public function changePassword()
    {
        $user = auth()->user();
        if (! $user) {
            return redirect()->to(site_url('auth/login'));
        }

        $rules = [
            'current_password' => 'required',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $currentPassword = (string) $this->request->getPost('current_password');
        $newPassword     = (string) $this->request->getPost('password');

        $db = db_connect();

        //----------------------------------------------------------------------
        // 1. Get the user's password identity
        //----------------------------------------------------------------------
        $row = $db->table('auth_identities')
            ->where('user_id', $user->id)
            ->where('type', 'password')
            ->get()
            ->getRow();

        if (! $row) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Password identity not found. Contact an administrator.');
        }

        //----------------------------------------------------------------------
        // 2. Verify current password against secret2
        //----------------------------------------------------------------------
        if (! password_verify($currentPassword, $row->secret2)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Current password is incorrect.');
        }

        //----------------------------------------------------------------------
        // 3. Hash & update new password (Shield API)
        //----------------------------------------------------------------------
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);

        $db->table('auth_identities')
            ->where('id', $row->id)
            ->update([
                'secret2' => $hash,
                'updated_at' => date('Y-m-d H:i:s'),
                'force_reset' => 0,
            ]);

        //----------------------------------------------------------------------
        // 4. Logout user after changing password
        //----------------------------------------------------------------------
        auth()->logout();

        return redirect()->to(site_url('auth/login'))
                        ->with('message', 'Password updated! Please sign in again.');
    }
}
