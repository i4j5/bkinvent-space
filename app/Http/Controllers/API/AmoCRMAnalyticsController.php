<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Actions\AmoCRM\RequestActions;

class AmoCRMAnalyticsController extends Controller
{

    private $api;
   
    public function __construct()
    {
        $this->api = new RequestActions;
    }

    public function NewLeads(Request $request) {

        $created_from = '10.05.2021';
        $created_to = '16.05.2021';

        $created_at = [
            'from' => strtotime($created_from),
            'to' => strtotime("$created_to 23:59:59.000"),
        ];

        $leads = [];
        $limit = 200;

        for ($page=1, $run=true; $run; $page++) { 
            
            $data = [
                'filter' => [
                    'pipeline_id' => [2291194],
                    'created_at' => $created_at,
                    'page' => $page,
                ],
                'limit' => $limit 
            ];
    
            $query = http_build_query($data);
    
            $res = $this->api->execute("/api/v4/leads?$query");

            if ($res) {

                $arr = $res->_embedded->leads;

                $run = !!(count($arr) == $limit);

                $leads = array_merge($leads, $arr);

            } else {
                $run = false;
            }

        }

        return [
            'count' => count($leads)
        ];
    }

    public function ClosedLeads(Request $request) {


        $created_from = $request->input('created_from');
        $created_to = $request->input('created_to');

        $closed_from = $request->input('closed_from');
        $closed_to = $request->input('closed_to');

        $created_at = [
            'from' => strtotime($created_from),
            'to' => strtotime("$created_to 23:59:59.000"),
        ];

        $closed_at = [
            'from' => strtotime($closed_from),
            'to' => strtotime("$closed_to 23:59:59.000"),
        ];

        $leads = [];
        $limit = 200;

        for ($page=1, $run=true; $run; $page++) { 
            
            $data = [
                'filter' => [
                    
                    'statuses' => [
                        0 => [
                            'pipeline_id' => 2291194,
                            'status_id' => 142
                        ]
                    ],
                    'created_at' => $created_at,
                    'closed_at' => $closed_at,
                    'page' => $page,
                ],
                'limit' => $limit 
            ];

            // dd( $data);
    
            $query = http_build_query($data);
    
            $res = $this->api->execute("/api/v4/leads?$query");

            if ($res) {

                $arr = $res->_embedded->leads;

                $run = !!(count($arr) == $limit);

                $leads = array_merge($leads, $arr);

            } else {
                $run = false;
            }

        }

        $all_price = 0;
        foreach ($leads as $lead) {
            $all_price = $all_price + $lead->price;
        }

        return [
            'all_price' => $all_price, 
            'count' => count($leads)
        ];
    }
}

// closed_at
//statuses