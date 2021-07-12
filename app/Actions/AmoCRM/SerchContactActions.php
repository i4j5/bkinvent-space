<?php

namespace App\Actions\AmoCRM;
use App\Actions\AmoCRM\RequestActions;

class SerchContactActions
{
    public function execute($phone)
    {

        $amoCRM = new RequestActions;

        $contact_id = null;
        $leads = [];

        if (strlen($phone) < 10) {
            return $contact_id;
        } 

        $query = str_replace(['+', '(', ')', ' ', '-', '_', '*', '–'], '', $phone);
        $query = substr($query, 1);

        $res = $amoCRM->execute("/api/v4/contacts?query=$query&with=leads");

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
                        if (isset($contact->_embedded) and isset($contact->_embedded->leads)) {
                        
                            foreach ($contact->_embedded->leads as $lead) {
                                $res = $amoCRM->execute("/api/v4/leads/$lead->id");

                                if (isset($res->id) and $res->id == $lead->id) {
                                    $leads[] = [
                                        'id' => $res->id,
                                        'is_deleted' => $res->is_deleted,
                                        'closed_at' => $res->closed_at,
                                        'responsible_user_id' => $res->responsible_user_id,
                                    ];
                                }
                            }
                        }
                        break 2;
                    }
                }
            }
        }

        return [
            'id' => $contact_id,
            'leads' => $leads, 
        ];
    }
}
