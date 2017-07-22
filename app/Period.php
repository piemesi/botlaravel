<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Period extends Model
{
    use SoftDeletes;

    protected $table='periods';

    protected $fillable = ['start'];

    public function task(){
        return $this->belongsTo(Task::class);
    }
}
