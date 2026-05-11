<?php

namespace App\Http\Requests\Study;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionAnswerRequest extends FormRequest
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
            'study_session_id' => ['nullable', 'exists:study_sessions,id'],
            'question_id' => ['required', 'exists:questions,id'],
            'selected_option_id' => ['required', 'exists:question_options,id'],
            'response_time_ms' => ['nullable', 'integer', 'min:0'],
            'confidence_level' => ['nullable', 'integer', 'min:1', 'max:5'],
            'answer_context' => ['nullable', 'string', 'max:30'],
        ];
    }
}
