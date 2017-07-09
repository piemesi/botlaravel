<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    protected $table='periods';

    protected $fillable = ['start'];

    public function task(){
        return $this->belongsTo(Task::class);
    }
}
