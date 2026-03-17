<?php

namespace App\Packages\Domains\StudyRegion;

use App\Enums\StudyRegion;

class ExceptionalStudyRegions
{
    /**
     * UNESCO site_id based overrides.
     *
     * @var array<int, StudyRegion>
     */
    public const SITE_ID_TO_REGION = [
        148 => StudyRegion::ASIA,
    ];
}