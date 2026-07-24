<?php

namespace App\Services;

use App\Models\Module;

class ModuleChartService
{
    private const WIDTH = 560;

    private const HEIGHT = 260;

    private const MARGIN_RIGHT = 20;

    private const MARGIN_TOP = 12;

    private const MARGIN_BOTTOM = 72;

    private const SECTION_LABEL_OFFSET = 8;

    private const X_LABEL_OFFSET = 36;

    private const Y_LABEL_COLUMN = 18;

    private const TICK_GAP = 10;

    private const Y_MIN = 0;

    private const Y_MAX = 5;

    private const PRIMARY_COLOR = '#3A7BB8';

    private const APP_PRIMARY_COLOR = '#48745D';

    private const AXIS_LABEL_SIZE = 9;

    private const TICK_SIZE = 9;

    private const SECTION_LABEL_SIZE = 8;

    private ?string $fontRegular = null;

    private ?string $fontBold = null;

    /** @var array<int, string>|null */
    private ?array $moduleColorsByOrder = null;

    /**
     * @return array<string, string> PNG binary strings keyed by chart name
     */
    public function generateCharts(array $report): array
    {
        $charts = [];

        if (!empty($report['emotions']['sections'])) {
            $pleasantChart = $this->renderEmotionSeriesChart(
                $report['emotions']['sections'],
                'pleasant'
            );
            if ($pleasantChart !== null) {
                $charts['emotions_pleasant'] = $pleasantChart;
            }

            $unpleasantChart = $this->renderEmotionSeriesChart(
                $report['emotions']['sections'],
                'unpleasant'
            );
            if ($unpleasantChart !== null) {
                $charts['emotions_unpleasant'] = $unpleasantChart;
            }
        }

        if (!empty($report['presence']['sections'])) {
            $charts['presence'] = $this->renderSectionSeriesChart($report['presence']['sections']);
        }

        if (!empty($report['awareness']['sections'])) {
            $charts['awareness'] = $this->renderSectionSeriesChart($report['awareness']['sections']);
        }

        return array_filter($charts);
    }

    private function renderEmotionSeriesChart(array $sections, string $field): ?string
    {
        $seriesSections = [];

        foreach ($sections as $section) {
            if (($section[$field] ?? null) === null) {
                continue;
            }

            $seriesSections[] = [
                'label' => $section['label'],
                'part_order' => $section['part_order'] ?? null,
                'value' => $section[$field],
            ];
        }

        if ($seriesSections === []) {
            return null;
        }

        return $this->renderSectionSeriesChart($seriesSections);
    }

    private function renderSectionSeriesChart(array $sections): ?string
    {
        if ($sections === []) {
            return null;
        }

        $image = $this->createCanvas();
        $plot = $this->plotArea();
        $this->drawAxes($image, $plot, 'Section', 'Score');

        $groupCount = count($sections);
        $groupWidth = $plot['width'] / max($groupCount, 1);
        $barWidth = (int) min(40, $groupWidth - 24);

        foreach ($sections as $index => $section) {
            if (($section['value'] ?? null) === null) {
                continue;
            }

            $barHex = $this->sectionColor($section);
            $centerX = (int) ($plot['left'] + ($groupWidth * $index) + ($groupWidth / 2));
            $barColor = $this->allocateHexColor($image, $barHex);
            $this->drawBar($image, $centerX - (int) ($barWidth / 2), $barWidth, (float) $section['value'], $plot, $barColor);
            $this->drawSectionLabel($image, $section['label'], $centerX, $plot['bottom'] + self::SECTION_LABEL_OFFSET);
        }

        return $this->toPng($image);
    }

    private function sectionColor(array $section): string
    {
        $partOrder = $section['part_order'] ?? null;

        if ($partOrder === 0) {
            return self::APP_PRIMARY_COLOR;
        }

        if ($partOrder) {
            return $this->moduleColorsByOrder()[$partOrder] ?? self::PRIMARY_COLOR;
        }

        return self::PRIMARY_COLOR;
    }

    /** @return array<int, string> */
    private function moduleColorsByOrder(): array
    {
        if ($this->moduleColorsByOrder === null) {
            $this->moduleColorsByOrder = Module::query()
                ->orderBy('order')
                ->pluck('color', 'order')
                ->all();
        }

        return $this->moduleColorsByOrder;
    }

    private function createCanvas()
    {
        $image = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);

