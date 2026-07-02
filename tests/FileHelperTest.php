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

use Berlioz\Helpers\FileHelper;
use PHPUnit\Framework\TestCase;

class FileHelperTest extends TestCase
{
    public function testHumanFileSize()
    {
        $this->assertEquals('foo', FileHelper::humanFileSize('foo'));
        $this->assertEquals('200 bytes', FileHelper::humanFileSize(200));
        $this->assertEquals('1 KB', FileHelper::humanFileSize(1024));
        $this->assertEquals('976.56 KB', FileHelper::humanFileSize(1000000));
        $this->assertEquals('976.563 KB', FileHelper::humanFileSize(1000000, 3));
        $this->assertEquals('977 KB', FileHelper::humanFileSize(1000000, 0));
        $this->assertEquals('2 MB', FileHelper::humanFileSize(2097152));
        $this->assertEquals('1 GB', FileHelper::humanFileSize(pow(1024, 3)));
        $this->assertEquals('1 TB', FileHelper::humanFileSize(pow(1024, 4)));
        $this->assertEquals('1 PB', FileHelper::humanFileSize(pow(1024, 5)));
        $this->assertEquals('1024 PB', FileHelper::humanFileSize(pow(1024, 6)));
    }

    public function testSizeFromIni()
    {
        $this->assertEquals(100, FileHelper::sizeFromIni('100'));
        $this->assertEquals(102400, FileHelper::sizeFromIni('100k'));
        $this->assertEquals(102400, FileHelper::sizeFromIni('100kb'));
        $this->assertEquals(104857600, FileHelper::sizeFromIni('100m'));
        $this->assertEquals(104857600, FileHelper::sizeFromIni('100mb'));
        $this->assertEquals(107374182400, FileHelper::sizeFromIni('100g'));
        $this->assertEquals(107374182400, FileHelper::sizeFromIni('100gb'));
        $this->assertEquals(1, FileHelper::sizeFromIni('1foo'));
        $this->assertEquals(0, FileHelper::sizeFromIni('foo'));
    }

    public function absolutePathProvider(): array
    {
        return [
            ['index.md', 'foo/bar/foo.md', '/foo/bar/foo.md'],
            ['/index.md', '/foo/bar/baz.md', '/foo/bar/baz.md'],
            ['/index.md', 'bar.md', '/bar.md'],
            ['/index.md', '/baz.md', '/baz.md'],
            ['foo/bar/index.md', 'baz/qux.md', '/foo/bar/baz/qux.md'],
            ['foo/bar/index.md', '/baz/qux.md', '/baz/qux.md'],
            ['foo/bar/index.md', './baz.md', '/foo/bar/baz.md'],
            ['foo/bar/index.md', '../baz.md', '/foo/baz.md'],
            ['foo/bar/index.md', '../../qux.md', '/qux.md'],
            ['foo/bar/index', '../../qux/quux.foo', '/qux/quux.foo'],
            ['foo/bar/index.md', '../../../qux.md', null],
            ['foo/bar/index', '../../qux/quux.foo#anchor', '/qux/quux.foo#anchor'],
            ['foo/bar/index', '../../', '/'],
            ['foo/bar/index', '../..', '/'],
            ['foo/bar/', '../', '/foo/'],
            ['foo/bar/', '..', '/foo/'],
            ['foo/bar/', '../qux', '/foo/qux'],
            ['foo/bar/', '', '/foo/bar/'],
            ['foo/bar', '', '/foo/bar'],
            ['/foo/bar/', '/foo/bar.html', '/foo/bar.html'],
            ['/foo/bar/', '/foo/bar/baz/qux.html', '/foo/bar/baz/qux.html'],
            ['/foo/ba/', '/foo/ba/baz/qux.html', '/foo/ba/baz/qux.html'],
            // Paths with double slashes should be normalized
            ['foo//bar//index.md', 'baz.md', '/foo/bar/baz.md'],
            ['foo//bar/index.md', '../qux.md', '/foo/qux.md'],
        ];
    }

    /**
     * @param $src
     * @param $dst
     * @param $expected
     *
     * @dataProvider absolutePathProvider
     */
    public function testResolveAbsolutePath($src, $dst, $expected)
    {
        $this->assertEquals($expected, FileHelper::resolveAbsolutePath($src, $dst));
    }

