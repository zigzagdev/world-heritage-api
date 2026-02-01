<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    protected $table = 'world_heritage_site_images';
    protected $primaryKey = 'id';

    protected $fillable = [
        'url',
        'url_hash',
        'is_primary',
        'sort_order',
        'world_heritage_site_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function worldHeritage()
    {
        return $this->belongsTo(WorldHeritage::class, 'world_heritage_site_id', 'id');
    }
}
