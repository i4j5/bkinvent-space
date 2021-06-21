<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_visit', 

        'amocrm_visitor_uid',
        
        'landing_page', 
        'referrer',

        'utm_source', 
        'utm_medium', 
        'utm_campaign', 
        'utm_term', 
        'utm_content',
        'utm_referrer',

        'metrika_client_id',
        'google_client_id',
    ];
}
