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

/**
 * Class ImageHelper.
 *
 * @package Berlioz\Helpers
 */
final class ImageHelper
{
    const SIZE_RATIO       = 1;
    const SIZE_LARGER_EDGE = 2;
    const RESIZE_COVER     = 1;


    /**
     * Calculate a gradient destination color.
     *
     * @param string $color        Source color (hex)
     * @param string $colorToAdd   Color to add (hex)
     * @param float  $percentToAdd Percent to add
     *
     * @return string
     */
    public static function gradientColor(string $color, string $colorToAdd, float $percentToAdd): string
    {
        $cColor = $color;
        if (mb_strlen($color) == 7 && substr($color, 0, 1) == "#"
            && mb_strlen($colorToAdd) == 7 && substr($colorToAdd, 0, 1) == "#") {
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
                    $rgb_final[$i] = round(((max($rgb1[$i], $rgb2[$i]) - min($rgb1[$i], $rgb2[$i])) / 100) * $percentToAdd + min($rgb1[$i], $rgb2[$i]));
                } else {
                    $rgb_final[$i] = round(max($rgb1[$i], $rgb2[$i]) - ((max($rgb1[$i], $rgb2[$i]) - min($rgb1[$i], $rgb2[$i])) / 100) * $percentToAdd);
                }
            }
            $cColor = "#" . sprintf("%02s", dechex($rgb_final[0])) . sprintf("%02s", dechex($rgb_final[1])) . sprintf("%02s", dechex($rgb_final[2]));
        }
        return $cColor;
    }

    /**
     * Calculate sizes with new given width and height.
     *
     * @param int $originalWidth  Original width
     * @param int $originalHeight Original height
     * @param int $newWidth       New width
     * @param int $newHeight      New height
     * @param int $mode           Mode (default: B_IMG_SIZE_RATIO)
     *
     * @return array
     */
    public static function size(int $originalWidth, int $originalHeight, int $newWidth = null, int $newHeight = null, int $mode = self::SIZE_RATIO): array
    {
        // All sizes are given
        if (!is_null($newWidth) && !is_null($newHeight)) {
            // We keep ratio
            if ($mode | self::SIZE_RATIO == self::SIZE_RATIO) {
                $ratio = $originalWidth / $originalHeight;
                $newRatio = $newWidth / $newHeight;
                if (($newRatio >= $ratio && $mode | self::SIZE_LARGER_EDGE == self::SIZE_LARGER_EDGE) ||
                    ($newRatio <= $ratio && $mode | self::SIZE_LARGER_EDGE != self::SIZE_LARGER_EDGE)) {
                    $size = ['width'  => $newWidth,
                             'height' => (int) ceil($newWidth * $originalHeight / $originalWidth)];
                } else {
                    $size = ['width'  => (int) ceil($newHeight * $originalWidth / $originalHeight),
                             'height' => $newHeight];
                }
            } // We don't keep ratio, and all sizes are given, so we force new size !
            else {
                $size = ['width'  => $newWidth,
                         'height' => $newHeight];
            }
        } else {
            // Only width given, keep ratio so...
            if (!is_null($newWidth)) {
                $size = ['width'  => $newWidth,
                         'height' => (int) ceil($newWidth * $originalHeight / $originalWidth)];
            } else {
                // Only height given, keep ratio so...
                if (!is_null($newHeight)) {
                    $size = ['width'  => (int) ceil($newHeight * $originalWidth / $originalHeight),
                             'height' => $newHeight];
                } // No size given, we keep original sizes !
                else {
                    $size = ['width'  => $originalWidth,
                             'height' => $originalHeight];
                }
            }
        }
        return $size;
    }

    /**
     * Resize image.
     *
     * @param string|resource $img       File name or image resource
     * @param int             $newWidth  New width
     * @param int             $newHeight New height
     * @param int             $mode      Mode (default: B_IMG_SIZE_RATIO)
     *
     * @return resource
     * @throws \InvalidArgumentException if not valid input resource or file name
     */
    public static function resize($img, int $newWidth = null, int $newHeight = null, int $mode = self::SIZE_RATIO)
    {
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('Need GD extension');
        }

        // Get current size
        if (is_string($img)) {
            list($width, $height, $type) = \getimagesize($img);
        } else {
            if (is_resource($img)) {
                $width  = imagesx($img);
                $height = imagesy($img);
                $type   = 'RESOURCE';
            } else {
                throw new \InvalidArgumentException('Need valid resource of image or file name');
            }
        }
        // Definitions
        $dstWidth = $newWidth;
        $dstHeight = $newHeight;
        $posX = 0;
        $posY = 0;
        // We calculate cover sizes
        if ($mode === self::RESIZE_COVER && !is_null($newWidth) && !is_null($newHeight)) {
            $newSize = self::size($width,
                $height,
                $newWidth,
                $newHeight,
                $mode |self::RESIZE_COVER == self::RESIZE_COVER ? $mode & self::SIZE_LARGER_EDGE : $mode);
            $newWidth  = $newSize['width'];
            $newHeight = $newSize['height'];
            $posX = ceil(($dstWidth - $newWidth) / 2);
            $posY = ceil(($dstHeight - $newHeight) / 2);
        } else {
            // We calculate size
            $newSize = self::size($width, $height, $newWidth, $newHeight, $mode);
            $dstWidth = $newWidth = $newSize['width'];
            $dstHeight = $newHeight = $newSize['height'];
        }
        // Create image thumb
        $thumb = imagecreatetruecolor($dstWidth, $dstHeight);
        switch ($type) {
            case 'RESOURCE':
                $source = $img;
                break;
            case \IMAGETYPE_PNG:
                $source = imagecreatefrompng($img);
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
                break;
            case \IMAGETYPE_GIF:
                $source = imagecreatefromgif($img);
                break;
            default:
                $source = imagecreatefromjpeg($img);
        }
        // Resizing
        imagecopyresampled($thumb, $source, $posX, $posY, 0, 0, $newWidth, $newHeight, $width, $height);
        // Erase source resource
        imagedestroy($source);
        return $thumb;
    }

    /**
     * Resize support of image.
     *
     * @param string|resource $img       File name or image resource
     * @param int             $newWidth  New width
     * @param int             $newHeight New height
     *
     * @return resource
     * @throws \InvalidArgumentException if not valid input resource or file name
     */
    public static function resizeSupport($img, int $newWidth = null, int $newHeight = null)
    {
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('Need GD extension');
        }

        // Get current size
        if (is_string($img)) {
            list($width, $height, $type) = \getimagesize($img);
        } else {
            if (is_resource($img)) {
                $width  = imagesx($img);
                $height = imagesy($img);
                $type   = 'RESOURCE';
            } else {
                throw new \InvalidArgumentException('Need valid resource of image or file name');
            }
        }
        // Treatment
        switch ($type) {
            case 'RESOURCE':
                /** @var resource $source */
                $source = $img;
                break;
            case \IMAGETYPE_PNG:
                $source = imagecreatefrompng($img);
                imagealphablending($source, false);
                imagesavealpha($source, true);
                break;
            case \IMAGETYPE_GIF:
                $source = imagecreatefromgif($img);
                break;
            default:
                $source = imagecreatefromjpeg($img);
        }
        // Defaults sizes
        if (is_null($newWidth)) {
            $newWidth = $width;
        }
        if (is_null($newHeight)) {
            $newHeight = $height;
        }
        // Calculate position
        $dest_x = ($newWidth - $width) / 2;
        $dest_y = ($newHeight - $height) / 2;
        if ($newWidth == $width && $newHeight == $height) {
            $destination = $source;
        } else {
            $destination = imagecreatetruecolor($newWidth, $newHeight);
            // Set background to white
            $white = imagecolorallocate($destination, 255, 255, 255);
            imagefill($destination, 0, 0, $white);
            // Resizing
            imagecopyresampled($destination, $source, $dest_x, $dest_y, 0, 0, $width, $height, $width, $height);
            // Erase source resource
            imagedestroy($source);
        }
        return $destination;
    }
}