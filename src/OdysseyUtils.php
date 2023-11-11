<?php
/**
 * Odyssey, a Dotclear theme.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2023 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

use Dotclear\App;

// This class contains useful functions.
class OdysseyUtils
{
    /**
     * Returns true if the purpose of a value, block or behavior
     * is to be activated via the theme configuration currently not available.
     *
     * @param bool $true true by default.
     *
     * @return bool
     */
    public static function configuratorSetting(bool $true = true): bool
    {
        if ($true === true) {
            return true;
        }

        return false;
    }
    
    /**
     * Wraps a string in quotes if it contains a least one space.
     *
     * Avoids unnecessarily wrapping attributes in quotation marks.
     *
     * @param string $value The value.
     *
     * @return string The string.
     */
    public static function attrValueQuotes(string $value): string
    {
        return str_contains($value, ' ') === false ? $value : '"' . $value . '"';
    }

    /**
     * Gets the URL of the blog.
     *
     * @return string The URL.
     */
    public static function blogBaseURL(): string
    {
        $parsed_url = parse_url(App::blog()->url);

        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host   = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port   = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';

        return $scheme . $host . $port;
    }

    /**
     * Converts a style array into a minified style string.
     *
     * @param array $rules An array of styles.
     *
     * @return string $css The minified styles.
     */
    public static function stylesArrayToString($rules): string
    {
        $css = '';

        foreach ($rules as $key => $value) {
            if (!is_int($key)) {
                if (is_array($value) && !empty($value)) {
                    $selector   = $key;
                    $properties = $value;

                    $css .= str_replace(', ', ',', $selector) . '{';

                    if (is_array($properties) && !empty($properties)) {
                        foreach ($properties as $property => $rule) {
                            if ($rule !== '') {
                                $css .= $property . ':';
                                $css .= str_replace(', ', ',', $rule) . ';';
                            }
                        }
                    }

                    $css .= '}';
                }
            } else {
                // For @font-face.
                foreach ($value as $key_2 => $value_2) {
                    if (is_array($value) && !empty($value_2)) {
                        $selector   = $key_2;
                        $properties = $value_2;

                        $css .= str_replace(', ', ',', $selector) . '{';

                        if (is_array($properties) && !empty($properties)) {
                            foreach ($properties as $property => $rule) {
                                if ($rule !== '') {
                                    $css .= $property . ':';
                                    $css .= str_replace(', ', ',', $rule) . ';';
                                }
                            }
                        }

                        $css .= '}';
                    }
                }
            }
        }

        return $css;
    }

    /**
     * Removes 0 before decimal separator of numbers inferior to 1.
     *
     * @param string|int $number The number.
     *
     * @return string The cleaned number.
     */
    public static function removeZero($number): string
    {
        $number = strval($number);

        if (str_starts_with($number, '0.')) {
            $number = substr($number, 1);
        }

        return $number;
    }
}
