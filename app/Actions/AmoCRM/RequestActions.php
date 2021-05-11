<?php

namespace App\Actions\AmoCRM;

use App\Servises\AmoCRMClient;
use App\Actions\AmoCRM\GetTokenActions;
use App\Actions\AmoCRM\RefreshTokenActions;
use Curl\Curl;

class RequestActions
{
    public function execute($url = '', $method = 'get', $data =[])
    {
        $accessToken = (new GetTokenActions)->execute();

        $request = new Curl('https://' . env('AMO_DOMAIN') . '.amocrm.ru');
        
        $request->setHeader('Authorization', 'Bearer ' . $accessToken);
        $request->setHeader('Content-Type', 'application/json');

        $res = $request->{$method}($url, $data);

        if (isset($res->response->error)) {
            $newAccessToken = (new RefreshTokenActions)->execute();
            $request->setHeader('Authorization', 'Bearer ' . $newAccessToken);
            $res = $request->{$method}($url, $data);
        }
        
        return $res;
    }
}
