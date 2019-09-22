<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Auth\ObjectInterface;
use App\Auth\SubjectInterface;

class Node extends Model implements ObjectInterface
{
    protected $table = 'nodes';
    protected $visible = [
        'id', 'title', 'content', 'points', 'author', 'created_at', 'unlisted',
        'public_data', 'subjects', 'pivor', 'node_type',
    ];
    protected $fillable = [
        'title', 'content', 'unlisted', 'public_data', 'private_data',
    ];
    protected $casts = [
        'public_data' => 'array',
        'private_data' => 'array',
        'close_date' => 'datetime',
        'unlisted' => 'boolean'
    ];

    public function author()
    {
        return $this->belongsTo('App\Model\Subject');
    }

    public function node_type()
    {
        return $this->belongsTo('App\Model\NodeType', 'node_type_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(
            'App\Model\Subject', 'node_subject', 'node_id', 'subject_id'
        )->withPivot('relation', 'value');
    }

    public function relationsWith(SubjectInterface $subject)
    {
        return $subject->id == $this->author_id ? ['author'] : [];
    }
}
