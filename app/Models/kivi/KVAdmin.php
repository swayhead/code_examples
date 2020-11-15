<?php

namespace App\Models\kivi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KVAdmin extends Model
{
    use HasFactory;
    protected $table = 'kg_addresses';
    const CREATED_AT = 'registration_date';
    const UPDATED_AT = 'letzte_aenderung';

    protected $casts = [
        'modules' => 'array'
    ];

    protected $dates = [
        'paid_till',
    ];

    public function events()
    {
        return $this->hasMany(KVEvent::class, 'provider_id');
    }

    public function hasModule($moduleName)
    {
        return in_array($moduleName, $this->modules);
    }

    public function isValidMember($from = null)
    {
        if (is_null($from)) {
            $from = now();
        }
        return ($this->isFullMember() && !$this->blocked &&
            (!$this->cancelled || $this->paid_till->diffInDays($from, false) <= 0));
    }

    public function isFullMember()
    {
        return !empty($this->member_no);
    }
}
