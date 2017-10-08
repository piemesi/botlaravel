<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $fillable = ['title', 'manager_account','telegram_id'];

    public function company(){
        return $this->belongsTo(Company::class);
    }
}
