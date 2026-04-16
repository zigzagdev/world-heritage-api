<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorldHeritageDescription extends Model
{
    use SoftDeletes;

    protected $table = 'world_heritage_descriptions';
    protected $primaryKey = 'id';

    protected $fillable = [
        'world_heritage_site_id',
        'short_description_en',
        'short_description_ja',
        'description_en',
        'description_ja'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function worldHeritageSite(): BelongsTo
    {
        return $this->belongsTo(WorldHeritage::class, 'world_heritage_site_id', 'id');
    }
}
