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

use Berlioz\Helpers\ArrayHelper;
use PHPUnit\Framework\TestCase;

class ArrayHelperTest extends TestCase
{
    public function testIsList()
    {
        $this->assertTrue(ArrayHelper::isList([]));
        $this->assertTrue(ArrayHelper::isList(['foo', 'bar', 'hello', 'world']));
        $this->assertTrue(ArrayHelper::isList([0 => 'foo', 1 => 'bar', 2 => 'hello', 3 => 'world']));

        $this->assertFalse(ArrayHelper::isList(['0' => 'foo', '2' => 'bar', '1' => 'hello', '3' => 'world']));
        $this->assertFalse(ArrayHelper::isList([0 => 'foo', 2 => 'bar', 1 => 'hello', 3 => 'world']));
        $this->assertFalse(ArrayHelper::isList(['bar' => 'foo', 'foo' => 'bar', '1' => 'hello', '3' => 'world']));
        $this->assertFalse(ArrayHelper::isList(['bar' => 'foo', 'foo' => 'bar', 1 => 'hello', 3 => 'world']));
        $this->assertFalse(ArrayHelper::isList(['00' => 'foo', '01' => 'bar', '02' => 'hello', '03' => 'world']));
    }

    public function testColumn()
    {
        $this->assertEquals(
            ArrayHelper::column([['Foo', 'Bar'], ['Baz', 'Qux']], 1, 0),
            array_column([['Foo', 'Bar'], ['Baz', 'Qux']], 1, 0),
        );
        $this->assertEquals(
            ArrayHelper::column(
                [['key1' => 'Foo', 'key2' => 'Bar'], ['key1' => 'Baz', 'key2' => 'Qux']],
                'key2',
                'key1'
            ),
            array_column([['key1' => 'Foo', 'key2' => 'Bar'], ['key1' => 'Baz', 'key2' => 'Qux']], 'key2', 'key1'),
        );
        $this->assertEquals(
            ArrayHelper::column(
                [['key1' => 'Foo', 'key2' => 'Bar'], ['key1' => 'Baz', 'key2' => 'Qux']],
                function ($value) {
                    return $value['key2'];
                },
                function ($value) {
                    return $value['key1'];
                }
            ),
            array_column([['key1' => 'Foo', 'key2' => 'Bar'], ['key1' => 'Baz', 'key2' => 'Qux']], 'key2', 'key1'),
        );

        $array = [
            $obj1 = new class {
                public $key1 = 'Foo';
                public $key2 = 'Bar';
            },
            $obj2 = new class {
                public $key1 = 'Baz';
                public $key2 = 'Qux';
            }
        ];
        $this->assertEquals(
            ArrayHelper::column(
                $array,
                function ($value) {
                    return $value->key2;
                },
                function ($value) {
                    return $value->key1;
                }
            ),
            array_column($array, 'key2', 'key1'),
        );
        $this->assertEquals(
            ArrayHelper::column(
                $array,
                'key2',
                function ($value) {
                    return $value->key1;
                }
            ),
            array_column($array, 'key2', 'key1'),
        );
    }

    public function testColumnWithClosure()
    {
        $array = [
            $obj1 = new class {
                private $key1 = 'Foo';
                private $key2 = 'Bar';

                public function getKey1(): string
                {
                    return $this->key1;
                }

                public function getKey2(): string
                {
                    return $this->key2;
                }
            },
            $obj2 = new class {
                private $key1 = 'Baz';
                private $key2 = 'Qux';

                public function getKey1(): string
                {
                    return $this->key1;
                }

                public function getKey2(): string
                {
                    return $this->key2;
                }
            }
        ];

        $this->assertEquals(
            [
                'Bar' => 'Foo',
                'Qux' => 'Baz',
            ],
            ArrayHelper::column(
                $array,
                function ($value) {
                    return $value->getKey1();
                },
                function ($value) {
                    return $value->getKey2();
                }
            )
        );
        $this->assertEquals(
            [
                'Bar' => $obj1,
                'Qux' => $obj2,
            ],
            ArrayHelper::column(
                $array,
                null,
                function ($value) {
                    return $value->getKey2();
                }
            )
        );
    }

