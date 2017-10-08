<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['telegram_auth_data','auth_key','created_at', 'updated_at','telegram_first_name','telegram_last_name'];

    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value ?? "";
    }
}
