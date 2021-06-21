<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallTrackerPhoneNumbers extends Model
{
    use HasFactory;

    protected $fillable = [
        'number', 
        'tag', 
        'static', 
        'visit_id',
        'default_source',
        'reservation_at',
    ];
}
