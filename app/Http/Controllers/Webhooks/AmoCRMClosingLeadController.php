<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Actions\AmoCRM\RequestActions;


///
use App\Actions\GetUserGoogleClientActions;
use App\Models\User;
///

class AmoCRMClosingLeadController extends Controller
{

    private $api;
   
    public function __construct()
    {
        $this->api = new RequestActions;
    }

    public function __invoke(Request $request) {

        // $lead_id = $request->input('leads')['status'][0]['id'];
        $lead_id = 20040247;


        $lead = $this->api->execute("/api/v4/leads/$lead_id?with=contacts");

        $company_id = !!count($lead->_embedded->companies) ? $lead->_embedded->companies[0]->id : null;
        
        $contacts = $lead->_embedded->contacts;
        $main_contact_id = null;
        foreach ($contacts as $contact) {
            if ($contact->is_main) {
                $main_contact_id = $contact->id;
            }
        }

        $email = 'sal@bkinvent.net';
        $user = User::where('email', $email)->first();

        $googleClient = (new GetUserGoogleClientActions)->execute($user);

        // ГУГЛ 
        $contacts_folder_id = '1VMEVT9NKPce_Ox4_ojMRDeJf-ddGCaVG';
        $projects_folder_id = '1ZFJvYAWAPH1THVz31H4KmzLI-vOYc0Jv';


        $googleClient->addScope(\Google_Service_Drive::DRIVE);
        $service = new \Google_Service_Drive($googleClient);

        $project_folder_1 = $this->CreateFolder($service, $projects_folder_id, 'Проект 3');
        $this->CreateFolder($service, $project_folder_1->id, '1.2 ПАПКА МЕНЕДЖЕРА');
        $this->CreateFolder($service, $project_folder_1->id, '1.4 ИСХОДНЫЕ ДОКУМЕНТЫ');
        $this->CreateFolder($service, $project_folder_1->id, '1.5 УСЛУГА');
        $folder = $this->CreateFolder($service, $project_folder_1->id, '1.3 ОБСЛЕДОВАНИЕ ОБЪЕКТА');
        $this->CreateFolder($service, $folder->id, '1.3.1 ФОТООТЧЁТ');
        $this->CreateFolder($service, $folder->id, '1.3.2 РЕЗЮМЕ');
        
        // $project_folder_2 = $this->CreateFolder($service, $projects_folder_id, 'Проект 2');
        // $this->CreateFolder($service, $project_folder_2->id, '1.2 ПАПКА МЕНЕДЖЕРА');
        // $this->CreateFolder($service, $project_folder_2->id, '1.4 ИСХОДНЫЕ ДОКУМЕНТЫ');
        // $this->CreateFolder($service, $project_folder_2->id, '1.5 УСЛУГА');
        // $folder = $this->CreateFolder($service, $project_folder_2->id, '1.3 ОБСЛЕДОВАНИЕ ОБЪЕКТА');
        // $this->CreateFolder($service, $folder->id, '1.3.1 ФОТООТЧЁТ');
        // $this->CreateFolder($service, $folder->id, '1.3.2 РЕЗЮМЕ');

        $contact_folder = $this->CreateFolder($service, $contacts_folder_id, 'Клиент 3');
        $contact_folder__projects = $this->CreateFolder($service, $contact_folder->id, '1.1 ПРОЕКТЫ');
        // TODO: Создать структуру contact_folder

        // ссылка на клиента
        $contact_fshortcut = $this->CreateShortcut($service, $project_folder_1->id, $contact_folder->id, '1.1 ПАПКА КЛИЕНТА');
        // $contact_fshortcut = $this->CreateShortcut($service, $project_folder_2->id, $contact_folder->id, '1.1 ПАПКА КЛИЕНТА');

        // ссылка на проект
        $project_fshortcut = $this->CreateShortcut($service, $contact_folder__projects->id, $project_folder_1->id, $project_folder_1->name);
        // $project_fshortcut = $this->CreateShortcut($service, $contact_folder__projects->id, $project_folder_2->id, $project_folder_2->name);

        

        // $service->files->get($project_fshortcut->id) Получить

        // Изменить имя 
        // $folder = new \Google_Service_Drive_DriveFile();
        // $folder->setName('ввв');
        // $service->files->update($contacts_folder_id, $folder); 
        
        dd('JR');






        exit;

        $fields = [];
        for ($i=0, $run=true; $run ; $i++) { 

            $res = $this->api->execute("/api/v4/leads/custom_fields?page=$i&limit=50");

            $fields  = array_merge($fields, $res->_embedded->custom_fields);

            if (!isset($res->_links->next)){
                $run = false;
            }
        }

        $resuscitation_id = null;
        foreach ($fields as $field) {
            switch ($field->code) {
                case 'RESUSCITATION': $resuscitation_id = $field->id; break;
                default: break;
            }
        }

        if (!$resuscitation_id) {
            $res = $this->api->execute('/api/v4/leads/custom_fields', 'post', [
                'code' => 'RESUSCITATION',
                'name' => 'Реанимация',
                'type' => 'checkbox'
            ]);
            //$resuscitation_id = ...
        }

        $lead = $this->api->execute("/api/v4/leads/$lead_id");


        $custom_fields = [];
        foreach ($lead->custom_fields_values as $field) {
            $custom_fields[$field->field_id] = [
                'code' => $field->field_code,
                'type' => $field->field_type,
                'values' => $field->values,
            ];
        }

        $send_in_resuscitation = false;

        if ( isset($custom_fields[$resuscitation_id]) and !!($custom_fields[$resuscitation_id]['values'][0]->value) ) {
            $send_in_resuscitation = true;
        }

        if ($send_in_resuscitation) {
            $this->CheckingClosing($lead);
        }

        return 'ok';
    }


