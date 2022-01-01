<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;

class ForeignKeyExists implements Rule
{
    private $ReferencedTableModelClass;
    private $value;
    private $nonExistingFks;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($ReferencedTableModelClass)
    {
        $this->ReferencedTableModelClass = $ReferencedTableModelClass;
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
        $this->value = $value;
        if (is_array($value) || $value instanceof Collection) {
            $fks = $value;

            $this->nonExistingFks = [];
            foreach($fks as $fk) {
                if (!$this->ReferencedTableModelClass::where('id', $fk)->exists()) {
                    $this->nonExistingFks[] = $fk;
                }
            }
            return empty($this->nonExistingFks);
        } else {
            return $this->ReferencedTableModelClass::where('id', $value)->exists();
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $tableName = (new $this->ReferencedTableModelClass())->getTable();
        if (is_array($this->value) || $this->value instanceof Collection) {
            $values = collect($this->nonExistingFks);
            $values = $values->implode(', ');
            return "The following values in :attribute array do not match any ${tableName} record: ${values}";
        } else {
            return ":attributes with value of :input does not match any ${tableName} record";
        }
    }
}
