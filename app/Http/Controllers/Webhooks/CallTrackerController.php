<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visit;
use App\Models\CallTrackerPhoneNumbers;
use Illuminate\Support\Carbon;
use App\Actions\AmoCRM\AddLead;
use App\Actions\AmoCRM\SerchContactActions;
use Curl\Curl;
use App\Models\User;
use App\Actions\Yandex\GetUserYandexTokenActions;
use Illuminate\Support\Facades\Storage;

class CallTrackerController extends Controller
{

    public function __invoke(Request $request, AddLead $addLead, SerchContactActions $serchContact) 
    {

        if ($request->isMethod('get')) {
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
                    'metrika_id' => env('YANDEX_METRIKA_ID'),
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

        $double = flase;

        $contact = $serchContact->execute($data['phone']);

        if ($contact['id']) {
            foreach ($contact['leads'] as $lead) {
                if (!$lead['is_deleted'] and !$lead['closed_at']) {
                    $double = true;
                }
            }
        }

        $res = null;
        if (!$double) {
            $res = $addLead->execute($data);
        }
    
        // Отправка звонок в google
        if (isset($data['google_client_id']) && $data['google_client_id']) {
            $this->sendCallToGoogleAnalytics([
                'client_id' => $data['google_client_id'],
                'event-сategory' => 'call',
                'event-action' => 'tracking',
            ]);
        }

        // Отправка звонок в яндекс
        if (isset($data['metrika_client_id']) && $data['metrika_client_id']) {
            $this->sendCallToYandexMetrika([
                'client_id' => $data['metrika_client_id'],
                'landing_page' => $data['landing_page'],
                'phone' => $data['phone'],
            ]);
        }

        return $res;
    }


    /**
     * Передать звонок в Google Analytics 
     */
    private function sendCallToGoogleAnalytics($params = []) {
        
        if (!isset($params['client_id']) || !$params['client_id']) {
            return false;
        }

        $default_data = [
            'v' => '1',
            't' => 'event',
            'ni' => '1',
            'ds' => 'api',
            'tid' => env('GOOGLE_ANALYTICS_ID'),
            'ec' => '',
            'ea' => '',
            'z' => '',
            'cid' => '',
            'cd4' => '',
        ];

        $data = array_merge($default_data, [
            'cid' => $params['client_id'],
            'cd4' => $params['client_id'],
            'ec' => $params['event-сategory'],
            'ea' => $params['event-action'],
        ]);

        $data['z'] = md5($data['cid'] . $data['ec'] . $data['ea'] . date('dmY'));

        $curl = new Curl();
        $curl->setUserAgent('user_agent_string');
        $curl->setHeader('Content-type', 'application/x-www-form-urlencoded');
        $curl->post('https://google-analytics.com/collect', $data);

        return true;
    }

    private function sendCallToYandexMetrika($params = []) {

        if (!isset($params['client_id']) || !$params['client_id']) {
            return false;
        }

        $client_id = $params['client_id'];
        $phone = $params['phone'];
        $landing_page = $params['landing_page'];
        $dateTime = time();

        $phone = '+' . str_replace(['+', '(', ')', ' ', '-', '_', '*', '–'], '', $phone);

        $fileName = uniqid('call_', true) . '.csv';
        $calls = "StaticCall,ClientId,DateTime,PhoneNumber,URL".PHP_EOL;
        $calls .= "0,$client_id,$dateTime,$phone,$landing_page".PHP_EOL;
        
        Storage::put($fileName, $calls);
        $path = Storage::path($fileName);

        $file = new \CurlFile(realpath($path));

        $user = User::where('email', env('ROOT_EMAIL'))->first();
        $token = (new GetUserYandexTokenActions)->execute($user);
        $counter_id = env('YANDEX_METRIKA_ID');

        $url = "https://api-metrika.yandex.ru/management/v1/counter/$counter_id/offline_conversions/upload_calls?client_id_type=CLIENT_ID";
        
        $curl = new Curl();
        $curl->setHeader('Content-type', 'multipart/form-data');
        $curl->setHeader('Authorization', "OAuth $token");

        $curl->post($url, [
            'file' => $file,
        ]);

        Storage::delete($fileName);

        return true;
    }

}