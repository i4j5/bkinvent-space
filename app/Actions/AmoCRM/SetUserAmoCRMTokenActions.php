<?php

namespace App\Actions\AmoCRM;
use App\Models\User;
use League\OAuth2\Client\Token\AccessToken;

class SetUserAmoCRMTokenActions
{
    public function execute(User $user, AccessToken $accessToken)
    {
        $user->amocrm_access_token = $accessToken->getToken();
        $user->amocrm_refresh_token = $accessToken->getRefreshToken();
        $user->amocrm_expires_in = $accessToken->getExpires();
        $user->save();
    }
}
