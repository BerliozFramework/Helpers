<?php

use Berlioz\Helpers\ArrayHelper;
use Berlioz\Helpers\FileHelper;
use Berlioz\Helpers\ImageHelper;
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
 * Simplify multi-dimensional array.
 *
 * @param array $array
 * @param string|null $prefix
 *
 * @return array
 */
function b_array_simple(array $array, ?string $prefix = null): array
{
    return ArrayHelper::simpleArray($array, $prefix = null);
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