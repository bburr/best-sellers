<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

class IsbnLength implements InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        if (! in_array(strlen($value), [10, 13])) {
            $fail('The :attribute must be 10 or 13 digits long');
        }
    }
}
