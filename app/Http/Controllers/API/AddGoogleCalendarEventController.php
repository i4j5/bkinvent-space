<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Actions\AddGoogleCalendarEventActions;
use App\Models\User;
use Illuminate\Support\Carbon;

class AddGoogleCalendarEventController extends Controller
{
   
    public function __invoke(Request $request, AddGoogleCalendarEventActions $addGoogleCalendarEvent) {

        $email = $request->email; //'sal@bkvent.space';
        $dataStart = $request->start; //'01.03.2021 10:50';
        $dataEnd = $request->end; //'01.03.2021 12:50';
        $summary = $request->summary; //'Название сделки';
        $description =$request->description; //'Описание';
        $location = $request->location; //'Ростов на доеу Ленина 5';

        $user = User::where('email', $email)->first();

        $start = Carbon::createFromFormat('d.m.Y H:i', $dataStart);
        $end = Carbon::createFromFormat('d.m.Y H:i', $dataEnd);

        if ($user) {
            return $addGoogleCalendarEvent->execute($user, $start, $end, $summary, $description, $location);
        } else {
            return false;
        }
    }
}
