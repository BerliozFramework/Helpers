<?php

use Berlioz\Helpers\ArrayHelper;
use Berlioz\Helpers\FileHelper;
use Berlioz\Helpers\ImageHelper;
use Berlioz\Helpers\NetworkHelper;
use Berlioz\Helpers\ObjectHelper;
use Berlioz\Helpers\StringHelper;

////////////////////
/// ARRAY HELPER ///
////////////////////

/**
 * Is sequential array?
 *
 * @param array $array
 *
 * @return bool
 * @deprecated Use b_array_is_list() instead
 * @see b_array_is_list()
 */
function b_array_is_sequential(array $array): bool
{
    return ArrayHelper::isList($array);
}

/**
 * Is array list?
 *
 * @param array $array
 *
 * @return bool
 */
function b_array_is_list(array $array): bool
{
    return ArrayHelper::isList($array);
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
function b_array_column(array $array, $column_key, $index_key = null): array
{
    return ArrayHelper::column($array, $column_key, $index_key);
}

/**
 * Convert array to an XML element.
 *
 * @param array $array
 * @param SimpleXMLElement|null $root
 * @param string|null $rootName
 *
 * @return SimpleXMLElement
 */
function b_array_to_xml(array $array, ?SimpleXMLElement $root = null, ?string $rootName = null): SimpleXMLElement
{
    return ArrayHelper::toXml($array, $root, $rootName);
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
function b_array_merge_recursive(array ...$arrays): array
{
    return ArrayHelper::mergeRecursive(...$arrays);
}

/**
 * Traverse array with path and return if path exists.
 *
 * @param iterable $mixed Source
 * @param string $path Path
 *
 * @return bool
 * @throws InvalidArgumentException if first argument is not a traversable data
 */
function b_array_traverse_exists(&$mixed, string $path)
{
    return ArrayHelper::traverseExists($mixed, $path);
}

/**
 * Traverse array with path and get value.
 *
 * @param iterable $mixed Source
 * @param string $path Path
 * @param mixed|null $default Default value
 *
 * @return mixed|null
 * @throws InvalidArgumentException if first argument is not a traversable data
 */
function b_array_traverse_get(iterable &$mixed, string $path, $default = null)
{
    return ArrayHelper::traverseGet($mixed, $path, $default);
}

/**
 * Traverse array with path and set value.
 *
 * @param iterable $mixed Source
 * @param string $path Path
 * @param mixed $value Value
 *
 * @return bool
 * @throws InvalidArgumentException if first argument is not a traversable data
 */
function b_array_traverse_set(iterable &$mixed, string $path, $value): bool
{
    return ArrayHelper::traverseSet($mixed, $path, $value);
}

/**
 * Traverse array with path and unset value.
 *
 * @param iterable $mixed Source
 * @param string $path Path
 *
 * @return bool
 */
function b_array_traverse_unset(iterable &$mixed, string $path): bool
{
    return ArrayHelper::traverseUnset($mixed, $path);
}

/**
 * Get a subset of the array containing only the given keys.
 *
 * @param array $array
 * @param array $keys Keys to keep
 *
 * @return array
 */
function b_array_only(array $array, array $keys): array
{
    return ArrayHelper::only($array, $keys);
}

/**
 * Get a subset of the array excluding the given keys.
 *
 * @param array $array
 * @param array $keys Keys to remove
 *
 * @return array
 */
function b_array_except(array $array, array $keys): array
{
    return ArrayHelper::except($array, $keys);
}

/**
 * Simplify multi-dimensional array.
 *
 * @param array $array
 * @param string|null $prefix
 *
 * @return array
 */
function b_array_simple(array $array, ?string $prefix = null): array
{
    return ArrayHelper::simpleArray($array, $prefix);
}

/**
 * Multi-dimensional array from flat array.
 *
 * @param array $array
 *
 * @return array
 */
function b_array_nested(array $array): array
{
    return ArrayHelper::nestedArray($array);
}


///////////////////
/// FILE HELPER ///
///////////////////

/**
 * Get a human see file size.
 *
 * @param float|int $size
 * @param int $precision
 *
 * @return string
 */
function b_human_file_size($size, int $precision = 2): string
{
    return FileHelper::humanFileSize($size, $precision);
}

/**
 * Get size in bytes from ini conf file.
 *
 * @param string $size
 *
 * @return int
 */
function b_size_from_ini(string $size): int
{
    return FileHelper::sizeFromIni($size);
}

/**
 * Resolve absolute path.
 *
 * @param string $srcPath
 * @param string $dstPath
 *
 * @return string|null
 */
function b_resolve_absolute_path(string $srcPath, string $dstPath): ?string
{
    return FileHelper::resolveAbsolutePath($srcPath, $dstPath);
}

/**
 * Resolve relative path.
 *
 * @param string $srcPath
 * @param string $dstPath
 *
 * @return string
 */
function b_resolve_relative_path(string $srcPath, string $dstPath): string
{
    return FileHelper::resolveRelativePath($srcPath, $dstPath);
}

/**
 * File write in insertion mode.
 *
 * Use seekable and writeable resource and not mode 'a+'.
 *
 * @param resource $resource
 * @param string $data
 * @param int|null $length
 * @param int|null $offset
 *
 * @return int|false
 */
function b_fwritei($resource, string $data, ?int $length = null, ?int $offset = null)
{
    return FileHelper::fwritei($resource, $data, $length, $offset);
}

/**
 * Truncate a part of file and shift rest of data.
 *
 * @param resource $resource
 * @param int $size
 * @param int|null $offset Truncate $size chars from $offset
 *
 * @return bool
 */
function b_ftruncate($resource, int $size, ?int $offset = null): bool
{
    return FileHelper::ftruncate($resource, $size, $offset);
}


//////////////////////
/// NETWORK HELPER ///
//////////////////////

/**
 * Is valid IP (v4 or v6)?
 *
 * @param string $ip
 *
 * @return bool
 */
function b_net_validate_ip(string $ip): bool
{
    return NetworkHelper::isValidIp($ip);
}

/**
 * Is valid IP v4?
 *
 * @param string $ip
 *
 * @return bool
 */
function b_net_validate_ipv4(string $ip): bool
{
    return NetworkHelper::isValidIpv4($ip);
}

/**
 * Is valid IP v6?
 *
 * @param string $ip
 *
 * @return bool
 */
function b_net_validate_ipv6(string $ip): bool
{
    return NetworkHelper::isValidIpv6($ip);
}

/**
 * Get IP version.
 *
 * @param string $ip
 *
 * @return int|null 4, 6 or null if not a valid IP
 */
function b_net_ip_version(string $ip): ?int
{
    return NetworkHelper::getIpVersion($ip);
}

/**
 * Is a private (non-public) IP address?
 *
 * @param string $ip
 *
 * @return bool
 */
function b_net_is_private_ip(string $ip): bool
{
    return NetworkHelper::isPrivateIp($ip);
}

/**
 * Is a public (publicly routable) IP address?
 *
 * @param string $ip
 *
 * @return bool
 */
function b_net_is_public_ip(string $ip): bool
{
    return NetworkHelper::isPublicIp($ip);
}

/**
 * Is IP within the inclusive range [start, end]?
 *
 * @param string $ip
 * @param string $start
 * @param string $end
 *
 * @return bool
 */
function b_net_ip_in_range(string $ip, string $start, string $end): bool
{
    return NetworkHelper::ipInRange($ip, $start, $end);
}

/**
 * Expand an IPv6 address to its full, uncompressed form.
 *
 * @param string $ip
 *
 * @return string|null The expanded address, or null if not a valid IPv6
 */
function b_net_expand_ipv6(string $ip): ?string
{
    return NetworkHelper::expandIpv6($ip);
}

/**
 * Compress an IPv6 address to its shortest canonical form.
 *
 * @param string $ip
 *
 * @return string|null The compressed address, or null if not a valid IPv6
 */
function b_net_compress_ipv6(string $ip): ?string
{
    return NetworkHelper::compressIpv6($ip);
}

/**
 * Is valid netmask?
 *
 * Accepts a dotted netmask or a CIDR prefix length.
 *
 * @param string $mask
 * @param int|null $version IP version constraint (4 or 6), or null to accept both
 *
 * @return bool
 */
function b_net_validate_netmask(string $mask, ?int $version = null): bool
{
    return NetworkHelper::isValidNetmask($mask, $version);
}

/**
 * Is valid CIDR notation?
 *
 * @param string $cidr
 *
 * @return bool
 */
function b_net_validate_cidr(string $cidr): bool
{
    return NetworkHelper::isValidCidr($cidr);
}

/**
 * Is IP in network?
 *
 * @param string $ip
 * @param string $network CIDR notation or "ip mask"
 *
 * @return bool
 */
function b_net_ip_in_network(string $ip, string $network): bool
{
    return NetworkHelper::ipInNetwork($ip, $network);
}

/**
 * Get network range.
 *
 * @param string $network CIDR notation or "ip mask"
 *
 * @return array
 */
function b_net_range(string $network): array
{
    return NetworkHelper::getNetworkRange($network);
}

/**
 * Parse an `X-Forwarded-For` header value into a list of IP addresses.
 *
 * @param string $header Raw header value
 *
 * @return string[] List of valid IP addresses, in header order
 */
function b_net_forwarded_for_parse(string $header): array
{
    return NetworkHelper::forwardedForParse($header);
}

/**
 * Determine the real client IP address from server parameters.
 *
 * @param string[] $trustedProxies List of trusted proxy IPs or CIDR ranges
 * @param array|null $server Server parameters (defaults to `$_SERVER` when null)
 * @param string $header Forwarded header name (default: `X-Forwarded-For`)
 *
 * @return string|null The client IP, or null if `REMOTE_ADDR` is missing/invalid
 */
function b_net_client_ip(
    array $trustedProxies = [],
    ?array $server = null,
    string $header = 'X-Forwarded-For'
): ?string {
    return NetworkHelper::clientIp($trustedProxies, $server, $header);
}

/**
 * Is the given IP a trusted proxy?
 *
 * @param string $ip
 * @param string[] $trustedProxies List of trusted proxy IPs or CIDR ranges
 *
 * @return bool
 */
function b_net_is_trusted_proxy(string $ip, array $trustedProxies): bool
{
    return NetworkHelper::isTrustedProxy($ip, $trustedProxies);
}


/////////////////////
/// OBJECT HELPER ///
/////////////////////

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
function b_get_property_value(object $object, string $property, ?bool &$exists = null)
{
    return ObjectHelper::getPropertyValue($object, $property, $exists);
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
function b_set_property_value(object$object, string $property, $value): bool
{
    return ObjectHelper::setPropertyValue($object, $property, $value);
}


/////////////////////
/// STRING HELPER ///
/////////////////////

const B_STR_RANDOM_ALPHA = 1;
const B_STR_RANDOM_NUMERIC = 2;
const B_STR_RANDOM_SPECIAL_CHARACTERS = 4;
const B_STR_RANDOM_LOWER_CASE = 8;
const B_STR_RANDOM_NEED_ALL = 16;
const B_TRUNCATE_LEFT = 1;
const B_TRUNCATE_MIDDLE = 2;
const B_TRUNCATE_RIGHT = 3;


/**
 * Generate an random string.
 *
 * @param int $length Length of string
 * @param int $options Options
 *
 * @return string
 */
function b_str_random(
    int $length = 12,
    int $options = B_STR_RANDOM_ALPHA | B_STR_RANDOM_NUMERIC | B_STR_RANDOM_SPECIAL_CHARACTERS | B_STR_RANDOM_NEED_ALL
): string {
    return StringHelper::random($length, $options);
}

/**
 * Surrounds paragraphs with "P" HTML tag and inserts HTML line breaks before all newlines; in a string.
 *
 * @param string $str
 *
 * @return string
 */
function b_nl2p(string $str): string
{
    return StringHelper::nl2p($str);
}

/**
 * Remove accents.
 *
 * @param string $str
 *
 * @return string
 */
function b_str_remove_accents(string $str): string
{
    return StringHelper::removeAccents($str);
}

/**
 * String to URI string.
 *
 * @param string $str
 *
 * @return string
 */
function b_str_to_uri(string $str): string
{
    return StringHelper::strToUri($str);
}

/**
 * Minify HTML string.
 *
 * @param string $str
 *
 * @return string
 * @link https://stackoverflow.com/a/5324014
 */
function b_minify_html(string $str): string
{
    return StringHelper::minifyHtml($str);
}

/**
 * Truncate string.
 *
 * @param string $str String
 * @param int $nbCharacters Number of characters
 * @param int $where Where option: B_TRUNCATE_LEFT, B_TRUNCATE_MIDDLE or B_TRUNCATE_RIGHT
 * @param string $separator Separator string
 *
 * @return string
 */
function b_str_truncate(
    string $str,
    int $nbCharacters = 128,
    int $where = B_TRUNCATE_RIGHT,
    string $separator = '...'
): string {
    return StringHelper::truncate($str, $nbCharacters, $where, $separator);
}

/**
 * Parses the string into variables.
 *
 * Similar to `parse_str()` function but keep dots and spaces.
 *
 * @param string $str
 * @param bool $keepDots
 *
 * @return array
 * @see https://www.php.net/manual/function.parse-str.php
 */
function b_parse_str(string $str, bool $keepDots = true): array
{
    return StringHelper::parseStr($str, $keepDots);
}

/**
 * Get pascal case convention of string.
 *
 * @param string $str
 *
 * @return string
 */
function b_pascal_case(string $str): string
{
    return StringHelper::pascalCase($str);
}

/**
 * Get camel case convention of string.
 *
 * @param string $str
 *
 * @return string
 */
function b_camel_case(string $str): string
{
    return StringHelper::camelCase($str);
}

/**
 * Get snake case convention of string.
 *
 * @param string $str
 *
 * @return string
 */
function b_snake_case(string $str): string
{
    return StringHelper::snakeCase($str);
}

/**
 * Get spinal case convention of string.
 *
 * @param string $str
 *
 * @return string
 */
function b_spinal_case(string $str): string
{
    return StringHelper::spinalCase($str);
}

/////////////////////
/// IMAGE HELPER ///
/////////////////////

const B_IMG_SIZE_RATIO = 1;
const B_IMG_SIZE_LARGER_EDGE = 2;
const B_IMG_RESIZE_COVER = 4;

/**
 * Calculate a gradient destination color.
 *
 * @param string $color Source color (hex)
 * @param string $colorToAdd Color to add (hex)
 * @param float $percentToAdd Percent to add
 *
 * @return string
 */
function b_gradient_color(string $color, string $colorToAdd, float $percentToAdd): string
{
    return ImageHelper::gradientColor($color, $colorToAdd, $percentToAdd);
}

/**
 * Calculate sizes with new given width and height.
 *
 * @param int $originalWidth Original width
 * @param int $originalHeight Original height
 * @param int|null $newWidth New width
 * @param int|null $newHeight New height
 * @param int $mode Mode (default: B_IMG_SIZE_RATIO)
 *
 * @return array
 */
function b_img_size(
    int $originalWidth,
    int $originalHeight,
    ?int $newWidth = null,
    ?int $newHeight = null,
    int $mode = B_IMG_SIZE_RATIO
): array {
    return ImageHelper::size($originalWidth, $originalHeight, $newWidth, $newHeight, $mode);
}

/**
 * Resize image.
 *
 * @param string|resource|GdImage $img File name or image resource
 * @param int|null $newWidth New width
 * @param int|null $newHeight New height
 * @param int $mode Mode (default: B_IMG_SIZE_RATIO)
 *
 * @return resource|GdImage
 */
function b_img_resize(
    $img,
    ?int $newWidth = null,
    ?int $newHeight = null,
    int $mode = B_IMG_SIZE_RATIO
) {
    return ImageHelper::resize($img, $newWidth, $newHeight, $mode);
}

/**
 * Resize support of image.
 *
 * @param string|resource|GdImage $img File name or image resource
 * @param int|null $newWidth New width
 * @param int|null $newHeight New height
 *
 * @return resource|GdImage
 */
function b_img_support(
    $img,
    ?int $newWidth = null,
    ?int $newHeight = null
) {
    return ImageHelper::resizeSupport($img, $newWidth, $newHeight);
}