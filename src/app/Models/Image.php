<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $table = 'images';
    protected $connection = 'mysql';
    protected $primaryKey = 'id';

    protected $fillable = [
        'disk',
        'path',
        'width',
        'height',
        'format',
        'checksum',
        'sort_order',
        'alt',
        'credit',
        'world_heritage_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function worldHeritage()
    {
        return $this->belongsTo(WorldHeritage::class, 'world_heritage_id', 'id');
    }
}
