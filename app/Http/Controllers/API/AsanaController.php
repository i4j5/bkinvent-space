<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Actions\Asana\GetUserAsanaClientActions;
use \Curl\Curl;
use App\Actions\AmoCRM\RequestActions;
use Illuminate\Support\Carbon;

class AsanaController extends Controller
{

    private $client;

    public function __construct(GetUserAsanaClientActions $asanaClient)
    {
        $user = User::where('email', env('ROOT_EMAIL'))->first();
        $this->client = $asanaClient->execute($user);

        $this->client->options['headers'] = [
            'Asana-Enable' => 'new_user_task_lists'
        ];
    }


    public function createDealProject(Request $request)
    {   
        $deal_id = $request->input('deal');
        $project_id = $request->input('project');
        $task_id = $request->input('task');
        $section_id = $request->input('section');

        if (!$project_id || !$deal_id) {
            return 'error';
        }

        $amoCRM = new RequestActions;
        
        $deal = $amoCRM->execute('/api/v4/leads', 'get', [
            'id' => $deal_id,
            'with' => [
                '0' => 'contacts'
            ]
        ])->_embedded->leads[0];

        $description = "$deal->name #$deal->id";

        $description .= "\n";
        $description .= "https://" . env('AMO_DOMAIN') . ".amocrm.ru/leads/detail/$deal->id\n";

        if ($deal->custom_fields_values) {
            foreach ($deal->custom_fields_values as $field) {

                if ((int) $field->field_id == 518607) {
                    if ($field->values[0]->value != '') {
                        $description .= "\n";
                        $date =  (new Carbon($field->values[0]->value))->format('Y-m-d');
                        $description .= "Дата сдачи по договору: $date";
                    }
                }

                if ((int) $field->field_id == 329647) {
                    if ($field->values[0]->value != '') {
                        $description .= "\n";
                        $description .= "Заказ 1С: {$field->values[0]->value}";
                    }
                }

                if ((int) $field->field_id == 75413) {
                    if ($field->values[0]->value != '') {
                        $description .= "\n";
                        $description .= "Приоритет выполнения работ: {$field->values[0]->value}";
                    }
                }

                if ((int) $field->field_id == 75415) {
                    if ($field->values[0]->value != '') {
                        $description .= "\n";
                        $description .= "Условия работы: {$field->values[0]->value}";
                    }
                }

                if ((int) $field->field_id == 75401) {
                    if ($field->values[0]->value != '') {
                        $description .= "\n";
                        $description .= "География работ (адрес): {$field->values[0]->value}";
                    }
                }

                if ((int) $field->field_id == 75417) {
                    if ($field->values[0]->value != '') {
                        $description .= "\n";
                        $description .= "Информация по проекту: {$field->values[0]->value}";
                    }
                }

                if ((int) $field->field_id == 75429) {
                    if ($field->values[0]->value != '') {
                        $description .= "\n";
                        $description .= "Папка: {$field->values[0]->value}";
                    }
                }

                if ((int) $field->field_id == 284979) {
                    if ($field->values[0]->value != '') {
                        $description .= "\n";
                        $description .= "ТЗ: {$field->values[0]->value}";
                    }
                }

                if ((int) $field->field_id == 75295) {

                    $arr = [];
                    foreach ($field->values as $iiem) {
                        $arr[] = $iiem->value;
                    }
                    
                    if ($arr) {
                        $str = implode(', ', $arr);
                        $description .= "\n";
                        $description .= "Поставленные задачи: {$str}";
                    }
                }

            }
        }


        $contact_ids = [];
        foreach ($deal->_embedded->contacts as $item) {
            $contact_ids[] = $item->id;
        }

        $contacts = [];
        if ($contact_ids) {
            $contacts = $amoCRM->execute('/api/v4/contacts', 'get', [
                'id' => $contact_ids
            ])->_embedded->contacts;
            $description .= "\n";
        }

        foreach ($contacts as $contact)
        {
            $description .= "\n\n";
            $description .= "$contact->name #$contact->id";
            
            if ($contact->custom_fields_values) {
                foreach ($contact->custom_fields_values as $field) {
                    if (isset($field->field_code) && $field->field_code == 'PHONE') {
                        foreach ($field->values as $item) {
                            $description .= "\n$item->value";
                        } 
                    }
                    if (isset($field->field_code) && $field->field_code == 'EMAIL') {
                        foreach ($field->values as $item) {
                            $description .= "\n$item->value";
                        } 
                    }
                }
            }
        }

        $companies_ids = [];
        foreach ($deal->_embedded->companies as $item) {
            $companies_ids[] = $item->id;
        }
        $companies = [];
        if ($companies_ids) {
            $companies = $amoCRM->execute('/api/v4/companies', 'get', [
                'id' => $companies_ids
            ])->_embedded->companies;
        }

        foreach ($companies as $company)
        {
            $description .= "\n\n";
            $description .= "$company->name #$company->id";
            
            if ($company->custom_fields_values) {
                foreach ($company->custom_fields_values as $field) {
                    if (isset($field->field_code) && $field->field_code == 'PHONE') {
                        foreach ($field->values as $item) {
                            $description .= "\n$item->value";
                        } 
                    }
                    if (isset($field->field_code) && $field->field_code == 'EMAIL') {
                        foreach ($field->values as $item) {
                            $description .= "\n$item->value";
                        } 
                    }
                }
            }
        }

        $description .= "\n\nПромо код: $deal_id";


        $link = '';

        $new_project = [
            'gid' => 0,
            'responsible' => $deal->responsible_user_id,
            'type' => '',
            'deal_id' => $deal_id,
        ];


        if($task_id) {

            $data = [
                'name' => "$deal->name #$deal->id",
                'include' => [
                    'notes',
                    'assignee',
                    'subtasks',
                    'attachments',
                    'tags',
                    'followers',
                    'projects',
                    'dates',
                    'parent',
                ]
            ];

            $res = $this->client->tasks->duplicateTask($task_id, $data, ['opt_pretty' => 'true']);

            $gid = $res->new_task->gid;
            
            $link = "https://app.asana.com/0/$project_id/$gid";
            
            if ($section_id) {;
                $this->client->sections->addTaskForSection($section_id, [
                    'task' => $gid
                ]);
            }

            $this->client->tasks->removeProjectForTask($gid, [
                'project' => '1172502221110985'
            ]);

            $this->client->tasks->updateTask($gid, [
                'notes' => $description,
                'due_on' => date('Y-m-d')
            ]);

            $new_project['gid'] = $gid;
            $new_project['type'] = 'task';

        } else {

            $data = [
                'name' => "$deal->name #$deal->id",
                'include' => [
                    'task_notes',
                    'task_subtasks',
                    'task_projects',
                    'task_assignee',
                    'task_attachments',
                    'notes',
                ],
                'team' => '882014108971315'
            ];

            $res = $this->client->projects->duplicateProject($project_id, $data);
            
            $gid = $res->new_project->gid;
            
            $link = 'https://app.asana.com/0/' . $gid;

            $new_project['gid'] = $gid;
            $new_project['type'] = 'project';

            $this->client->projects->updateProject($gid, [
                'notes' => $description
            ]);
        }

        
        $update_data = [
            'custom_fields_values' => [
                [
                    'field_id' => 75437,
                    'values' => [
                        ['value' => $link]
                    ]
                ]
            ] 
        ];

        $amoCRM->execute("/api/v4/leads/$deal_id", 'patch', $update_data);
        
        return $new_project;
    }

