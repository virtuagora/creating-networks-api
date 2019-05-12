<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GroupType extends Model
{
    public $incrementing = false;
    protected $table = 'group_types';
    protected $visible = [
        'id', 'name', 'description', 'role_policy',
        'public_schema', 'private_schema', 'allowed_relations',
    ];
    protected $fillable = [
        'id', 'name', 'description', 'role_policy', 'role_id',
        'public_schema', 'private_schema', 'allowed_relations',
    ];
    protected $casts = [
        'public_schema' => 'array',
        'private_schema' => 'array',
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
