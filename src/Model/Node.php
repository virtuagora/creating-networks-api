<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Node extends Model
{
    // use SoftDeletes;

    protected $table = 'nodes';
    protected $dates = ['deleted_at', 'close_date'];
    protected $visible = [
        'id', 'title', 'points', 'author', 'created_at',
    ];
    protected $casts = [
        'meta' => 'array',
        'unlisted' => 'boolean',
        'supporter' => 'boolean',
        'close_date' => 'datetime',
        'meta' => 'array',
    ];

    public function author()
    {
        return $this->belongsTo('App\Model\Subject');
    }

    public function setMeta($key, $value = null)
    {
        $temp = $this->meta ?? [];
        if (is_array($key)) {
            $temp = array_merge($temp, $key);
        } else {
            $temp[$key] = $value;
        }
        $this->meta = $temp;
    }
}