    public function updateDealProject(Request $request)
    {
        
        set_time_limit(0);

        $users = [];
        foreach (User::all() as $user) {
            $users[$user->email] = $user->asana_user_id;
        }

        $gid = $request->input('gid');
        $type = $request->input('type');
        $amo_user_id = $request->input('responsible');
        $deal_id = $request->input('deal_id');

        $asana_user_id = 0;
        $description = '';
        $tasks = [];

        if ($amo_user_id) {
            $user = User::where('amo_user_id', $amo_user_id)->first();

            if($user) {
                $asana_user_id = $user->asana_user_id;
            }
        }

        $asana = new Curl();
        $asana->setHeader('Authorization', 'Bearer ' . $this->client->dispatcher->accessToken);
        $asana->setHeader('Content-Type', 'application/x-www-form-urlencoded');

        if ($type == 'project') {

            $project = $this->client->projects->getProject($gid);

            $description = $project->notes;

            $tasks = $asana->get("https://app.asana.com/api/1.0/projects/$gid/tasks")->data;

        } else if ($type == 'task') {
            $project = $this->client->tasks->getTask($gid);
            
            $description = $project->notes;

            $tasks = $asana->get("https://app.asana.com/api/1.0/tasks/$gid/subtasks")->data;
        }

        $subtasks = [];
        foreach ( $tasks as $task ) {
            usleep(2);
            $data = $asana->get("https://app.asana.com/api/1.0/tasks/$task->gid/subtasks")->data;
            $subtasks =  array_merge( $subtasks, $data);
        }

        $subsubtasks = [];
        foreach ( $subtasks as $task ) {
            usleep(2);
            $data = $asana->get("https://app.asana.com/api/1.0/tasks/$task->gid/subtasks")->data;
            $subsubtasks =  array_merge( $subsubtasks, $data);
        }

        $tasks = array_merge($tasks, $subtasks, $subsubtasks);

        foreach ( $tasks as $task )
        {
            $rename = false;

            $data = [];

            $name = $task->name;

            $name = str_replace("%date%", "", $name, $count);
            if ($count > 0) {
                $data['due_on'] = date('Y-m-d');
                $rename = true;
            }

            $name = str_replace("%date+1day%", "", $name, $count);
            if ($count > 0) {
                $data['due_on'] = date("Y-m-d", microtime(true)+(60*60*24));
                $rename = true;
            }

            $name = str_replace("%crm%", "", $name, $count);
            if ($count > 0) {
                if ($asana_user_id) $data['assignee'] = $asana_user_id;
                $rename = true;
            }

            preg_match_all("/[-a-z0-9!#$&'*_`{|}~]+[-a-z0-9!#$%&'*_`{|}~\.=?]*@[a-zA-Z0-9_-]+[a-zA-Z0-9\._-]+/i", $name, $result);
            $result = $result[0];
            if (count($result)) {
                foreach ($result as $email) {
                    $name = str_replace("%$email%", "", $name, $count);
                    if ($count > 0) {
                        if($users[$email]) $data['assignee'] = $users[$email];
                        $rename = true;
                    }
                }
            }

            $name = str_replace("%description%", "", $name, $count);
            if ($count > 0) {
                $data['notes'] = $description;
                $rename = true;
            }

            if($rename) {
                $data['name'] = $name;
                $this->client->tasks->updateTask($task->gid, $data);
                usleep(30);
            } 
        }

        // TODO: проверить есть ли вебхук с $deal_id
        // Подписать на события
        // $asana = new Curl();
        // $asana->setHeader('Authorization', 'Bearer ' . $this->client->dispatcher->accessToken);
        // $asana->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        // $asana->post('https://app.asana.com/api/1.0/webhooks', [
        //     'target' => env('APP_URL') . "/api/asana/webhook/$deal_id/$gid",
        //     'resource' => $gid,
        // ]);

        return 'ok';
    }

