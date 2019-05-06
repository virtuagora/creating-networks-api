<?php namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use SoftDeletes;

    protected $table = 'groups';
    protected $visible = [
        'id', 'name', 'acronym', 'description', 'quota',
        'created_at', 'group_type', 'subject',
    ];
    protected $with = [
        'group_type', 'subject',
    ];
    protected $casts = [
        'deleted_at',
    ];

    public function subject()
    {
        return $this->belongsTo('App\Model\Subject');
    }

    public function users()
    {
        return $this->belongsToMany('App\Model\User', 'user_group')->withPivot('relation', 'title');
    }
}
