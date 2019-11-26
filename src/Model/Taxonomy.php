<?php

namespace App\Model;

class Taxonomy extends ResourceModel
{
    public $incrementing = false;
    protected $table = 'taxonomies';
    protected $visible = [
        'id', 'name', 'description', 'schema', 'rules', 'localization',
    ];
    protected $fillable = [
        'id', 'name', 'description', 'schema', 'rules', 'localization',
    ];
    protected $casts = [
        'schema' => 'array',
        'rules' => 'array',
        'localization' => 'array',
    ];

    public function terms()
    {
        return $this->hasMany('App\Model\Term');
    }
}
