<?php

namespace App\Actions\AmoCRM;

use App\Models\AmocrmToken as Token;

class AddTokenActions
{
    public function execute($token)
    {
        Token::create([
            'type' => 'access',
            'value' => $token->getToken(),
            'expires' => $token->getExpires() + 82800000,
            'active' => 1
        ]);

        Token::create([
            'type' => 'refresh',
            'value' => $token->getRefreshToken(),
            'expires' => $token->getExpires() + 5616000000,
            'active' => 1
        ]);
    }
}
