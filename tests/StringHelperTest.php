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

use Berlioz\Helpers\StringHelper;
use PHPUnit\Framework\TestCase;

class StringHelperTest extends TestCase
{
    public function testRandom()
    {
        // Length
        $this->assertEquals(12, mb_strlen(StringHelper::random(12)));
        $this->assertEquals(256, mb_strlen(StringHelper::random(256)));
        $this->assertEquals(1024, mb_strlen(StringHelper::random(1024)));

        // Only alpha lower case
        $str = StringHelper::random(32, StringHelper::RANDOM_ALPHA | StringHelper::RANDOM_NEED_ALL);
        $this->assertMatchesRegularExpression('/^[a-z]+$/i', $str);
        $this->assertMatchesRegularExpression('/[a-z]+/', $str);
        $this->assertMatchesRegularExpression('/[A-Z]+/', $str);

        // Only alpha lower case
        $this->assertMatchesRegularExpression('/^[a-z]+$/', StringHelper::random(32, StringHelper::RANDOM_LOWER_CASE));

        // With alpha & numeric
        $str = StringHelper::random(32, StringHelper::RANDOM_ALPHA | StringHelper::RANDOM_NUMERIC);
        $this->assertMatchesRegularExpression('/^[a-z0-9]+$/i', $str);

        // With numeric
        $str = StringHelper::random(32, StringHelper::RANDOM_NUMERIC);
        $this->assertMatchesRegularExpression('/^[0-9]+$/', $str);

        // With special alpha & Not required
        $str = StringHelper::random(32, StringHelper::RANDOM_SPECIAL_CHARACTERS);
        $this->assertMatchesRegularExpression('/^[^a-z0-9]+$/i', $str);

        // With only numeric & special alpha & required
        $str = StringHelper::random(
            32,
            StringHelper::RANDOM_NUMERIC |
            StringHelper::RANDOM_SPECIAL_CHARACTERS |
            StringHelper::RANDOM_NEED_ALL
        );
        $this->assertMatchesRegularExpression('/[0-9]+/', $str);
        $this->assertDoesNotMatchRegularExpression('/[a-z]+/i', $str);
        $this->assertMatchesRegularExpression('/[^0-9a-z]+/i', $str);
    }

    public function testNl2p()
    {
        $this->assertEquals("<p>Test</p>\n<p>Test</p>", StringHelper::nl2p("Test\n\nTest"));
        $this->assertEquals("<p>Test<br />\nTest</p>", StringHelper::nl2p("Test\nTest"));
        $this->assertEquals(
            "<p>Test</p>\n<p>Test<br />\nTest</p>\n<p>Test</p>",
            StringHelper::nl2p("Test\n\nTest\nTest\n\nTest")
        );
    }

    public function testRemoveAccents()
    {
        $this->assertEquals('This is a wrong sentence!', StringHelper::removeAccents('Thîs îs à wrong séntènce!'));
        $this->assertEquals('I a lublu PHP!', StringHelper::removeAccents('И я люблю PHP!'));
        $this->assertEquals(
            'This is a wrong sentence!',
            StringHelper::removeAccents("Thîs îs à wrong s\xC3\xA9ntènce!")
        );
        $this->assertEquals('', StringHelper::removeAccents(utf8_decode("Thîs îs à wrong s\xC3\xA9ntènce!")));
    }

    public function testStrToUri()
    {
        $this->assertEquals('this-is-a-wrong-sentence', StringHelper::strToUri('Thîs îs à wrong séntènce!'));
        $this->assertEquals('i-a-lublu-php', StringHelper::strToUri('И я люблю PHP!'));
    }

    public function testMinifyHtml()
    {
        $this->assertEquals(
            "<p> Test </p> <p> Test test </p>",
            StringHelper::minifyHtml("<p>\nTest\n</p>\n<p>\nTest\n  test\n</p>")
        );
        $this->assertEquals(
            "<p> Test </p> <textarea>Test   test\nTest\n\n</textarea> <p> Test test </p>",
            StringHelper::minifyHtml(
                "<p>\nTest\n</p>\n<textarea>Test   test\nTest\n\n</textarea>\n<p>\nTest\n  test\n</p>"
            )
        );
    }

    public function testTruncate()
    {
        $this->assertEquals(
            'Domi ne agmina quos spatia praet ...',
            StringHelper::truncate(
                'Domi ne agmina quos spatia praetermitto ut equos silices quod.',
                32,
                StringHelper::TRUNCATE_RIGHT
            )
        );
        $this->assertEquals(
            'Domi ne agmina q ... os silices quod.',
            StringHelper::truncate(
                'Domi ne agmina quos spatia praetermitto ut equos silices quod.',
                32,
                StringHelper::TRUNCATE_MIDDLE
            )
        );
        $this->assertEquals(
            '... etermitto ut equos silices quod.',
            StringHelper::truncate(
                'Domi ne agmina quos spatia praetermitto ut equos silices quod.',
                32,
                StringHelper::TRUNCATE_LEFT
            )
        );
    }

