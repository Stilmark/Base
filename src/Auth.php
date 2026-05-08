<?php

namespace Stilmark\Base;

use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\AbstractProvider;
use Stilmark\Base\Request;

final class Auth
{
    private AbstractProvider $provider;
    private string $providerType;
    private string $authSessionName;

    public function __construct(string $providerType = 'google')
    {
        $this->authSessionName = Env::get('SESSION_AUTH_NAME', 'auth');
        $this->providerType = strtolower($providerType);
        $this->provider = $this->createProvider($this->providerType);
    }

    private function createProvider(string $providerType): AbstractProvider
    {
        switch ($providerType) {
            case 'google':
                return new Google([
                    'clientId' => Env::get('GOOGLE_CLIENT_ID'),
                    'clientSecret' => Env::get('GOOGLE_CLIENT_SECRET'),
                    'redirectUri' => 'https://'.Env::get('SERVER_NAME', 'localhost').Env::get('GOOGLE_REDIRECT_URI'),
                ]);
            
            case 'microsoft':
                // Microsoft provider would be instantiated here
                // return new Microsoft([
                //     'clientId' => Env::get('MICROSOFT_CLIENT_ID'),
                //     'clientSecret' => Env::get('MICROSOFT_CLIENT_SECRET'),
                //     'redirectUri' => 'https://'.Env::get('SERVER_NAME', 'localhost').Env::get('MICROSOFT_REDIRECT_URI'),
                // ]);
                throw new \InvalidArgumentException('Microsoft provider not yet implemented');
            
            default:
                throw new \InvalidArgumentException('Unsupported provider: ' . $providerType);
        }
    }

    public function callout()
    {
        $authUrl = $this->provider->getAuthorizationUrl();
        Session::set('oauth2state', $this->provider->getState());
        Session::set('oauth2provider', $this->providerType);
        header('Location: ' . $authUrl);
        exit;
    }

    public function callback(Request $request)
    {
        $state = $request->get('state');
        $code = $request->get('code');
        
        if (!$state || $state !== Session::get('oauth2state')) {
            Session::remove('oauth2state');
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

        // Regenerate session ID to prevent fixation
        Session::regenerate(true);
        
        // Store comprehensive session data in auth array
        Session::set($this->authSessionName, [
            'access_token' => $token->getToken(),
            'token_expires' => $token->getExpires(),
            'refresh_token' => $token->getRefreshToken(),
            'user' => $user->toArray(),
            'provider' => $this->providerType,
            'auth_time' => time()
        ]);
        
        // Set session timestamps for timeout tracking
        Session::setLoginTime('login_time');
        Session::updateActivity('last_activity');
        
        // Clean up temporary session data
        Session::remove('oauth2state');
        Session::remove('oauth2provider');
        
        return [
            'status' => 'success',
            'provider' => $this->providerType,
            'user' => $user->toArray(),
        ];
    }

    public function logout()
    {
        Session::remove($this->authSessionName);
        Session::destroy();
    }
}