    public function deleteWebhook(Request $request) {
        
        $lead_id = $request->input('leads')['delete'][0]['id'];

        if (!$lead_id) return 'error'; 

        $asana = new Curl();
        $asana->setHeader('Authorization', 'Bearer ' . $this->client->dispatcher->accessToken);
        $asana->setHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        $asana_webhooks = $asana->get('https://app.asana.com/api/1.0/webhooks?workspace=' . env('ASANA_WORKSPACE_ID'))->data;

        foreach ($asana_webhooks as $webhook) {
            
            $arr = explode('/', $webhook->target);
            $i = ((int) count($arr)) - 2;

            if ($arr[$i] == $lead_id) {
                $this->client->webhooks->deleteWebhook($webhook->gid);
            }
        }

        return 'ok';
    }

    public function webhook(Request $request, $deal_id, $project_id)
    {
        set_time_limit(0);
        
        $secret = $request->header('X-Hook-Secret');;
        $events = $request->input('events') ? $request->input('events') : [];

        if ($secret) {
            if (!$deal_id) {
                return response('No Content', 204)->header('X-Hook-Secret', $secret);
            }

            return response('OK', 200)->header('X-Hook-Secret', $secret);
        } 

        $amoCRM = new RequestActions;

        foreach ($events as $event) 
        {

            if ($event['action'] == 'sync_error') {
                // TODO: писать в лог
                continue;
            }

            $user_name = '';

            if (
                isset($event['user']) &&
                isset($event['user']['gid']) 
            ) {
                $user_name = $this->client->users->getUser($event['user']['gid'])->name;
            }

            $text = "Пользователь $user_name";

            $change = isset($event['change']) ? $event['change'] : null;

            // Перенос задачи в секцию 
            if (
                $event['action'] == 'added' && 
                $event['resource']['resource_type'] == 'task' && 
                $event['parent']['resource_type'] == 'section'
            ) {
                $task = $this->client->tasks->getTask($event['resource']['gid']);

                $text .= " перенёс задачу «{$task->name}»";

                $section = $this->client->sections->getSection($event['parent']['gid']);

                $text .= " в секцию «{$section->name}»";

                $data_notes = [];
                $data_notes[] = [
                    'note_type' => 'invoice_paid',
                    'params' => [
                        'text' => $text,
                        'service' => 'ASANA',
                        'icon_url' => env('APP_URL') . '/storage/asana/crm.png', 
                    ]
                ];
                $amoCRM->execute("/api/v4/leads/$deal_id/notes", 'post', $data_notes);
            }

            // Комментарий добавлен 
            if (
                $event['action'] == 'added' && 
                $event['resource']['resource_type'] == 'story' && 
                $event['resource']['resource_subtype'] == 'comment_added' &&
                isset($event['resource']['gid']) &&
                isset($event['created_at']) &&
                isset($event['user']['gid']) &&
                isset($event['parent']['resource_type'])
            ) {



                $text .= " добавил комментарий";

                $resource = $this->client->stories->getStory($event['resource']['gid']);

                if ($resource->type == 'comment') {
                    $text .= " «{$resource->text}»";
                }

                if ($event['parent']['resource_type'] == 'task') {
                    $task = $this->client->tasks->getTask($event['parent']['gid']);
                    $text .= " к задаче «{$task->name}»";
                }

                $data_notes = [];
                $data_notes[] = [
                    'note_type' => 'invoice_paid',
                    'params' => [
                        'text' => $text,
                        'service' => 'ASANA',
                        'icon_url' => env('APP_URL') . '/storage/asana/crm.png', 
                    ]
                ];
                $amoCRM->execute("/api/v4/leads/$deal_id/notes", 'post', $data_notes);
            }

            // Задача закрыта
            if (isset($change['field'])) {
                if (
                    $change['field'] == 'completed' &&
                    $event['resource']['resource_type'] == 'task' &&
                    // $event['action'] == 'added' && 
                    $event['resource']['resource_subtype'] == 'comment_added' &&
                    isset($event['resource']['gid']) &&
                    isset($event['created_at']) &&
                    isset($event['user']['gid']) &&
                    isset($event['parent']['resource_type'])
                ) {

                    $resource = $this->client->tasks->getTask($event['resource']['gid']);
    
                    $text .= " закрыл задачу «{$resource->name}»";

                    $data_notes = [];
                    $data_notes[] = [
                        'note_type' => 'invoice_paid',
                        'params' => [
                            'text' => $text,
                            'service' => 'ASANA',
                            'icon_url' => env('APP_URL') . '/storage/asana/crm.png', 
                        ]
                    ];
                    $amoCRM->execute("/api/v4/leads/$deal_id/notes", 'post', $data_notes);
                }
            }

            usleep(20);
        }

        return 'ok';
    }

}
