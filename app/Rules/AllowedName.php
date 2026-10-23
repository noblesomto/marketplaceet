<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AllowedName implements Rule
{
    protected $bannedNames = [
        'market',
        'marketplace',
        'marketplace naija',
        'marketplace ng',
        'admin',
        'administrator',
        'moderator',
        'support',
        'system',
        'official',
    ];

    protected $failedName;

    public function passes($attribute, $value)
    {
        // Check for emojis
        if (preg_match('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{1F900}-\x{1F9FF}]/u', $value)) {
            $this->failedName = 'emojis';
            return false;
        }

        // Check for banned names
        foreach ($this->bannedNames as $banned) {
            if (preg_match('/\b' . preg_quote($banned, '/') . '\b/i', $value)) {
                $this->failedName = $banned;
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        if ($this->failedName === 'emojis') {
            return 'The :attribute cannot contain emojis.';
        }

        return 'The :attribute contains a restricted word: "' . $this->failedName . '".';
    }
}
