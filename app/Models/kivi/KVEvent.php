<?php

namespace App\Models\kivi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KVEvent extends Model
{
    use HasFactory;
    protected $table = 'kg_events';
    const CREATED_AT = 'reg_date';
    
    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'save';
}
