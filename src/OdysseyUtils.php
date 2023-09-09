<?php
/**
 * Odyssey, a Dotclear theme.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2023 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

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
    public static function attrValueQuotes(string $value)
    {
        return str_contains($value, ' ') === false ? $value : '"' . $value . '"';
    }
}
