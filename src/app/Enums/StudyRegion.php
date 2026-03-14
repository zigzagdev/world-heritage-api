<?php

namespace App\Enums;

enum StudyRegion: string
{
    case AFRICA = 'Africa';
    case ASIA = 'Asia';
    case EUROPE = 'Europe';
    case NORTH_AMERICA = 'North America';
    case SOUTH_AMERICA = 'South America';
    case OCEANIA = 'Oceania';
    case UNKNOWN = 'Unknown';
}