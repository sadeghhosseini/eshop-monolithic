<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class MinWord implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($min)
    {
        $this->min = $min;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $newValue = trim(preg_replace('/\s+/', ' ', $value));
        $wordCount = count(explode(" ", $newValue));
        return $wordCount >= $this->min;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be at least :value words';
    }
}
