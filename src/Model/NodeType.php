<?php

namespace App\Model;

class NodeType extends ResourceModel
{
    public $incrementing = false;
    protected $table = 'node_types';
    protected $visible = [
        'id', 'name', 'description',
        'public_schema', 'private_schema', 'subtypes',
    ];
    protected $fillable = [
        'id', 'name', 'description',
        'public_schema', 'private_schema', 'subtypes',
    ];
    protected $casts = [
        'public_schema' => 'array',
        'private_schema' => 'array',
        'subtypes' => 'array',
    ];

    public function nodes()
    {
        return $this->hasMany('App\Model\Node');
    }
}
