<?php

return [
    'algolia_app_id' => env('ALGOLIA_APP_ID'),
    'algolia_write_api_key' => env('ALGOLIA_WRITE_KEY'),
    'algolia_search_api_key' => env('ALGOLIA_SEARCH_KEY'),
    'algolia_admin_api_key' => env('ALGOLIA_ADMIN_KEY'),
    'algolia_index' => env('ALGOLIA_INDEX', 'world_heritages'),
    'algolia_fail_mode' => env('ALGOLIA_FAIL_MODE', 'throw'),
];