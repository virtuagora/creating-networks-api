<?php namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GroupType extends Model
{
    protected $table = 'group_type';
    protected $visible = [
        'id', 'names', 'description', 'meta',
    ];
    protected $casts = [
        'meta' => 'array',
    ];

    public function groups()
    {
        return $this->hasMany('App\Model\Group');
    }
}
