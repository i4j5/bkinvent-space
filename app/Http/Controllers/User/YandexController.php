<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Actions\Yandex\SetUserYandexTokenActions;
use Aego\OAuth2\Client\Provider\Yandex;
use League\OAuth2\Client\Grant\AuthorizationCode;

class YandexController extends Controller
{

    private $provider;

    public function __construct()
    {
        $this->provider = new Yandex([
            'clientId' => env('YANDEX_CLIENT_ID'),
            'clientSecret' => env('YANDEX_CLIENT_SECRET'),
            // 'redirectUri' => env('YANDEX_REDIRECT_URI'),
            'redirectUri' => 'https://bkinvent.space/login/yandex/callback',
        ]);
    }

    public function callback(Request $request) {

        $user = $request->user();

        $code = $request->get('code');

        if ($code) {
           
            try {
               
                $accessToken = $this->provider->getAccessToken(new AuthorizationCode(), [
                    'code' => $code,
                ]);

                if (!$accessToken->hasExpired()) {
                    (new SetUserYandexTokenActions)->execute($user, $accessToken);
                }

            } catch (\ErrorException $e) {
                dd('Error: ' . $e->getMessage());
            }

            return redirect('/user/profile');

        } else {
            $authorizationUrl = $this->provider->getAuthorizationUrl();
            return redirect($authorizationUrl);
        }

    }

}
