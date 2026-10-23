<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class EthiopianPhoneNumber implements Rule
{
    public function passes($attribute, $value)
    {
        // Only accept exactly 10 digits starting with 0 — no spaces, dashes, or +251
        return (bool) preg_match('/^0(7|9)[0-9]{8}$/', $value);
    }

    public function message()
    {
        return 'Please enter a valid Ethiopian phone number in the format 0912345678 (10 digits, starting with 07 or 09).';
    }
}
