<?php

namespace App\Actions\AmoCRM;

use App\Actions\AmoCRM\RequestActions;
use Illuminate\Support\Facades\Log;

class AddLead
{

    private $amoCRM;

    private $default_data = [
        'google_client_id' => '',
        'metrika_client_id' => '',
        'metrika_id' => '',
        'landing_page' => '',
        'referrer' => '',
        'utm_medium' => '',
        'utm_source' =>  '',
        'utm_campaign' => '',
        'utm_term' => '',
        'utm_content' => '',
        'utm_referrer' => '',
        'visit' => '',
        'title' => '',
        'tags' => [],
        'amocrm_visitor_uid' => '',
        'phone' => '',
        'email' => '',
        'name' => '',
        'comment' => '',
        'ip' => '8.8.8.8',
    ];

    public function __construct() {
        $this->amoCRM = new RequestActions;
    }

    public function execute($data = [])
    {

        $data = $this->mergeData($data);

        $contact_id = $this->serchContact( $data['phone'] );

        $lead_id = null;
        
        if (strlen($data['phone']) >= 11) {
            if ($data['phone'][0] == 8) {
                $data['phone'][0] = 7;
            }
            $data['phone'] = '+' . $data['phone'];
        } else if (strlen($data['phone']) == 10) {
            $data['phone'] = '+7' . $data['phone'];
        }

        $lead_custom_fields_values = [];

        $lead_custom_fields_values[] = [
            'field_id' => 173485,
            'values' => [
                '0' => [
                    'value' => $data['visit']
                ]
            ]
        ];

        $lead_custom_fields_values[] = [
            'field_id' => 75451,
            'values' => [
                '0' => [
                    'value' => $data['landing_page']
                ]
            ]
        ];

        $lead_custom_fields_values[] = [
            'field_code' => 'UTM_SOURCE',
            'values' => [
                '0' => [
                    'value' => $data['utm_source']
                ]
            ]
        ];
        $lead_custom_fields_values[] = [
            'field_id' => 75455,
            'values' => [
                '0' => [
                    'value' => $data['utm_source']
                ]
            ]
        ];

        $lead_custom_fields_values[] = [
            'field_code' => 'UTM_MEDIUM',
            'values' => [
                '0' => [
                    'value' => $data['utm_medium']
                ]
            ]
        ];
        $lead_custom_fields_values[] = [
            'field_id' => 75457,
            'values' => [
                '0' => [
                    'value' => $data['utm_medium']
                ]
            ]
        ];

        $lead_custom_fields_values[] = [
            'field_code' => 'UTM_CAMPAIGN',
            'values' => [
                '0' => [
                    'value' => $data['utm_campaign']
                ]
            ]
        ];
        $lead_custom_fields_values[] = [
            'field_id' => 75461,
            'values' => [
                '0' => [
                    'value' => $data['utm_campaign']
                ]
            ]
        ];

        $lead_custom_fields_values[] = [
            'field_code' => 'UTM_TERM',
            'values' => [
                '0' => [
                    'value' => $data['utm_term']
                ]
            ]
        ];
        $lead_custom_fields_values[] = [
            'field_id' => 75453,
            'values' => [
                '0' => [
                    'value' => $data['utm_term']
                ]
            ]
        ];

        $lead_custom_fields_values[] = [
            'field_code' => 'UTM_CONTENT',
            'values' => [
                '0' => [
                    'value' => $data['utm_content']
                ]
            ]
        ];
        $lead_custom_fields_values[] = [
            'field_id' => 75459,
            'values' => [
                '0' => [
                    'value' => $data['utm_content']
                ]
            ]
        ];

        $lead_custom_fields_values[] = [
            'field_code' => 'UTM_REFERRER',
            'values' => [
                '0' => [
                    'value' => $data['utm_referrer']
                ]
            ]
        ];

        $lead_custom_fields_values[] = [
            'field_code' => 'REFERRER',
            'values' => [
                '0' => [
                    'value' => $data['referrer']
                ]
            ]
        ];
        $lead_custom_fields_values[] = [
            'field_id' => 75465,
            'values' => [
                '0' => [
                    'value' => $data['referrer']
                ]
            ]
        ];

        $lead_custom_fields_values[] = [
            'field_code' => '_YM_UID',
            'values' => [
                '0' => [
                    'value' => $data['metrika_client_id']
                ]
            ]
        ];
        $lead_custom_fields_values[] = [
            'field_id' => 75469,
            'values' => [
                '0' => [
                    'value' => $data['metrika_client_id']
                ]
            ]
        ];

        $lead_custom_fields_values[] = [
            'field_code' => '_YM_COUNTER',
            'values' => [
                '0' => [
                    'value' => $data['metrika_id']
                ]
            ]
        ];

        $lead_custom_fields_values[] = [
            'field_code' => 'GCLIENTID',
            'values' => [
                '0' => [
                    'value' => $data['google_client_id']
                ]
            ]
        ];
        $lead_custom_fields_values[] = [
            'field_id' => 75467,
            'values' => [
                '0' => [
                    'value' => $data['google_client_id']
                ]
            ]
        ];

        $tags = [];

        foreach ($data['tags'] as $tag) {
            $tags[] = [
                'name' => $tag
            ];
        }
    
        $lead = [
            'name' => $data['title'],
            '_embedded' => [
                'tags' => $tags
            ],
            'custom_fields_values' => $lead_custom_fields_values
        ];

        if ( !empty($data['amocrm_visitor_uid']) ) {
            $lead['visitor_uid'] = $data['amocrm_visitor_uid'];
        }

        // Мета данные
        $metadata = [
            'form_id' => '1',
            'form_name' => '1',
            'form_page' => $data['landing_page'] ?  $data['landing_page'] : '-',
            'ip' => $data['ip'],
            'form_sent_at' =>  time()
        ];

        if ( !empty($data['referrer']) ) {
            $metadata['referer'] = $data['referrer'];
        }
        
        $unsorted_data = [];
        $unsorted_data[0] = [
            'source_uid' => uniqid(),
            'source_name' => 'Cайта',
            'created_at' => time(),
            '_embedded' => [
                'leads' => [
                    0 => $lead
                ],
            ],
            'metadata' => $metadata,
        ];

        if (!$contact_id) {
            $unsorted_data[0]['_embedded']['contacts'] = [
                0 => [
                    'name' => $data['name'] ? $data['name'] : $data['phone'],
                    'custom_fields_values' => [
                        '0' => [
                            'field_code' => 'PHONE',
                            'values' => [
                                '0' => [
                                    'value' => $data['phone'],
                                    'enum_code' => 'MOB'
                                ]
                            ]
                        ],
        
                        '1' => [
                            'field_code' => 'EMAIL',
                            'values' => [
                                '0' => [
                                    'value' => $data['email'],
                                    'enum_code' => 'WORK'
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        } else {
            $unsorted_data[0]['_embedded']['contacts'] = [
                0 => [
                    'id' => $contact_id,
                ]
            ];
        }

        $res = $this->amoCRM->execute('/api/v4/leads/unsorted/forms', 'post', $unsorted_data);

        if (!isset($res->_total_items) or !isset($res->_embedded)) {

            $masege = 'addLead';

            if (isset($res->status)) {
                $masege .= " status: $res->status \n" ;
            }

            if ( isset($res->{'validation-errors'}) ) {
                $masege .= ' validation-errors(serialize): ' . serialize($res->{'validation-errors'}) . "\n";
                $masege .= ' validation-errors(json_encode): ' . json_encode($res->{'validation-errors'}) . "\n";
            }

            $masege .= ' unsorted_data(serialize): ' . serialize($unsorted_data) . "\n";
            $masege .= ' unsorted_data(json_encode): ' . json_encode($unsorted_data) . "\n";

            Log::channel('amocrm')->error($masege);

        }

        $unsorted_id = $res->_embedded->unsorted[0]->uid;
        $lead_id = $res->_embedded->unsorted[0]->_embedded->leads[0]->id;

        if (!$contact_id) {
            $contact_id = $res->_embedded->unsorted[0]->_embedded->contacts[0]->id;
        }

        $note = "{$data['comment']} \n";
        $note = "{$note} {$data['title']} \n";
        $note = "{$note} Имя: {$data['name']} \n";
        $note = "{$note} Телефон: {$data['phone']} \n";
        $note = "{$note} E-mail: {$data['email']} \n";
        $note = "{$note} Ключевое слово: {$data['utm_term']} \n";
        $note = "{$note} Страница захвата: {$data['landing_page']} \n";
        
        $this->amoCRM->execute("/api/v4/contacts/$contact_id/notes", 'post', [
            0 => [
                'note_type' => 'common',
                'params' => [
                    'text' => $note
                ] 
            ]
        ]);

        return [
            'unsorted_id' => $unsorted_id,
            'lead_id' => $lead_id,
            'contact_id' => $contact_id,
        ];

    }

    private function serchContact($phone) {

        $contact_id = null;

        if (strlen($phone) < 10) {
            return $contact_id;
        } 

        $query = str_replace(['+', '(', ')', ' ', '-', '_', '*', '–'], '', $phone);
        $query = substr($query, 1);

        $res = $this->amoCRM->execute("/api/v4/contacts?query=$query");

        $contacts = [];
        if ($res and isset($res->_embedded->contacts)) {
            $contacts = $res->_embedded->contacts;
        }

        foreach ($contacts as $contact) {
            if (isset($contact->custom_fields_values)) {
                foreach ($contact->custom_fields_values as $field) {
                    $value = $field->values[0]->value;

                    $value = str_replace(['+', '(', ')', ' ', '-', '_', '*', '–'], '', $value);
                    $value = substr($value, 1);

                    if ($query === $value) {
                        $contact_id = $contact->id;
                        break 2;
                    }
                }
            }
        }

        return $contact_id;
    }

    private function mergeData($data) {
        
        $_data = array_merge($this->default_data, array_intersect_key($data, $this->default_data));

        $_data['phone'] = str_replace(['+', '(', ')', ' ', '-', '_', '*', '–'], '', $_data['phone']);

        foreach ($_data as $index => $value) {
            if (!$value){
                $_data[$index] = '';
            }

            if (gettype($value) === 'integer') {
                $_data[$index] = (string) $_data[$index];
            }
        }

        if (gettype($_data['tags']) != 'array') {
            $_data['tags'] = [];
        }

        if (strlen($_data['referrer']) > 255)  {
            $parse = parse_url($_data['referrer']);
            $_data['referrer'] = $parse['host'];
        }
        
        return $_data;
    }
}
