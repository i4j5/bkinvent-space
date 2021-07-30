<?php

namespace App\Actions\Asana;
use App\Models\User;
use Asana\Client;
use Asana\Errors\NoAuthorizationError;

class GetUserAsanaClientActions
{

    public function execute(User $user)
    {

        $access_token = $user->asana_access_token;
        $refresh_token = $user->asana_refresh_token;


        $client;

        if ($access_token && $refresh_token) {
            $client = Client::oauth(array(
                'client_id'     => env('ASANA_CLIENT_ID'),
                'client_secret' => env('ASANA_CLIENT_SECRET'),
                'redirect_uri'  => env('ASANA_REDIRECT_URI'),
                'token' =>  $access_token,
                'refresh_token' => $refresh_token,
            ));

            try {
                $client->users->me();
            } catch (NoAuthorizationError $e) {
                try {
                    $client->dispatcher->refreshAccessToken();
                    
                    $user->asana_access_token = $client->dispatcher->accessToken;
                    $user->asana_refresh_token = $client->dispatcher->refreshToken;
                    $user->asana_expires_in = $client->dispatcher->expiresIn;
                    $user->save();

                } catch (\ErrorException $e) {
                    $client = Client::oauth(array(
                        'client_id'     => env('ASANA_CLIENT_ID'),
                        'client_secret' => env('ASANA_CLIENT_SECRET'),
                        'redirect_uri'  => env('ASANA_REDIRECT_URI'),
                    ));
                }
            }

        } else {
            $client = Client::oauth(array(
                'client_id'     => env('ASANA_CLIENT_ID'),
                'client_secret' => env('ASANA_CLIENT_SECRET'),
                'redirect_uri'  => env('ASANA_REDIRECT_URI'),
            ));
        }

        return  $client;
    }
}
