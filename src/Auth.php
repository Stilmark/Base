<?php

namespace Stilmark\Base;

use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\AbstractProvider;

use Stilmark\Base\Env;
use Stilmark\Base\Request;

final class Auth
{
    private AbstractProvider $provider;
    private string $providerType;
    private string $authSessionName;

    public function __construct(string $providerType = 'google')
    {
        $this->authSessionName = Env::get('AUTH_SESSION_NAME', 'auth');
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
        $_SESSION['oauth2state'] = $this->provider->getState();
        $_SESSION['oauth2provider'] = $this->providerType;
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
            'provider' => $this->providerType,
            'auth_time' => time()
        ];
        
        // Clean up temporary session data
        unset($_SESSION['oauth2state'], $_SESSION['oauth2provider']);
        
        return [
            'status' => 'success',
            'provider' => $this->providerType,
            'user' => $user->toArray(),
        ];
    }

    public function logout()
    {
        unset($_SESSION[$this->authSessionName]);
    }
}
