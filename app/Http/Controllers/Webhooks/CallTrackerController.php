<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visit;
use App\Models\CallTrackerPhoneNumbers;
use Illuminate\Support\Carbon;
use App\Actions\AmoCRM\AddLead;

class CallTrackerController extends Controller
{

    public function __invoke(Request $request, AddLead $addLead) 
    {

        if (Request::isMethod('get')) {
            $echo = $request->get('zd_echo');
            exit($echo);
        }

        if ($request->event != 'NOTIFY_START') {
            return $request->event;
        }

        $caller = $request->caller_id; //Номер звонящего
        $callee = $request->called_did; //Номер, на который позвонили

        $number = CallTrackerPhoneNumbers::where('number', $callee)->first();

        $visit_id = null;
        
        $now = Carbon::now(); 
        $reservation_at = Carbon::createFromFormat('Y-m-d H:i:s', $number->reservation_at);
        
        if ($reservation_at > $now) {
            $visit_id = $number->visit_id;
        }

        $data = [
            'phone' => $caller,
            'title' => "Новая сделка по звонку с $caller",
            'tags' => [$number->tag],
        ];

        if ($visit_id and !$number->static) {
            $visit = Visit::find($visit_id);

            if ($visit) {
                $data = array_merge($data, [
                    'google_client_id' => $visit->google_client_id,
                    'metrika_client_id' => $visit->metrika_client_id,
                    'landing_page' => $visit->landing_page, 
                    'referrer' => $visit->referrer,
                    'utm_medium' => $visit->utm_medium, 
                    'utm_source' =>  $visit->utm_source, 
                    'utm_campaign' => $visit->utm_campaign, 
                    'utm_term' => $visit->utm_term, 
                    'utm_content' => $visit->utm_content,
                    'utm_referrer' => $visit->utm_referrer,
                    'visit' => $visit_id,
                    'amocrm_visitor_uid' => $visit->amocrm_visitor_uid,
                ]);
            }
        } else {
            $data['utm_source'] = $number->default_source;
        }

        return $addLead->execute($data);
        //TODO: Передать звонок в яндекс //sendCallToYandexMetrika
        //TODO: Передать звонок в google //sendCallToGoogleAnalytics
    }

}


