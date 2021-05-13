<?php

namespace App\Actions\AmoCRM;

use App\Actions\AmoCRM\GetUserAmoCRMTokenActions;
use Curl\Curl;
use App\Models\User;

class RequestActions
{
    public function execute($url = '', $method = 'get', $data =[])
    {

        $user = User::where('email', env('ROOT_EMAIL'))->first();

        $token = (new GetUserAmoCRMTokenActions)->execute($user);

        $request = new Curl('https://' . env('AMO_DOMAIN') . '.amocrm.ru');
        
        $request->setHeader('Authorization', 'Bearer ' . $token);
        $request->setHeader('Content-Type', 'application/json');

        $res = $request->{$method}($url, $data);

        return $res;
    }
}
