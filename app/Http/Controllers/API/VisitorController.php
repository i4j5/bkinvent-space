<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visit;
use App\Models\CallTrackerPhoneNumbers;
use Illuminate\Support\Carbon;

class VisitorController extends Controller
{

    private $data = [
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
        
        'first_visit' => 0,
        'visit' => ''
    ];

    public function create(Request $request)
    {
        
        $data = $this->mergeData($request->all());

        $visit = Visit::create($data);

        if ($data['first_visit'] == 0) {
            $visit->first_visit = $visit->id;
            $visit->save();
        } 

        $phone = false;

        if ($data['referrer'] || $data['utm_source']) {
            $phone = $this->reservationNumber($visit->id);
        } else {
            $phone = $this->reservationNumber(0);
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

        $data = $this->mergeData($request->all());

        $visit_id = $data['visit'];

        $visit = Visit::find($visit_id);

        if (!$visit) {
            return ['error' => ''];
        }

        if (!$visit->google_client_id) $visit->google_client_id = $data['google_client_id'];
        if (!$visit->metrika_client_id) $visit->metrika_client_id = $data['metrika_client_id'];
        if (!$visit->amocrm_visitor_uid) $visit->amocrm_visitor_uid = $data['amocrm_visitor_uid'];

        $visit->save();

        $phone = false;

        if ($visit->referrer || $visit->utm_source) {
            $phone = $this->reservationNumber($visit->id);
        } else {
            $phone = $this->reservationNumber(0);
        }

        return [
            'data' => [
                'visit' => $visit->id,
                'first_visit' => $visit->first_visit,
                'phone' => $phone
            ]
        ];
    }

    private function mergeData($data) {
        
        $_data = array_merge($this->data, array_intersect_key($data, $this->data));
        $_data = array_merge($_data , array_intersect_key($data['utm'], $this->data));

        foreach ($_data as $index => $value) {
            if ($value === null){
                $_data[$index] = '';
            }
        }

        return $_data;
    }

    private function reservationNumber($visit_id)
    {
        $now = Carbon::now();
        $number = null;
        $reservation_at = $now->copy()->addSeconds(env('CALL_TRACKER_TRACK_TIME'))->format('Y-m-d H:i:s');

        if ($visit_id) {
            $number = CallTrackerPhoneNumbers::where('visit_id', $visit_id)->first();

            if (!$number) {
                $number = CallTrackerPhoneNumbers::where([['reservation_at', '<', $now], ['static', '=', 0]])->first();
            }
        }

        if ($number) {
            $number->reservation_at = $reservation_at;
            $number->visit_id = $visit_id;
            $number->save();

            return [
                'number' => $number->number,
                'ttl' => $reservation_at,
            ];
        } else {
            return [
                'number' => env('CALL_TRACKER_DEFAULT_NUMBER'),
                'ttl' => $reservation_at,
            ];
        }
        
        return false;
    }
}