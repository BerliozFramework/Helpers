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

use Berlioz\Helpers\ObjectHelper;
use PHPUnit\Framework\TestCase;

class ObjectHelperTest extends TestCase
{
    public function provider()
    {
        return
            new class() {
                private $foo = 'bar';
                private $foo2 = 'bar2';
                public $foo3 = 'bar3';
                private $foo4 = 'bar4';

                public function getFoo()
                {
                    return $this->foo;
                }

                public function setFoo($value)
                {
                    $this->foo = $value;
                }

                public function getFoo2()
                {
                    return $this->foo2;
                }

                public function setFoo2($value)
                {
                    $this->foo2 = $value;
                }

                public function get_foo3()
                {
                    return $this->foo3;
                }

                public function __call($name, $args = [])
                {
                    if ($name == 'set_foo4') {
                        $this->foo4 = $args[0];

                        return;
                    }

                    throw new \BadMethodCallException(sprintf('Method "%s" does not exists', $name));
                }
            };
    }

    public function testGetPropertyValue()
    {
        $obj = $this->provider();
        $exists = false;

        $this->assertEquals('bar', ObjectHelper::getPropertyValue($obj, 'foo'));
        $this->assertEquals('bar2', ObjectHelper::getPropertyValue($obj, 'foo2', $exists));
        $this->assertTrue($exists);
        $this->assertEquals('bar3', ObjectHelper::getPropertyValue($obj, 'foo3', $exists));
        $this->assertTrue($exists);
        $this->assertNull(ObjectHelper::getPropertyValue($obj, 'foo4', $exists));
        $this->assertFalse($exists);
    }

    public function testGetPropertyValueViaMagicCall()
    {
        $obj = new class() {
            private $foo = 'bar';

            public function __call($name, $args = [])
            {
                if ($name === 'getFoo') {
                    return $this->foo;
                }
                throw new \BadMethodCallException(sprintf('Method "%s" does not exist', $name));
            }
        };

        $exists = false;
        $result = ObjectHelper::getPropertyValue($obj, 'foo', $exists);
        $this->assertEquals('bar', $result);
        $this->assertTrue($exists);
    }

    public function testSetPropertyValue()
    {
        $obj = $this->provider();

        $this->assertTrue(ObjectHelper::setPropertyValue($obj, 'foo', 'bob'));
        $this->assertEquals('bob', ObjectHelper::getPropertyValue($obj, 'foo'));
        $this->assertTrue(ObjectHelper::setPropertyValue($obj, 'foo2', 'bob2'));
        $this->assertEquals('bob2', ObjectHelper::getPropertyValue($obj, 'foo2'));
        $this->assertTrue(ObjectHelper::setPropertyValue($obj, 'foo3', 'bob3'));
        $this->assertEquals('bob3', ObjectHelper::getPropertyValue($obj, 'foo3'));

        $objReflection = new \ReflectionObject($obj);
        $propertyReflection = $objReflection->getProperty('foo4');
        $propertyReflection->setAccessible(true);
        $this->assertTrue(ObjectHelper::setPropertyValue($obj, 'foo4', 'bob4'));
        $this->assertEquals('bob4', $propertyReflection->getValue($obj));
    }
}
