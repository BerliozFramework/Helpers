<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2019 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

namespace Berlioz\Helpers\Tests;

use Berlioz\Helpers\ImageHelper;
use PHPUnit\Framework\TestCase;

class ImageHelperTest extends TestCase
{
    public function providerSizes(): array
    {
        return [
            [
                'params' => [
                    'originalWidth' => 100,
                    'originalHeight' => 100,
                    'newWidth' => 50,
                    'newHeight' => null,
                    'mode' => B_IMG_SIZE_RATIO
                ],
                'expected' => ['width' => 50, 'height' => 50]
            ],
            [
                'params' => [
                    'originalWidth' => 100,
                    'originalHeight' => 150,
                    'newWidth' => 50,
                    'newHeight' => null,
                    'mode' => B_IMG_SIZE_RATIO
                ],
                'expected' => ['width' => 50, 'height' => 75]
            ],
            [
                'params' => [
                    'originalWidth' => 150,
                    'originalHeight' => 100,
                    'newWidth' => 50,
                    'newHeight' => null,
                    'mode' => B_IMG_SIZE_RATIO
                ],
                'expected' => ['width' => 50, 'height' => 34]
            ],
            [
                'params' => [
                    'originalWidth' => 150,
                    'originalHeight' => 100,
                    'newWidth' => 50,
                    'newHeight' => 50,
                    'mode' => B_IMG_SIZE_LARGER_EDGE | B_IMG_SIZE_RATIO
                ],
                'expected' => ['width' => 75, 'height' => 50]
            ],
            [
                'params' => [
                    'originalWidth' => 100,
                    'originalHeight' => 150,
                    'newWidth' => 50,
                    'newHeight' => 50,
                    'mode' => B_IMG_SIZE_RATIO | B_IMG_SIZE_LARGER_EDGE
                ],
                'expected' => ['width' => 50, 'height' => 75]
            ],
            [
                'params' => [
                    'originalWidth' => 100,
                    'originalHeight' => 150,
                    'newWidth' => 50,
                    'newHeight' => 50,
                    'mode' => 8
                ],
                'expected' => ['width' => 50, 'height' => 50]
            ]
        ];
    }

    /**
     * @dataProvider providerSizes
     */
    public function testSize(array $params, array $expected)
    {
        $this->assertEquals($expected, call_user_func_array(sprintf('%s::%s', ImageHelper::class, 'size'), $params));
    }

    public function testGradientColor()
    {
        $this->assertEquals('#808080', ImageHelper::gradientColor('#ffffff', '#000000', 50));
        $this->assertEquals('#ff8080', ImageHelper::gradientColor('#ffffff', '#ff0000', 50));
        $this->assertEquals('#f78080', ImageHelper::gradientColor('#ffffff', '#ee0000', 50));
    }

    /**
     * @requires extension gd
     */
    public function testResizeLandscapeImage()
    {
        $filename = __DIR__ . '/files/image.jpg';

        $resource = ImageHelper::resize($filename, 100);
        $size = ImageHelper::getImageSize($resource);
        $this->assertEquals(100, $size['width']);
        $this->assertEquals(50, $size['height']);

        $resource = ImageHelper::resize($filename, null, 100);
        $size = ImageHelper::getImageSize($resource);
        $this->assertEquals(200, $size['width']);
        $this->assertEquals(100, $size['height']);

        $resource = ImageHelper::resize($filename, 100, 100);
        $size = ImageHelper::getImageSize($resource);
        $this->assertEquals(100, $size['width']);
        $this->assertEquals(50, $size['height']);

        $resource = ImageHelper::resize($filename, 100, 100, B_IMG_RESIZE_COVER);
        $size = ImageHelper::getImageSize($resource);
        $this->assertEquals(100, $size['width']);
        $this->assertEquals(100, $size['height']);
    }

    /**
     * @requires extension gd
     */
    public function testResizePortraitImage()
    {
        $filename = __DIR__ . '/files/image2.jpg';

        $resource = ImageHelper::resize($filename, 50);
        $size = ImageHelper::getImageSize($resource);
        $this->assertEquals(50, $size['width']);
        $this->assertEquals(100, $size['height']);

        $resource = ImageHelper::resize($filename, null, 200);
        $size = ImageHelper::getImageSize($resource);
        $this->assertEquals(100, $size['width']);
        $this->assertEquals(200, $size['height']);

        $resource = ImageHelper::resize($filename, 100, 100);
        $size = ImageHelper::getImageSize($resource);
        $this->assertEquals(50, $size['width']);
        $this->assertEquals(100, $size['height']);

        $resource = ImageHelper::resize($filename, 100, 100, B_IMG_RESIZE_COVER);
        $size = ImageHelper::getImageSize($resource);
        $this->assertEquals(100, $size['width']);
        $this->assertEquals(100, $size['height']);
    }

    /**
     * @requires extension gd
     */
    public function testResizeSupport()
    {
        $filename = __DIR__ . '/files/image.jpg';

        $resource = ImageHelper::resizeSupport($filename, 100, 100);
        $size = ImageHelper::getImageSize($resource);
        $this->assertEquals(100, $size['width']);
        $this->assertEquals(100, $size['height']);

        $resource = ImageHelper::resizeSupport($filename, 1024, 32);
        $size = ImageHelper::getImageSize($resource);
        $this->assertEquals(1024, $size['width']);
        $this->assertEquals(32, $size['height']);

        $resource = ImageHelper::resizeSupport($filename, null, 32);
        $size = ImageHelper::getImageSize($resource);
        $this->assertEquals(1024, $size['width']);
        $this->assertEquals(32, $size['height']);
    }

    /**
     * @requires extension gd
     */
    public function testResizeSupportCentersImageWithoutStretching()
    {
        // Create a small 10x10 red image
        $source = imagecreatetruecolor(10, 10);
        $red = imagecolorallocate($source, 255, 0, 0);
        imagefill($source, 0, 0, $red);

        // Resize support to 30x30 (should center the 10x10 image on white background)
        $result = ImageHelper::resizeSupport($source, 30, 30);

        $size = ImageHelper::getImageSize($result);
        $this->assertEquals(30, $size['width']);
        $this->assertEquals(30, $size['height']);

        // Top-left corner (0,0) should be white background
        $topLeft = imagecolorat($result, 0, 0);
        $this->assertEquals(0xFFFFFF, $topLeft, 'Top-left corner should be white');

        // Center pixel (15,15) should be red (from the original image)
        $center = imagecolorat($result, 15, 15);
        $this->assertEquals(0xFF0000, $center, 'Center should be red from original image');

        imagedestroy($result);
    }
}
