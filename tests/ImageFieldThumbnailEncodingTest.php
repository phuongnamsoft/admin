<?php

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use PHPUnit\Framework\TestCase;

/**
 * @requires extension gd
 */
class ImageFieldThumbnailEncodingTest extends TestCase
{
    public function test_contain_produces_exact_thumbnail_dimensions(): void
    {
        $fixture = __DIR__.'/assets/test.jpg';
        $this->assertFileExists($fixture);

        $manager = new ImageManager(new Driver());
        $image = $manager->read($fixture);
        $image->contain(120, 80, 'ffffff', 'center');

        $tmp = sys_get_temp_dir().'/admin-thumb-test-'.uniqid().'.jpg';
        $image->save($tmp);

        $out = $manager->read($tmp);
        $this->assertSame(120, $out->width());
        $this->assertSame(80, $out->height());

        @unlink($tmp);
    }

    public function test_encode_by_path_returns_non_empty_string_bytes(): void
    {
        $fixture = __DIR__.'/assets/test.jpg';
        $manager = new ImageManager(new Driver());
        $image = $manager->read($fixture);
        $image->contain(60, 60, 'ffffff', 'center');

        $encoded = $image->encodeByPath('thumb.jpeg');
        $binary = (string) $encoded;

        $this->assertNotSame('', $binary);
        $this->assertGreaterThan(100, strlen($binary));
    }
}
