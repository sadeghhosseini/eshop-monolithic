<?php

namespace App\Rules;

use App\Models\Image;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Storage;

/**
 * Both image record in db and image file on filesystem must exist 
 */
class ImageExists implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($imageId)
    {
        $this->imageId = $imageId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value is the path of an image
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //check if path exists in the database
        $this->recordExists = Image::where('id', $this->imageId)
            ->where('path', $value)->exists();
        //check if image with that path exists on the filesystem
        $this->fileExists = Storage::exists($value);

        return $this->recordExists && $this->fileExists;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $message = $this->recordExists ? '' : 'image record does not exist';
        $message = ($this->recordExists && $this->fileExists) ? ' and ' : '';
        $message .= $this->fileExists ? '' : 'image file does not exist';
        return $message;
    }
}
