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

/**
 * Class StringHelper.
 *
 * @package Berlioz\Helpers
 */
final class StringHelper
{
    // Random
    public const RANDOM_ALPHA = 1;
    public const RANDOM_NUMERIC = 2;
    public const RANDOM_SPECIAL_CHARACTERS = 4;
    public const RANDOM_LOWER_CASE = 8;
    public const RANDOM_NEED_ALL = 16;
    // Truncate
    public const TRUNCATE_LEFT = 1;
    public const TRUNCATE_MIDDLE = 2;
    public const TRUNCATE_RIGHT = 3;

    /**
     * Generate an random string.
     *
     * @param int $length Length of string
     * @param int $options Options
     *
     * @return string
     */
    public static function random(
        int $length = 12,
        int $options = StringHelper::RANDOM_ALPHA | StringHelper::RANDOM_NUMERIC | StringHelper::RANDOM_SPECIAL_CHARACTERS | StringHelper::RANDOM_NEED_ALL
    ): string {
        // Options
        $withAlpha = ($options & StringHelper::RANDOM_ALPHA) == StringHelper::RANDOM_ALPHA;
        $withNumeric = ($options & StringHelper::RANDOM_NUMERIC) == StringHelper::RANDOM_NUMERIC;
        $withSpecialCharacters = ($options & StringHelper::RANDOM_SPECIAL_CHARACTERS) == StringHelper::RANDOM_SPECIAL_CHARACTERS;
        $onlyLowerCase = ($options & StringHelper::RANDOM_LOWER_CASE) == StringHelper::RANDOM_LOWER_CASE;
        $needAllRequiredParameters = ($options & StringHelper::RANDOM_NEED_ALL) == StringHelper::RANDOM_NEED_ALL;

        // Defaults
        $numeric = '0123456789';
        $alpha_lowercase = 'abcdefghkjmnopqrstuvwxyz';
        $alpha_uppercase = 'ABCDEFGHKJMNOPQRSTUVWXYZ';
        $specials = '~!@#$%^&*()-_=+[]{};:,.<>/?';

        // Make global source
        $source = '';
        if ($withAlpha || $onlyLowerCase) {
            $source .= $alpha_lowercase;

            if (!$onlyLowerCase) {
                $source .= $alpha_uppercase;
            }
        }
        if ($withNumeric) {
            $source .= $numeric;
        }
        if ($withSpecialCharacters) {
            $source .= $specials;
        }

        $length = abs((int)$length);
        $n = strlen($source);
        $str = [];

        // If all parameters are required
        if ($needAllRequiredParameters === true) {
            if ($withAlpha || $onlyLowerCase) {
                // Lower case
                $str[] = $alpha_lowercase[mt_rand(1, strlen($alpha_lowercase)) - 1];
                $length--;

                // Upper case
                if ($onlyLowerCase === false) {
                    $str[] = $alpha_uppercase[mt_rand(1, strlen($alpha_uppercase)) - 1];
                    $length--;
                }
            }

            // Numeric
            if ($withNumeric === true) {
                $str[] = $numeric[mt_rand(1, strlen($numeric)) - 1];
                $length--;
            }

            // Special characters
            if ($withSpecialCharacters === true) {
                $str[] = $specials[mt_rand(1, strlen($specials)) - 1];
                $length--;
            }
        }

        // Generate the main string
        for ($i = 0; $i < $length; $i++) {
            $str[] = $source[mt_rand(1, $n) - 1];
        }

        // Shuffle the string
        shuffle($str);

        return implode('', $str);
    }

    /**
     * Surrounds paragraphs with "P" HTML tag and inserts HTML line breaks before all newlines; in a string.
     *
     * @param string $str
     *
     * @return string
     */
    public static function nl2p(string $str): string
    {
        $str = preg_split('/(\r?\n){2,}/', $str);
        array_walk(
            $str,
            function (&$str) {
                $str = '<p>' . nl2br(trim($str)) . '</p>';
            }
        );

        return implode("\n", $str);
    }

    /**
     * Remove accents.
     *
     * @param string $str
     *
     * @return string
     */
    public static function removeAccents(string $str): string
    {
        $str = transliterator_transliterate('Any-Latin; Latin-ASCII', $str);

        return $str ?: '';
    }

    /**
     * String to URI string.
     *
     * @param string $str
     *
     * @return string
     */
    public static function strToUri(string $str): string
    {
        $str = StringHelper::removeAccents($str);
        $str = strtolower($str);
        $str = preg_replace('/[^0-9a-z\-]+/', '-', $str);
        $str = preg_replace('/-{2,}/', '-', $str);
        $str = trim($str, '-');

        return $str;
    }

