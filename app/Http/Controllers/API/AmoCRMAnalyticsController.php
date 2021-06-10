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

    /**
     * Новые лиды
     */
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

        $leads = $this->RemoveTrash($leads);

        $all_price = 0;
        $cost_price = 0;
        $paid = 0;
        foreach ($leads as $lead) {
            $all_price = $all_price + $lead->price;

            if (isset($lead->custom_fields_values)) {
                foreach ($lead->custom_fields_values as $field) {
                    
                    // Себестоимость
                    if ($field->field_id == 75197) {
                        $cost_price = $cost_price + $field->values[0]->value;
                    }

                    // Оплачено клиентом
                    if ($field->field_id == 506505) {
                        $paid = $paid + $field->values[0]->value;
                    }
                }
            }
        }

        return [
            'all_price' => $all_price,
            'cost_price' => $cost_price,
            'paid' => $paid,
            'count' => count($leads)
        ];
    }

    /**
     * Переданы в производство
     */
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

        $leads = $this->RemoveTrash($leads);

        $all_price = 0;
        $cost_price = 0;
        $paid = 0;
        foreach ($leads as $lead) {
            $all_price = $all_price + $lead->price;

            if (isset($lead->custom_fields_values)) {
                foreach ($lead->custom_fields_values as $field) {
                    
                    // Себестоимость
                    if ($field->field_id == 75197) {
                        $cost_price = $cost_price + $field->values[0]->value;
                    }

                    // Оплачено клиентом
                    if ($field->field_id == 506505) {
                        $paid = $paid + $field->values[0]->value;
                    }
                }
            }
        }

        return [
            'all_price' => $all_price,
            'cost_price' => $cost_price,
            'paid' => $paid,
            'count' => count($leads)
        ];
    }

    /**
     * Активные сделки
     */
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

        $leads = $this->RemoveTrash($leads);

        $all_price = 0;
        $cost_price = 0;
        $paid = 0;
        foreach ($leads as $lead) {
            $all_price = $all_price + $lead->price;

            if (isset($lead->custom_fields_values)) {
                foreach ($lead->custom_fields_values as $field) {
                    
                    // Себестоимость
                    if ($field->field_id == 75197) {
                        $cost_price = $cost_price + $field->values[0]->value;
                    }

                    // Оплачено клиентом
                    if ($field->field_id == 506505) {
                        $paid = $paid + $field->values[0]->value;
                    }
                }
            }
        }

        return [
            'all_price' => $all_price,
            'cost_price' => $cost_price,
            'paid' => $paid,
            'count' => count($leads)
        ];
    }

    /**
     * Закрытые сделки
     */
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

        $leads = $this->RemoveTrash($leads);

        $all_price = 0;
        $cost_price = 0;
        $paid = 0;
        foreach ($leads as $lead) {
            $all_price = $all_price + $lead->price;

            if (isset($lead->custom_fields_values)) {
                foreach ($lead->custom_fields_values as $field) {
                    
                    // Себестоимость
                    if ($field->field_id == 75197) {
                        $cost_price = $cost_price + $field->values[0]->value;
                    }

                    // Оплачено клиентом
                    if ($field->field_id == 506505) {
                        $paid = $paid + $field->values[0]->value;
                    }
                }
            }
        }

        return [
            'all_price' => $all_price,
            'cost_price' => $cost_price,
            'paid' => $paid,
            'count' => count($leads)
        ];
    }

    public function CountLeads(Request $request) {


        $created_from = $request->input('created_from');
        $created_to = $request->input('created_to');

        $closed_from = $request->input('closed_from');
        $closed_to = $request->input('closed_to');

        $status = explode(',', $request->input('status'));

        $statuses = [];

        foreach ($status as $item) {
            $statuses[] = [
                'pipeline_id' => 2291194,
                'status_id' => $item
            ];
        }

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

    /**
     * TODO
     * Данные по фильтру
     */
    public function Leads(Request $request) {

        
        // $res = $this->api->execute("/api/v4/leads/20323447");

        $id = 20323447;

        $status_id = 31519303;
        
        $after = "&filter[value_after][leads_statuses][0][pipeline_id]=2291194&filter[value_after][leads_statuses][0][status_id]=$status_id";

        $f = "?filter[entity]=lead&filter[entity_id]=$id&filter[type]=lead_status_changed$after";

        $res = $this->api->execute("/api/v4/events$f");


        dd($res);

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


    private function RemoveTrash($leads) {

        foreach ($leads as $id => $lead) {
            // 4074595// Дубль
            // 4104691// Спам
            if (isset($lead->loss_reason_id)) {
                if ($lead->loss_reason_id == 4104691 or $lead->loss_reason_id == 4074595) {
                    unset($leads[$id]);
                }
            }
        }

        return $leads;

    }
}