<?php

namespace App\Packages\Domains\User\Subscription;

enum SubscriptionTier: string
{
    case Free    = 'free';
    case Premium = 'premium';
}