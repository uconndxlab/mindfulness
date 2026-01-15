<?php

namespace App\Services;

use App\Models\QuizAnswers;

class QuizAnswerFormatter
{
    // format quiz answers
    //  "Q1: 2, Q2: 1, Q3: 4"
    public static function formatAnswers(?array $answers, ?int $maxLength = null): string
    {
        if (empty($answers)) {
            return 'No answers';
        }

        try {
            $formatted = self::processAnswers($answers);
            
            if ($maxLength && strlen($formatted) > $maxLength) {
                return substr($formatted, 0, $maxLength) . '...';
            }
            
            return $formatted;
        } catch (\Exception $e) {
            return 'Error formatting answers';
        }
    }

    private static function processAnswers(array $answers): string
    {
        $parts = [];
        $questionNum = 1;
        
        foreach ($answers as $key => $value) {
            $parts[] = "Q{$questionNum}: ".self::formatValue($value);
            $questionNum++;
        }
        
        return implode(', ', $parts);
    }

    // examples of values (answers to a single question)
    // multiple select: [{"1":null},{"2":null},{"3":null},{"4":null},{"5":null},{"6":null}]
    // radio {"2":null} 
    // slider: [{"1":77},{"2":75},{"3":75},{"4":75},{"5":18},{"6":18}]
    private static function formatValue($value): string
    {
        if (is_null($value)) {
            return 'null';
        }
        
        if (is_array($value)) {
            $formatted = [];
            foreach($value as $optionKey => $optionData) {
                // Handle direct values: {"1": null} or {"1": 77}
                if (is_null($optionData)) {
                    // Multi-select or radio: null means selected, show option key
                    $formatted[] = $optionKey;
                } elseif (is_array($optionData)) {
                    // Nested array like [{"7":"something"}] - iterate to get actual keys
                    foreach($optionData as $innerKey => $innerValue) {
                        if (is_null($innerValue)) {
                            $formatted[] = $innerKey;
                        } elseif (is_numeric($innerValue)) {
                            $formatted[] = (string) $innerValue;
                        } elseif (is_string($innerValue)) {
                            $formatted[] = $innerKey . ': ' . $innerValue;
                        } else {
                            $formatted[] = 'n/a';
                        }
                    }
                } elseif (is_numeric($optionData)) {
                    // Direct numeric: {"1": 77}
                    $formatted[] = (string) $optionData;
                } elseif (is_string($optionData)) {
                    // Direct string: {"1": "answer"}
                    $formatted[] = $optionKey . ': ' . $optionData;
                } else {
                    $formatted[] = 'n/a';
                }
            }
            return '[' . implode(', ', $formatted) . ']';
        }
        
        if (is_numeric($value)) {
            return (string) $value;
        }
        
        return (string) $value;
    }

    // formatting the detailed answers
    public static function getQuestionsWithAnswers(QuizAnswers $reflection): array
    {
        $quiz = $reflection->quiz;
        
        if (!$quiz || !$quiz->question_options) {
            return [];
        }

        $answers = $reflection->answers ?? [];
        $questionOptions = $quiz->question_options;
        $result = [];

        // iterate through question options
        foreach ($questionOptions as $index => $questionData) {
            // get user answer
            $questionNumber = $questionData['number'] ?? ($index + 1);
            $answerKey = array_keys($answers)[$index] ?? null;
            $userAnswer = $answerKey !== null ? $answers[$answerKey] : null;

            // save formatted result
            $result[] = [
                'number' => $questionNumber,
                'question' => $questionData['question'] ?? 'No question text',
                'type' => $questionData['type'] ?? 'unknown',
                'options' => $questionData['options'] ?? [],
                'user_answer' => $userAnswer,
                'formatted_answer' => self::formatAnswerByType(
                    $questionData['type'] ?? 'unknown',
                    $userAnswer,
                    $questionData['options'] ?? []
                )
            ];
        }

        return $result;
    }

    private static function formatAnswerByType(string $type, $answer, array $options): array
    {
        if (is_null($answer)) {
            return [
                'type' => $type,
                'display' => 'No answer provided',
                'items' => []
            ];
        }

        // format answer based on type
        switch ($type) {
            case 'slider':
                return self::formatSliderAnswer($answer, $options);
            
            case 'checkbox':
                return self::formatCheckboxAnswer($answer, $options);
            
            case 'radio':
                return self::formatRadioAnswer($answer, $options);
            
            default:
                return [
                    'type' => $type,
                    'display' => 'Unknown question type',
                    'items' => []
                ];
        }
    }

    private static function formatSliderAnswer($answer, array $options): array
    {
        $items = [];

        if (!is_array($answer)) {
            return ['type' => 'slider', 'display' => 'Invalid answer format', 'items' => []];
        }

        foreach ($answer as $keyValuePair) {
            $optionId = (string)array_key_first($keyValuePair);
            $value = $keyValuePair[$optionId];

            // get slider option
            $option = self::findOptionById($options, $optionId);
            
            // get question and answer
            if ($option) {
                $items[] = [
                    'text' => $option['text'] ?? 'Unknown',
                    'value' => is_numeric($value) ? $value : 0
                ];
            }
        }

        return [
            'type' => 'slider',
            'display' => count($items) . ' slider response(s)',
            'items' => $items
        ];
    }

    private static function formatCheckboxAnswer($answer, array $options): array
    {
        $items = [];

        if (!is_array($answer)) {
            return ['type' => 'checkbox', 'display' => 'Invalid answer format', 'items' => []];
        }

        foreach ($answer as $selection) {
            if (is_array($selection)) {
                foreach ($selection as $optionId => $value) {
                    $option = self::findOptionById($options, $optionId);
                    $items[] = self::getAnswerItem($option, $value);
                }
            }
        }

        return [
            'type' => 'checkbox',
            'display' => count($items) . ' option(s) selected',
            'items' => $items
        ];
    }

    private static function formatRadioAnswer($answer, array $options): array
    {
        $items = [];

        if (!is_array($answer)) {
            return ['type' => 'radio', 'display' => 'Invalid answer format', 'items' => []];
        }

        foreach ($answer as $optionId => $value) {
            $option = self::findOptionById($options, $optionId);
            $items[] = self::getAnswerItem($option, $value);
            
            return [
                'type' => 'radio',
                'display' => $option['text'] ?? "Option $optionId",
                'items' => $items
            ];
        }

        return [
            'type' => 'radio',
            'display' => 'No selection',
            'items' => []
        ];
    }

    private static function getAnswerItem(array $option, $value): array
    {
        if ($option) {
            if (is_null($value)) {
                return [
                    'text' => $option['text'] ?? "Option $optionId",
                    'selected' => true
                ];
            }
            elseif ($option['allow_other']) {
                return [
                    'text' => '**'.$option['text'].':** '.$value,
                    'selected' => true
                ];
            }
        }
        return null;
    }

    private static function findOptionById(array $options, $id)
    {
        foreach ($options as $option) {
            if (isset($option['id']) && $option['id'] == $id) {
                return $option;
            }
        }
        return null;
    }
}


