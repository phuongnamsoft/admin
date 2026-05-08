<?php

namespace PNS\Admin\Form\Field;

final class InterventionLegacyCallNormalizer
{
    private const DEFAULT_PAD_BACKGROUND = 'ffffff';

    private const DEFAULT_POSITION = 'center';

    /** @var array<string, string> v2-style name => v3 method name */
    private const METHOD_ALIASES = [
        'insert' => 'place',
        'orientate' => 'orient',
    ];

    /** @var array<string, string> method => guidance */
    private const REMOVED_METHODS = [
        'widen' => 'Use scale(width: …) or scaleDown(width: …) instead of widen().',
        'heighten' => 'Use scale(height: …) or scaleDown(height: …) instead of heighten().',
        'fit' => 'Use cover() for crop-to-fill, pad() for letterbox without upscaling, or contain() for letterbox with upscaling.',
        'backup' => 'Clone the image object in PHP (native cloning) instead of backup()/reset().',
        'reset' => 'Clone the image object in PHP (native cloning) instead of backup()/reset().',
        'cache' => 'Image caching is not part of intervention/image v3; handle caching in application code.',
        'response' => 'Build HTTP responses from EncodedImage bytes in your framework; psrResponse()/response() were removed.',
        'psrResponse' => 'Build HTTP responses from EncodedImage bytes in your framework; psrResponse()/response() were removed.',
        'stream' => 'Use EncodedImage::toFilePointer() or raw string bytes instead of stream().',
        'destroy' => 'Unset the variable or let scope end; destroy() was removed.',
        'make' => 'Reading uses ImageManager::read(); make() is not queued on the instance.',
    ];

    /**
     * @return list<array{0: string, 1: array<int, mixed>}>
     */
    public function normalize(string $method, array $arguments): array
    {
        $original = $method;
        $method = self::METHOD_ALIASES[$method] ?? $method;

        if (isset(self::REMOVED_METHODS[$original])) {
            throw new UnsupportedLegacyInterventionCallException(
                $original,
                'This API was removed or is not representable as a v3 modifier call.',
                self::REMOVED_METHODS[$original]
            );
        }

        return match (true) {
            $method === 'resize' && array_key_exists(2, $arguments) && $arguments[2] instanceof \Closure
                => $this->normalizeResizeWithConstraint($arguments),
            $method === 'resizeCanvas' => $this->normalizeResizeCanvas($arguments),
            default => [[$method, $arguments]],
        };
    }

    /**
     * @return list<array{0: string, 1: array<int, mixed>}>
     */
    private function normalizeResizeWithConstraint(array $arguments): array
    {
        $width = $arguments[0] ?? null;
        $height = $arguments[1] ?? null;

        if (! is_int($width) || ! is_int($height) || $width <= 0 || $height <= 0) {
            throw new UnsupportedLegacyInterventionCallException(
                'resize',
                'resize() with a constraint closure is only supported when width and height are positive integers (letterbox semantics).',
                'Use contain(width, height, background, position) with explicit integers, or rewrite using scale()/cover()/pad().'
            );
        }

        return [[
            'contain',
            [$width, $height, self::DEFAULT_PAD_BACKGROUND, self::DEFAULT_POSITION],
        ]];
    }

    /**
     * Map Intervention v2 resizeCanvas ($width, $height, $anchor, $relative, $bgcolor) to v3.
     *
     * @return list<array{0: string, 1: array<int, mixed>}>
     */
    private function normalizeResizeCanvas(array $arguments): array
    {
        $count = count($arguments);

        if ($count === 2) {
            [$w, $h] = $this->nullableDimensionsPair($arguments[0], $arguments[1]);

            return [['resizeCanvas', [$w, $h]]];
        }

        if ($count === 5) {
            [$width, $height, $position, $relative, $background] = $arguments;
            $background = $this->normalizeBackground($background);
            $pair = $this->nullableDimensionsPair($width, $height);

            if ($relative === true) {
                return [['resizeCanvasRelative', array_merge($pair, [$background, (string) $position])]];
            }

            if ($relative === false) {
                return [['resizeCanvas', array_merge($pair, [$background, (string) $position])]];
            }

            throw new UnsupportedLegacyInterventionCallException(
                'resizeCanvas',
                'Legacy resizeCanvas(..., $relative, ...) requires $relative to be boolean to choose resizeCanvas vs resizeCanvasRelative.',
                'Pass true for relative canvas delta sizing (v3 resizeCanvasRelative) or false for absolute canvas size (v3 resizeCanvas).'
            );
        }

        if ($count === 3 || $count === 4) {
            throw new UnsupportedLegacyInterventionCallException(
                'resizeCanvas',
                sprintf('Unsupported argument count (%d) for legacy resizeCanvas mapping.', $count),
                'Use v3 resizeCanvas(?width, ?height, background, position) or resizeCanvasRelative with explicit booleans for relative mode in five-arg legacy form.'
            );
        }

        if ($count === 0 || $count === 1) {
            throw new UnsupportedLegacyInterventionCallException(
                'resizeCanvas',
                sprintf('Unsupported argument count (%d) for resizeCanvas.', $count),
                'Use v3 resizeCanvas(?width, ?height, background, position).'
            );
        }

        return [['resizeCanvas', $arguments]];
    }

    /**
     * @return array{0: null|int, 1: null|int}
     */
    private function nullableDimensionsPair(mixed $width, mixed $height): array
    {
        $w = $this->nullableDimension($width);
        $h = $this->nullableDimension($height);

        return [$w, $h];
    }

    private function nullableDimension(mixed $value): ?int
    {
        if ($value === null || $value === false) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        throw new UnsupportedLegacyInterventionCallException(
            'resizeCanvas',
            'Canvas dimensions must be int, null, or false (treated as null) for v3 mapping.',
            'Use null for “leave axis unchanged” per Intervention v3 resizeCanvas().'
        );
    }

    private function normalizeBackground(mixed $background): string
    {
        if ($background === null) {
            return self::DEFAULT_PAD_BACKGROUND;
        }

        if (is_string($background)) {
            $trim = ltrim($background, '#');

            return $trim !== '' ? $trim : self::DEFAULT_PAD_BACKGROUND;
        }

        throw new UnsupportedLegacyInterventionCallException(
            'resizeCanvas',
            'Intervention Image v3 does not accept legacy array color values for canvas background.',
            'Pass a hex string such as "ffffff" or a named color accepted by Intervention v3.'
        );
    }
}
