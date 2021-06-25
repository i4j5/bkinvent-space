<?php

namespace App\Actions\Yandex;
use App\Models\User;
use League\OAuth2\Client\Token\AccessToken;

class SetUserYandexTokenActions
{
    public function execute(User $user, AccessToken $accessToken)
    {
        $user->yandex_access_token = $accessToken->getToken();
        $user->yandex_refresh_token = $accessToken->getRefreshToken();
        $user->yandex_expires_in = $accessToken->getExpires();
        $user->save();
    }
}
