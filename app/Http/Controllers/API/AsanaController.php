<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Actions\GetUserAsanaClientActions;

class AsanaController extends Controller
{

    private $asanaClient;

    public function __construct(GetUserAsanaClientActions $asanaClient)
    {
        $this->asanaClient = $asanaClient;
    }

    public function callback(Request $request) {

        $user = User::where('email', env('ROOT_EMAIL'))->first();
        $token = (new GetUserAsanaTokenActions)->execute($user);

    }

}
