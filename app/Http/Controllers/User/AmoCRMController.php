<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Actions\AmoCRM\SetUserAmoCRMTokenActions;
use AmoCRM\OAuth2\Client\Provider\AmoCRM;
use League\OAuth2\Client\Grant\AuthorizationCode;

class AmoCRMController extends Controller
{

    private $provider;

    public function __construct()
    {
        $this->provider = new AmoCRM([
            'clientId' => env('AMO_CLIENT_ID'),
            'clientSecret' => env('AMO_CLIENT_SECRET'),
            'redirectUri' => env('AMO_REDIRECT_URI'),
            // 'baseDomain' => env('AMO_DOMAIN') . '.amocrm.ru'
        ]);
    }

    public function callback(Request $request) {

        $user = $request->user();

        $code = $request->get('code');

        if ($code) {

            $this->provider->setBaseDomain( env('AMO_DOMAIN') . '.amocrm.ru');
           
            try {
               
                $accessToken = $this->provider->getAccessToken(new AuthorizationCode(), [
                    'code' => $code,
                ]);

                if (!$accessToken->hasExpired()) {
                    (new SetUserAmoCRMTokenActions)->execute($user, $accessToken);
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
