<?php

namespace App\Models;

use App\Models\WorldHeritage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Country extends Model
{
    protected $table = 'countries';
    protected $connection = 'mysql';

    protected $fillable = [
        'state_party_code',
        'name_en',
        'name_jp',
        'region'
    ];

    protected $primaryKey = 'state_party_code';
    public $incrementing = false;
    protected $keyType = 'string';

    public function worldHeritageSites(): BelongsToMany
    {
        return $this->belongsToMany(
            WorldHeritage::class,
            'site_state_parties',
            'state_party_code',
            'world_heritage_site_id',
            'state_party_code',
            'id'
        )->withPivot(['is_primary','inscription_year'])
            ->withTimestamps();
    }
}
