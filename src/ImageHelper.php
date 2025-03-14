<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2019 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 * @author    Nicolas GESLIN <https://github.com/NicolasGESLIN>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Helpers;

use GdImage;
use InvalidArgumentException;
use RuntimeException;

use function getimagesize;
use function imagealphablending;
use function imagecolorallocate;
use function imagecopyresampled;
use function imagecreatefromgif;
use function imagecreatefromjpeg;
use function imagecreatefrompng;
use function imagecreatetruecolor;
use function imagedestroy;
use function imagefill;
use function imagesavealpha;
use function imagesx;
use function imagesy;

use const IMAGETYPE_GIF;
use const IMAGETYPE_PNG;

/**
 * Class ImageHelper.
 *
 * @package Berlioz\Helpers
 */
final class ImageHelper
{
    public const SIZE_RATIO = 1;
    public const SIZE_LARGER_EDGE = 2;
    public const RESIZE_COVER = 4;

    /**
     * Calculate a gradient destination color.
     *
     * @param string $color Source color (hex)
     * @param string $colorToAdd Color to add (hex)
     * @param float $percentToAdd Percent to add
     *
     * @return string
     */
    public static function gradientColor(string $color, string $colorToAdd, float $percentToAdd): string
    {
        if (mb_strlen($color) != 7 ||
            substr($color, 0, 1) != "#" ||
            mb_strlen($colorToAdd) != 7 ||
            substr($colorToAdd, 0, 1) != "#") {
            return $color;
        }

        // RGB of color
        $rgb1 = [];
        $rgb1[0] = hexdec(substr($color, 1, 2));
        $rgb1[1] = hexdec(substr($color, 3, 2));
        $rgb1[2] = hexdec(substr($color, 5, 2));
        $rgb_final = $rgb1;

        // RGB of color to add
        $rgb2 = [];
        $rgb2[0] = hexdec(substr($colorToAdd, 1, 2));
        $rgb2[1] = hexdec(substr($colorToAdd, 3, 2));
        $rgb2[2] = hexdec(substr($colorToAdd, 5, 2));

        // Add percent
        for ($i = 0; $i < 3; $i++) {
            if ($rgb1[$i] < $rgb2[$i]) {
                $rgb_final[$i] = round(
                    ((max($rgb1[$i], $rgb2[$i]) - min($rgb1[$i], $rgb2[$i])) / 100)
                    * $percentToAdd
                    + min($rgb1[$i], $rgb2[$i])
                );
            } else {
                $rgb_final[$i] = round(
                    max($rgb1[$i], $rgb2[$i])
                    - ((max($rgb1[$i], $rgb2[$i]) - min($rgb1[$i], $rgb2[$i])) / 100)
                    * $percentToAdd
                );
            }
        }

        return
            "#" .
            sprintf("%02s", dechex((int)$rgb_final[0])) .
            sprintf("%02s", dechex((int)$rgb_final[1])) .
            sprintf("%02s", dechex((int)$rgb_final[2]));
    }

    /**
     * Calculate sizes with new given width and height.
     *
     * @param int $originalWidth Original width
     * @param int $originalHeight Original height
     * @param int|null $newWidth New width
     * @param int|null $newHeight New height
     * @param int $mode Mode (default: B_IMG_SIZE_RATIO)
     *
     * @return array
     */
    public static function size(
        int $originalWidth,
        int $originalHeight,
        ?int $newWidth = null,
        ?int $newHeight = null,
        int $mode = self::SIZE_RATIO
    ): array {
        // No size given, we keep original sizes!
        if (null === $newWidth && null === $newHeight) {
            return [
                'width' => $originalWidth,
                'height' => $originalHeight
            ];
        }

        // Only width given, keep ratio so...
        if (null === $newHeight) {
            return [
                'width' => $newWidth,
                'height' => (int)ceil($newWidth * $originalHeight / $originalWidth)
            ];
        }

        // Only height given, keep ratio so...
        if (null === $newWidth) {
            return [
                'width' => (int)ceil($newHeight * $originalWidth / $originalHeight),
                'height' => $newHeight
            ];
        }

        // We keep ratio?
        if (($mode & self::SIZE_RATIO) == self::SIZE_RATIO) {
            $ratio = $originalWidth / $originalHeight;
            $newRatio = $newWidth / $newHeight;

            if (($newRatio >= $ratio && ($mode & self::SIZE_LARGER_EDGE) == self::SIZE_LARGER_EDGE) ||
                ($newRatio <= $ratio && ($mode & self::SIZE_LARGER_EDGE) != self::SIZE_LARGER_EDGE)) {
                return [
                    'width' => $newWidth,
                    'height' => (int)ceil($newWidth * $originalHeight / $originalWidth)
                ];
            }

            return [
                'width' => (int)ceil($newHeight * $originalWidth / $originalHeight),
                'height' => $newHeight
            ];
        }

        // We don't keep ratio, and all sizes are given, so we force new size !
        return [
            'width' => $newWidth,
            'height' => $newHeight
        ];
    }