    public function relativePathProvider(): array
    {
        return [
            ['index.md', 'foo/bar/foo.md', './foo/bar/foo.md'],
            ['index.md', '/foo/bar/baz.md', './foo/bar/baz.md'],
            ['/index.md', 'foo/bar/bar.md', './foo/bar/bar.md'],
            ['/index.md', 'bar.md', './bar.md'],
            ['/index.md', '/baz.md', './baz.md'],
            ['./index.md', '/qux.md', './qux.md'],
            ['index.md', 'quux.md', './quux.md'],
            ['foo/index.md', '/foo/baz.md', './baz.md'],
            ['./foo/bar/index.md', '/baz.md', '../../baz.md'],
            ['/foo/bar/index.md', '/qux/baz.md', '../../qux/baz.md'],
            ['/foo/bar/index.md', 'qux/baz.md', './qux/baz.md'],
            ['/foo/bar/quux/index.md', '/foo/qux/corge/baz.md', '../../qux/corge/baz.md'],
            ['./foo/index.md', './bar/baz.md', './bar/baz.md'],
            ['foo/index.md', '/bar/baz.md', '../bar/baz.md'],
            ['/foo/bar/index.md', '/foo/qux/baz.md', '../qux/baz.md'],
            ['/foo/bar/baz/qux/index.md', '/foo/qux/bar/baz/baz.md', '../../../qux/bar/baz/baz.md'],
            ['./foo/index.md', '../bar/baz.md', '../bar/baz.md'],
            ['./foo/index.md', '../foo/baz.md', './baz.md'],
            ['./foo/index.md', '../foo/baz.md#anchor', './baz.md#anchor'],
            ['/foo/bar/index.md', '/foo/qux/', '../qux/'],
            ['/foo/bar/index.md', '../../', '../../'],
            ['/foo/bar/index.md', '..', '../'],
            ['/foo/bar/index.md', '../', '../'],
            ['/foo/bar/', '', './'],
            ['/foo/bar/index.md', '', './index.md'],
        ];
    }

    /**
     * @dataProvider relativePathProvider
     */
    public function testResolveRelativePath($src, $dst, $expected)
    {
        $this->assertEquals($expected, FileHelper::resolveRelativePath($src, $dst));
    }

    public function testFwritei()
    {
        $resource = fopen('php://memory', 'w+');
        fwrite($resource, 'Populari magistro');

        FileHelper::fwritei($resource, ' commeatus');

        $this->assertEquals(
            'Populari magistro commeatus',
            stream_get_contents($resource, -1, 0)
        );

        fseek($resource, 9);
        FileHelper::fwritei($resource, 'quae referente ');

        $this->assertEquals(
            'Populari quae referente magistro commeatus',
            stream_get_contents($resource, -1, 0)
        );

        fseek($resource, 0);
        FileHelper::fwritei($resource, 'BERLIOZ: ');

        $this->assertEquals(
            'BERLIOZ: Populari quae referente magistro commeatus',
            stream_get_contents($resource, -1, 0)
        );
    }

    public function testFwritei_withLength()
    {
        $resource = fopen('php://memory', 'w+');
        fwrite($resource, 'Populari magistro');

        FileHelper::fwritei($resource, ' commeatus', 4, 8);

        $this->assertEquals(
            'Populari com magistro',
            stream_get_contents($resource, -1, 0)
        );
    }

    public function testFwritei_withOffset()
    {
        $resource = fopen('php://memory', 'w+');
        fwrite($resource, 'Populari magistro');

        FileHelper::fwritei($resource, ' commeatus', null, 8);

        $this->assertEquals(
            'Populari commeatus magistro',
            stream_get_contents($resource, -1, 0)
        );
    }

    public function testFtruncate()
    {
        $resource = fopen('php://memory', 'w+');
        fwrite($resource, 'Populari magistro');

        FileHelper::ftruncate($resource, 13);

        $this->assertEquals(
            'Populari magi',
            stream_get_contents($resource, -1, 0)
        );

        FileHelper::ftruncate($resource, 4, 4);

        $this->assertEquals(
            'Popu magi',
            stream_get_contents($resource, -1, 0)
        );
    }

    public function testFtruncateWithLargeData()
    {
        $resource = fopen('php://memory', 'w+');

        // Create data larger than the internal buffer size (8192 bytes)
        $prefix = 'HEAD';
        $toRemove = str_repeat('X', 100);
        $suffix = str_repeat('A', 20000);
        fwrite($resource, $prefix . $toRemove . $suffix);

        // Remove the 100 'X' characters at offset 4
        FileHelper::ftruncate($resource, 100, 4);

        $result = stream_get_contents($resource, -1, 0);
        $this->assertEquals(strlen($prefix) + strlen($suffix), strlen($result));
        $this->assertEquals($prefix . $suffix, $result);
    }

    public function testNormalizePath()
    {
        // Backslashes to slashes
        $this->assertEquals('C:/foo/bar', FileHelper::normalizePath('C:\\foo\\bar'));

        // Collapse consecutive slashes
        $this->assertEquals('/foo/bar/baz', FileHelper::normalizePath('/foo//bar///baz'));

        // Mixed separators
        $this->assertEquals('C:/a/b/c', FileHelper::normalizePath('C:\\a\\\\b/c'));

        // Already normalized
        $this->assertEquals('/foo/bar', FileHelper::normalizePath('/foo/bar'));

        // Empty string
        $this->assertEquals('', FileHelper::normalizePath(''));
    }
}
