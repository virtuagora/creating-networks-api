<?php

namespace App\Model;

use App\Util\Utils;
use App\Auth\SubjectInterface;
use App\Auth\ObjectInterface;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model implements SubjectInterface, ObjectInterface
{
    protected $table = 'subjects';
    protected $visible = [
        'id', 'display_name', 'img_type', 'img_hash', 'type', 'points',
    ];
    protected $fillable = [
        'username', 'password', 'display_name', 'img_type', 'img_hash', 'type',
    ];

    public function person()
    {
        return $this->belongsTo('App\Model\Person');
    }

    public function group()
    {
        return $this->belongsTo('App\Model\Group');
    }

    public function groups()
    {
        return $this->belongsToMany('App\Model\Group', 'subject_group')->withPivot('relation', 'title');
    }

    public function roles()
    {
        return $this->belongsToMany('App\Model\Role', 'subject_role');
    }

    public function setPaswordAttribute($value)
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
    }

    public function setDisplayNameAttribute($value)
    {
        $this->attributes['display_name'] = $value;
        $this->attributes['trace'] = Utils::traceStr($value);
    }

    public function rolesList()
    {
        return $this->roles->pluck();
    }

    public function relationsWith(SubjectInterface $subject)
    {
        return (isset($this->id) && $this->id == $subject->id) ? ['self'] : [];
    }
}
