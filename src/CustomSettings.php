<?php
/**
 * Odyssey, a Dotclear theme.
 *
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2023 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

/**
 * This class contains functions related to the theme custom settings
 * available through the theme configurator.
 */
class odysseySettings
{
    /**
     * Returns the value of a theme setting.
     *
     * @param string $setting_id The setting id.
     *
     * @return mixed The value.
     */
    public static function value($setting_id = '')
    {
        return $setting_id ? dcCore::app()->blog->settings->odyssey->$setting_id : '';
    }

    /**
     * Returns the content width of the blog.
     *
     * @param string $unit The unit of the value ("em" or "px").
     *
     * @return int The content width.
     */
    public static function contentWidth($unit): int
    {
        $units_allowed      = ['em', 'px'];
        $content_width      = 30;
        $content_width_unit = 'em';

        if (self::value('global_page_width_value')) {
            $content_width = (int) self::value('global_page_width_value');
        }

        if (self::value('global_page_width_unit') === 'px') {
            $content_width_unit = 'px';

            $content_width *= 16;
        }

        if (isset($unit) && in_array($unit, $units_allowed)) {
            if ($unit !== $content_width_unit && $unit === 'px') {
                $content_width *= 16;
            }
        }

        return $content_width;
    }

    /**
     * A list of supported sites to use for social links.
     *
     * @return array The list.
     */
    public static function socialSites(): array
    {
        return [
            '500px',
            'dailymotion',
            'diaspora',
            'discord',
            'facebook',
            'github',
            'mastodon',
            'peertube',
            'signal',
            'telegram',
            'tiktok',
            'twitch',
            'vimeo',
            'whatsapp',
            'youtube',
            'x'
        ];
    }

    /**
     * Gets an array of the content width of the blog.
     *
     * @param string $unit           Should be 'em' or 'px'.
     * @param int    $value          The value of the width.
     * @param bool   $return_default If true, the default width will be returned.
     *
     * @return array The unit and the value of the width.
     */
    public static function getContentWidth($unit = 'em', $value = 30, $return_default = false)
    {
        $value = (int) $value;

        $content_width_default = [];

        if ($return_default === true) {
            $content_width_default = [
                'unit'  => 'em',
                'value' => 30
            ];
        }

        if ($unit === 'em' && $value === 30 && $return_default === false) {
            return $content_width_default;
        }

        if ($unit === 'em' && ($value < 30 || $value > 80)) {
            return $content_width_default;
        }

        if ($unit === 'px' && ($value < 480 || $value > 1280)) {
            return $content_width_default;
        }

        if (!in_array($unit, ['em', 'px'], true)) {
            return $content_width_default;
        }

        return [
            'unit'  => $unit,
            'value' => $value
        ];
    }
}
