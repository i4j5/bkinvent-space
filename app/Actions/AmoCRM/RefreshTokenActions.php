<?php

namespace App\Actions\AmoCRM;

use App\Servises\AmoCRMClient;
use App\Models\AmocrmToken as Token;
use App\Actions\AmoCRM\AddTokenActions;

class RefreshTokenActions
{
    public function execute()
    {
        $token = Token::where([
            ['expires', '>=',  time() + 3600000 ], 
            ['type', '=', 'refresh'],
            ['active', '=', 1]
        ])->orderByDesc('expires')->first(); //? Desc
        
        if ($token) {
            try {

                $newAccessToken = AmoCRMClient::getProvider()->getAccessToken('refresh_token', [
                    'refresh_token' => $token->value
                ]);

                $oldAccessTokens = Token::where([
                    ['type', '=', 'access'],
                    ['active', '=', 1]
                ])->get();

                foreach ($oldAccessTokens as $oldAccessToken) {
                    $oldAccessToken->active = 0;
                    $oldAccessToken->save();
                }

                (new AddTokenActions)->execute($newAccessToken);

                return $newAccessToken;

            } catch (\Exception $e) {  
                //$this->error('ERROR: amoCRM refreshToken');  
                return false;
            }

        } else {
            return false;
        }
    }
}
