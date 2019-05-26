<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Auth\ObjectInterface;
use App\Auth\SubjectInterface;

class Group extends Model implements ObjectInterface
{
    protected $table = 'groups';
    protected $visible = [
        'id', 'name', 'description', 'quota', 'created_at', 'public_data',
    ];
    protected $fillable = [
        'name', 'description', 'quota', 'public_data', 'private_data',
    ];
    protected $casts = [
        'public_data' => 'array',
        'private_data' => 'array',
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