        return $image;
    }

    private function plotArea(): array
    {
        $left = 12 + self::Y_LABEL_COLUMN + $this->textWidth('5', self::TICK_SIZE) + self::TICK_GAP;

        return [
            'left' => $left,
            'right' => self::WIDTH - self::MARGIN_RIGHT,
            'top' => self::MARGIN_TOP,
            'bottom' => self::HEIGHT - self::MARGIN_BOTTOM,
            'width' => self::WIDTH - self::MARGIN_RIGHT - $left,
            'height' => self::HEIGHT - self::MARGIN_BOTTOM - self::MARGIN_TOP,
        ];
    }

    private function drawAxes($image, array $plot, string $xLabel, string $yLabel): void
    {
        $black = imagecolorallocate($image, 51, 51, 51);
        $grid = imagecolorallocate($image, 225, 225, 225);
        $tickRight = $plot['left'] - 6;

        for ($tick = self::Y_MIN; $tick <= self::Y_MAX; $tick++) {
            $y = $this->valueToY((float) $tick, $plot);
            imageline($image, $plot['left'], $y, $plot['right'], $y, $grid);
            $this->drawTextRight($image, self::TICK_SIZE, $tickRight, $y, $black, (string) $tick);
        }

        imageline($image, $plot['left'], $plot['top'], $plot['left'], $plot['bottom'], $black);
        imageline($image, $plot['left'], $plot['bottom'], $plot['right'], $plot['bottom'], $black);

        $this->drawTextCentered($image, self::AXIS_LABEL_SIZE, (int) (($plot['left'] + $plot['right']) / 2), $plot['bottom'] + self::X_LABEL_OFFSET, $black, $xLabel);
        $this->drawVerticalLabel(
            $image,
            self::AXIS_LABEL_SIZE,
            10,
            (int) (($plot['top'] + $plot['bottom']) / 2),
            $black,
            $yLabel
        );
    }

    private function drawBar($image, int $left, int $width, float $value, array $plot, int $color): void
    {
        $top = $this->valueToY($value, $plot);
        imagefilledrectangle($image, $left, $top, $left + $width, $plot['bottom'], $color);
    }

    private function drawSectionLabel($image, string $label, int $centerX, int $y, int $maxChars = 14): void
    {
        $black = imagecolorallocate($image, 51, 51, 51);
        $lines = $this->wrapLabel($label, $maxChars);
        $lineHeight = self::SECTION_LABEL_SIZE + 4;

        foreach ($lines as $index => $line) {
            $this->drawTextCentered(
                $image,
                self::SECTION_LABEL_SIZE,
                $centerX,
                $y + ($index * $lineHeight) + self::SECTION_LABEL_SIZE,
                $black,
                $line
            );
        }
    }

    private function drawText(
        $image,
        int $size,
        int $x,
        int $y,
        int $color,
        string $text,
        bool $bold = false
    ): void {
        imagettftext($image, $size, 0, $x, $y, $color, $this->fontPath($bold), $text);
    }

    private function drawTextCentered($image, int $size, int $centerX, int $baselineY, int $color, string $text): void
    {
        $width = $this->textWidth($text, $size);
        $this->drawText($image, $size, (int) ($centerX - ($width / 2)), $baselineY, $color, $text);
    }

    private function drawTextRight($image, int $size, int $rightX, int $middleY, int $color, string $text): void
    {
        $box = imagettfbbox($size, 0, $this->fontPath(), $text);
        $width = abs($box[2] - $box[0]);
        $height = abs($box[7] - $box[1]);
        $x = $rightX - $width;
        $y = $middleY + (int) ($height / 2);

        $this->drawText($image, $size, $x, $y, $color, $text);
    }

    private function drawVerticalLabel($image, int $size, int $x, int $centerY, int $color, string $text): void
    {
        $box = imagettfbbox($size, 90, $this->fontPath(), $text);
        $height = abs($box[7] - $box[1]);
        $y = $centerY + (int) ($height / 2);

        imagettftext($image, $size, 90, $x, $y, $color, $this->fontPath(), $text);
    }

    private function textWidth(string $text, int $size, bool $bold = false): int
    {
        $box = imagettfbbox($size, 0, $this->fontPath($bold), $text);

        return abs($box[2] - $box[0]);
    }

    private function wrapLabel(string $label, int $maxChars): array
    {
        $words = explode(' ', $label);
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : "{$current} {$word}";
            if (strlen($candidate) > $maxChars && $current !== '') {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $candidate;
            }
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }

    private function fontPath(bool $bold = false): string
    {
        if ($bold) {
            return $this->fontBold ??= $this->resolveFont('DejaVuSans-Bold.ttf');
        }

        return $this->fontRegular ??= $this->resolveFont('DejaVuSans.ttf');
    }

    private function resolveFont(string $filename): string
    {
        $bundled = resource_path("fonts/{$filename}");
        if (is_readable($bundled)) {
            return $bundled;
        }

        $fallbacks = [
            '/usr/share/fonts/truetype/dejavu/'.$filename,
            '/usr/share/fonts/dejavu/'.$filename,
        ];

        foreach ($fallbacks as $path) {
            if (is_readable($path)) {
                return $path;
            }
        }

        throw new \RuntimeException("Chart font not found: {$filename}");
    }

    private function valueToY(float $value, array $plot): int
    {
        $clamped = max(self::Y_MIN, min(self::Y_MAX, $value));
        $ratio = ($clamped - self::Y_MIN) / (self::Y_MAX - self::Y_MIN);

        return (int) ($plot['bottom'] - ($ratio * $plot['height']));
    }

    private function toPng($image): string
    {
        ob_start();
        imagepng($image);
        $png = ob_get_clean() ?: '';
        imagedestroy($image);

        return $png;
    }

    private function allocateHexColor($image, string $hexColor): int
    {
        $hex = ltrim($hexColor, '#');

        if (strlen($hex) !== 6) {
            return imagecolorallocate($image, 58, 123, 184);
        }

        return imagecolorallocate(
            $image,
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        );
    }
}
