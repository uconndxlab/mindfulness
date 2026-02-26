<?php

namespace App\Rules;

use App\Models\Quiz;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class QuizAnswersValidRule implements ValidationRule
{
    protected $quiz;
    protected $errorMessage = '';

    public function __construct($quizId)
    {
        $this->quiz = Quiz::find($quizId);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->quiz) {
            $fail("Quiz not found.");
            return;
        }

        // Decode JSON if string
        $answers = is_string($value) ? json_decode($value, true) : $value;

        if (!is_array($answers)) {
            $fail("Answers must be a valid array or JSON object.");
            return;
        }

        $questionOptions = $this->quiz->question_options;
        if (!$questionOptions || !is_array($questionOptions)) {
            $fail("Quiz has invalid question structure.");
            return;
        }

        // validate each answer against quiz structure
        foreach ($answers as $questionNumber => $answerValue) {
            // find the corresponding question
            $question = collect($questionOptions)->firstWhere('number', (int)$questionNumber);
            
            if (!$question) {
                $fail("Invalid question number: {$questionNumber}.");
                return;
            }

            // validate based on question type
            if (!$this->validateAnswerForQuestion($question, $answerValue)) {
                $fail($this->errorMessage ?: "Invalid answer format for question {$questionNumber}.");
                return;
            }
        }
    }

    // validate based on type
    protected function validateAnswerForQuestion(array $question, mixed $answerValue): bool
    {
        $type = $question['type'] ?? null;
        $options = $question['options'] ?? [];
        \Log::info('NUMBER: ' . $question['number'] ?? 'null');
        \Log::info('Question: ' . json_encode($question));
        \Log::info('Answer value: ' . json_encode($answerValue));

        switch ($type) {
            case 'radio':
                \Log::info('Validating radio answer');
                return $this->validateRadioAnswer($answerValue, $options);
            
            case 'checkbox':
                \Log::info('Validating checkbox answer');
                return $this->validateCheckboxAnswer($answerValue, $options);
            
            case 'slider':
                \Log::info('Validating slider answer');
                return $this->validateSliderAnswer($answerValue, $options);

            case 'survey':
                \Log::info('Validating survey answer');
                // use the slider validations, but with a max value of 5
                return $this->validateSliderAnswer($answerValue, $options, 'survey');
            
            default:
                $this->errorMessage = "Unknown question type: {$type}.";
                return false;
        }
    }

    protected function validateRadioAnswer(mixed $answer, array $options): bool
    {
        // validate answer is an array with exactly one item
        if (!is_array($answer) || count($answer) !== 1) {
            $this->errorMessage = "Radio question must have exactly one answer.";
            return false;
        }
        \Log::info('Answer: ' . json_encode($answer));

        $optionId = (string)array_key_first($answer);
        $otherText = $answer[$optionId];

        // Validate option ID exists
        $validOptionIds = collect($options)->pluck('id')->map(fn($id) => (string)$id)->toArray();
        if (!in_array($optionId, $validOptionIds)) {
            $this->errorMessage = "Invalid option ID for radio question.";
            return false;
        }

        // If "other" is provided, validate it's a string
        if ($otherText !== null && !is_string($otherText)) {
            $this->errorMessage = "Other text must be a string.";
            return false;
        }

        \Log::info('Validated!');
        return true;
    }

    protected function validateCheckboxAnswer(mixed $answer, array $options): bool
    {
        if (!is_array($answer) || empty($answer)) {
            $this->errorMessage = "Checkbox question must have at least one answer.";
            return false;
        }

        $validOptionIds = collect($options)->pluck('id')->map(fn($id) => (string)$id)->toArray();

        // loop over selected items, and validate same as radio questions
        foreach ($answer as $answerItem) {
            if (!is_array($answerItem) || count($answerItem) !== 1) {
                $this->errorMessage = "Invalid checkbox answer format.";
                return false;
            }

            $optionId = (string)array_key_first($answerItem);
            $otherText = $answerItem[$optionId];

            // Validate option ID exists
            if (!in_array($optionId, $validOptionIds)) {
                $this->errorMessage = "Invalid option ID for checkbox question.";
                return false;
            }

            // If "other" is provided, validate it's a string
            if ($otherText !== null && !is_string($otherText)) {
                $this->errorMessage = "Other text must be a string.";
                return false;
            }
        }

        return true;
    }

    protected function validateSliderAnswer(mixed $answer, array $options, string $type = 'slider'): bool
    {
        if (!is_array($answer)) {
            $this->errorMessage = "Answer must be an array.";
            return false;
        }

        $validOptionIds = collect($options)->pluck('id')->map(fn($id) => (string)$id)->toArray();

        foreach ($answer as $answerItem) {
            if (!is_array($answerItem) || count($answerItem) !== 1) {
                $this->errorMessage = "Invalid answer format.";
                return false;
            }

            $optionId = (string)array_key_first($answerItem);
            $value = $answerItem[$optionId];

            // Validate option ID exists
            if (!in_array($optionId, $validOptionIds)) {
                $this->errorMessage = "Invalid option ID.";
                return false;
            }

            // validate numeric value
            if (!is_numeric($value)) {
                $this->errorMessage = "Value must be numeric.";
                return false;
            }

            $numericValue = (float)$value;
            
            // validate value is within config range
            if ($type === 'survey') {
                $min = 1;
                $max = 5;
            }
            else {
                $min = 0;
                $max = 100;
            }

            if ($numericValue < $min || $numericValue > $max) {
                $this->errorMessage = "Value must be between {$min} and {$max}.";
                return false;
            }
        }

        return true;
    }
}

