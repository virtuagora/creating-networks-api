<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'groups';
    protected $visible = [
        'id', 'name', 'description', 'quota', 'created_at', 'data',
    ];
    protected $fillable = [
        'name', 'description', 'quota', 'data',
    ];
    protected $casts = [
        'data' => 'array',
    ];

    public function subject()
    {
        return $this->hasOne('App\Model\Subject');
    }

    public function city()
    {
        return $this->belongsTo('App\Model\City');
    }

    public function parent()
    {
        return $this->belongsTo('App\Model\Group');
    }

    public function members()
    {
        return $this->belongsToMany('App\Model\Subject', 'subject_group')->withPivot('relation', 'title');
    }
}