    /**
     * @requires extension simplexml
     */
    public function testToXml()
    {
        $array = [
            'foo' => 'Bar',
            'bar' => [
                'bar1' => 'Foo1',
                'bar2' => 'Foo2',
                'bar3' => 'Foo3',
            ],
            'fooSeq' => [
                'foo',
                'bar',
            ],
        ];

        $xmlExcepted = ArrayHelper::toXml($array);
        $this->assertEquals(
            "<?xml version=\"1.0\"?>\n" .
            "<root><foo>Bar</foo><bar><bar1>Foo1</bar1><bar2>Foo2</bar2><bar3>Foo3</bar3></bar><fooSeq>foo</fooSeq><fooSeq>bar</fooSeq></root>\n",
            $xmlExcepted->asXML()
        );

        $array = [
            'foo',
            'foo2',
            'foo3',
            'bar',
            'bar2',
            'bar3',
        ];

        $xmlExcepted = ArrayHelper::toXml($array, null, 'test');
        $this->assertEquals(
            "<?xml version=\"1.0\"?>\n" .
            "<root><test>foo</test><test>foo2</test><test>foo3</test><test>bar</test><test>bar2</test><test>bar3</test></root>\n",
            $xmlExcepted->asXML()
        );
    }

    public function testMergeRecursive()
    {
        $arr1 = [
            'foo' => 'hello',
            'bar' => 'world',
            'test' => ['foo', 'bar', 'hello' => 'world'],
        ];
        $arr2 = ['test' => ['hello', 'foo']];
        $arr3 = ['foo' => 'world'];
        $arr4 = [
            'foo' => 'world',
            'test' => ['hello' => 'world2'],
        ];
        $arr5 = [
            'foo' => 'world',
            'test' => ['hello' => ['world2', 'world3']],
        ];

        $this->assertEquals(
            [
                'foo' => 'hello',
                'bar' => 'world',
                'test' => ['foo', 'bar', 'hello', 'foo', 'hello' => 'world'],
            ],
            ArrayHelper::mergeRecursive($arr1, $arr2)
        );
        $this->assertEquals(
            [
                'foo' => 'world',
                'bar' => 'world',
                'test' => ['foo', 'bar', 'hello' => 'world'],
            ],
            ArrayHelper::mergeRecursive($arr1, $arr3)
        );
        $this->assertEquals(
            [
                'foo' => 'world',
                'bar' => 'world',
                'test' => ['foo', 'bar', 'hello' => 'world2'],
            ],
            ArrayHelper::mergeRecursive($arr1, $arr4)
        );
        $this->assertEquals(
            [
                'foo' => 'world',
                'bar' => 'world',
                'test' => ['foo', 'bar', 'hello' => ['world2', 'world3']],
            ],
            ArrayHelper::mergeRecursive($arr1, $arr5)
        );

        $this->assertEquals(
            [
                321 => '321 value',
                'foo' => 'foo value',
                'bar' => 'bar value',
                123 => '123 value',
            ],
            ArrayHelper::mergeRecursive(
                ['321' => '321 value'],
                [],
                ['foo' => 'foo value', 'bar' => 'bar value', '123' => '123 value'],
                [],
            )
        );
        $this->assertEquals(
            [
                'foo' => 'foo value',
                'bar' => 'bar value',
                123 => '123 value',
                321 => '321 value',
            ],
            ArrayHelper::mergeRecursive(
                [],
                ['foo' => 'foo value', 'bar' => 'bar value', '123' => '123 value'],
                ['321' => '321 value'],
            )
        );

        $this->assertEquals([], ArrayHelper::mergeRecursive());
    }

    public function testTraverseExists()
    {
        $tArray = [
            'foo' => 'bar',
            'foo2' => [
                'foo3' => ['foo4' => 'bar4'],
                'foo5' => 'bar5',
                'foo6' => [
                    'foo7' => 'bar7',
                    'foo8' => 'bar8',
                    'foo9' => null,
                ],
            ],
        ];

        $this->assertTrue(ArrayHelper::traverseExists($tArray, 'foo'));
        $this->assertTrue(ArrayHelper::traverseExists($tArray, 'foo2.foo6'));
        $this->assertTrue(ArrayHelper::traverseExists($tArray, 'foo2.foo6.foo8'));
        $this->assertTrue(ArrayHelper::traverseExists($tArray, 'foo2.foo6.foo9'));
        $this->assertFalse(ArrayHelper::traverseExists($tArray, 'bar'));
        $this->assertFalse(ArrayHelper::traverseExists($tArray, 'foo2.foo999.foo8'));
        $this->assertFalse(ArrayHelper::traverseExists($tArray, 'foo2[foo999][foo8]'));
        $this->assertFalse(ArrayHelper::traverseExists($tArray, 'foo3.foo4'));
        $this->assertFalse(ArrayHelper::traverseExists($tArray, 'foo.bar.foo'));
        $this->assertFalse(ArrayHelper::traverseExists($tArray, 'bar.foo'));
    }

