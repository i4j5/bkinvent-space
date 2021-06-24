<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visit;
use Illuminate\Support\Carbon;
use App\Actions\AmoCRM\AddLead;

class SiteController extends Controller
{

    public function createLead(Request $request, AddLead $addLead)
    {
        $default_data = [
            'google_client_id' => '',
            'metrika_client_id' => '',
            'landing_page' => '',
            'referrer' => '',
            'utm_medium' => '',
            'utm_source' =>  '',
            'utm_campaign' => '',
            'utm_term' => '',
            'utm_content' => '',
            'utm_referrer' => '',
            'visit' => '',
            'title' => 'LEAD',
            'comment' => '',
            'tags' => ['Заявка с сайта'],
            'amocrm_visitor_uid' => '',
            'phone' => '',
            'email' => '',
            'name' => '',
            'ip' => $request->ip()
        ];

        $data = array_merge($request->all(), [
            'landing_page' => $request->input('url'),
            'title' => $request->input('order') . '.',
        ]);

        $data = array_merge($default_data, array_intersect_key($data, $default_data));

        return $addLead->execute($data);

    }
}