<?php

namespace App\Domain\Banking\ValueObjects;

enum AccountType: string
{
    case Savings = 'savings';
    case Checking = 'checking';
    case Investments = 'investments';
}
