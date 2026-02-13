<?php

namespace App\Enums;

enum MilestoneType: string
{
    case Registered = 'registered';
    case FirstActivity = 'first_activity';
    case Module1 = 'module_1';
    case Module2 = 'module_2';
    case Module3 = 'module_3';
    case Module4 = 'module_4';

    public function label(): string
    {
        return match ($this) {
            self::Registered => 'Registered',
            self::FirstActivity => 'Completed First Activity',
            self::Module1 => 'Completed Module 1',
            self::Module2 => 'Completed Module 2',
            self::Module3 => 'Completed Module 3',
            self::Module4 => 'Completed Module 4',
        };
    }

    public static function forModule(int $moduleOrder): ?self
    {
        return match ($moduleOrder) {
            1 => self::Module1,
            2 => self::Module2,
            3 => self::Module3,
            4 => self::Module4,
            default => null,
        };
    }
}
