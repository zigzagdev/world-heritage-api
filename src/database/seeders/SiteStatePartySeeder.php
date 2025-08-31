<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SiteStatePartySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $pivots = [
            ['state_party_code'=>'SVK','world_heritage_site_id'=>1133,'is_primary'=>true ,'inscription_year'=>2007,'created_at'=>$now,'updated_at'=>$now],
            ['state_party_code'=>'DEU','world_heritage_site_id'=>1133,'is_primary'=>false,'inscription_year'=>2011,'created_at'=>$now,'updated_at'=>$now],
            ['state_party_code'=>'UKR','world_heritage_site_id'=>1133,'is_primary'=>false,'inscription_year'=>2007,'created_at'=>$now,'updated_at'=>$now],
            ['state_party_code'=>'POL','world_heritage_site_id'=>1133,'is_primary'=>false,'inscription_year'=>2021,'created_at'=>$now,'updated_at'=>$now],
            ['state_party_code'=>'ROU','world_heritage_site_id'=>1133,'is_primary'=>false,'inscription_year'=>2017,'created_at'=>$now,'updated_at'=>$now],
            ['state_party_code'=>'CHN','world_heritage_site_id'=>1442,'is_primary'=>true ,'inscription_year'=>2014,'created_at'=>$now,'updated_at'=>$now],
            ['state_party_code'=>'KAZ','world_heritage_site_id'=>1442,'is_primary'=>false,'inscription_year'=>2014,'created_at'=>$now,'updated_at'=>$now],
            ['state_party_code'=>'KGZ','world_heritage_site_id'=>1442,'is_primary'=>false,'inscription_year'=>2014,'created_at'=>$now,'updated_at'=>$now],
            ['state_party_code'=>'UZB','world_heritage_site_id'=>1662,'is_primary'=>true ,'inscription_year'=>2023,'created_at'=>$now,'updated_at'=>$now],
            ['state_party_code'=>'TJK','world_heritage_site_id'=>1662,'is_primary'=>false,'inscription_year'=>2023,'created_at'=>$now,'updated_at'=>$now],
            ['state_party_code'=>'TKM','world_heritage_site_id'=>1662,'is_primary'=>false,'inscription_year'=>2023,'created_at'=>$now,'updated_at'=>$now],
            ['state_party_code'=>'JPN','world_heritage_site_id'=>661 ,'is_primary'=>true,'inscription_year'=>1993,'created_at'=>$now,'updated_at'=>$now],
            ['state_party_code'=>'JPN','world_heritage_site_id'=>688 ,'is_primary'=>true,'inscription_year'=>1994,'created_at'=>$now,'updated_at'=>$now],
            ['state_party_code'=>'JPN','world_heritage_site_id'=>662 ,'is_primary'=>true,'inscription_year'=>1993,'created_at'=>$now,'updated_at'=>$now],
            ['state_party_code'=>'JPN','world_heritage_site_id'=>663 ,'is_primary'=>true,'inscription_year'=>1993,'created_at'=>$now,'updated_at'=>$now],
            ['state_party_code'=>'JPN','world_heritage_site_id'=>1142,'is_primary'=>true,'inscription_year'=>2004,'created_at'=>$now,'updated_at'=>$now],
            ['state_party_code'=>'JPN','world_heritage_site_id'=>1418,'is_primary'=>true,'inscription_year'=>2013,'created_at'=>$now,'updated_at'=>$now],
        ];

        DB::table('site_state_parties')->upsert(
            $pivots,
            ['state_party_code','world_heritage_site_id'],
            ['is_primary','inscription_year','updated_at']
        );
    }
}
