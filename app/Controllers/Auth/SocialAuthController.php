<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\Shield\Models\UserIdentityModel;
use CodeIgniter\Shield\Entities\User as ShieldUser;
use League\OAuth2\Client\Provider\Google;
use CodeIgniter\HTTP\RedirectResponse;

class SocialAuthController extends BaseController
{
    protected function getGoogleClient(): Google
    {
        $config  = config('OAuth')->providers['google'];

        return new Google([
            'clientId'     => $config['clientId'],
            'clientSecret' => $config['clientSecret'],
            'redirectUri'  => $config['redirectUri'],
            'hostedDomain' => null,
        ]);
    }

    // ---------------------------------------------------------------------

    public function redirect(string $provider): RedirectResponse
    {
        if ($provider !== 'google') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $google = $this->getGoogleClient();
        $authUrl = $google->getAuthorizationUrl();

        session()->set('oauth2state', $google->getState());

        return redirect()->to($authUrl);
    }

    // ---------------------------------------------------------------------

    public function callback(string $provider)
    {
        if ($provider !== 'google') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $google = $this->getGoogleClient();

        // Validate state
        if (
            ! $this->request->getGet('state')
            || $this->request->getGet('state') !== session()->get('oauth2state')
        ) {
            session()->remove('oauth2state');
            return redirect()->to('/login')->with('error', 'Invalid OAuth state – please try again.');
        }

        try {
            $token = $google->getAccessToken('authorization_code', [
                'code' => $this->request->getGet('code'),
            ]);

            $googleUser = $google->getResourceOwner($token);

            $googleId = $googleUser->getId();
            $email    = $googleUser->getEmail();
            $name     = $googleUser->getName();

        } catch (\Exception $e) {
            return redirect()->to('/login')->with('error', 'OAuth error: ' . $e->getMessage());
        }

        // ---------------------------------------------------------------------
        // FIND OR CREATE USER
        // ---------------------------------------------------------------------

        $identities = new UserIdentityModel();

        // 1. Check if there is already a google identity
        $identity = $identities
            ->where('type', 'google')
            ->where('secret', $googleId)
            ->first();

        if ($identity) {
            $user = auth()->getProvider()->findById($identity->user_id);

            if (! $user->active) {
                return redirect()->to('/login')->with('error', 'Your account is pending approval.');
            }

            auth()->login($user);
            return redirect()->to('/');
        }

        // 2. Auto-link: if email exists → link Google identity
        $user = auth()->getProvider()->findByCredentials(['email' => $email]);

        if ($user) {
            // Create identity
            $identities->insert([
                'user_id' => $user->id,
                'type'    => 'google',
                'secret'  => $googleId,
                'data'    => json_encode([
                    'email' => $email,
                    'name'  => $name,
                ]),
            ]);

            if (! $user->active) {
                return redirect()->to('/login')->with('error', 'Your account is pending approval.');
            }

            auth()->login($user);
            return redirect()->to('/');
        }

        // 3. No user → Auto-create (inactive)
        $user = new ShieldUser([
            'email'    => $email,
            'username' => $email,
            'active'   => false,   // <<< pending approval
        ]);

        $userId = auth()->getProvider()->insert($user);

        // Add google identity
        $identities->insert([
            'user_id' => $userId,
            'type'    => 'google',
            'secret'  => $googleId,
            'data'    => json_encode([
                'email' => $email,
                'name'  => $name,
            ]),
        ]);

        return redirect()->to('/login')->with(
            'error',
            'Your account has been created and is pending approval by an administrator.'
        );
    }
}
