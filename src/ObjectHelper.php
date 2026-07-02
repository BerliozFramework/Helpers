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

use BadMethodCallException;
use InvalidArgumentException;
use ReflectionException;
use ReflectionObject;

/**
 * Class ObjectHelper.
 *
 * @package Berlioz\Helpers
 */
final class ObjectHelper
{
    /**
     * Get property value with getter method.
     *
     * @param object $object
     * @param string $property
     * @param bool $exists
     *
     * @return mixed
     * @throws ReflectionException
     */
    public static function getPropertyValue($object, string $property, &$exists = null)
    {
        $exists = false;

        if (!is_object($object)) {
            throw new InvalidArgumentException('Method excepts an object in first argument');
        }

        $reflectionObject = new ReflectionObject($object);

        // Different naming convention
        $methods = [
            sprintf('get%s', StringHelper::pascalCase($property)),
            sprintf('is%s', StringHelper::pascalCase($property)),
            sprintf('get_%s', StringHelper::snakeCase($property)),
            sprintf('is_%s', StringHelper::snakeCase($property))
        ];

        // Test different formats
        foreach ($methods as $method) {
            if ($exists = ($reflectionObject->hasMethod($method) &&
                $reflectionObject->getMethod($method)->isPublic())) {
                return $reflectionObject->getMethod($method)->invoke($object);
            }
        }

        // If property is public
        $exists = $reflectionObject->hasProperty($property) && $reflectionObject->getProperty($property)->isPublic();
        if ($exists) {
            return $object->$property;
        }

        // If magic methods __get() and __isset() are declared
        if ($reflectionObject->hasMethod('__isset') && $reflectionObject->hasMethod('__get')) {
            if ($exists = $object->__isset($property)) {
                return $object->__get($property);
            }
        }

        // Test __call() method
        if ($reflectionObject->hasMethod('__call')) {
            foreach ($methods as $method) {
                try {
                    $exists = true;

                    return $reflectionObject->getMethod('__call')->invoke($object, $method);
                } catch (BadMethodCallException $e) {
                    $exists = false;
                }
            }
        }

        return null;
    }

    /**
     * Has property?
     *
     * Checks whether a readable property exists, using the same detection logic
     * as getPropertyValue() (getter method, public property or magic methods).
     *
     * @param object $object
     * @param string $property
     *
     * @return bool
     * @throws ReflectionException
     */
    public static function hasProperty($object, string $property): bool
    {
        ObjectHelper::getPropertyValue($object, $property, $exists);

        return $exists;
    }

    /**
     * Set property value with setter method.
     *
     * @param object $object
     * @param string $property
     * @param mixed $value
     *
     * @return bool
     * @throws ReflectionException
     */
    public static function setPropertyValue($object, string $property, $value): bool
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException('Method excepts an object in first argument');
        }

        $reflectionObject = new ReflectionObject($object);

        // Different naming convention
        $methods = [
            sprintf('set%s', StringHelper::pascalCase($property)),
            sprintf('set_%s', StringHelper::snakeCase($property))
        ];

        // Test different formats
        foreach ($methods as $method) {
            if ($reflectionObject->hasMethod($method) &&
                $reflectionObject->getMethod($method)->isPublic()) {
                $reflectionObject->getMethod($method)->invoke($object, $value);
                return true;
            }
        }

        // If property is public
        if ($reflectionObject->hasProperty($property) &&
            $reflectionObject->getProperty($property)->isPublic()) {
            $object->$property = $value;

            return true;
        }

        // If magic methods __set() and __isset() are declared
        if ($reflectionObject->hasMethod('__isset') && $reflectionObject->hasMethod('__set')) {
            if ($object->__isset($property)) {
                $object->__set($property, $value);

                return true;
            }
        }

        // Test __call() method
        if ($reflectionObject->hasMethod('__call')) {
            foreach ($methods as $method) {
                try {
                    $reflectionObject->getMethod('__call')->invoke($object, $method, [$value]);

                    return true;
                } catch (BadMethodCallException $e) {
                }
            }
        }

        return false;
    }
}