<?php

namespace App\Actions\Yandex;
use App\Models\User;
use Aego\OAuth2\Client\Provider\Yandex;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Grant\RefreshToken;
use App\Actions\Yandex\SetUserYandexTokenActions;

class GetUserYandexTokenActions
{
    private $provider;

    public function __construct()
    {
        $this->provider = new Yandex([
            'clientId' => env('YANDEX_CLIENT_ID'),
            'clientSecret' => env('YANDEX_CLIENT_SECRET'),
            'redirectUri' => env('YANDEX_REDIRECT_URI'),
        ]);
    }

    public function execute(User $user)
    {

        $token = false;
        
        $json_token = [
            'access_token' => $user->yandex_access_token ? $user->yandex_access_token : '',
            'refresh_token' => $user->yandex_refresh_token? $user->yandex_refresh_token : '',
            'expires' => $user->yandex_expires_in ? $user->yandex_expires_in : 0,
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

                    (new SetUserYandexTokenActions)->execute($user, $accessToken);

                } catch (Exception $e) {
                    ///=======
                }
            }

            $token = $accessToken->getToken();
        }

        return  $token;
    }
}
