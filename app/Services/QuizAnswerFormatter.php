<?php

namespace App\Services;

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
}


