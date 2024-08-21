<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoSpaces implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strpos($value, ' ') !== false) {
            $fail('Il campo :attribute non può contenere spazi.');
        }
    }
    
    public function message()
    {
        return 'Il campo :attribute non può contenere spazi.';
    }
}