    /**
     * Minify HTML string.
     *
     * @param string $str
     *
     * @return string
     * @link https://stackoverflow.com/a/5324014
     */
    public static function minifyHtml(string $str): string
    {
        // Save and change PHP configuration value
        $oldPcreRecursionLimit = ini_get('pcre.recursion_limit');
        ini_set('pcre.recursion_limit', PHP_OS == 'WIN' ? '524' : '16777');

        $regex = <<<EOT
%# Collapse whitespace everywhere but in blacklisted elements.
(?>             # Match all whitespans other than single space.
  [^\S ]\s*     # Either one [\t\r\n\f\v] and zero or more ws,
| \s{1,}        # or two or more consecutive-any-whitespace.
) # Note: The remaining regex consumes no text at all...
(?=             # Ensure we are not in a blacklist tag.
  [^<]*+        # Either zero or more non-"<" {normal*}
  (?:           # Begin {(special normal*)*} construct
    <           # or a < starting a non-blacklist tag.
    (?!/?(?:textarea|pre|script)\b)
    [^<]*+      # more non-"<" {normal*}
  )*+           # Finish "unrolling-the-loop"
  (?:           # Begin alternation group.
    <           # Either a blacklist start tag.
    (?>textarea|pre|script)\b
  | \z          # or end of file.
  )             # End alternation group.
)  # If we made it here, we are not in a blacklist tag.
%Six
EOT;

        // Reset PHP configuration value
        ini_set('pcre.recursion_limit', $oldPcreRecursionLimit);

        $str = preg_replace($regex, ' ', $str);

        return $str;
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
    public static function truncate(
        string $str,
        int $nbCharacters = 128,
        int $where = StringHelper::TRUNCATE_RIGHT,
        string $separator = '...'
    ): string {
        $str = html_entity_decode($str);

        if (mb_strlen(trim($str)) === 0 || mb_strlen(trim($str)) <= $nbCharacters) {
            return $str;
        }

        switch ($where) {
            case StringHelper::TRUNCATE_LEFT:
                return $separator . ' ' . mb_substr($str, (int)(mb_strlen($str) - $nbCharacters), mb_strlen($str));
            case StringHelper::TRUNCATE_RIGHT:
                return mb_substr($str, 0, $nbCharacters) . ' ' . $separator;
            case StringHelper::TRUNCATE_MIDDLE:
                return mb_substr($str, 0, (int)ceil($nbCharacters / 2)) .
                    ' ' .
                    $separator .
                    ' ' .
                    mb_substr($str, (int)(mb_strlen($str) - floor($nbCharacters / 2)), mb_strlen($str));
            default:
                return $str;
        }
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
    public static function parseStr(string $str, bool $keepDots = true): array
    {
        if (empty($str)) {
            return [];
        }

        if (false === $keepDots) {
            $result = [];
            parse_str($str, $result);

            return $result;
        }

        $final = [];
        $variables = explode('&', $str);

        foreach ($variables as $variable) {
            $result = [];
            parse_str($variable, $result);

            $split = preg_split('/((%5B|\[).*(%5D|\]))?=/i', $variable, 2);

            if (false === $split) {
                continue;
            }

            if (empty($split[0])) {
                $split = explode('=', $variable, 2);

                if (false === $split) {
                    continue;
                }

                if (empty($split[0])) {
                    continue;
                }
            }

            $result = reset($result);
            if (false === $result && null !== ($split[1] ?: null)) {
                $result = urldecode($split[1] ?? '');
            }
            $final = b_array_merge_recursive($final, [urldecode($split[0]) => $result]);
        }

        return $final;
    }

    /**
     * Get pascal case convention of string.
     *
     * @param string $str
     *
     * @return string
     */
    public static function pascalCase(string $str): string
    {
        $str =
            preg_replace_callback(
                '/(?:^|_)(.?)/',
                function ($matches) {
                    return mb_strtoupper($matches[1]);
                },
                $str
            );

        return $str;
    }

    /**
     * Get camel case convention of string.
     *
     * @param string $str
     *
     * @return string
     */
    public static function camelCase(string $str): string
    {
        $str = StringHelper::pascalCase($str);
        $str = mb_strtolower(substr($str, 0, 1)) . substr($str, 1);

        return $str;
    }

    /**
     * Get snake case convention of string.
     *
     * @param string $str
     *
     * @return string
     */
    public static function snakeCase(string $str): string
    {
        $str =
            preg_replace_callback(
                '/([a-z0-9])([A-Z])/',
                function ($matches) {
                    return sprintf('%s_%s', $matches[1], mb_strtolower($matches[2]));
                },
                $str
            );
        $str = mb_strtolower($str);

        return $str;
    }

    /**
     * Get spinal case convention of string.
     *
     * @param string $str
     *
     * @return string
     */
    public static function spinalCase(string $str): string
    {
        $str =
            preg_replace_callback(
                '/([a-z0-9])([A-Z])/',
                function ($matches) {
                    return sprintf('%s-%s', $matches[1], mb_strtolower($matches[2]));
                },
                $str
            );
        $str = str_replace('_', '-', $str);
        $str = mb_strtolower($str);

        return $str;
    }
}