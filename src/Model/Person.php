<?php

namespace App\Model;

class Person extends ResourceModel
{
    protected $table = 'people';
    protected $visible = [
        'id', 'names', 'surnames', 'created_at', 'subject', 'terms',
    ];
    protected $fillable = [
        'names', 'surnames', 'email', 'facebook', 'phone', 'person_id',
    ];

    public function subject()
    {
        return $this->hasOne('App\Model\Subject');
    }

    public function terms()
    {
        return $this->morphToMany('App\Model\Term', 'object', 'term_object')->withTimestamps();
    }

    // public function groups()
    // {
    //     return $this->belongsToMany('App\Model\Group')->withPivot('relation', 'title');
    // }

    // public function setNamesAttribute($value)
    // {
    //     $this->attributes['names'] = $value;
    //     $fullname = $this->attributes['names'] . ' ' . $this->attributes['surnames'];
    //     $this->attributes['trace'] = mb_strtolower(trim($fullname));
    // }

    // public function setSurnamesAttribute($value)
    // {
    //     $this->attributes['surnames'] = $value;
    //     $fullname = $this->attributes['names'] . ' ' . $this->attributes['surnames'];
    //     $this->attributes['trace'] = mb_strtolower(trim($fullname));
    // }

    

    /*public function meta()
    {
        return $this->hasMany('App\Model\UserMeta');
    }*/
}
