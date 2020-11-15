<?php

namespace App\Models\kivi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KiviModeratorLink extends Model
{
    use HasFactory;
    protected $table = 'kivi_moderator_links';

    protected $dates = [
        'expiry_date',
    ];
    
    public function isExpired()
    {
        return  $this->expiry_date->addHours(24)->isPast();
    }
}
