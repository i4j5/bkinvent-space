<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visit;
use App\Models\CallTrackerPhoneNumbers;
use Illuminate\Support\Carbon;

class VisitorController extends Controller
{

    public function create(Request $request)
    {


        dd( $this->reservationNumber(1) );


        $d =  Carbon::now();

        dd( $d->format('Y-m-d H:i:s'), $d->copy()->addSeconds( config('calltracking.track_time') )->format('Y-m-d H:i:s') );



        $data = [
            'metrika_client_id' => '',
            'google_client_id' => '',

            'amocrm_visitor_uid' => '',
            
            'utm_source' => '',
            'utm_medium' => '',
            'utm_campaign' => '',
            'utm_content' => '',
            'utm_term' => '',
            'utm_referrer' => '',
            
            'landing_page' => '',
            'referrer' => '',
            
            'first_visit' => 0
        ];

        $data = array_merge($data, array_intersect_key($request->json(), $data));

        $visit = Visit::create($data);

        if ($data['first_visit'] == 0) {
            $visit->first_visit = $visit->id;
            $visit->save();
        } 

        $phone = false;
        // TODO: Оптимизировать
        if ($data['referrer'] || $data['utm_source']) {
            // Получаем телефон
            $phone = $this->reservationNumber($visit->id);
        }
        
        return [
            'data' => [
                'visit' => $visit->id,
                'first_visit' => $visit->first_visit,
                'phone' => $phone
            ]
        ];
    }

    public function update(Request $request)
    {
        $visit_id = $request->json('visit');

        $visit = Visit::find($visit_id);

        if (!$visit) {
            return ['error' => ''];
        }

        if (!$visit->google_client_id) $visit->google_client_id = $request->json('google_client_id');
        if (!$visit->metrika_client_id) $visit->metrika_client_id = $request->json('metrika_client_id');
        if (!$visit->amocrm_visitor_uid) $visit->amocrm_visitor_uid = $request->json('amocrm_visitor_uid');

        $visit->save();

        $phone = false;

        if ($visit->referrer || $visit->utm_source) {
            $phone = $this->reservationNumber($visit->id);
        }

        return [
            'data' => [
                'visit' => $visit->id,
                'first_visit' => $visit->first_visit,
                'phone' => $phone
            ]
        ];
    }

    private function reservationNumber($visit_id)
    {
        if (!$visit_id) {
            return false;
        }

        $now = Carbon::now();
        
        $number = CallTrackerPhoneNumbers::where('visit_id', $visit_id)->first();

        //TODO: Проверить сессию
        
        if (!$number) {
            $number = CallTrackerPhoneNumbers::where([['reservation_at', '<', $now], ['static', '=', 0]])->first();
        }

        if ($number) {
            $reservation_at = $now->copy()->addSeconds(config('calltracking.track_time'))->format('Y-m-d H:i:s');
            
            $number->reservation_at = $reservation_at;
            $number->visit_id = $visit_id;
            $number->save();

            return [
                'number' => $number->number,
                'ttl' => $reservation_at,
            ];
        }
        
        return false;
    }
}