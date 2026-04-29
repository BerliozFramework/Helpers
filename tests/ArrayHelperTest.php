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

    public function testColumnWithNonClosureIndexKey()
    {
        // Test with Closure column_key and string index_key (exercises the non-Closure index_key path)
        $array = [
            ['key1' => 'Foo', 'key2' => 'Bar'],
            ['key1' => 'Baz', 'key2' => 'Qux'],
        ];

        $result = ArrayHelper::column(
            $array,
            function ($value) {
                return $value['key2'];
            },
            'key1'
        );

        $this->assertEquals(['Foo' => 'Bar', 'Baz' => 'Qux'], $result);

        // Test with objects
        $array = [
            (object)['key1' => 'Foo', 'key2' => 'Bar'],
            (object)['key1' => 'Baz', 'key2' => 'Qux'],
        ];

        $result = ArrayHelper::column(
            $array,
            function ($value) {
                return $value->key2;
            },
            'key1'
        );

        $this->assertEquals(['Foo' => 'Bar', 'Baz' => 'Qux'], $result);
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

    public function testTraverseUnset()
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
            'foo9' => null,
        ];

        // Unset a top-level key
        $this->assertTrue(ArrayHelper::traverseUnset($tArray, 'foo'));
        $this->assertArrayNotHasKey('foo', $tArray);

        // Unset a nested key (dot notation)
        $this->assertTrue(ArrayHelper::traverseUnset($tArray, 'foo2.foo6.foo8'));
        $this->assertArrayNotHasKey('foo8', $tArray['foo2']['foo6']);

        // Unset a nested key (bracket notation)
        $this->assertTrue(ArrayHelper::traverseUnset($tArray, 'foo2[foo6][foo7]'));
        $this->assertEmpty($tArray['foo2']['foo6']);

        // Unset a null value key
        $this->assertTrue(ArrayHelper::traverseUnset($tArray, 'foo9'));
        $this->assertArrayNotHasKey('foo9', $tArray);

        // Unset a non-existent key returns false
        $this->assertFalse(ArrayHelper::traverseUnset($tArray, 'nonexistent'));

        // Unset a non-existent nested key returns false
        $this->assertFalse(ArrayHelper::traverseUnset($tArray, 'foo2.foo999.foo8'));

        // Traverse into scalar returns false
        $this->assertFalse(ArrayHelper::traverseUnset($tArray, 'foo2.foo5.bar'));

        // Verify remaining structure is intact
        $this->assertEquals(
            [
                'foo2' => [
                    'foo3' => ['foo4' => 'bar4'],
                    'foo5' => 'bar5',
                    'foo6' => [],
                ],
            ],
            $tArray,
        );
    }

    private static function getTraverseTestArray(): array
    {
        return [
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
            'special/key' => 'slash-value',
            'special~key' => 'tilde-value',
            'list' => ['first', 'second', 'third'],
        ];
    }

    public function traverseExistsProvider(): array
    {
        return [
            // Dot notation
            'dot: top-level' => ['foo', true],
            'dot: nested 2 levels' => ['foo2.foo6', true],
            'dot: nested 3 levels' => ['foo2.foo6.foo8', true],
            'dot: null value exists' => ['foo2.foo6.foo9', true],
            'dot: nonexistent top-level' => ['bar', false],
            'dot: missing intermediate' => ['foo2.foo999.foo8', false],
            'dot: wrong level' => ['foo3.foo4', false],
            'dot: traverse into scalar' => ['foo.bar.foo', false],
            'dot: nonexistent nested' => ['bar.foo', false],

            // Bracket notation
            'bracket: missing intermediate' => ['foo2[foo999][foo8]', false],

            // JSON Pointer
            'pointer: top-level' => ['/foo', true],
            'pointer: nested 2 levels' => ['/foo2/foo6', true],
            'pointer: nested 3 levels' => ['/foo2/foo6/foo8', true],
            'pointer: null value exists' => ['/foo2/foo6/foo9', true],
            'pointer: nonexistent top-level' => ['/bar', false],
            'pointer: missing intermediate' => ['/foo2/foo999/foo8', false],
            'pointer: traverse into scalar' => ['/foo/bar/foo', false],
            'pointer: escape slash ~1' => ['/special~1key', true],
            'pointer: escape tilde ~0' => ['/special~0key', true],
            'pointer: numeric index' => ['/list/0', true],
            'pointer: numeric index OOB' => ['/list/5', false],
        ];
    }

    /**
     * @dataProvider traverseExistsProvider
     */
    public function testTraverseExistsWithProvider(string $path, bool $expected): void
    {
        $tArray = self::getTraverseTestArray();
        $this->assertSame($expected, ArrayHelper::traverseExists($tArray, $path));
    }

    public function traverseGetProvider(): array
    {
        return [
            // Dot notation
            'dot: top-level' => ['foo', null, 'bar'],
            'dot: nested' => ['foo2.foo6.foo8', null, 'bar8'],
            'dot: null value' => ['foo2.foo6.foo9', null, null],
            'dot: missing path' => ['foo2.foo999.foo8', null, null],
            'dot: missing with default' => ['foo2.foo999.foo8', 'dflt', 'dflt'],
            'dot: nonexistent' => ['foo3.foo4', null, null],
            'dot: traverse into scalar' => ['foo.bar.foo', null, null],
            'dot: missing with default 2' => ['bar.foo', 'dflt', 'dflt'],

            // Bracket notation
            'bracket: missing with default' => ['foo2[foo999].foo8', 'dflt', 'dflt'],

            // JSON Pointer
            'pointer: top-level' => ['/foo', null, 'bar'],
            'pointer: nested' => ['/foo2/foo6/foo8', null, 'bar8'],
            'pointer: null value' => ['/foo2/foo6/foo9', null, null],
            'pointer: missing path' => ['/foo2/foo999/foo8', null, null],
            'pointer: missing with default' => ['/foo2/foo999/foo8', 'dflt', 'dflt'],
            'pointer: traverse into scalar' => ['/foo/bar/foo', null, null],
            'pointer: escape slash ~1' => ['/special~1key', null, 'slash-value'],
            'pointer: escape tilde ~0' => ['/special~0key', null, 'tilde-value'],
            'pointer: numeric index 0' => ['/list/0', null, 'first'],
            'pointer: numeric index 2' => ['/list/2', null, 'third'],
            'pointer: numeric index OOB' => ['/list/5', 'dflt', 'dflt'],
        ];
    }

    /**
     * @dataProvider traverseGetProvider
     */
    public function testTraverseGetWithProvider(string $path, $default, $expected): void
    {
        $tArray = self::getTraverseTestArray();
        $this->assertSame($expected, ArrayHelper::traverseGet($tArray, $path, $default));
    }

    public function traverseSetProvider(): array
    {
        return [
            // Dot notation
            'dot: overwrite top-level' => ['foo', 'bob', true, 'bob'],
            'dot: overwrite nested' => ['foo2.foo6.foo8', 'bob8', true, 'bob8'],
            'dot: create nested path' => ['foo2.foo999.foo8', 'new', true, 'new'],
            'dot: traverse into scalar' => ['foo.bar.baz', 'val', false, null],

            // Bracket notation
            'bracket: create nested' => ['bar[foo]', 'baz', true, 'baz'],

            // JSON Pointer
            'pointer: overwrite top-level' => ['/foo', 'bob', true, 'bob'],
            'pointer: overwrite nested' => ['/foo2/foo6/foo8', 'bob8', true, 'bob8'],
            'pointer: create nested path' => ['/foo2/foo999/foo8', 'new', true, 'new'],
            'pointer: traverse into scalar' => ['/foo/bar/baz', 'val', false, null],
        ];
    }

    /**
     * @dataProvider traverseSetProvider
     */
    public function testTraverseSetWithProvider(string $path, $value, bool $expectedResult, $expectedValue): void
    {
        $tArray = self::getTraverseTestArray();
        $this->assertSame($expectedResult, ArrayHelper::traverseSet($tArray, $path, $value));

        if ($expectedResult) {
            $this->assertSame($expectedValue, ArrayHelper::traverseGet($tArray, $path));
        }
    }

    public function testTraverseSetAppend(): void
    {
        $tArray = self::getTraverseTestArray();

        // Legacy append
        $this->assertTrue(ArrayHelper::traverseSet($tArray, 'list[]', 'fourth'));
        $this->assertSame('fourth', $tArray['list'][3]);

        // JSON Pointer append with -
        $this->assertTrue(ArrayHelper::traverseSet($tArray, '/list/-', 'fifth'));
        $this->assertSame('fifth', $tArray['list'][4]);
    }

    public function traverseUnsetProvider(): array
    {
        return [
            // Dot notation
            'dot: top-level' => ['foo', true],
            'dot: nested' => ['foo2.foo6.foo8', true],
            'dot: null value' => ['foo2.foo6.foo9', true],
            'dot: nonexistent' => ['nonexistent', false],
            'dot: missing intermediate' => ['foo2.foo999.foo8', false],
            'dot: traverse into scalar' => ['foo.bar.baz', false],

            // Bracket notation
            'bracket: nested' => ['foo2[foo6][foo7]', true],

            // JSON Pointer
            'pointer: top-level' => ['/foo', true],
            'pointer: nested' => ['/foo2/foo6/foo8', true],
            'pointer: nonexistent' => ['/nonexistent', false],
            'pointer: missing intermediate' => ['/foo2/foo999/foo8', false],
            'pointer: traverse into scalar' => ['/foo/bar/baz', false],
            'pointer: escape slash ~1' => ['/special~1key', true],
            'pointer: escape tilde ~0' => ['/special~0key', true],
        ];
    }

    /**
     * @dataProvider traverseUnsetProvider
     */
    public function testTraverseUnsetWithProvider(string $path, bool $expectedResult): void
    {
        $tArray = self::getTraverseTestArray();
        $this->assertSame($expectedResult, ArrayHelper::traverseUnset($tArray, $path));

        if ($expectedResult) {
            $this->assertFalse(ArrayHelper::traverseExists($tArray, $path));
        }
    }

    public function testJsonPointerEscaping(): void
    {
        $tArray = [
            '~1' => 'tilde-one',
            'a/b' => 'slash',
            '' => 'empty-key',
        ];

        // ~01 decodes to literal key "~1" (~0 → ~, then 1 stays)
        $this->assertSame('tilde-one', ArrayHelper::traverseGet($tArray, '/~01'));

        // ~1 decodes to "/"
        $this->assertSame('slash', ArrayHelper::traverseGet($tArray, '/a~1b'));

        // Empty key (single slash = key "")
        $this->assertSame('empty-key', ArrayHelper::traverseGet($tArray, '/'));
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
