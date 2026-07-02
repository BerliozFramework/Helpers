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

/**
 * Class NetworkHelper.
 *
 * @package Berlioz\Helpers
 */
final class NetworkHelper
{
    /**
     * Is valid IP (v4 or v6)?
     *
     * @param string $ip
     *
     * @return bool
     */
    public static function isValidIp(string $ip): bool
    {
        return false !== filter_var($ip, FILTER_VALIDATE_IP);
    }

    /**
     * Is valid IP v4?
     *
     * @param string $ip
     *
     * @return bool
     */
    public static function isValidIpv4(string $ip): bool
    {
        return false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    /**
     * Is valid IP v6?
     *
     * @param string $ip
     *
     * @return bool
     */
    public static function isValidIpv6(string $ip): bool
    {
        return false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    /**
     * Get IP version.
     *
     * @param string $ip
     *
     * @return int|null 4, 6 or null if not a valid IP
     */
    public static function getIpVersion(string $ip): ?int
    {
        if (self::isValidIpv4($ip)) {
            return 4;
        }

        if (self::isValidIpv6($ip)) {
            return 6;
        }

        return null;
    }

    /**
     * Is valid netmask?
     *
     * Accepts a dotted netmask (e.g. "255.255.255.0" or IPv6 mask) or a CIDR
     * prefix length (e.g. "24", "0" to "32" for IPv4 and "0" to "128" for IPv6).
     *
     * A valid netmask must be a contiguous sequence of high bits followed by
     * low bits (e.g. "255.255.255.0" is valid, "255.0.255.0" is not).
     *
     * @param string $mask
     * @param int|null $version IP version constraint (4 or 6), or null to accept both
     *
     * @return bool
     */
    public static function isValidNetmask(string $mask, ?int $version = null): bool
    {
        // CIDR prefix length notation
        if (preg_match('/^\d+$/', $mask) === 1) {
            $prefix = (int)$mask;

            switch ($version) {
                case 4:
                    return $prefix <= 32;
                case 6:
                    return $prefix <= 128;
                default:
                    return $prefix <= 128;
            }
        }

        // Dotted netmask notation
        $ipVersion = self::getIpVersion($mask);

        if (null === $ipVersion) {
            return false;
        }

        if (null !== $version && $version !== $ipVersion) {
            return false;
        }

        // Validate contiguous bits
        $binary = inet_pton($mask);

        if (false === $binary) {
            return false;
        }

        return self::isContiguousMask($binary);
    }

    /**
     * Is valid CIDR notation (e.g. "192.168.1.0/24" or "2001:db8::/32")?
     *
     * @param string $cidr
     *
     * @return bool
     */
    public static function isValidCidr(string $cidr): bool
    {
        if (substr_count($cidr, '/') !== 1) {
            return false;
        }

        [$ip, $prefix] = explode('/', $cidr, 2);

        $version = self::getIpVersion($ip);

        if (null === $version) {
            return false;
        }

        if (preg_match('/^\d+$/', $prefix) !== 1) {
            return false;
        }

        return self::isValidNetmask($prefix, $version);
    }

    /**
     * Is IP in network?
     *
     * The network can be expressed:
     *  - in CIDR notation: "192.168.1.0/24", "2001:db8::/32"
     *  - as "ip mask" with a dotted mask: "192.168.1.0 255.255.255.0"
     *
     * @param string $ip
     * @param string $network
     *
     * @return bool
     * @throws InvalidArgumentException if network is invalid
     */
    public static function ipInNetwork(string $ip, string $network): bool
    {
        [$subnet, $prefix] = self::parseNetwork($network);

        $ipVersion = self::getIpVersion($ip);
        $subnetVersion = self::getIpVersion($subnet);

        // IP must be valid and same family as subnet
        if (null === $ipVersion || $ipVersion !== $subnetVersion) {
            return false;
        }

        $ipBinary = inet_pton($ip);
        $subnetBinary = inet_pton($subnet);

        if (false === $ipBinary || false === $subnetBinary) {
            return false;
        }

        $maskBinary = self::prefixToBinaryMask($prefix, $subnetVersion);

        return ($ipBinary & $maskBinary) === ($subnetBinary & $maskBinary);
    }

    /**
     * Get network range.
     *
     * Returns an array describing the network with the following keys:
     *  - version: int (4 or 6)
     *  - prefix: int (prefix length)
     *  - network: string (network address)
     *  - netmask: string (dotted netmask)
     *  - first: string (first usable/assignable address of the range)
     *  - last: string (last address of the range)
     *  - broadcast: string|null (broadcast address, IPv4 only)
     *  - count: int|string (number of addresses in the range)
     *
     * @param string $network CIDR notation or "ip mask"
     *
     * @return array
     * @throws InvalidArgumentException if network is invalid
     */
    public static function getNetworkRange(string $network): array
    {
        [$subnet, $prefix] = self::parseNetwork($network);

        $version = self::getIpVersion($subnet);

        if (null === $version) {
            throw new InvalidArgumentException(sprintf('Invalid network "%s"', $network));
        }

        $subnetBinary = inet_pton($subnet);
        $maskBinary = self::prefixToBinaryMask($prefix, $version);

        $networkBinary = $subnetBinary & $maskBinary;
        $broadcastBinary = $networkBinary | ~$maskBinary;

        $bits = 4 === $version ? 32 : 128;
        $hostBits = $bits - $prefix;
        $count = self::countAddresses($hostBits);

        return [
            'version' => $version,
            'prefix' => $prefix,
            'network' => inet_ntop($networkBinary),
            'netmask' => inet_ntop($maskBinary),
            'first' => inet_ntop($networkBinary),
            'last' => inet_ntop($broadcastBinary),
            'broadcast' => 4 === $version ? inet_ntop($broadcastBinary) : null,
            'count' => $count,
        ];
    }

    /**
     * Count number of addresses for the given number of host bits.
     *
     * Returns an int when it fits in a native integer, otherwise a numeric
     * string (e.g. for large IPv6 ranges), without relying on the bcmath
     * extension.
     *
     * @param int $hostBits
     *
     * @return int|string
     */
    private static function countAddresses(int $hostBits)
    {
        if ($hostBits < PHP_INT_SIZE * 8 - 1) {
            return 1 << $hostBits;
        }

        // Large ranges: compute 2^hostBits as a decimal string
        $result = '1';
        for ($i = 0; $i < $hostBits; $i++) {
            $carry = 0;
            for ($j = strlen($result) - 1; $j >= 0; $j--) {
                $value = ((int)$result[$j] * 2) + $carry;
                $result[$j] = (string)($value % 10);
                $carry = intdiv($value, 10);
            }
            if ($carry > 0) {
                $result = $carry . $result;
            }
        }

        return $result;
    }

    /**
     * Parse an `X-Forwarded-For` header value into a list of IP addresses.
     *
     * Splits the comma-separated header, trims each entry, strips an optional
     * port (including the `[ipv6]:port` form) and discards invalid entries.
     *
     * The returned list keeps the original left-to-right order: the left-most
     * entry is the (claimed) originating client, the right-most is the closest
     * upstream proxy. To find the trusted hop, iterate from the right.
     *
     * @param string $header Raw header value
     *
     * @return string[] List of valid IP addresses, in header order
     */
    public static function forwardedForParse(string $header): array
    {
        if ('' === trim($header)) {
            return [];
        }

        $ips = [];

        foreach (explode(',', $header) as $part) {
            $part = trim($part);

            if ('' === $part) {
                continue;
            }

            $ip = self::stripPort($part);

            if (null !== self::getIpVersion($ip)) {
                $ips[] = $ip;
            }
        }

        return $ips;
    }

    /**
     * Determine the real client IP address from server parameters.
     *
     * Returns `REMOTE_ADDR` unless the request comes from a trusted proxy, in
     * which case the forwarded header chain is walked from right to left and
     * the first address that is not itself a trusted proxy is returned.
     *
     * Trusted proxies may be given as exact IP addresses or CIDR ranges
     * (e.g. `10.0.0.0/8`). If `REMOTE_ADDR` is not trusted, the forwarded
     * header is ignored entirely (it cannot be trusted).
     *
     * @param string[] $trustedProxies List of trusted proxy IPs or CIDR ranges
     * @param array|null $server Server parameters (defaults to `$_SERVER` when null)
     * @param string $header Forwarded header name (default: `X-Forwarded-For`)
     *
     * @return string|null The client IP, or null if `REMOTE_ADDR` is missing/invalid
     */
    public static function clientIp(
        array $trustedProxies = [],
        ?array $server = null,
        string $header = 'X-Forwarded-For'
    ): ?string {
        $server = $server ?? $_SERVER;
        $remoteAddr = isset($server['REMOTE_ADDR']) ? trim((string)$server['REMOTE_ADDR']) : '';

        if (null === self::getIpVersion($remoteAddr)) {
            return null;
        }

        // No trusted proxies, or the direct peer is not trusted: never trust the header
        if ([] === $trustedProxies || false === self::isTrustedProxy($remoteAddr, $trustedProxies)) {
            return $remoteAddr;
        }

        // Resolve the forwarded header key (e.g. "X-Forwarded-For" => "HTTP_X_FORWARDED_FOR")
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        $forwarded = isset($server[$serverKey]) ? self::forwardedForParse((string)$server[$serverKey]) : [];

        // Build the full chain (header IPs then the direct peer) and walk it
        // from right to left, returning the first non-trusted hop.
        $chain = array_merge($forwarded, [$remoteAddr]);

        for ($i = count($chain) - 1; $i >= 0; $i--) {
            if (false === self::isTrustedProxy($chain[$i], $trustedProxies)) {
                return $chain[$i];
            }
        }

        // Every hop is trusted: fall back to the left-most forwarded address
        return $chain[0];
    }

    /**
     * Is the given IP a trusted proxy?
     *
     * A proxy is trusted when it matches one of the given entries, either as an
     * exact IP address or by belonging to a CIDR range (e.g. `10.0.0.0/8`).
     * Invalid entries are ignored.
     *
     * @param string $ip
     * @param string[] $trustedProxies List of trusted proxy IPs or CIDR ranges
     *
     * @return bool
     */
    public static function isTrustedProxy(string $ip, array $trustedProxies): bool
    {
        foreach ($trustedProxies as $trusted) {
            if (false !== strpos($trusted, '/')) {
                if (self::isValidCidr($trusted) && self::ipInNetwork($ip, $trusted)) {
                    return true;
                }
                continue;
            }

            if (self::isValidIp($trusted) && @inet_pton($ip) === @inet_pton($trusted)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Strip an optional port from an IP entry.
     *
     * Handles the `[ipv6]:port` form and the `ipv4:port` form, while leaving
     * bare IPv6 addresses (which legitimately contain colons) untouched.
     *
     * @param string $entry
     *
     * @return string
     */
    private static function stripPort(string $entry): string
    {
        // [ipv6]:port or [ipv6]
        if (isset($entry[0]) && '[' === $entry[0]) {
            $end = strpos($entry, ']');

            if (false !== $end) {
                return substr($entry, 1, $end - 1);
            }
        }

        // ipv4:port (single colon only; bare IPv6 has multiple colons)
        if (substr_count($entry, ':') === 1) {
            return explode(':', $entry, 2)[0];
        }

        return $entry;
    }

    /**
     * Parse network expression to subnet address and prefix length.
     *
     * @param string $network CIDR notation or "ip mask"
     *
     * @return array{0: string, 1: int} [subnet, prefix]
     * @throws InvalidArgumentException if network is invalid
     */
    private static function parseNetwork(string $network): array
    {
        // "ip mask" notation
        if (false !== strpos($network, ' ')) {
            [$subnet, $mask] = preg_split('/\s+/', trim($network), 2);

            $version = self::getIpVersion($subnet);

            if (null === $version || false === self::isValidNetmask($mask, $version)) {
                throw new InvalidArgumentException(sprintf('Invalid network "%s"', $network));
            }

            return [$subnet, self::maskToPrefix($mask)];
        }

        // CIDR notation
        if (substr_count($network, '/') === 1) {
            [$subnet, $prefix] = explode('/', $network, 2);

            $version = self::getIpVersion($subnet);

            if (null === $version || self::isValidNetmask($prefix, $version) === false) {
                throw new InvalidArgumentException(sprintf('Invalid network "%s"', $network));
            }

            // Dotted mask in CIDR position
            if (preg_match('/^\d+$/', $prefix) !== 1) {
                return [$subnet, self::maskToPrefix($prefix)];
            }

            return [$subnet, (int)$prefix];
        }

        // Single IP, treated as host (/32 or /128)
        $version = self::getIpVersion($network);

        if (null === $version) {
            throw new InvalidArgumentException(sprintf('Invalid network "%s"', $network));
        }

        return [$network, 4 === $version ? 32 : 128];
    }

    /**
     * Convert a netmask (dotted or prefix length) to a prefix length.
     *
     * @param string $mask
     *
     * @return int
     */
    private static function maskToPrefix(string $mask): int
    {
        if (preg_match('/^\d+$/', $mask) === 1) {
            return (int)$mask;
        }

        $binary = inet_pton($mask);

        if (false === $binary) {
            throw new InvalidArgumentException(sprintf('Invalid netmask "%s"', $mask));
        }

        $prefix = 0;
        $length = strlen($binary);

        for ($i = 0; $i < $length; $i++) {
            $prefix += substr_count(decbin(ord($binary[$i])), '1');
        }

        return $prefix;
    }

    /**
     * Convert a prefix length to a binary mask for the given IP version.
     *
     * @param int $prefix
     * @param int $version 4 or 6
     *
     * @return string Binary string (as returned by inet_pton)
     */
    private static function prefixToBinaryMask(int $prefix, int $version): string
    {
        $bytes = 4 === $version ? 4 : 16;
        $bits = $bytes * 8;

        $binary = '';
        for ($i = 0; $i < $bytes; $i++) {
            $remaining = $prefix - ($i * 8);

            if ($remaining >= 8) {
                $binary .= chr(255);
                continue;
            }

            if ($remaining <= 0) {
                $binary .= chr(0);
                continue;
            }

            $binary .= chr((0xFF << (8 - $remaining)) & 0xFF);
        }

        return $binary;
    }

    /**
     * Is binary mask a contiguous sequence of high bits?
     *
     * @param string $binary Binary string (as returned by inet_pton)
     *
     * @return bool
     */
    private static function isContiguousMask(string $binary): bool
    {
        $bits = '';
        $length = strlen($binary);

        for ($i = 0; $i < $length; $i++) {
            $bits .= str_pad(decbin(ord($binary[$i])), 8, '0', STR_PAD_LEFT);
        }

        // Valid mask matches: ones followed by zeros (e.g. 1110...0)
        return preg_match('/^1*0*$/', $bits) === 1;
    }
}
