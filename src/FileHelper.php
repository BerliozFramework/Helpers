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

declare(strict_types=1);

namespace Berlioz\Helpers;

use InvalidArgumentException;
use RuntimeException;

/**
 * Class FileHelper.
 *
 * @package Berlioz\Helpers
 */
final class FileHelper
{
    /**
     * Get a human see file size.
     *
     * @param int|float $size
     * @param int $precision
     *
     * @return string
     */
    public static function humanFileSize($size, int $precision = 2): string
    {
        if (!is_numeric($size)) {
            return (string)$size;
        }

        // PB
        if (($size / pow(1024, 5)) >= 1) {
            return sprintf(
                "%s PB",
                round($size / pow(1024, 5), $precision)
            );
        }

        // TB
        if (($size / pow(1024, 4)) >= 1) {
            return sprintf(
                "%s TB",
                round($size / pow(1024, 4), $precision)
            );
        }

        // GB
        if (($size / pow(1024, 3)) >= 1) {
            return sprintf(
                "%s GB",
                round($size / pow(1024, 3), $precision)
            );
        }

        // MB
        if (($size / pow(1024, 2)) >= 1) {
            return sprintf(
                "%s MB",
                round($size / pow(1024, 2), $precision)
            );
        }

        // KB
        if (($size / pow(1024, 1)) >= 1) {
            return sprintf(
                "%s KB",
                round($size / pow(1024, 1), $precision)
            );
        }

        // Bytes
        return sprintf("%s bytes", $size);
    }

    /**
     * Get size in bytes from ini conf file.
     *
     * @param string $size
     *
     * @return int
     */
    public static function sizeFromIni(string $size): int
    {
        switch (mb_strtolower(substr($size, -1))) {
            case 'k':
                return (int)substr($size, 0, -1) * 1024;
            case 'm':
                return (int)substr($size, 0, -1) * 1024 * 1024;
            case 'g':
                return (int)substr($size, 0, -1) * 1024 * 1024 * 1024;
            default:
                switch (mb_strtolower(substr($size, -2))) {
                    case 'kb':
                        return (int)substr($size, 0, -2) * 1024;
                    case 'mb':
                        return (int)substr($size, 0, -2) * 1024 * 1024;
                    case 'gb':
                        return (int)substr($size, 0, -2) * 1024 * 1024 * 1024;
                    default:
                        return (int)$size;
                }
        }
    }

    /**
     * Resolve absolute path.
     *
     * @param string $srcPath
     * @param string $dstPath
     *
     * @return string|null
     */
    public static function resolveAbsolutePath(string $srcPath, string $dstPath): ?string
    {
        $srcPath = self::uniformizePathSeparator($srcPath);
        $dstPath = self::uniformizePathSeparator($dstPath);
        $finalPath = $dstPath ?: $srcPath;

        if (strlen($dstPath) > 0 && substr($dstPath, 0, 1) !== '/') {
            // Complete absolute link
            if (substr($dstPath, 0, 2) === './') {
                $dstPath = substr($dstPath, 2);
            }

            // Unification of directories separators
            $finalPath = $srcPath;
            if (substr($finalPath, -1) !== '/') {
                $finalPath = self::uniformizePathSeparator(dirname($finalPath));
            }
            $finalPath = rtrim($finalPath, '/');
            if ($finalPath === '.') {
                $finalPath = '';
            }

            // Concatenation
            $finalPath = sprintf('%s/%s', $finalPath, $dstPath);
        }

        // Replacement of './'
        $finalPath = str_replace('/./', '/', $finalPath);

        // Replacement of '../'
        do {
            $finalPath = preg_replace('#(/|^)([^\\\/?%*:|"<>.]+)/\.\.(/|$)#', '/', $finalPath, -1, $nbReplacements);
        } while ($nbReplacements > 0);

        if (false === strpos($finalPath, './')) {
            return '/' . ltrim($finalPath, '/');
        }

        return null;
    }

