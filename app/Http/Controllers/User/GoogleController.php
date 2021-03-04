<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Actions\GetUserGoogleClientActions;
use Illuminate\Support\Facades\Auth;

class GoogleController extends Controller
{

    private $googleClient;

    public function __construct(GetUserGoogleClientActions $googleClient)
    {
        $this->googleClient = $googleClient;
    }

    public function callback(Request $request) {

        $user = $request->user();
        
        $client = $this->googleClient->execute($user);

        $client->setScopes([
            'https://www.googleapis.com/auth/calendar',
            'https://www.googleapis.com/auth/drive',
            'https://www.googleapis.com/auth/userinfo.profile',
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/spreadsheets',
        ]);

        if ($request->get('code')) {
            $res = $client->authenticate($request->get('code')); 

            if (isset($res['error'])) {
                return $res['error'] . ' ' . $res['error_description'];
            } else if ($res['access_token']) {
                $user->google_access_token = $res['access_token'];
                $user->google_refresh_token = $res['refresh_token'];
                $user->google_expires_in = $res['expires_in'];

                $service = new \Google_Service_Calendar($client);
                $calendar = new \Google_Service_Calendar_Calendar();
                $calendar->setSummary("Рабочий календарь - $user->name");
                $calendar->setTimeZone(config('app.timezone'));
                $createdCalendar = $service->calendars->insert($calendar);

                $user->google_calendar_id = $createdCalendar->getId();

                $user->save();
            }

            return redirect('/user/profile');

        } else {
            return redirect($client->createAuthUrl());
        }

    }

    // private function creaale
    
}
