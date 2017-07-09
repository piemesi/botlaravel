<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $table='tasks';

    protected $fillable = ['title', 'text','hide', 'active'];


    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at']; //

    public function periods(){
        return $this->hasMany(Period::class);
    }

}