    /**
     * Resolve relative path.
     *
     * @param string $srcPath
     * @param string $dstPath
     *
     * @return string
     */
    public static function resolveRelativePath(string $srcPath, string $dstPath): string
    {
        $srcPath = ltrim(self::resolveAbsolutePath('/', $srcPath), '/');
        $dstPath = ltrim(self::resolveAbsolutePath($srcPath, $dstPath), '/');

        if (substr($srcPath, 0, 2) === '..') {
            throw new InvalidArgumentException('Source path must be a relative path');
        }
        if (substr($srcPath, 0, 2) === './') {
            $srcPath = substr($srcPath, 2);
        }

        $srcPath = explode('/', $srcPath);
        $dstPath = explode('/', $dstPath);

        // Already relative?
        if (in_array(reset($dstPath), ['.', '..'])) {
            return implode('/', $dstPath);
        }

        // Get filename of destination path
        $dstFilename = self::extractFilename($dstPath);
        self::extractFilename($srcPath);

        $srcDepth = count($srcPath);
        $dstDepth = count($dstPath);
        $differentDepthPath = false;

        for ($i = 0; $i < $srcDepth; $i++) {
            if (!isset($dstPath[$i]) || $srcPath[$i] !== $dstPath[$i]) {
                $differentDepthPath = $i;
                break;
            }
        }

        $relativePath = '';
        if (false !== $differentDepthPath) {
            $relativePath .= str_repeat('../', $srcDepth - $differentDepthPath);
            $relativePath .= implode('/', array_slice($dstPath, min($dstDepth, $differentDepthPath)));
        }
        if (false === $differentDepthPath) {
            $relativePath .= './';
            $relativePath .= implode('/', array_slice($dstPath, $srcDepth, $dstDepth));
        }

        // Add file to relative path
        if (null !== $dstFilename) {
            $relativePath .= '/' . $dstFilename;
        }

        return preg_replace('#/{2,}#', '/', $relativePath);
    }

    /**
     * Normalize path.
     *
     * Uniformize directory separators (backslashes to slashes) and
     * collapse consecutive slashes into a single one.
     *
     * @param string $path
     *
     * @return string
     */
    public static function normalizePath(string $path): string
    {
        $path = str_replace(['\\', '/'], '/', $path);

        return preg_replace('#/{2,}#', '/', $path);
    }

    /**
     * Uniformize path separator.
     *
     * @param string $path
     *
     * @return string
     */
    private static function uniformizePathSeparator(string $path): string
    {
        return self::normalizePath($path);
    }

    /**
     * Extract filename.
     *
     * @param array $path
     *
     * @return string|null
     */
    private static function extractFilename(array &$path): ?string
    {
        if (false === ($filename = end($path))) {
            return null;
        }

        unset($path[count($path) - 1]);

        return $filename;
    }

    /**
     * File write in insertion mode.
     *
     * Use seekable and writeable resource and not mode 'a+'.
     *
     * @param resource $resource
     * @param string $data
     * @param int|null $length
     * @param int|null $offset
     *
     * @return int|false
     */
    public static function fwritei($resource, string $data, ?int $length = null, ?int $offset = null)
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException('Argument #1 must be a valid resource');
        }

        // Shift content
        fseek($resource, $currentPos = $offset ?? ftell($resource));
        $dataLength = $length ?? strlen($data);
        $i = 0;
        $totalWritten = 0;
        do {
            $shiftData = fread($resource, $dataLength) ?: false;
            fseek($resource, $currentPos + ($dataLength * $i++));
            if (false === ($written = fwrite($resource, $data, $dataLength))) {
                return false;
            }
            $totalWritten += $written;
            $data = $shiftData;
        } while (false !== $shiftData);

        return $totalWritten;
    }

    /**
     * Truncate a part of file and shift rest of data.
     *
     * @param resource $resource
     * @param int $size
     * @param int|null $offset Truncate $size chars from $offset
     *
     * @return bool
     */
    public static function ftruncate($resource, int $size, ?int $offset = null): bool
    {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException('Argument #1 must be a valid resource');
        }

        // Default comportment
        if (null === $offset) {
            return ftruncate($resource, $size);
        }

        // Find total length
        if (false === ($totalLength = fstat($resource)['size'] ?? false)) {
            throw new RuntimeException('Unable to get length of resource');
        }

        // Shift data using buffered reads
        $bufferSize = 8192;
        for ($pos = $offset; $pos < $totalLength - $size; $pos += $bufferSize) {
            $readLength = min($bufferSize, $totalLength - $size - $pos);

            if (-1 == fseek($resource, $pos + $size)) {
                return false;
            }

            if (false === ($data = fread($resource, $readLength))) {
                return false;
            }

            fseek($resource, $pos);
            fwrite($resource, $data);
        }

        return ftruncate($resource, $totalLength - $size);
    }
}