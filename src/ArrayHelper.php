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

use ArrayObject;
use Closure;
use Exception;
use InvalidArgumentException;
use SimpleXMLElement;
use Traversable;

/**
 * Class ArrayHelper.
 *
 * @package Berlioz\Helpers
 */
final class ArrayHelper
{
    /**
     * Is sequential array?
     *
     * @param array $array
     *
     * @return bool
     * @deprecated Use ArrayHelper::isList() instead
     * @see ArrayHelper::isList()
     */
    public static function isSequential(array $array): bool
    {
        return static::isList($array);
    }

    /**
     * Is array list?
     *
     * @param array $array
     *
     * @return bool
     */
    public static function isList(array $array): bool
    {
        if (function_exists('\array_is_list')) {
            return array_is_list($array);
        }

        if ($array === []) {
            return true;
        }

        if (!array_key_exists(0, $array)) {
            return false;
        }

        $keys = array_keys($array);

        // Not numbers keys
        $NaNKeys = array_filter($keys, function ($key) {
            return !is_int($key);
        });
        if (count($NaNKeys) > 0) {
            return false;
        }

        return $keys === range(0, count($array) - 1);
    }

    /**
     * Get values from a single column in the input array.
     *
     * Difference between native array_column() and b_array_column() is
     * that b_array_column() accept a \Closure in keys arguments.
     *
     * @param array $array
     * @param string|int|Closure|null $column_key
     * @param string|int|Closure|null $index_key
     *
     * @return array
     */
    public static function column(array $array, $column_key, $index_key = null): array
    {
        if (!(is_int($column_key) ||
            is_string($column_key) ||
            null === $column_key ||
            $column_key instanceof Closure)) {
            throw new InvalidArgumentException('$column_key argument must be int|string|\Closure|null');
        }

        if (!(is_int($index_key) ||
            is_string($index_key) ||
            null === $index_key ||
            $index_key instanceof Closure)) {
            throw new InvalidArgumentException('$index_key argument must be int|string|\Closure|null');
        }

        if (!$column_key instanceof Closure && !$index_key instanceof Closure) {
            return array_column($array, $column_key, $index_key);
        }

        $final = [];

        foreach ($array as $key => $value) {
            $finalValue = $value;
            $finalIndex = $key;

            if (null !== $column_key) {
                if ($column_key instanceof Closure) {
                    $finalValue = $column_key($value, $key);
                }

                if (!$column_key instanceof Closure) {
                    if (is_object($value)) {
                        $finalValue = $value->$column_key;
                    }
                    if (is_array($value)) {
                        $finalValue = $value[$column_key];
                    }
                }
            }

            if (null !== $index_key) {
                if ($index_key instanceof Closure) {
                    $finalIndex = $index_key($value, $key);
                }

                if (!$index_key instanceof Closure) {
                    if (is_object($value)) {
                        $finalValue = $value->$index_key;
                    }
                    if (is_array($value)) {
                        $finalValue = $value[$index_key];
                    }
                }
            }

            $final[$finalIndex] = $finalValue;
        }

        return $final;
    }

    /**
     * Convert array to an XML element.
     *
     * @param $array
     * @param SimpleXMLElement|null $root
     * @param string|null $rootName
     *
     * @return SimpleXMLElement
     * @throws Exception
     */
    public static function toXml($array, ?SimpleXMLElement $root = null, ?string $rootName = null): SimpleXMLElement
    {
        // Traversable or array
        if (!(is_array($array) || $array instanceof Traversable)) {
            throw new InvalidArgumentException('First argument must be an array or instance of \Traversable interface');
        }

        if (null === $root) {
            $root = new SimpleXMLElement(sprintf('<root/>'));
        }

        foreach ($array as $key => $value) {
            if (is_array($value) || $value instanceof Traversable) {
                if (static::isList($value)) {
                    static::toXml($value, $root, (string)$key);
                    continue;
                }

                static::toXml($value, $root->addChild((string)($rootName ?? $key)));
                continue;
            }

            $root->addChild((string)($rootName ?? $key), $value);
        }

        return $root;
    }

    /**
     * Merge two or more arrays recursively.
     *
     * Difference between native array_merge_recursive() is that
     * b_array_merge_recursive() do not merge strings values
     * into an array.
     *
     * @param array[] $arrays Arrays to merge
     *
     * @return array
     */
    public static function mergeRecursive(array ...$arrays): array
    {
        $arraySrc = array_shift($arrays);

        if (null === $arraySrc) {
            return [];
        }

        foreach ($arrays as $array) {
            if (empty($array)) {
                continue;
            }

            if (empty($arraySrc)) {
                $arraySrc = $array;
                continue;
            }

            if (self::isList($arraySrc) || self::isList($array)) {
                $arraySrc = array_merge($arraySrc, $array);
                continue;
            }

            foreach ($array as $key => $value) {
                if (!array_key_exists($key, $arraySrc)) {
                    $arraySrc[$key] = $value;
                    continue;
                }

                if (is_array($arraySrc[$key]) && is_array($value)) {
                    $arraySrc[$key] = self::mergeRecursive($arraySrc[$key], $value);
                    continue;
                }

                $arraySrc[$key] = $value;
            }
        }

        return $arraySrc;
    }

