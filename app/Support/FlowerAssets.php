<?php

namespace App\Support;

use App\Models\Module;
use Illuminate\Support\Facades\Storage;

class FlowerAssets
{
    public const MAX_PETALS = 5;
    public const DEFAULT_COLOR = 'default';
    public const COLORS = ['blue', 'purple', 'orange', 'pink', 'default'];

    public static function colorSlug(Module $module): string
    {
        return $module->flowerColorSlug();
    }

    public static function framePath(string $color, int $petals): string
    {
        $petals = max(0, min(self::MAX_PETALS, $petals));
        $color = self::resolveColor($color);

        return "flowers/{$color}/{$petals}.svg";
    }

    public static function frameUrl(string $color, int $petals): string
    {
        return Storage::url(self::framePath($color, $petals));
    }

    public static function moduleFrameUrl(Module $module, int $petals): string
    {
        return self::frameUrl(self::colorSlug($module), $petals);
    }

    /**
     * @return array<int, string>
     */
    public static function frameUrls(string $color, int $targetPetals): array
    {
        $frames = [];
        for ($petals = 0; $petals <= $targetPetals; $petals++) {
            $frames[] = self::frameUrl($color, $petals);
        }

        return $frames;
    }

    public static function animationData(Module $module, int $targetPetals): array
    {
        $color = self::colorSlug($module);
        $config = self::animationConfig($color);

        return [
            'type' => 'flower',
            'color' => $color,
            'frames' => self::frameUrls($color, $targetPetals),
            'targetPetals' => $targetPetals,
            'frameDurationMs' => $config['frameDurationMs'],
            'holdDurationMs' => $config['holdDurationMs'],
            'loop' => $config['loop'],
        ];
    }

    public static function animationConfig(string $color): array
    {
        $path = storage_path('app/public/flowers/animations/'.self::resolveColor($color).'/config.json');

        if (!is_readable($path)) {
            return self::defaultAnimationConfig();
        }

        $config = json_decode(file_get_contents($path), true);

        if (!is_array($config)) {
            return self::defaultAnimationConfig();
        }

        return array_merge(self::defaultAnimationConfig(), $config);
    }

    public static function defaultAnimationConfig(): array
    {
        return [
            'frameDurationMs' => 450,
            'holdDurationMs' => 2500,
            'loop' => true,
        ];
    }

    private static function resolveColor(string $color): string
    {
        $color = strtolower($color);

        return in_array($color, self::COLORS, true) ? $color : self::DEFAULT_COLOR;
    }
}
