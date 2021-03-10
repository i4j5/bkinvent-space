<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Actions\GetUserAsanaClientActions;
use Illuminate\Support\Facades\Auth;
use Asana\Client;

class AsanaController extends Controller
{

    private $asanaClient;

    public function __construct(GetUserAsanaClientActions $asanaClient)
    {
        $this->asanaClient = $asanaClient;
    }

    public function callback(Request $request) {

        $user = $request->user();
        
        $client = $this->asanaClient->execute($user);

        if ($request->get('code')) {
           
            try {
                $client->dispatcher->fetchToken($request->get('code'));
                $user->asana_access_token = $client->dispatcher->accessToken;
                $user->asana_refresh_token = $client->dispatcher->refreshToken;
                $user->asana_expires_in = $client->dispatcher->expiresIn;
                $user->save();
            } catch (\ErrorException $e) {
                dd('Error: ' . $e->getMessage());
            }

            return redirect('/user/profile');

        } else {
            return redirect($client->dispatcher->authorizationUrl());
        }

    }

}
