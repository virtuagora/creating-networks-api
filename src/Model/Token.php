<?php namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Token extends Model
{
    protected $table = 'tokens';
    protected $visible = [
        'id', 'type', 'finder', 'token', 'data', 'expires_at',
    ];
    protected $fillable = [
        'type', 'finder', 'token', 'data', 'expires_at', 'subject_id',
    ];
    protected $casts = [
        'data' => 'array',
        'expires_at' => 'datetime',
    ];
}
