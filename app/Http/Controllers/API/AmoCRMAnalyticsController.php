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

        $created_from = $request->input('created_from');
        $created_to = $request->input('created_to');

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

    public function ProductionLeads(Request $request) {

        $created_from = $request->input('created_from');
        $created_to = $request->input('created_to');

        $created_at = [
            'from' => strtotime($created_from),
            'to' => strtotime("$created_to 23:59:59.000"),
        ];

        $leads = [];
        $limit = 200;

        $statuses = [];
        
        $statuses[] = [
            'pipeline_id' => 2291194,
            'status_id' => 31519303
        ];
        
        $statuses[] = [
            'pipeline_id' => 2291194,
            'status_id' => 31519306
        ];
        
        $statuses[] = [
            'pipeline_id' => 2291194,
            'status_id' => 31519309
        ];

        for ($page=1, $run=true; $run; $page++) { 
            $data = [
                'filter' => [
                    'statuses' => $statuses,
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

    public function ActiveLeads(Request $request) {

        $created_from = $request->input('created_from');
        $created_to = $request->input('created_to');

        $created_at = [
            'from' => strtotime($created_from),
            'to' => strtotime("$created_to 23:59:59.000"),
        ];

        $leads = [];
        $limit = 200;

        $statuses = [];

        $statuses[] = [
            'pipeline_id' => 2291194,
            'status_id' => 31518124
        ];
        
        $statuses[] = [
            'pipeline_id' => 2291194,
            'status_id' => 31518130
        ];
        
        $statuses[] = [
            'pipeline_id' => 2291194,
            'status_id' => 31518133
        ];
        
        $statuses[] = [
            'pipeline_id' => 2291194,
            'status_id' => 31518136
        ];
        
        $statuses[] = [
            'pipeline_id' => 2291194,
            'status_id' => 31519288
        ];
        
        $statuses[] = [
            'pipeline_id' => 2291194,
            'status_id' => 31519291
        ];
        
        $statuses[] = [
            'pipeline_id' => 2291194,
            'status_id' => 31519297
        ];
        
        $statuses[] = [
            'pipeline_id' => 2291194,
            'status_id' => 31519300
        ];
        
        $statuses[] = [
            'pipeline_id' => 2291194,
            'status_id' => 31519303
        ];
        
        $statuses[] = [
            'pipeline_id' => 2291194,
            'status_id' => 31519306
        ];
        
        $statuses[] = [
            'pipeline_id' => 2291194,
            'status_id' => 31519309
        ];
        
        $statuses[] = [
            'pipeline_id' => 2291194,
            'status_id' => 35272936
        ];
        
        $statuses[] = [
            'pipeline_id' => 2291194,
            'status_id' => 36053917
        ];
        
        $statuses[] = [
            'pipeline_id' => 2291194,
            'status_id' => 36089398
        ];
        
        // БИРЖА ЛИДОВ
        // $statuses[] = [
        //     'pipeline_id' => 2291194,
        //     'status_id' => 38176489
        // ];
        
        $statuses[] = [
            'pipeline_id' => 2291194,
            'status_id' => 38206177
        ];

        for ($page=1, $run=true; $run; $page++) { 
            $data = [
                'filter' => [
                    'statuses' => $statuses,
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

    public function Leads(Request $request) {
        $created_from = $request->input('created_from');
        $created_to = $request->input('created_to');

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
                'limit' => $limit, 
                'with' => 'contacts'
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


        $data = [];
        foreach ($leads as $lead) {
            if (isset($lead->custom_fields_values)) {
                if ($lead->custom_fields_values) {
                    foreach ($lead->custom_fields_values as $field) {
                        if ($field->field_id == 75455) {
                            if ($field->values[0]->value) {
                                $data[$field->values[0]->value][] = $lead->id;
                            } else {
                                $data['_NULL'][] = $lead->id;
                            }
                        }
                    }
                }
            }
        }


        dd($data);











        // $users = [];
        // foreach ($leads as $lead) {
        //     $lead->_embedded->companies;
        //     foreach ($lead->_embedded->contacts as $contact) {
        //         if ($contact->is_main) {
        //             $users[$contact->id]['deals'][] = [
        //                 'id' => $lead->id,
        //                 'name' => $lead->name,
        //                 'status_id' => $lead->name,
        //                 'created_at'=> $lead->created_at,
        //                 'closed_at'=> $lead->closed_at,
        //                 'price' => $lead->price,
        //             ];
        //         }
        //     }
        // }

        // foreach ($users as $id => $user) {
        
        //     $first_deal_index = 0;
        //     $_created_at = false;

        //     foreach ($user['deals'] as $deal) {

        //         if (!$_created_at) {
        //             $_created_at = $deal['created_at'];
        //         } elseif ($_created_at > $deal['created_at']) {
        //             $_created_at = $deal['created_at'];
        //         }
        //     }

        //     $users[$id]['created_at'] = $_created_at;
        // }


        // foreach ($users as $id => $user) {

        //     if ( 
        //         !($created_at['from'] <= $user['created_at'])
        //         or !($created_at['to'] >= $user['created_at']) 
        //     ) {
        //         unset($users[$id]);
        //     }
        // }
        

        // dd($users);
        // return $users;

        dd([
            'count' => [
                'leads' => count($leads),
                'users' => count($users),
            ],
            'data' => $users
        ]);
    }
}