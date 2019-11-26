<?php

namespace App\Model;

class Log extends ResourceModel
{
    protected $table = 'logs';
    protected $visible = [
        'id', 'subject_id', 'proxy_id', 'action_id',
        'object_type', 'object_id', 'parameters'
    ];
    protected $fillable = [
        'parameters',
    ];
    protected $casts = [
        'parameters' => 'array',
    ];

    public function subject()
    {
        return $this->belongsTo('App\Model\Subject', 'subject_id');
    }

    public function action()
    {
        return $this->belongsTo('App\Model\Action', 'action_id');
    }

    public function object()
    {
        return $this->morphTo();
    }
}
