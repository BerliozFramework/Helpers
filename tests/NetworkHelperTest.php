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

use Berlioz\Helpers\NetworkHelper;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class NetworkHelperTest extends TestCase
{
    public function testIsValidIp()
    {
        // IPv4
        $this->assertTrue(NetworkHelper::isValidIp('192.168.1.1'));
        $this->assertTrue(NetworkHelper::isValidIp('0.0.0.0'));
        $this->assertTrue(NetworkHelper::isValidIp('255.255.255.255'));
        // IPv6
        $this->assertTrue(NetworkHelper::isValidIp('::1'));
        $this->assertTrue(NetworkHelper::isValidIp('2001:db8::1'));
        // Invalid
        $this->assertFalse(NetworkHelper::isValidIp('256.1.1.1'));
        $this->assertFalse(NetworkHelper::isValidIp('192.168.1'));
        $this->assertFalse(NetworkHelper::isValidIp('foo'));
        $this->assertFalse(NetworkHelper::isValidIp(''));
    }

    public function testIsValidIpv4()
    {
        $this->assertTrue(NetworkHelper::isValidIpv4('192.168.1.1'));
        $this->assertTrue(NetworkHelper::isValidIpv4('10.0.0.0'));
        $this->assertFalse(NetworkHelper::isValidIpv4('2001:db8::1'));
        $this->assertFalse(NetworkHelper::isValidIpv4('::1'));
        $this->assertFalse(NetworkHelper::isValidIpv4('256.0.0.1'));
    }

    public function testIsValidIpv6()
    {
        $this->assertTrue(NetworkHelper::isValidIpv6('::1'));
        $this->assertTrue(NetworkHelper::isValidIpv6('2001:db8::1'));
        $this->assertTrue(NetworkHelper::isValidIpv6('fe80::1ff:fe23:4567:890a'));
        $this->assertFalse(NetworkHelper::isValidIpv6('192.168.1.1'));
        $this->assertFalse(NetworkHelper::isValidIpv6('foo'));
    }

    public function testGetIpVersion()
    {
        $this->assertSame(4, NetworkHelper::getIpVersion('192.168.1.1'));
        $this->assertSame(6, NetworkHelper::getIpVersion('2001:db8::1'));
        $this->assertSame(6, NetworkHelper::getIpVersion('::1'));
        $this->assertNull(NetworkHelper::getIpVersion('not an ip'));
    }

    public function testIsValidNetmaskDotted()
    {
        // Valid contiguous IPv4 masks
        $this->assertTrue(NetworkHelper::isValidNetmask('255.255.255.0'));
        $this->assertTrue(NetworkHelper::isValidNetmask('255.255.255.255'));
        $this->assertTrue(NetworkHelper::isValidNetmask('0.0.0.0'));
        $this->assertTrue(NetworkHelper::isValidNetmask('255.255.240.0'));
        // Non-contiguous mask is invalid
        $this->assertFalse(NetworkHelper::isValidNetmask('255.0.255.0'));
        $this->assertFalse(NetworkHelper::isValidNetmask('255.255.1.0'));
        // IPv6 mask
        $this->assertTrue(NetworkHelper::isValidNetmask('ffff:ffff::'));
        $this->assertFalse(NetworkHelper::isValidNetmask('ffff:0:ffff::'));
    }

    public function testIsValidNetmaskPrefix()
    {
        $this->assertTrue(NetworkHelper::isValidNetmask('0', 4));
        $this->assertTrue(NetworkHelper::isValidNetmask('24', 4));
        $this->assertTrue(NetworkHelper::isValidNetmask('32', 4));
        $this->assertFalse(NetworkHelper::isValidNetmask('33', 4));
        $this->assertTrue(NetworkHelper::isValidNetmask('128', 6));
        $this->assertFalse(NetworkHelper::isValidNetmask('129', 6));
    }

    public function testIsValidNetmaskVersionConstraint()
    {
        $this->assertFalse(NetworkHelper::isValidNetmask('255.255.255.0', 6));
        $this->assertTrue(NetworkHelper::isValidNetmask('255.255.255.0', 4));
    }

    public function testIsValidCidr()
    {
        $this->assertTrue(NetworkHelper::isValidCidr('192.168.1.0/24'));
        $this->assertTrue(NetworkHelper::isValidCidr('10.0.0.0/8'));
        $this->assertTrue(NetworkHelper::isValidCidr('0.0.0.0/0'));
        $this->assertTrue(NetworkHelper::isValidCidr('2001:db8::/32'));
        $this->assertTrue(NetworkHelper::isValidCidr('::/0'));
        // Invalid
        $this->assertFalse(NetworkHelper::isValidCidr('192.168.1.0'));
        $this->assertFalse(NetworkHelper::isValidCidr('192.168.1.0/33'));
        $this->assertFalse(NetworkHelper::isValidCidr('192.168.1.0/24/8'));
        $this->assertFalse(NetworkHelper::isValidCidr('foo/24'));
        $this->assertFalse(NetworkHelper::isValidCidr('192.168.1.0/foo'));
    }

    public function testIpInNetworkIpv4Cidr()
    {
        $this->assertTrue(NetworkHelper::ipInNetwork('192.168.1.42', '192.168.1.0/24'));
        $this->assertTrue(NetworkHelper::ipInNetwork('192.168.1.0', '192.168.1.0/24'));
        $this->assertTrue(NetworkHelper::ipInNetwork('192.168.1.255', '192.168.1.0/24'));
        $this->assertFalse(NetworkHelper::ipInNetwork('192.168.2.42', '192.168.1.0/24'));
        $this->assertTrue(NetworkHelper::ipInNetwork('8.8.8.8', '0.0.0.0/0'));
        // Different families never match
        $this->assertFalse(NetworkHelper::ipInNetwork('2001:db8::1', '192.168.1.0/24'));
    }

    public function testIpInNetworkIpv4Mask()
    {
        $this->assertTrue(NetworkHelper::ipInNetwork('192.168.1.5', '192.168.1.0 255.255.255.0'));
        $this->assertFalse(NetworkHelper::ipInNetwork('192.168.2.5', '192.168.1.0 255.255.255.0'));
    }

    public function testIpInNetworkIpv6()
    {
        $this->assertTrue(NetworkHelper::ipInNetwork('2001:db8::5', '2001:db8::/32'));
        $this->assertFalse(NetworkHelper::ipInNetwork('2001:db9::5', '2001:db8::/32'));
        $this->assertTrue(NetworkHelper::ipInNetwork('::1', '::/0'));
    }

    public function testIpInNetworkInvalidNetwork()
    {
        $this->expectException(InvalidArgumentException::class);
        NetworkHelper::ipInNetwork('192.168.1.1', 'foo');
    }

    public function testGetNetworkRangeIpv4()
    {
        $range = NetworkHelper::getNetworkRange('192.168.1.0/24');

        $this->assertSame(4, $range['version']);
        $this->assertSame(24, $range['prefix']);
        $this->assertSame('192.168.1.0', $range['network']);
        $this->assertSame('255.255.255.0', $range['netmask']);
        $this->assertSame('192.168.1.0', $range['first']);
        $this->assertSame('192.168.1.255', $range['last']);
        $this->assertSame('192.168.1.255', $range['broadcast']);
        $this->assertSame(256, $range['count']);
    }

    public function testGetNetworkRangeIpv4Host()
    {
        $range = NetworkHelper::getNetworkRange('192.168.1.42/32');

        $this->assertSame('192.168.1.42', $range['network']);
        $this->assertSame('192.168.1.42', $range['last']);
        $this->assertSame(1, $range['count']);
    }

    public function testGetNetworkRangeIpv4Full()
    {
        $range = NetworkHelper::getNetworkRange('0.0.0.0/0');

        $this->assertSame('0.0.0.0', $range['network']);
        $this->assertSame('255.255.255.255', $range['last']);
        $this->assertSame(4294967296, $range['count']);
    }

    public function testGetNetworkRangeNonAlignedNetwork()
    {
        // Host bits are masked out: network address is computed
        $range = NetworkHelper::getNetworkRange('192.168.1.42/24');

        $this->assertSame('192.168.1.0', $range['network']);
        $this->assertSame('192.168.1.255', $range['last']);
    }

    public function testGetNetworkRangeIpv6()
    {
        $range = NetworkHelper::getNetworkRange('2001:db8::/126');

        $this->assertSame(6, $range['version']);
        $this->assertSame(126, $range['prefix']);
        $this->assertSame('2001:db8::', $range['network']);
        $this->assertSame('2001:db8::3', $range['last']);
        $this->assertNull($range['broadcast']);
        $this->assertSame(4, $range['count']);
    }

    public function testGetNetworkRangeIpv6LargeCount()
    {
        $range = NetworkHelper::getNetworkRange('2001:db8::/32');

        // 2^96 does not fit in a native int, returned as a numeric string
        $this->assertSame('79228162514264337593543950336', $range['count']);
    }

    public function testGetNetworkRangeFromMask()
    {
        $range = NetworkHelper::getNetworkRange('192.168.1.0 255.255.255.0');

        $this->assertSame(24, $range['prefix']);
        $this->assertSame('192.168.1.0', $range['network']);
        $this->assertSame('192.168.1.255', $range['last']);
    }

    public function testGetNetworkRangeInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        NetworkHelper::getNetworkRange('not a network');
    }

    public function testForwardedForParse()
    {
        $this->assertSame(
            ['203.0.113.7', '70.41.3.18', '150.172.238.178'],
            NetworkHelper::forwardedForParse('203.0.113.7, 70.41.3.18 , 150.172.238.178')
        );
    }

    public function testForwardedForParseEmpty()
    {
        $this->assertSame([], NetworkHelper::forwardedForParse(''));
        $this->assertSame([], NetworkHelper::forwardedForParse('   '));
        $this->assertSame([], NetworkHelper::forwardedForParse(', ,'));
    }

    public function testForwardedForParseIgnoresInvalid()
    {
        $this->assertSame(
            ['9.9.9.9'],
            NetworkHelper::forwardedForParse('foo, , bar, 9.9.9.9, 999.1.1.1')
        );
    }

    public function testForwardedForParseStripsPort()
    {
        // IPv4 with port
        $this->assertSame(
            ['198.51.100.5'],
            NetworkHelper::forwardedForParse('198.51.100.5:8080')
        );
        // IPv6 in brackets with port
        $this->assertSame(
            ['2001:db8::1'],
            NetworkHelper::forwardedForParse('[2001:db8::1]:443')
        );
        // Bare IPv6 (colons must be preserved)
        $this->assertSame(
            ['2001:db8::1'],
            NetworkHelper::forwardedForParse('2001:db8::1')
        );
    }

    public function testForwardedForParseMixed()
    {
        $this->assertSame(
            ['2001:db8::1', '198.51.100.5', '9.9.9.9'],
            NetworkHelper::forwardedForParse('[2001:db8::1]:443, 198.51.100.5:8080, foo, , 9.9.9.9')
        );
    }

    public function testClientIpWithoutTrustedProxies()
    {
        $server = [
            'REMOTE_ADDR' => '8.8.8.8',
            'HTTP_X_FORWARDED_FOR' => '1.2.3.4',
        ];

        // Header is never trusted when no proxies are configured
        $this->assertSame('8.8.8.8', NetworkHelper::clientIp([], $server));
    }

    public function testClientIpBehindTrustedProxy()
    {
        $server = [
            'REMOTE_ADDR' => '10.0.0.5',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.7, 10.0.0.9',
        ];

        $this->assertSame('203.0.113.7', NetworkHelper::clientIp(['10.0.0.0/8'], $server));
    }

    public function testClientIpUntrustedRemoteAddrIgnoresHeader()
    {
        $server = [
            'REMOTE_ADDR' => '8.8.8.8',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.7',
        ];

        // REMOTE_ADDR is not a trusted proxy: header must be ignored
        $this->assertSame('8.8.8.8', NetworkHelper::clientIp(['10.0.0.0/8'], $server));
    }

    public function testClientIpAllHopsTrustedFallsBackToLeftMost()
    {
        $server = [
            'REMOTE_ADDR' => '10.0.0.5',
            'HTTP_X_FORWARDED_FOR' => '192.168.1.1, 192.168.1.2',
        ];

        $this->assertSame(
            '192.168.1.1',
            NetworkHelper::clientIp(['10.0.0.0/8', '192.168.0.0/16'], $server)
        );
    }

    public function testClientIpWithExactProxyIp()
    {
        $server = [
            'REMOTE_ADDR' => '10.0.0.5',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.7',
        ];

        $this->assertSame('203.0.113.7', NetworkHelper::clientIp(['10.0.0.5'], $server));
    }

    public function testClientIpCustomHeader()
    {
        $server = [
            'REMOTE_ADDR' => '10.0.0.5',
            'HTTP_X_REAL_IP' => '203.0.113.7',
            'HTTP_X_FORWARDED_FOR' => '1.1.1.1',
        ];

        $this->assertSame(
            '203.0.113.7',
            NetworkHelper::clientIp(['10.0.0.0/8'], $server, 'X-Real-IP')
        );
    }

    public function testClientIpNoForwardedHeader()
    {
        $server = ['REMOTE_ADDR' => '10.0.0.5'];

        // Trusted proxy but no header: returns the peer itself
        $this->assertSame('10.0.0.5', NetworkHelper::clientIp(['10.0.0.0/8'], $server));
    }

    public function testClientIpInvalidRemoteAddr()
    {
        $this->assertNull(NetworkHelper::clientIp([], ['REMOTE_ADDR' => 'bogus']));
        $this->assertNull(NetworkHelper::clientIp([], []));
    }

    public function testClientIpIpv6Proxy()
    {
        $server = [
            'REMOTE_ADDR' => '2001:db8::1',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.7, 2001:db8::99',
        ];

        $this->assertSame('203.0.113.7', NetworkHelper::clientIp(['2001:db8::/32'], $server));
    }

    public function testClientIpDefaultsToGlobalServer()
    {
        $previous = $_SERVER;
        $_SERVER['REMOTE_ADDR'] = '8.8.4.4';
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);

        try {
            $this->assertSame('8.8.4.4', NetworkHelper::clientIp());
        } finally {
            $_SERVER = $previous;
        }
    }

    public function testIsTrustedProxyEmptyList()
    {
        $this->assertFalse(NetworkHelper::isTrustedProxy('10.0.0.5', []));
    }

    public function testIsTrustedProxyExactIp()
    {
        $this->assertTrue(NetworkHelper::isTrustedProxy('10.0.0.5', ['10.0.0.5']));
        $this->assertFalse(NetworkHelper::isTrustedProxy('10.0.0.6', ['10.0.0.5']));
        // IPv6 exact match (different textual forms of the same address)
        $this->assertTrue(NetworkHelper::isTrustedProxy('2001:db8::1', ['2001:0db8:0000::1']));
        $this->assertFalse(NetworkHelper::isTrustedProxy('2001:db8::2', ['2001:db8::1']));
    }

    public function testIsTrustedProxyCidr()
    {
        $this->assertTrue(NetworkHelper::isTrustedProxy('10.1.2.3', ['10.0.0.0/8']));
        $this->assertFalse(NetworkHelper::isTrustedProxy('11.1.2.3', ['10.0.0.0/8']));
        // IPv6 CIDR
        $this->assertTrue(NetworkHelper::isTrustedProxy('2001:db8::99', ['2001:db8::/32']));
        $this->assertFalse(NetworkHelper::isTrustedProxy('2001:db9::99', ['2001:db8::/32']));
    }

    public function testIsTrustedProxyMixedList()
    {
        $proxies = ['192.168.1.1', '10.0.0.0/8', '2001:db8::/32'];

        $this->assertTrue(NetworkHelper::isTrustedProxy('192.168.1.1', $proxies));
        $this->assertTrue(NetworkHelper::isTrustedProxy('10.255.255.254', $proxies));
        $this->assertTrue(NetworkHelper::isTrustedProxy('2001:db8::abcd', $proxies));
        $this->assertFalse(NetworkHelper::isTrustedProxy('203.0.113.7', $proxies));
    }

    public function testIsTrustedProxyIgnoresInvalidEntries()
    {
        $this->assertFalse(NetworkHelper::isTrustedProxy('10.0.0.5', ['foo', '10.0.0.0/33', '999.1.1.1']));
        // Valid entry after invalid ones is still honoured
        $this->assertTrue(NetworkHelper::isTrustedProxy('10.0.0.5', ['foo', '10.0.0.0/8']));
    }

    public function testIsTrustedProxyAliasPrivate()
    {
        $this->assertTrue(NetworkHelper::isTrustedProxy('10.0.0.5', ['private']));
        $this->assertTrue(NetworkHelper::isTrustedProxy('192.168.1.1', ['private']));
        $this->assertTrue(NetworkHelper::isTrustedProxy('fc00::1', ['private']));
        $this->assertFalse(NetworkHelper::isTrustedProxy('8.8.8.8', ['private']));
        $this->assertFalse(NetworkHelper::isTrustedProxy('2001:4860:4860::8888', ['private']));
    }

    public function testIsTrustedProxyAliasPublic()
    {
        $this->assertTrue(NetworkHelper::isTrustedProxy('8.8.8.8', ['public']));
        $this->assertTrue(NetworkHelper::isTrustedProxy('2001:4860:4860::8888', ['public']));
        $this->assertFalse(NetworkHelper::isTrustedProxy('10.0.0.5', ['public']));
        $this->assertFalse(NetworkHelper::isTrustedProxy('192.168.1.1', ['public']));
    }

    public function testIsTrustedProxyAliasLoopback()
    {
        $this->assertTrue(NetworkHelper::isTrustedProxy('127.0.0.1', ['loopback']));
        $this->assertTrue(NetworkHelper::isTrustedProxy('127.255.255.254', ['loopback']));
        $this->assertTrue(NetworkHelper::isTrustedProxy('::1', ['loopback']));
        $this->assertFalse(NetworkHelper::isTrustedProxy('10.0.0.5', ['loopback']));
        $this->assertFalse(NetworkHelper::isTrustedProxy('2001:db8::1', ['loopback']));
    }

    public function testIsTrustedProxyAliasCatchAll()
    {
        foreach (['*', 'any', '0.0.0.0/0', '::/0'] as $alias) {
            $this->assertTrue(NetworkHelper::isTrustedProxy('8.8.8.8', [$alias]), $alias);
            $this->assertTrue(NetworkHelper::isTrustedProxy('10.0.0.5', [$alias]), $alias);
            $this->assertTrue(NetworkHelper::isTrustedProxy('2001:db8::1', [$alias]), $alias);
            $this->assertTrue(NetworkHelper::isTrustedProxy('::1', [$alias]), $alias);
            // Invalid IP is never trusted, even with catch-all
            $this->assertFalse(NetworkHelper::isTrustedProxy('not an ip', [$alias]), $alias);
        }
    }

    public function testIsTrustedProxyAliasCaseInsensitive()
    {
        $this->assertTrue(NetworkHelper::isTrustedProxy('10.0.0.5', ['PRIVATE']));
        $this->assertTrue(NetworkHelper::isTrustedProxy('127.0.0.1', ['LoopBack']));
        $this->assertTrue(NetworkHelper::isTrustedProxy('8.8.8.8', ['Any']));
        // Surrounding whitespace is trimmed
        $this->assertTrue(NetworkHelper::isTrustedProxy('10.0.0.5', ['  private  ']));
    }

    public function testIsTrustedProxyUnknownAliasIgnored()
    {
        // Unknown alias behaves like any other invalid entry: ignored
        $this->assertFalse(NetworkHelper::isTrustedProxy('10.0.0.5', ['reserved']));
        // But a valid entry in the same list is still honoured
        $this->assertTrue(NetworkHelper::isTrustedProxy('10.0.0.5', ['reserved', '10.0.0.0/8']));
    }

    public function testIsTrustedProxyAliasMixedList()
    {
        $proxies = ['192.168.1.1', 'loopback', '2001:db8::/32'];

        $this->assertTrue(NetworkHelper::isTrustedProxy('192.168.1.1', $proxies));
        $this->assertTrue(NetworkHelper::isTrustedProxy('127.0.0.1', $proxies));
        $this->assertTrue(NetworkHelper::isTrustedProxy('2001:db8::abcd', $proxies));
        $this->assertFalse(NetworkHelper::isTrustedProxy('203.0.113.7', $proxies));
    }

    public function testClientIpWithPrivateAlias()
    {
        // The direct peer is a private proxy; the forwarded client is returned
        $client = NetworkHelper::clientIp(
            ['private'],
            [
                'REMOTE_ADDR' => '10.0.0.1',
                'HTTP_X_FORWARDED_FOR' => '203.0.113.7',
            ]
        );

        $this->assertSame('203.0.113.7', $client);
    }

    public function testIsPrivateIp()
    {
        // IPv4 private ranges
        $this->assertTrue(NetworkHelper::isPrivateIp('10.0.0.1'));
        $this->assertTrue(NetworkHelper::isPrivateIp('172.16.5.4'));
        $this->assertTrue(NetworkHelper::isPrivateIp('192.168.1.1'));
        // IPv4 reserved ranges
        $this->assertTrue(NetworkHelper::isPrivateIp('127.0.0.1'));
        $this->assertTrue(NetworkHelper::isPrivateIp('169.254.1.1'));
        // Public
        $this->assertFalse(NetworkHelper::isPrivateIp('8.8.8.8'));
        $this->assertFalse(NetworkHelper::isPrivateIp('203.0.113.7'));
        // IPv6
        $this->assertTrue(NetworkHelper::isPrivateIp('fc00::1'));
        $this->assertTrue(NetworkHelper::isPrivateIp('::1'));
        $this->assertFalse(NetworkHelper::isPrivateIp('2001:4860:4860::8888'));
        // Invalid
        $this->assertFalse(NetworkHelper::isPrivateIp('not an ip'));
    }

    public function testIsPublicIp()
    {
        $this->assertTrue(NetworkHelper::isPublicIp('8.8.8.8'));
        $this->assertTrue(NetworkHelper::isPublicIp('203.0.113.7'));
        $this->assertTrue(NetworkHelper::isPublicIp('2001:4860:4860::8888'));
        $this->assertFalse(NetworkHelper::isPublicIp('10.0.0.1'));
        $this->assertFalse(NetworkHelper::isPublicIp('127.0.0.1'));
        $this->assertFalse(NetworkHelper::isPublicIp('fc00::1'));
        $this->assertFalse(NetworkHelper::isPublicIp('not an ip'));
    }

    public function testIsPrivateAndPublicAreComplementary()
    {
        foreach (['10.0.0.1', '8.8.8.8', '::1', '2001:4860:4860::8888', 'fc00::1'] as $ip) {
            $this->assertNotSame(
                NetworkHelper::isPrivateIp($ip),
                NetworkHelper::isPublicIp($ip),
                sprintf('Private/public must be complementary for %s', $ip)
            );
        }
    }

    public function testIpInRangeIpv4()
    {
        $this->assertTrue(NetworkHelper::ipInRange('192.168.1.50', '192.168.1.1', '192.168.1.100'));
        // Inclusive bounds
        $this->assertTrue(NetworkHelper::ipInRange('192.168.1.1', '192.168.1.1', '192.168.1.100'));
        $this->assertTrue(NetworkHelper::ipInRange('192.168.1.100', '192.168.1.1', '192.168.1.100'));
        // Outside
        $this->assertFalse(NetworkHelper::ipInRange('192.168.1.200', '192.168.1.1', '192.168.1.100'));
        $this->assertFalse(NetworkHelper::ipInRange('192.168.0.255', '192.168.1.1', '192.168.1.100'));
    }

    public function testIpInRangeReversedBounds()
    {
        // Bounds given in any order
        $this->assertTrue(NetworkHelper::ipInRange('192.168.1.50', '192.168.1.100', '192.168.1.1'));
    }

    public function testIpInRangeIpv6()
    {
        $this->assertTrue(NetworkHelper::ipInRange('2001:db8::5', '2001:db8::1', '2001:db8::10'));
        $this->assertFalse(NetworkHelper::ipInRange('2001:db8::ffff', '2001:db8::1', '2001:db8::10'));
    }

    public function testIpInRangeMixedFamiliesOrInvalid()
    {
        $this->assertFalse(NetworkHelper::ipInRange('10.0.0.1', '192.168.1.1', '2001:db8::1'));
        $this->assertFalse(NetworkHelper::ipInRange('2001:db8::1', '10.0.0.1', '10.0.0.255'));
        $this->assertFalse(NetworkHelper::ipInRange('foo', '10.0.0.1', '10.0.0.255'));
    }

    public function testExpandIpv6()
    {
        $this->assertSame(
            '2001:0db8:0000:0000:0000:0000:0000:0001',
            NetworkHelper::expandIpv6('2001:db8::1')
        );
        $this->assertSame(
            '0000:0000:0000:0000:0000:0000:0000:0001',
            NetworkHelper::expandIpv6('::1')
        );
        $this->assertSame(
            '0000:0000:0000:0000:0000:0000:0000:0000',
            NetworkHelper::expandIpv6('::')
        );
        // Invalid / not IPv6
        $this->assertNull(NetworkHelper::expandIpv6('192.168.1.1'));
        $this->assertNull(NetworkHelper::expandIpv6('foo'));
    }

    public function testCompressIpv6()
    {
        $this->assertSame(
            '2001:db8::1',
            NetworkHelper::compressIpv6('2001:0db8:0000:0000:0000:0000:0000:0001')
        );
        $this->assertSame('::1', NetworkHelper::compressIpv6('0000:0000:0000:0000:0000:0000:0000:0001'));
        $this->assertSame('::', NetworkHelper::compressIpv6('0000:0000:0000:0000:0000:0000:0000:0000'));
        // Invalid / not IPv6
        $this->assertNull(NetworkHelper::compressIpv6('192.168.1.1'));
        $this->assertNull(NetworkHelper::compressIpv6('foo'));
    }

    public function testExpandCompressRoundTrip()
    {
        foreach (['2001:db8::1', '::1', 'fe80::1ff:fe23:4567:890a', '::'] as $ip) {
            $expanded = NetworkHelper::expandIpv6($ip);
            $this->assertNotNull($expanded);
            $this->assertSame(
                NetworkHelper::compressIpv6($ip),
                NetworkHelper::compressIpv6($expanded)
            );
        }
    }

    public function testIpToLongIpv4()
    {
        $this->assertSame(0, NetworkHelper::ipToLong('0.0.0.0'));
        $this->assertSame(4294967295, NetworkHelper::ipToLong('255.255.255.255'));
        $this->assertSame(3232235521, NetworkHelper::ipToLong('192.168.0.1'));
    }

    public function testIpToLongInvalid()
    {
        $this->assertNull(NetworkHelper::ipToLong('not an ip'));
        $this->assertNull(NetworkHelper::ipToLong('999.999.999.999'));
        $this->assertNull(NetworkHelper::ipToLong(''));
    }

    public function testLongToIpIpv4()
    {
        $this->assertSame('0.0.0.0', NetworkHelper::longToIp(0));
        $this->assertSame('255.255.255.255', NetworkHelper::longToIp(4294967295));
        $this->assertSame('192.168.0.1', NetworkHelper::longToIp(3232235521));

        // Explicit version and string input
        $this->assertSame('192.168.0.1', NetworkHelper::longToIp('3232235521', 4));
    }

    public function testLongToIpInvalid()
    {
        $this->assertNull(NetworkHelper::longToIp('not numeric'));
        $this->assertNull(NetworkHelper::longToIp('4294967296', 4));
        $this->assertNull(NetworkHelper::longToIp(-1, 4));
    }

    public function testIpToLongRoundTripIpv4()
    {
        foreach (['0.0.0.0', '10.0.0.1', '172.16.5.4', '192.168.1.254', '255.255.255.255'] as $ip) {
            $this->assertSame($ip, NetworkHelper::longToIp(NetworkHelper::ipToLong($ip)));
        }
    }

    public function testIpToLongIpv6()
    {
        if (!extension_loaded('gmp')) {
            $this->markTestSkipped('The "gmp" extension is not loaded');
        }

        $this->assertSame('0', NetworkHelper::ipToLong('::'));
        $this->assertSame('1', NetworkHelper::ipToLong('::1'));
        $this->assertSame(
            '42540766411282592856903984951653826561',
            NetworkHelper::ipToLong('2001:db8::1')
        );
    }

    public function testIpToLongRoundTripIpv6()
    {
        if (!extension_loaded('gmp')) {
            $this->markTestSkipped('The "gmp" extension is not loaded');
        }

        foreach (['::', '::1', '2001:db8::1', 'fe80::1ff:fe23:4567:890a'] as $ip) {
            $long = NetworkHelper::ipToLong($ip);
            $this->assertSame($ip, NetworkHelper::longToIp($long, 6));
        }
    }
}
