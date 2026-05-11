<?php

namespace App\Http\Requests\Study;

use Illuminate\Foundation\Http\FormRequest;

class StartStudySessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'mode' => ['nullable', 'string', 'max:30'],
        ];
    }
}
