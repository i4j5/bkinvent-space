<?php

namespace App\Servises;

use App\Models\AmocrmToken as Token;

class AmoCRMClient
{

    protected static $_provider;

    public function __construct()
    {
        // $this->_provider = new \League\OAuth2\Client\Provider\GenericProvider([
        //     'clientId'                => env('AMO_CLIENT_ID'),
        //     'clientSecret'            => env('AMO_CLIENT_SECRET'),
        //     'redirectUri'             => env('AMO_REDIRECT_URI'),
        //     'urlAuthorize'            => 'https://www.amocrm.ru/oauth',
        //     'urlAccessToken'          => 'https://' . env('AMO_DOMAIN') . '.amocrm.ru/oauth2/access_token',
        //     'urlResourceOwnerDetails' => 'https://' . env('AMO_DOMAIN') . '.amocrm.ru/v3/user'
        // ]);
    }


    public static function getProvider() 
    {
        $provider = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => env('AMO_CLIENT_ID'),
            'clientSecret'            => env('AMO_CLIENT_SECRET'),
            'redirectUri'             => env('AMO_REDIRECT_URI'),
            'urlAuthorize'            => 'https://www.amocrm.ru/oauth',
            'urlAccessToken'          => 'https://' . env('AMO_DOMAIN') . '.amocrm.ru/oauth2/access_token',
            'urlResourceOwnerDetails' => 'https://' . env('AMO_DOMAIN') . '.amocrm.ru/v3/user'
        ]);

        return $provider;
    }

}