    private static function parseKey(string $key): array
    {
        $normalized = preg_replace('/\.([^.\[\]]+)/', '[$1]', $key);
        preg_match_all('/([^\[\]]+)/', $normalized, $matches);
        if (substr($key, -2) == '[]') {
            $matches[1][] = '';
        }

        return $matches[1];
    }

    /**
     * Traverse array with path and return if path exists.
     *
     * @param iterable $mixed Source
     * @param string $path Path
     *
     * @return bool
     */
    public static function traverseExists(iterable &$mixed, string $path): bool
    {
        $path = self::parseKey($path);

        $temp = &$mixed;
        foreach ($path as $key) {
            if (!is_iterable($temp)) {
                return false;
            }

            // An array, so we check existent of key
            if (is_array($temp) && !array_key_exists($key, $temp)) {
                return false;
            }

            // Not an array, so isset
            if (!is_array($temp) && !isset($key, $temp)) {
                return false;
            }

            $temp = &$temp[$key];
        }

        return true;
    }

    /**
     * Traverse array with path and get value.
     *
     * @param iterable $mixed Source
     * @param string $path Path
     * @param mixed $default Default value
     *
     * @return mixed|null
     */
    public static function traverseGet(iterable &$mixed, string $path, $default = null)
    {
        $path = self::parseKey($path);

        $temp = &$mixed;
        foreach ($path as $key) {
            if (!is_iterable($temp)) {
                return $default;
            }

            // An array, so we check existent of key
            if ((is_array($temp) || $temp instanceof ArrayObject) && !array_key_exists($key, $temp)) {
                return $default;
            }

            // Not an array, so isset
            if (!(is_array($temp) || $temp instanceof ArrayObject) && !isset($key, $temp)) {
                return $default;
            }

            $temp = &$temp[$key];
        }

        return $temp;
    }

    /**
     * Traverse array with path and set value.
     *
     * @param iterable $mixed Source
     * @param string $path Path
     * @param mixed $value Value
     *
     * @return bool
     */
    public static function traverseSet(iterable &$mixed, string $path, $value): bool
    {
        $path = self::parseKey($path);

        $temp = &$mixed;
        foreach ($path as $key) {
            if (null !== $temp && !is_iterable($temp)) {
                return false;
            }

            if ($key === '') {
                $temp[] = null;
                end($temp);
                $temp = &$temp[key($temp)];
                continue;
            }

            if (!isset($temp[$key])) {
                $temp[$key] = null;
            }

            $temp = &$temp[$key];
        }
        $temp = $value;

        return true;
    }

    /**
     * Transform multidimensional array to simple level.
     *
     * @param array $array
     * @param string|null $prefix
     *
     * @return array
     */
    public static function simpleArray(array $array, ?string $prefix = null): array
    {
        $output = [];

        foreach ($array as $key => $value) {
            // Prefix key if necessary
            if (null !== $prefix) {
                $key = $prefix . '.' . $key;
            }

            if (is_array($value)) {
                $output = array_merge($output, self::simpleArray($value, $key));
                continue;
            }

            $output[$key] = $value;
        }

        return $output;
    }

    /**
     * Transform simple level array to multidimensional.
     *
     * @param array $array
     *
     * @return array
     */
    public static function nestedArray(array $array): array
    {
        $output = [];

        foreach ($array as $key => $value) {
            // Normalize dot notation to bracket notation
            $normalized = preg_replace('/\.([^.\[\]]+)/', '[$1]', $key);

            // Extract segments (ex: foo[bar][baz] → ['foo', 'bar', 'baz'])
            preg_match_all('/([^\[\]]+)/', $normalized, $matches);
            $segments = $matches[1];

            $ref = &$output;

            foreach ($segments as $i => $segment) {
                $isLast = ($i === count($segments) - 1);

                if ($isLast) {
                    if ($segment === '') {
                        $ref[] = $value;
                        continue;
                    }

                    if (is_numeric($segment)) {
                        $ref[(int)$segment] = $value;
                        continue;
                    }

                    $ref[$segment] = $value;
                    continue;
                }

                if ($segment === '') {
                    $ref[] = [];
                    end($ref);
                    $ref = &$ref[key($ref)];
                    continue;
                }

                if (is_numeric($segment)) {
                    $segment = (int)$segment;
                    if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
                        $ref[$segment] = [];
                    }
                    $ref = &$ref[$segment];
                    continue;
                }

                if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
                    $ref[$segment] = [];
                }
                $ref = &$ref[$segment];
            }
        }

        return $output;
    }
}