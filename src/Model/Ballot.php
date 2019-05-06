<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Ballot extends Model
{
    public $timestamps = false;
    protected $table = 'ballots';
    protected $visible = [
        'id', 'options', 'secret', 'total_votes'
    ];
    protected $casts = [
        'options' => 'array',
    ];

    public function node()
    {
        return $this->belongsTo('App\Model\Node');
    }
}
