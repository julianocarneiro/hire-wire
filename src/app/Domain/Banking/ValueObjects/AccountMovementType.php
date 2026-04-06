<?php

namespace App\Domain\Banking\ValueObjects;

enum AccountMovementType: string
{
    case Deposit = 'deposit';
    case MonthlyAdjustment = 'monthly_adjustment';
}
