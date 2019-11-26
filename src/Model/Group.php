<?php

namespace App\Model;

use App\Auth\ObjectInterface;
use App\Auth\SubjectInterface;

class Group extends ResourceModel implements ObjectInterface
{
    protected $table = 'groups';
    protected $visible = [
        'id', 'name', 'description', 'quota', 'created_at', 'public_data',
        'terms', 'countries', 'pivot', 'city', 'group_type', 'subject',
        'pictures',
    ];
    protected $fillable = [
        'name', 'description', 'quota', 'public_data', 'private_data',
    ];
    protected $casts = [
        'public_data' => 'array',
        'private_data' => 'array',
        'pictures' => 'array',
    ];

    public function subject()
    {
        return $this->hasOne('App\Model\Subject', 'group_id');
    }

    public function group_type()
    {
        return $this->belongsTo('App\Model\GroupType', 'group_type_id');
    }

    public function city()
    {
        return $this->belongsTo('App\Model\City');
    }

    public function parent()
    {
        return $this->belongsTo('App\Model\Group');
    }

    public function countries()
    {
        return $this->belongsToMany(
            'App\Model\Country', 'group_country', 'group_id', 'country_id'
        );
    }

    public function members()
    {
        return $this->belongsToMany(
            'App\Model\Subject', 'subject_group', 'group_id', 'subject_id'
        )->withPivot('relation', 'title');
    }

    public function terms()
    {
        return $this->morphToMany('App\Model\Term', 'object', 'term_object')->withTimestamps();
    }

    public function relationsWith(SubjectInterface $subject)
    {
        $user = $this->members()->where('subject_id', $subject->id)->first();
        return isset($user) ? [$user->pivot->relation] : [];
    }
}
