<?php

namespace Stilmark\Base;

use League\OAuth2\Client\Provider\Google;

use Stilmark\Base\Env;
use Stilmark\Base\Request;

final class Auth
{
    private Google $provider;
    private $authSessionName;

    public function __construct()
    {
        $this->authSessionName = Env::get('AUTH_SESSION_NAME', 'auth');
        $this->provider = new Google([
            'clientId' => Env::get('GOOGLE_CLIENT_ID'),
            'clientSecret' => Env::get('GOOGLE_CLIENT_SECRET'),
            'redirectUri' => 'https://'.Env::get('SERVER_NAME', 'localhost').Env::get('GOOGLE_REDIRECT_URI'),
        ]);
    }

    public function callout()
    {
        $authUrl = $this->provider->getAuthorizationUrl();
        $_SESSION['oauth2state'] = $this->provider->getState();
        header('Location: ' . $authUrl);
        exit;
    }

    public function callback(Request $request)
    {
        $state = $request->get('state');
        $code = $request->get('code');
        
        if (!$state || $state !== ($_SESSION['oauth2state'] ?? null)) {
            unset($_SESSION['oauth2state']);
            throw new \Exception('Invalid state');
        }

        $token = $this->provider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);

        try {
            $user = $this->provider->getResourceOwner($token);
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }

        // Store comprehensive session data in auth array
        $_SESSION[$this->authSessionName] = [
            'access_token' => $token->getToken(),
            'token_expires' => $token->getExpires(),
            'refresh_token' => $token->getRefreshToken(),
            'user' => $user->toArray(),
            'auth_time' => time()
        ];
        
        return [
            'status' => 'success',
            'access_token' => $token->getToken(),
            'expires' => $token->getExpires(),
            'refresh_token' => $token->getRefreshToken(),
            'user' => $user->toArray(),
        ];
    }
}
