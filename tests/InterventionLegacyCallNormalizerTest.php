<?php

use PHPUnit\Framework\TestCase;
use PNS\Admin\Form\Field\InterventionLegacyCallNormalizer;
use PNS\Admin\Form\Field\UnsupportedLegacyInterventionCallException;

class InterventionLegacyCallNormalizerTest extends TestCase
{
    public function test_exception_extends_invalid_argument_exception(): void
    {
        $e = new UnsupportedLegacyInterventionCallException(
            'widen',
            'Intervention Image v3 removed widen(); use scale() / scaleDown() with named arguments.',
            'Use $image->scale(width: 200) or scaleDown(width: 200) depending on whether upscaling is allowed.'
        );

        $this->assertInstanceOf(\InvalidArgumentException::class, $e);
        $this->assertStringContainsString('widen', $e->getMessage());
        $this->assertStringContainsString('scale', $e->getMessage());
    }

    public function test_insert_alias_to_place(): void
    {
        $n = new InterventionLegacyCallNormalizer();
        $steps = $n->normalize('insert', ['/tmp/watermark.png', [10, 20]]);
        $this->assertSame([['place', ['/tmp/watermark.png', [10, 20]]]], $steps);
    }

    public function test_orientate_alias_to_orient(): void
    {
        $n = new InterventionLegacyCallNormalizer();
        $steps = $n->normalize('orientate', []);
        $this->assertSame([['orient', []]], $steps);
    }

    public function test_widen_throws_with_suggestion(): void
    {
        $this->expectException(UnsupportedLegacyInterventionCallException::class);
        $this->expectExceptionMessage('widen');

        (new InterventionLegacyCallNormalizer())->normalize('widen', [100]);
    }

    public function test_resize_with_closure_becomes_contain(): void
    {
        $n = new InterventionLegacyCallNormalizer();
        $steps = $n->normalize('resize', [100, 80, static fn () => null]);
        $this->assertSame([['contain', [100, 80, 'ffffff', 'center']]], $steps);
    }

    public function test_resize_two_integers_passes_through(): void
    {
        $n = new InterventionLegacyCallNormalizer();
        $steps = $n->normalize('resize', [100, 80]);
        $this->assertSame([['resize', [100, 80]]], $steps);
    }

    public function test_resize_invalid_dimensions_with_closure_throw(): void
    {
        $this->expectException(UnsupportedLegacyInterventionCallException::class);
        (new InterventionLegacyCallNormalizer())->normalize('resize', [0, 100, static fn () => null]);
    }

    public function test_resize_canvas_five_arg_absolute_maps_to_v3_resize_canvas(): void
    {
        $n = new InterventionLegacyCallNormalizer();
        $steps = $n->normalize('resizeCanvas', [800, 600, 'center', false, '#ff0000']);
        $this->assertSame([['resizeCanvas', [800, 600, 'ff0000', 'center']]], $steps);
    }

    public function test_resize_canvas_five_arg_relative_maps_to_relative(): void
    {
        $n = new InterventionLegacyCallNormalizer();
        $steps = $n->normalize('resizeCanvas', [40, 0, 'bottom', true, null]);
        $this->assertSame([['resizeCanvasRelative', [40, 0, 'ffffff', 'bottom']]], $steps);
    }

    public function test_resize_canvas_two_arg_passes_dimensions_only(): void
    {
        $n = new InterventionLegacyCallNormalizer();
        $steps = $n->normalize('resizeCanvas', [320, 240]);
        $this->assertSame([['resizeCanvas', [320, 240]]], $steps);
    }
}