    /**
     * Get size of image.
     *
     * @param string|resource|GdImage $img File name or image resource
     *
     * @return array
     * @throws InvalidArgumentException if not valid input resource or file name
     */
    public static function getImageSize($img): array
    {
        if (is_string($img)) {
            if (!file_exists($img)) {
                throw new InvalidArgumentException(sprintf('File name "%s" does not exists', $img));
            }

            list($width, $height, $type) = getimagesize($img);

            return [
                'width' => $width,
                'height' => $height,
                'type' => $type
            ];
        }

        if ((PHP_MAJOR_VERSION < 8 && is_resource($img)) ||
            (PHP_MAJOR_VERSION > 7 && $img instanceof GdImage)) {
            return [
                'width' => imagesx($img),
                'height' => imagesy($img),
                'type' => 'RESOURCE'
            ];
        }

        throw new InvalidArgumentException('Need valid resource of image or file name');
    }

    /**
     * Resize image.
     *
     * @param string|resource|GdImage $img File name or image resource
     * @param int|null $newWidth New width
     * @param int|null $newHeight New height
     * @param int $mode Mode (default: B_IMG_SIZE_RATIO)
     *
     * @return resource|GdImage
     * @throws InvalidArgumentException if not valid input resource or file name
     */
    public static function resize($img, ?int $newWidth = null, ?int $newHeight = null, int $mode = self::SIZE_RATIO)
    {
        if (!extension_loaded('gd')) {
            throw new RuntimeException('Need GD extension');
        }

        // Get current dimensions as variables $width, $height and $type
        list($width, $height, $type) = array_values(self::getImageSize($img));

        // Definitions
        $dstWidth = $newWidth;
        $dstHeight = $newHeight;
        $posX = 0;
        $posY = 0;

        // We calculate cover sizes
        if ($mode === self::RESIZE_COVER && null !== $newWidth && null !== $newHeight) {
            $newSize = self::size(
                $width,
                $height,
                $newWidth,
                $newHeight,
                self::SIZE_RATIO | self::SIZE_LARGER_EDGE
            );

            $posX = (int)ceil(($dstWidth - $newSize['width']) / 2);
            $posY = (int)ceil(($dstHeight - $newSize['height']) / 2);
            $newWidth = $newSize['width'];
            $newHeight = $newSize['height'];
        } else {
            // We calculate size
            $newSize = self::size($width, $height, $newWidth, $newHeight, $mode);
            $dstWidth = $newWidth = $newSize['width'];
            $dstHeight = $newHeight = $newSize['height'];
        }

        // Create image thumb
        $thumb = imagecreatetruecolor($dstWidth, $dstHeight);
        $source = self::createImageFromType($type, $img);

        // Resizing
        imagecopyresampled($thumb, $source, $posX, $posY, 0, 0, $newWidth, $newHeight, $width, $height);

        // Erase source resource
        imagedestroy($source);

        return $thumb;
    }

    /**
     * Resize support of image.
     *
     * @param string|resource|GdImage $img File name or image resource
     * @param int|null $newWidth New width
     * @param int|null $newHeight New height
     *
     * @return resource|GdImage
     * @throws InvalidArgumentException if not valid input resource or file name
     */
    public static function resizeSupport($img, ?int $newWidth = null, ?int $newHeight = null)
    {
        if (!extension_loaded('gd')) {
            throw new RuntimeException('Need GD extension');
        }

        // Get current dimensions
        list($width, $height, $type) = array_values(self::getImageSize($img));

        // Treatment
        $source = static::createImageFromType($type, $img);

        // Defaults sizes
        if (null === $newWidth) {
            $newWidth = $width;
        }
        if (null === $newHeight) {
            $newHeight = $height;
        }

        // Calculate position
        $dest_x = (int)ceil(($newWidth - $width) / 2);
        $dest_y = (int)ceil(($newHeight - $height) / 2);
        if ($newWidth == $width && $newHeight == $height) {
            return $source;
        }

        $destination = imagecreatetruecolor($newWidth, $newHeight);
        // Set background to white
        $white = imagecolorallocate($destination, 255, 255, 255);
        imagefill($destination, 0, 0, $white);
        // Resizing
        imagecopyresampled($destination, $source, $dest_x, $dest_y, 0, 0, $newWidth, $newHeight, $width, $height);
        // Erase source resource
        imagedestroy($source);

        return $destination;
    }

    /**
     * Create image from type.
     *
     * @param string|int $type
     * @param resource|GdImage|string $img
     *
     * @return false|resource|GdImage
     */
    private static function createImageFromType($type, $img)
    {
        switch ($type) {
            case 'RESOURCE':
                /** @var resource $source */
                return $img;
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($img);
                imagealphablending($source, false);
                imagesavealpha($source, true);

                return $source;
                break;
            case IMAGETYPE_GIF:
                return imagecreatefromgif($img);
                break;
            default:
                return imagecreatefromjpeg($img);
        }
    }
}