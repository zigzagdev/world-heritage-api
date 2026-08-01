<?php

namespace App\Packages\Domains\User;

enum AgeRange: string
{
    case Teens     = 'teens';
    case Twenties  = '20s';
    case Thirties  = '30s';
    case Forties   = '40s';
    case Fifties   = '50s';
    case SixtyPlus = '60plus';
}