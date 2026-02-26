<?php

namespace App\Http\Requests;

use App\Models\Quiz;
use App\Rules\QuizAccessRule;
use App\Rules\QuizAnswersValidRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SubmitQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        // authorization is handled by the QuizAccessRule
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $quizId = $this->input('quiz_id') ?? $this->route('quiz_id');
        $quiz = $quizId ? Quiz::find($quizId) : null;
        $expectsAverage = $quiz && in_array($quiz->type, ['check_in', 'self_rating'], true);
        $quizType = $quiz ? ($quiz->question_options[0]['type'] ?? null) : null;

        if ($quizType) {
            if ($quizType === 'survey' || $quizType === 'slider') {
                $averageRule = ['required', 'numeric', 'min:0', 'max:100'];
            } else {
                $averageRule = ['prohibited'];
            }
        }
        else {
            $averageRule = ['prohibited'];
        }

        return [
            'quiz_id' => ['required', 'integer', new QuizAccessRule()],
            'answers' => ['required', 'json', new QuizAnswersValidRule($quizId)],
            'average' => $averageRule,
        ];
    }

    public function messages(): array
    {
        return [
            'quiz_id.required' => 'Quiz ID is required.',
            'quiz_id.integer' => 'Quiz ID must be an integer.',
            'answers.required' => 'Answers are required.',
            'answers.json' => 'Answers must be valid JSON.',
            'average.numeric' => 'Average must be a number.',
            'average.min' => 'Average must be at least 0.',
            'average.max' => 'Average exceeds the maximum value.',
            'average.prohibited' => 'Average is not accepted for this quiz type.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // get quiz_id from route parameter if not in request body
        if (!$this->has('quiz_id') && $this->route('quiz_id')) {
            $this->merge([
                'quiz_id' => $this->route('quiz_id'),
            ]);
        }

        // ensure answers is JSON string for validation
        if ($this->has('answers') && is_array($this->input('answers'))) {
            $this->merge([
                'answers' => json_encode($this->input('answers')),
            ]);
        }
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'error_message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    public function getAnswersArray(): array
    {
        return json_decode($this->validated()['answers'], true);
    }
}

