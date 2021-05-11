<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Servises\AmoCRMClient;
use App\Models\AmocrmToken as Token;
use App\Actions\AmoCRM\AddTokenActions;

class ConnectionAmoCRMController extends Controller
{
   
    public function __invoke(Request $request, AddTokenActions $addToken) {

        $provider = AmoCRMClient::getProvider();

        $code = $request->query('code', false);

        if ($code) {
            try {
                $accessToken = $provider->getAccessToken('authorization_code', [
                    'code' => $code
                ]);

                Token::truncate();

                $addToken->execute($accessToken);

                return true;
            } catch (\Exception $e) { 
                return $e->getMessage();
            }
        } else {
            $authorizationUrl = $provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $provider->getState();
            // Session::set('variableName', $value);
            return redirect()->away($authorizationUrl);
        }
    }
}