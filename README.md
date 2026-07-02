# Berlioz Helpers (PHP Functions)

[![Latest Version](https://img.shields.io/packagist/v/berlioz/helpers.svg?style=flat-square)](https://github.com/BerliozFramework/Helpers/releases)
[![Software license](https://img.shields.io/github/license/BerliozFramework/Helpers.svg?style=flat-square)](https://github.com/BerliozFramework/Helpers/blob/1.x/LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/BerliozFramework/Helpers/tests.yml?branch=1.x&style=flat-square)](https://github.com/BerliozFramework/Helpers/actions/workflows/tests.yml?query=branch%3A1.x)
[![Quality Grade](https://img.shields.io/codacy/grade/cf7e947e6ddf4da28e540402bf08d957/1.x.svg?style=flat-square)](https://www.codacy.com/manual/BerliozFramework/Helpers)
[![Total Downloads](https://img.shields.io/packagist/dt/berlioz/helpers.svg?style=flat-square)](https://packagist.org/packages/berlioz/helpers)

Many PHP functions used in the Berlioz framework, which you can use in your developments.

## Array

All array path methods support the following path formats:
- Dot notation: `foo.bar.baz`
- Bracket notation: `foo[bar][baz]`
- JSON Pointer ([RFC 6901](https://www.rfc-editor.org/rfc/rfc6901)): `/foo/bar/baz`

- `b_array_is_list(array $array): bool`

  Is sequential array?

- `b_array_column(array $array, int|string|\Closure|null $column_key, int|string|\Closure|null $index_key = null): array`

  Get values from a single column in the input array.

  Difference between native array_column() and b_array_column() is that b_array_column() accept a \Closure in keys
  arguments.

- `b_array_merge_recursive(array $arraySrc, array ...$arrays): array`

  Merge two or more arrays recursively.

  Difference between native array_merge_recursive() is that b_array_merge_recursive() do not merge strings values into
  an array.

- `b_array_traverse_exists(&$mixed, string $path): bool`

  Traverse array with path and return if path exists.

- `b_array_traverse_get(&$mixed, string $path, $default = null): mixed|null`

  Traverse array with path and get value.

- `b_array_traverse_set(&$mixed, string $path, $value): bool`

  Traverse array with path and set value.

- `b_array_traverse_unset(&$mixed, string $path): bool`

  Traverse array with path and unset value.

- `b_array_only(array $array, array $keys): array`

  Get a subset of the array containing only the given keys. Missing keys are ignored and the source order is preserved.

- `b_array_except(array $array, array $keys): array`

  Get a subset of the array excluding the given keys. Missing keys are ignored and the source order is preserved.

- `b_array_simple(array $array, ?string $prefix = null): array`

  Simplify a multidimensional array to simple.

- `b_array_nested(array $array): array`

  Transform a simple array with dot/bracket notation keys into a multidimensional array.

## File

- `b_human_file_size($size, int $precision = 2): string`

  Get a human see file size.

- `b_size_from_ini(string $size): int`

  Get size in bytes from ini conf file.

- `b_resolve_absolute_path(string $srcPath, string $dstPath): ?string`

  Resolve absolute path from another.

- `b_resolve_relative_path(string $srcPath, string $dstPath): string`

  Resolve relative path from another.

- `b_fwritei(resource $resource, string $str, ?int $length = null, ?int $offset = null): int|false`

  File write in insertion mode.

- `b_ftruncate(resource $resource, int $size, ?int $offset = null): bool`

  Truncate a part of file and shift rest of data.

## Network

- `b_net_validate_ip(string $ip): bool`

  Is valid IP (v4 or v6)?

- `b_net_validate_ipv4(string $ip): bool`

  Is valid IP v4?

- `b_net_validate_ipv6(string $ip): bool`

  Is valid IP v6?

- `b_net_ip_version(string $ip): ?int`

  Get IP version (4, 6 or null if not a valid IP).

- `b_net_is_private_ip(string $ip): bool`

  Is a private (non-public) IP address? Returns `true` for private and reserved ranges (e.g. `10.0.0.0/8`,
  `192.168.0.0/16`, `fc00::/7`, loopback, link-local, ...).

- `b_net_is_public_ip(string $ip): bool`

  Is a publicly routable IP address?

- `b_net_ip_in_range(string $ip, string $start, string $end): bool`

  Is IP within the inclusive range `[start, end]`? Works for IPv4 and IPv6 (same family required); bounds may be given in
  any order.

- `b_net_expand_ipv6(string $ip): ?string`

  Expand an IPv6 address to its full form (e.g. `2001:db8::1` -> `2001:0db8:0000:0000:0000:0000:0000:0001`), or `null` if
  not a valid IPv6.

- `b_net_compress_ipv6(string $ip): ?string`

  Compress an IPv6 address to its shortest canonical form (e.g. `2001:0db8:0000:0000:0000:0000:0000:0001` ->
  `2001:db8::1`), or `null` if not a valid IPv6.

- `b_net_validate_netmask(string $mask, ?int $version = null): bool`

  Is valid netmask? Accepts a dotted netmask (e.g. `255.255.255.0`) or a CIDR prefix length (e.g. `24`). A valid netmask
  must be a contiguous sequence of high bits.

- `b_net_validate_cidr(string $cidr): bool`

  Is valid CIDR notation (e.g. `192.168.1.0/24`, `2001:db8::/32`)?

- `b_net_ip_in_network(string $ip, string $network): bool`

  Is IP in network? The network can be expressed in CIDR notation (`192.168.1.0/24`) or as `ip mask`
  (`192.168.1.0 255.255.255.0`).

- `b_net_range(string $network): array`

  Get network range. Returns an array with keys: `version`, `prefix`, `network`, `netmask`, `first`, `last`,
  `broadcast` (IPv4 only, `null` for IPv6) and `count` (int, or numeric string for large IPv6 ranges).

- `b_net_forwarded_for_parse(string $header): array`

  Split and trim an `X-Forwarded-For` header value into a list of IP addresses, stripping optional ports (including the
  `[ipv6]:port` form) and discarding invalid entries. The list keeps header order (left-most = claimed client,
  right-most = closest proxy).

- `b_net_client_ip(array $trustedProxies = [], ?array $server = null, string $header = 'X-Forwarded-For'): ?string`

  Determine the real client IP from server parameters (defaults to `$_SERVER`). Returns `REMOTE_ADDR` unless the request
  comes from a trusted proxy (exact IP or CIDR range), in which case the forwarded header chain is walked from right to
  left and the first non-trusted hop is returned. If `REMOTE_ADDR` is not trusted, the forwarded header is ignored.

- `b_net_is_trusted_proxy(string $ip, array $trustedProxies): bool`

  Is the given IP a trusted proxy? Matches an exact IP address or a CIDR range (e.g. `10.0.0.0/8`). Invalid entries are
  ignored.

## Object

- `b_get_property_value($object, string $property, &$exists = null): mixed`

  Get property value with getter method.

- `b_set_property_value($object, string $property, $value): bool`

  Set property value with setter method.

## String

- `b_str_random(int $length = 12, int $options = B_STR_RANDOM_NUMBER | B_STR_RANDOM_SPECIAL_CHARACTERS | B_STR_RANDOM_NEED_ALL): string`

  Generate an random string.

- `b_nl2p(string $str): string`

  Surrounds paragraphs with "P" HTML tag and inserts HTML line breaks before all newlines; in a string.

- `b_str_remove_accents(string $str): string`

  Remove accents.

- `b_str_to_uri(string $str): string`

  String to URI string.

- `b_minify_html(string $str): string`

  Minify HTML string.

- `b_str_truncate(string $str, int $nbCharacters = 128, int $where = B_TRUNCATE_RIGHT, string $separator = '...'): string`

  Truncate string.

- `b_parse_str(string $str, bool $keepDots = true): array`

  Parses the string into variables.

- `b_pascal_case(string $str): string`

  Get pascal case convention of string.

- `b_camel_case(string $str): string`

  Get camel case convention of string.

- `b_snake_case(string $str): string`

  Get snake case convention of string.

- `b_spinal_case(string $str): string`

  Get spinal case convention of string.