    public function testParseStr()
    {
        $this->assertEquals(
            ['foo.bar' => ['foo.bar' => ['baz']], 'foo_bar' => 'qux'],
            StringHelper::parseStr('foo.bar[foo.bar][0]=baz&foo_bar=qux')
        );
        $this->assertEquals(
            ['first' => 'value', 'arr' => ['foo bar', 'baz']],
            StringHelper::parseStr('first=value&arr[]=foo+bar&arr[]=baz')
        );
        $this->assertEquals(
            ['first' => 'value', 'arr' => ['foo bar', 'baz']],
            StringHelper::parseStr('first=value&arr%5B%5D=foo+bar&arr%5b%5d=baz')
        );
        $this->assertEquals(
            ['first' => 'value', 'arr' => [1 => 'foo bar', 2 => 'baz']],
            StringHelper::parseStr('first=value&arr[1]=foo+bar&arr[2]=baz')
        );
        $this->assertEquals(
            ['foo' => 'baz'],
            StringHelper::parseStr('foo=bar&foo=baz')
        );
        $this->assertEquals(
            ['f oo.b:a r' => 'qux'],
            StringHelper::parseStr('f oo.b%3Aa+r=qux')
        );
        $this->assertEquals(
            ['foo' => 'qux', 'bar' => ''],
            StringHelper::parseStr('foo=qux&bar')
        );
        $this->assertEquals(
            ['first' => 'value', 'arr' => ['foo' => 'bar', 'qux' => 'baz']],
            StringHelper::parseStr('first=value&arr%5Bfoo%5D=bar&arr[qux]=baz')
        );
        $this->assertEquals(
            ['first' => 'value', 'arr' => ['foo bar', 'baz']],
            StringHelper::parseStr('first=value&arr%5B%5D=foo+bar&arr%5b%5d=baz')
        );
        $this->assertEquals(
            ['[first]' => 'val[ue', '[foo]' => 'bar', 'arr' => ['baz']],
            StringHelper::parseStr('%5Bfirst%5D=val%5Bue&%5Bfoo%5D=bar&arr%5b%5d=baz')
        );

        $this->assertEquals([], StringHelper::parseStr(''));
        $this->assertEquals([], StringHelper::parseStr('=foo'));
        $this->assertSame(['foo' => ''], StringHelper::parseStr('foo'));
    }

    public function testParseStr_dontKeepDots()
    {
        $this->assertEquals(
            ['foo_bar' => 'qux'],
            StringHelper::parseStr('foo.bar[foo.bar][0]=baz&foo_bar=qux', false)
        );
        $this->assertEquals(
            ['first' => 'value', 'arr' => ['foo bar', 'baz']],
            StringHelper::parseStr('first=value&arr[]=foo+bar&arr[]=baz', false)
        );
        $this->assertEquals(
            ['first' => 'value', 'arr' => ['foo bar', 'baz']],
            StringHelper::parseStr('first=value&arr%5B%5D=foo+bar&arr%5b%5d=baz', false)
        );
        $this->assertEquals(
            ['foo' => 'baz'],
            StringHelper::parseStr('foo=bar&foo=baz', false)
        );
        $this->assertEquals(
            ['f_oo_b:a_r' => 'qux'],
            StringHelper::parseStr('f oo.b%3Aa+r=qux', false)
        );
        $this->assertEquals(
            ['foo' => 'qux', 'bar' => ''],
            StringHelper::parseStr('foo=qux&bar', false)
        );
        $this->assertEquals(
            ['first' => 'value', 'arr' => ['foo' => 'bar', 'qux' => 'baz']],
            StringHelper::parseStr('first=value&arr%5Bfoo%5D=bar&arr[qux]=baz', false)
        );
        $this->assertEquals(
            ['first' => 'value', 'arr' => ['foo bar', 'baz']],
            StringHelper::parseStr('first=value&arr%5B%5D=foo+bar&arr%5b%5d=baz', false)
        );

        $this->assertEquals([], StringHelper::parseStr('', false));
        $this->assertEquals([], StringHelper::parseStr('=foo', false));
    }

    public function testPascalCase()
    {
        $this->assertEquals('MyVariable123foo', StringHelper::pascalCase('my_variable_123foo'));
        $this->assertEquals('FooBar', StringHelper::pascalCase('foo_bar'));
        $this->assertEquals('FooBar', StringHelper::pascalCase('fooBar'));
    }

    public function testCamelCase()
    {
        $this->assertEquals('myVariable123foo', StringHelper::camelCase('my_variable_123foo'));
        $this->assertEquals('fooBar', StringHelper::camelCase('foo_bar'));
        $this->assertEquals('fooBar', StringHelper::camelCase('FooBar'));
    }

    public function testSnakeCase()
    {
        $this->assertEquals('my_variable_123foo', StringHelper::snakeCase('my_variable_123foo'));
        $this->assertEquals('foo_bar', StringHelper::snakeCase('foo_bar'));
        $this->assertEquals('foo_bar', StringHelper::snakeCase('fooBar'));
        $this->assertEquals('foo_bar1234', StringHelper::snakeCase('fooBar1234'));
        $this->assertEquals('foo_bar1234_foo', StringHelper::snakeCase('fooBar1234Foo'));
        $this->assertEquals('foo_bar', StringHelper::snakeCase('FooBar'));
    }

    public function testSpinalCase()
    {
        $this->assertEquals('my-variable-123foo', StringHelper::spinalCase('my_variable_123foo'));
        $this->assertEquals('foo-bar', StringHelper::spinalCase('foo_bar'));
        $this->assertEquals('foo-bar', StringHelper::spinalCase('fooBar'));
        $this->assertEquals('foo-bar1234', StringHelper::spinalCase('fooBar1234'));
        $this->assertEquals('foo-bar1234-foo', StringHelper::spinalCase('fooBar1234Foo'));
        $this->assertEquals('foo-bar', StringHelper::spinalCase('FooBar'));
    }
}
