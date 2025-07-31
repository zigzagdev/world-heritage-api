<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldHeritage extends Model
{
    protected $table = 'world_heritage_sites';
    protected $connection = 'mysql';

    protected $fillable = [
        'id',
        'unesco_id',
        'official_name',
        'name',
        'name_jp',
        'country',
        'region',
        'state_party',
        'category',
        'criteria',
        'year_inscribed',
        'area_hectares',
        'buffer_zone_hectares',
        'is_endangered',
        'latitude',
        'longitude',
        'short_description',
        'image_url',
        'unesco_site_url'
    ];

    protected $casts = [
        'criteria' => 'array',
    ];
}
