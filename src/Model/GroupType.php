<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GroupType extends Model
{
    public $incrementing = false;
    protected $table = 'group_types';
    protected $visible = [
        'id', 'name', 'description', 'role_policy',
        'schema', 'allowed_relations',
    ];
    protected $fillable = [
        'id', 'name', 'description', 'role_policy', 'role_id',
        'schema', 'allowed_relations',
    ];
    protected $casts = [
        'schema' => 'array',
        'allowed_relations' => 'array',
    ];

    public function groups()
    {
        return $this->hasMany('App\Model\Group');
    }

    public function role()
    {
        return $this->belongsTo('App\Model\Role');
    }
}
