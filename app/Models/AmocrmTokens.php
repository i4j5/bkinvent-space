<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmocrmTokens extends Model
{
    use HasFactory;

    protected $table = 'amocrm_tokens';

    protected $fillable = [
       'type', 
       'value',
       'expires',
       'active',
    ];
}
