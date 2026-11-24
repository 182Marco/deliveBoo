<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** stars and optional text of a review ("My orders"); who may write it is OrderPolicy@review */
class SaveReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        // a comment of only spaces is no comment (never an empty string in the db)
        $comment = $this->input('comment');
        $this->merge(['comment' => is_string($comment) && trim($comment) !== '' ? trim($comment) : null]);
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:'.config('reviews.comment_max')],
        ];
    }

    public function attributes(): array
    {
        return [
            'rating' => __('site.reviews.rating'),
            'comment' => __('site.reviews.comment'),
        ];
    }
}
