<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Actions\AmoCRM\RequestActions;
use App\Actions\GetUserGoogleClientActions;
use App\Models\User;

class GoogleDriveFoldersController extends Controller
{
    private $amoCRM;
    private $serviceGoogleDrive;

    public function __construct()
    {
        $this->amoCRM = new RequestActions;

        $user = User::where('email', env('ROOT_EMAIL'))->first();
        $googleClient = (new GetUserGoogleClientActions)->execute($user);
        $googleClient->addScope(\Google_Service_Drive::DRIVE);
        $this->serviceGoogleDrive = new \Google_Service_Drive($googleClient);

    }


    public function CreateProjectFolder(Request $request) {

        $lead_id = $request->input('id');

        $lead = $this->amoCRM->execute("/api/v4/leads/$lead_id?with=contacts");

        $company_id = !!count($lead->_embedded->companies) ? $lead->_embedded->companies[0]->id : null;
        
        $contacts = $lead->_embedded->contacts;
        $main_contact_id = null;
        foreach ($contacts as $contact) {
            if ($contact->is_main) {
                $main_contact_id = $contact->id;
            }
        }

        $client = false;
        $clientFields = [];

        if ($company_id) {
            $client = $this->amoCRM->execute("/api/v4/companies/$company_id");

            for ($i=0, $run=true; $run ; $i++) { 
    
                $res = $this->amoCRM->execute("/api/v4/companies/custom_fields?page=$i&limit=50");
    
                $clientFields  = array_merge($clientFields, $res->_embedded->custom_fields);
    
                if (!isset($res->_links->next)){
                    $run = false;
                }
            }

        } else if($main_contact_id) {
            $client = $this->amoCRM->execute("/api/v4/contacts/$main_contact_id");

            for ($i=0, $run=true; $run ; $i++) { 
    
                $res = $this->amoCRM->execute("/api/v4/contacts/custom_fields?page=$i&limit=50");
    
                $clientFields  = array_merge($clientFields, $res->_embedded->custom_fields);
    
                if (!isset($res->_links->next)){
                    $run = false;
                }
            }
        }

        $amoCRMClientFolderGoogleDrive = null;
        $amoCRMClientFolderGoogleDriveProjects = null;



        if ($client) {

            foreach ($clientFields as $field) {
                switch ($field->code) {
                    case 'FOLDER_GOOGLE_DRIVE': $amoCRMClientFolderGoogleDrive = $field->id; break;
                    case 'FOLDER_GOOGLE_DRIVE__PROJECTS': $amoCRMClientFolderGoogleDriveProjects = $field->id; break;
                    default: break;
                }
            }

            if (!$amoCRMClientFolderGoogleDrive) {

                $data = [
                    'code' => 'FOLDER_GOOGLE_DRIVE',
                    'name' => 'Папка клиента',
                    'type' => 'url'
                ];

                if ($main_contact_id) {
                    $res = $this->amoCRM->execute('/api/v4/contacts/custom_fields', 'post', $data);
                    $amoCRMClientFolderGoogleDrive = $res->id;
                } else if ($company_id) {
                    $res = $this->amoCRM->execute('/api/v4/companies/custom_fields', 'post', $data);
                    $amoCRMClientFolderGoogleDrive = $res->id;
                }

            }

            if (!$amoCRMClientFolderGoogleDriveProjects) {

                $data = [
                    'code' => 'FOLDER_GOOGLE_DRIVE__PROJECTS',
                    'name' => 'Папка проектов',
                    'type' => 'url'
                ];

                if ($main_contact_id) {
                    $res = $this->amoCRM->execute('/api/v4/contacts/custom_fields', 'post', $data);
                    $amoCRMClientFolderGoogleDriveProjects = $res->id;
                } else if ($company_id) {
                    $res = $this->amoCRM->execute('/api/v4/companies/custom_fields', 'post', $data);
                    $amoCRMClientFolderGoogleDriveProjects = $res->id;
                }

            }
            
        } else {
            return 'error';
        }

        $clientLink = false;
        $clientProjectsLink = false;

        if ( isset($client->custom_fields_values) ) {
            foreach ($client->custom_fields_values as $field) {
                if ($amoCRMClientFolderGoogleDrive == $field->field_id) {
                    $clientLink = $field->values[0]->value;
                } else if ($amoCRMClientFolderGoogleDriveProjects == $field->field_id) {
                    $clientProjectsLink = $field->values[0]->value;
                }
            }
        }

        $client_folder = null;
        $client_folder__projects = null;

        if (!$clientLink and !$clientProjectsLink) {
            $client_folder = $this->CreateFolder(env('GOOGLE_USERS_FOLDER_ID'), "$client->name #$client->id");
            $client_folder__projects = $this->CreateFolder($client_folder->id, '1.1 ПРОЕКТЫ');
            $this->CreateFolder($client_folder->id, '1.2 ДОКУМЕНТЫ');
            

            $clientLink = "https://drive.google.com/open?id=$client_folder->id";
            $clientProjectsLink = "https://drive.google.com/open?id=$client_folder__projects->id";

            $data = [
                'custom_fields_values' => []
            ];

            $data['custom_fields_values'][] = [
                'field_code' => 'FOLDER_GOOGLE_DRIVE',
                'values' => [
                    [
                        'value' =>  $clientLink
                    ]
                ]
            ];

            $data['custom_fields_values'][] = [
                'field_code' => 'FOLDER_GOOGLE_DRIVE__PROJECTS',
                'values' => [
                    [
                        'value' =>  $clientProjectsLink
                    ]
                ]
            ];

            if ($company_id) {
                $this->amoCRM->execute("/api/v4/companies/$company_id", 'patch', $data);
            } else if ($main_contact_id) {
                $this->amoCRM->execute("/api/v4/contacts/$main_contact_id", 'patch', $data);
            }
        } else {
            $client_folder = $this->serviceGoogleDrive->files->get( explode("?id=", $clientLink)[1] );
            $client_folder__projects = $this->serviceGoogleDrive->files->get( explode("?id=", $clientProjectsLink)[1] );
        }


        $lead_data = [
            'custom_fields_values' => []
        ];

        // TODO: Проверка пустые поля или нет!

        $project_folder = $this->CreateFolder(env('GOOGLE_PROJECTS_FOLDER_ID'), "$lead->name #$lead->id");
        $project_folder__1_2 = $this->CreateFolder($project_folder->id, '1.2 ПАПКА МЕНЕДЖЕРА');
        $project_folder__1_4 = $this->CreateFolder($project_folder->id, '1.4 ИСХОДНЫЕ ДОКУМЕНТЫ');
        $this->CreateFolder($project_folder->id, '1.5 УСЛУГА');
        $project_folder__1_3 = $this->CreateFolder($project_folder->id, '1.3 ОБСЛЕДОВАНИЕ ОБЪЕКТА');
        $this->CreateFolder($project_folder__1_3->id, '1.3.1 ФОТООТЧЁТ');
        $this->CreateFolder($project_folder__1_3->id, '1.3.2 РЕЗЮМЕ');


        $client_fshortcut = $this->CreateShortcut($project_folder->id, $client_folder->id, '1.1 ПАПКА КЛИЕНТА');
        $project_fshortcut = $this->CreateShortcut($client_folder__projects->id, $project_folder->id, $project_folder->name);

        $lead_data['custom_fields_values'][] = [
            'field_id' => 75429,
            'values' => [
                [
                    'value' =>  "https://drive.google.com/open?id=$project_folder->id"
                ]
            ]
        ];

        $lead_data['custom_fields_values'][] = [
            'field_id' => 75431,
            'values' => [
                [
                    'value' =>  "https://drive.google.com/open?id=$project_folder__1_2->id"
                ]
            ]
        ];

        $lead_data['custom_fields_values'][] = [
            'field_id' => 75433,
            'values' => [
                [
                    'value' =>  "https://drive.google.com/open?id=$project_folder__1_3->id"
                ]
            ]
        ];

        $lead_data['custom_fields_values'][] = [
            'field_id' => 75435,
            'values' => [
                [
                    'value' =>  "https://drive.google.com/open?id=$project_folder__1_4->id"
                ]
            ]
        ];

        $lead_data['custom_fields_values'][] = [
            'field_id' => 509647,
            'values' => [
                [
                    'value' =>  $project_fshortcut->id
                ]
            ]
        ];

        $this->amoCRM->execute("/api/v4/leads/$lead_id", 'patch', $lead_data);
    
        return 'ok';

    }

