<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorldHeritage extends Model
{
    use SoftDeletes;

    protected $table = 'world_heritage_sites';
    public $incrementing = false;
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
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
        'unesco_site_url'
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_endangered'  => 'boolean',
        'year_inscribed' => 'integer',
        'area_hectares'  => 'float',
        'buffer_zone_hectares' => 'float',
        'latitude'  => 'float',
        'longitude' => 'float',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(
            Country::class,
            'site_state_parties',
            'world_heritage_site_id',
            'state_party_code',
            'id',
            'state_party_code'
        )->withPivot(['is_primary','inscription_year']);
    }

    public function Images(): HasMany
    {
        return $this->hasMany(Image::class, 'world_heritage_id', 'id');
    }
}
