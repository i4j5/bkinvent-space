<?php

namespace App\Actions;
use App\Models\User;

class GetUserGoogleClientActions
{
    private $client;

    public function __construct()
    {
        $this->client = new \Google_Client();
        $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $this->client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $this->client->setAccessType('offline');   
        $this->client->setApprovalPrompt('force');
    }

    public function execute(User $user)
    {

        $json_token = [
            'access_token' => $user->google_access_token ? $user->google_access_token : '',
            'refresh_token' => $user->google_refresh_token? $user->google_refresh_token : '',
            'expires_in' => $user->google_expires_in ? $user->google_expires_in : 0,
        ];

        if (
            $json_token['access_token'] && 
            $json_token['refresh_token'] && 
            $json_token['expires_in']
        ){
            $this->client->setAccessToken($json_token);

            if ($this->client->isAccessTokenExpired()) {
                $json_token = $this->client->refreshToken($this->client->getRefreshToken());
    
                if (isset($res['error'])) {
                    return false;
                }
    
                $user->google_access_token = $json_token['access_token'];
                $user->google_refresh_token = $json_token['refresh_token'];
                $user->google_expires_in = $json_token['expires_in'];
                $user->save();
    
            }
        }

        return  $this->client;
    }
}