    public function CreateСlientFolder() {

    }

    public function RenameСlientFolder(Request $request) {


        $contacts = $request->input('contacts');

        $id =  $contacts['update'][0]['id'];
        $name = isset($contacts['update'][0]['name']) ? $contacts['update'][0]['name'] : '';
        $custom_fields = isset($contacts['update'][0]['custom_fields']) ? $contacts['update'][0]['custom_fields'] : [];

        $folder_id = null;

        foreach ($custom_fields as $field) {
            switch ($field['id']) {
                case '509695': 
                    $folder_id = explode("?id=", $field['values'][0]['value'])[1]; 
                    break;
                case '509703': 
                    $folder_id = explode("?id=", $field['values'][0]['value'])[1]; 
                    break;
                default: break;
            }
        }

        $new_name = "$name #$id";


        if ($folder_id) {
            $folder = $this->serviceGoogleDrive->files->get($folder_id);

            if ($folder->name != $new_name) {
                $this->RenameFile($folder_id, $new_name );
            }

        }

        return 'ok';

    }

    public function RenameProjectFolder(Request $request) {

        $lead = $request->input('leads');

        $id =  $lead['update'][0]['id'];
        $name =  isset($lead['update'][0]['name']) ? $lead['update'][0]['name'] : '';
        $custom_fields = isset($lead['update'][0]['custom_fields']) ? $lead['update'][0]['custom_fields'] : [];

        $folder_id = null;
        $fshortcut_id = null;

        foreach ($custom_fields as $field) {
            switch ($field['id']) {
                case '75429': 
                    $folder_id = explode("?id=", $field['values'][0]['value'])[1]; 
                    break;
                case '509647':
                    $fshortcut_id = $field['values'][0]['value'];
                    break;
                default: break;
            }
        }

        $new_name = "$name #$id";


        if ($folder_id) {
            $folder = $this->serviceGoogleDrive->files->get($folder_id);

            if ($folder->name != $new_name) {
                $this->RenameFile($folder_id, $new_name);
                if ($fshortcut_id) {
                    $this->RenameFile($fshortcut_id, $new_name);
                }
            }
        }

        return 'ok';
        
    }

    private function CreateFolder($parent_id, $name) {

        $folder = new \Google_Service_Drive_DriveFile([
            'parents' => [$parent_id],
            'name' => str_replace('"', "'", $name),
            'mimeType' => 'application/vnd.google-apps.folder'
        ]);

        return $this->serviceGoogleDrive->files->create($folder);
    }


    private function CreateShortcut($parent_id, $folder_id, $name) {

        $shortcut = new \Google_Service_Drive_DriveFile([
            'parents' => [$parent_id],
            'name' => str_replace('"', "'", $name),
            'shortcutDetails' => [
                'targetId' => $folder_id
            ],
            'mimeType' => 'application/vnd.google-apps.shortcut'
        ]);

        return $this->serviceGoogleDrive->files->create($shortcut);
    }

    private function RenameFile($file_id, $name) {
        $file = new \Google_Service_Drive_DriveFile();
        $file->setName( str_replace('"', "'", $name) );
        return $this->serviceGoogleDrive->files->update($file_id, $file); 
    }
}