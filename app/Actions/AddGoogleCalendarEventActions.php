<?php

namespace App\Actions;
use App\Actions\GetUserGoogleClientActions;
use App\Models\User;
use Illuminate\Support\Carbon;

class AddGoogleCalendarEventActions
{
    private $googleClient;

    public function __construct(GetUserGoogleClientActions $googleClient)
    {
        $this->googleClient = $googleClient;
    }

    public function execute(User $user, Carbon $start, Carbon $end, $summary, $description = '', $location = '')
    {
        $client = $this->googleClient->execute($user);
        $service = new \Google_Service_Calendar($client);

        if (!$user->google_calendar_id) {
            return false;
        }   
        
        $event = new \Google_Service_Calendar_Event(
            
            [
                'summary' => $summary,
                'description' => $description,
                'location' => $location,
                'start' => [
                    'dateTime' => $start->format('Y-m-d\TH:i:s'),
                    'timeZone' => config('app.timezone')
                ],
                'end' => [
                    'dateTime' => $end->format('Y-m-d\TH:i:s'),
                    'timeZone' => config('app.timezone')
                ]
            ]
            
          );

        return $service->events->insert($user->google_calendar_id, $event)->htmlLink;
    }
}