    private function CreateFolder($service, $parent_id, $name) {

        $folder = new \Google_Service_Drive_DriveFile([
            'parents' => [$parent_id],
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder'
        ]);

        return $service->files->create($folder);
    }


    private function CreateShortcut($service, $parent_id, $folder_id, $name) {

        $shortcut = new \Google_Service_Drive_DriveFile([
            'parents' => [$parent_id],
            'name' => $name,
            'shortcutDetails' => [
                'targetId' => $folder_id
            ],
            'mimeType' => 'application/vnd.google-apps.shortcut'
        ]);

        return $service->files->create($shortcut);
    }

    /**
     * Проверка причины закрытия
     */
    private function CheckingClosing($lead)
    {
        $loss_reasons_ids = [
            //4074595, // Дубль
            //5250691, // Не клиент -- Не лид
            //4105564, // Некорректный лид -- Убрать. Замена НЕ ЛИД
            //4105519, // Ошибочное обращение -- ?
            4074598, // Не дозвонились (лид) -- Убрать заменить на НЕ БЕРЕТ ТРУБКУ
            //4074592, // Не квалифицирован -- ?
            4071649, // Слишком дорого -- с
            //4074610, // Дорого в глобальном смысле --Убрать заменить на ДОРОГО/НЕТ ДЕНЕГ
            4071655, // Не устроили условия -- ?
            4071652, // Пропала потребность -- Убрать. Замена НЕ АКТУАЛЬНО/ВОЗМОЖНО ПОЗЖЕ
            //4074607, // Выбрал конкурентов
            //4074601, // Выбрал другой продукт --?
            //4104691, // Спам
            5297755, // Нет реакции на кп
            //5347657, // Не смогли выполнить производство // -- ВОЗВРАТ
        ];
        
        if ( in_array($lead->loss_reason_id, $loss_reasons_ids) ) {
            $pipeline = $this->api->execute('/api/v4/leads/pipelines/' .env('AMO_PIPELINE_RESUSCITATION_ID'));

            $res = $this->api->execute("/api/v4/leads/$lead->id", 'patch', [
                'status_id' => $pipeline->_embedded->statuses[1]->id,
                'custom_fields_values' => [
                    0 => [
                        'field_code' => 'RESUSCITATION',
                        'values' => [
                            [
                                'value' =>  false
                            ]
                        ]
                    ]
                ]
            ]);
        }
    }
}

