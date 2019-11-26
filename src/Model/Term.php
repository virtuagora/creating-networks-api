<?php

namespace App\Model;

use App\Auth\ObjectInterface;
use App\Auth\SubjectInterface;

class Term extends ResourceModel implements ObjectInterface
{
    protected $table = 'terms';
    protected $visible = [
        'id', 'name', 'data', 'localization', 'count', 'taxonomy_id',
    ];
    protected $fillable = [
        'id', 'name', 'data', 'localization', 'taxonomy_id',
    ];
    protected $casts = [
        'data' => 'array',
        'localization' => 'array',
    ];

    public function taxonomy()
    {
        return $this->belongsTo('App\Model\Taxonomy');
    }

    public function groups()
    {
        return $this->morphedByMany('App\Model\Group', 'object', 'term_object')->withTimestamps();
    }

    public function relationsWith(SubjectInterface $subject)
    {
        return [];
    }
}
