<?php

namespace App\Actions\AmoCRM;

use App\Models\AmocrmToken as Token;
use App\Actions\AmoCRM\RefreshTokenActions;

class GetTokenActions
{
    public function execute()
    {
        $token = Token::where([
            ['expires', '>=',  time() + 3600000],
            ['type', '=', 'access'],
            ['active', '=', 1]
        ])->first();

        if (!$token) {
            $newToken = (new RefreshTokenActions)->execute();
            if ($newToken) return $newToken->getToken();
        }

        return $token->value;
    }
}
