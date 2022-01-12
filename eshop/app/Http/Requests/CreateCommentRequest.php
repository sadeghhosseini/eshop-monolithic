<?php

namespace App\Http\Requests;

use App\Models\Comment;
use App\Rules\ForeignKeyExists;
use Illuminate\Foundation\Http\FormRequest;

class CreateCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'content' => 'required|min:1|max:500',
            'parent_id' => ['nullable', new ForeignKeyExists(Comment::class)],
        ];
    }
}
