<?php

namespace App\Enums;

enum RestaurantStatus: string
{
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Pending = 'pending';
}
