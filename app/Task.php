<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $table='tasks';

    protected $fillable = ['title', 'text','hide', 'active', 'minutes_to_read', 'preview', 'need_link'];

    protected $casts = [
        'need_link' => 'integer',
    ];

    public function setNeedLinkAttribute($value)
    {
        $this->attributes['need_link'] = $value === 'true' || $value === true || $value === 1 ? 1 : 0;
    }

    public function setActiveAttribute($value)
    {
        $this->attributes['active'] = $value === 'true' || $value === true || $value === '1' || $value === 1 ? 1 : 0;
    }

    public function setHideAttribute($value)
    {
        $this->attributes['hide'] = $value === 'true' || $value === true || $value === 1 ? 1 : 0;
    }

    public function setTextAttribute($value)
    {
        $this->attributes['text'] = $value ?? '';
    }


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
