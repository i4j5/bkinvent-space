<?php

namespace App\Actions\AmoCRM;
use App\Models\User;
use AmoCRM\OAuth2\Client\Provider\AmoCRM;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Grant\RefreshToken;
use App\Actions\AmoCRM\SetUserAmoCRMTokenActions;

class GetUserAmoCRMTokenActions
{
    private $provider;

    public function __construct()
    {
        $this->provider = new AmoCRM([
            'clientId' => env('AMO_CLIENT_ID'),
            'clientSecret' => env('AMO_CLIENT_SECRET'),
            'redirectUri' => env('AMO_REDIRECT_URI'),
            'baseDomain' => env('AMO_DOMAIN') . '.amocrm.ru'
        ]);
    }

    public function execute(User $user)
    {

        $token = false;
        
        $json_token = [
            'access_token' => $user->amocrm_access_token ? $user->amocrm_access_token : '',
            'refresh_token' => $user->amocrm_refresh_token? $user->amocrm_refresh_token : '',
            'expires' => $user->amocrm_expires_in ? $user->amocrm_expires_in : 0,
        ];

        if (
            $json_token['access_token'] && 
            $json_token['refresh_token'] && 
            $json_token['expires']
        ) {
            $accessToken = new AccessToken($json_token);

            /**
             * Проверяем активен ли токен и делаем запрос или обновляем токен
             */
            if ($accessToken->hasExpired()) {
                try {
                    $accessToken = $this->provider->getAccessToken(new RefreshToken(), [
                        'refresh_token' => $accessToken->getRefreshToken(),
                    ]);

                    (new SetUserAmoCRMTokenActions)->execute($user, $accessToken);

                } catch (Exception $e) {
                    ///=======
                }
            }

            $token = $accessToken->getToken();
        }

        return  $token;
    }
}
