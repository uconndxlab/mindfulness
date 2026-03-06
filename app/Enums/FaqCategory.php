<?php

namespace App\Enums;

enum FaqCategory: string
{
    case GeneralInformation = 'general_information';
    case UseTheApp = 'use_the_app';
    case TechnicalIssues = 'technical_issues';
    case PrivacyAndAccount = 'privacy_and_account';
    case SafetyAndSupport = 'safety_and_support';

    public function label(): string
    {
        return match ($this) {
            self::GeneralInformation => 'General Information',
            self::UseTheApp => 'Use the App',
            self::TechnicalIssues => 'Technical Issues',
            self::PrivacyAndAccount => 'Privacy & Account',
            self::SafetyAndSupport => 'Safety & Support',
        };
    }

    public function order(): int
    {
        return match ($this) {
            self::GeneralInformation => 1,
            self::UseTheApp => 2,
            self::TechnicalIssues => 3,
            self::PrivacyAndAccount => 4,
            self::SafetyAndSupport => 5,
        };
    }

    public static function sorted(): array
    {
        $cases = self::cases();
        usort($cases, fn($a, $b) => $a->order() <=> $b->order());
        return $cases;
    }
}