    public function testTraverseGet()
    {
        $tArray = [
            'foo' => 'bar',
            'foo2' => [
                'foo3' => ['foo4' => 'bar4'],
                'foo5' => 'bar5',
                'foo6' => [
                    'foo7' => 'bar7',
                    'foo8' => 'bar8',
                    'foo9' => null,
                ],
            ],
        ];

        $this->assertEquals('bar', ArrayHelper::traverseGet($tArray, 'foo'));
        $this->assertEquals('bar8', ArrayHelper::traverseGet($tArray, 'foo2.foo6.foo8'));
        $this->assertEquals(null, ArrayHelper::traverseGet($tArray, 'foo2.foo6.foo9'));
        $this->assertEquals(null, ArrayHelper::traverseGet($tArray, 'foo2.foo999.foo8'));
        $this->assertEquals('bar', ArrayHelper::traverseGet($tArray, 'foo2.foo999.foo8', 'bar'));
        $this->assertEquals('bar', ArrayHelper::traverseGet($tArray, 'foo2[foo999].foo8', 'bar'));
        $this->assertEquals(null, ArrayHelper::traverseGet($tArray, 'foo3.foo4'));
        $this->assertEquals(null, ArrayHelper::traverseGet($tArray, 'foo.bar.foo'));
        $this->assertEquals('bar', ArrayHelper::traverseGet($tArray, 'bar.foo', 'bar'));
    }

    public function testTraverseSet()
    {
        $tArray = [
            'foo' => 'bar',
            'foo2' => [
                'foo3' => ['foo4' => 'bar4'],
                'foo5' => 'bar5',
                'foo6' => [
                    'foo7' => 'bar7',
                    'foo8' => 'bar8',
                ],
            ],
        ];

        $this->assertTrue(ArrayHelper::traverseSet($tArray, 'foo', 'bob'));
        $this->assertTrue(ArrayHelper::traverseSet($tArray, 'foo2.foo6.foo8', 'bob8'));
        $this->assertTrue(ArrayHelper::traverseSet($tArray, 'foo2.foo999.foo8', 'bob999'));
        $this->assertFalse(ArrayHelper::traverseSet($tArray, 'foo.bar.foo', 'bar'));
        $this->assertTrue(ArrayHelper::traverseSet($tArray, 'bar[foo]', 'baz'));
        $this->assertTrue(ArrayHelper::traverseSet($tArray, 'foo3[]', 'foo3.1'));
        $this->assertTrue(ArrayHelper::traverseSet($tArray, 'foo3[]', 'foo3.2'));

        $this->assertEquals(
            [
                'foo' => 'bob',
                'foo2' => [
                    'foo3' => ['foo4' => 'bar4'],
                    'foo5' => 'bar5',
                    'foo6' => [
                        'foo7' => 'bar7',
                        'foo8' => 'bob8',
                    ],
                    'foo999' => [
                        'foo8' => 'bob999'
                    ]
                ],
                'bar' => [
                    'foo' => 'baz',
                ],
                'foo3' => [
                    'foo3.1',
                    'foo3.2'
                ]
            ],
            $tArray,
        );
    }

    public function testSimpleArray()
    {
        $arr = [
            'foo' => 'bar',
            'foo2' => [
                'foo3' => ['foo4' => 'bar4'],
                'foo5' => 'bar5',
                'foo6' => [
                    'foo7' => 'bar7',
                    'foo8' => 'bar8',
                ],
            ],
        ];

        $this->assertEquals(
            [
                'foo' => 'bar',
                'foo2.foo3.foo4' => 'bar4',
                'foo2.foo5' => 'bar5',
                'foo2.foo6.foo7' => 'bar7',
                'foo2.foo6.foo8' => 'bar8',
            ],
            ArrayHelper::simpleArray($arr),
        );
        $this->assertEquals(
            [
                'prefix.foo' => 'bar',
                'prefix.foo2.foo3.foo4' => 'bar4',
                'prefix.foo2.foo5' => 'bar5',
                'prefix.foo2.foo6.foo7' => 'bar7',
                'prefix.foo2.foo6.foo8' => 'bar8',
            ],
            ArrayHelper::simpleArray($arr, 'prefix'),
        );
    }

    public function testNestedArray()
    {
        $arr = [
            'foo' => 'bar',
            'foo2.foo3.foo4' => 'bar4',
            'foo2.foo5' => 'bar5',
            'foo2.foo6.foo7' => 'bar7',
            'foo2.foo6[foo8]' => 'bar8',
            'foo2.foo6.foo9[0]' => 'bar9',
            'foo2[foo6].foo9[1]' => 'bar9bis',
        ];

        $this->assertEquals(
            [
                'foo' => 'bar',
                'foo2' => [
                    'foo3' => ['foo4' => 'bar4'],
                    'foo5' => 'bar5',
                    'foo6' => [
                        'foo7' => 'bar7',
                        'foo8' => 'bar8',
                        'foo9' => [
                            'bar9',
                            'bar9bis'
                        ]
                    ],
                ],
            ],
            ArrayHelper::nestedArray($arr),
        );
    }
}
