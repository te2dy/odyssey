<?php
/**
 * Odyssey, a Dotclear theme.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2023 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

namespace Dotclear\Theme\odyssey;

use Dotclear\App;

/**
 * This class contains functions related to the theme custom settings
 * available through the theme configurator.
 */
class OdysseySettings
{
    /**
     * Defines the sections in which the theme settings will be displayed.
     *
     * The sections and sub-sections are placed in an array following this pattern:
     * $sections['section_id'] = [
     *     'name'         => 'The name of the section',
     *     'sub_sections' => [
     *         'sub_section_1_id' => 'The name of the subsection',
     *         'sub_section_2_id' => …
     *     ]
     * ];
     *
     * @return array Sections and sub-sections.
     */
    public static function sections(): array
    {
        $sections['global'] = [
            'name'         => __('section-global'),
            'sub_sections' => [
                'fonts' => __('section-global-fonts')
            ]
        ];

        return $sections;
    }

    /**
     * Defines all customization settings of the theme.
     *
     * $default_settings['setting_id'] = [
     *     'title'       => (string) The title of the setting,
     *     'description' => (string) The description of the setting,
     *     'type'        => (string) The type of the form input (checkbox, string, select, select_int),
     *     'choices'     => [
     *         __('The name of the option') => 'the-id-of-the-option', // Choices are only used with "select" and "select_int" types.
     *     ],
     *     'default'     => (string) The default value of the setting,
     *     'section'     => (array) ['section', 'sub_section'] The section where to put the setting
     * ];
     *
     * @return array The settings.
     */
    public static function default(): array
    {
        $default_settings['global_font_family'] = [
            'title'       => __('settings-global-fontfamily-title'),
            'description' => __('settings-global-fontfamily-description'),
            'type'        => 'select',
            'choices'     => [
                __('settings-global-fontfamily-sansserif-default') => 'sans-serif',
                __('settings-global-fontfamily-serif')             => 'serif',
                __('settings-global-fontfamily-mono')              => 'monospace',
                /*__('settings-global-fontfamily-sansserifbrowser')  => 'sans-serif-browser',
                __('settings-global-fontfamily-serifbrowser')      => 'serif-browser',
                __('settings-global-fontfamily-monobrowser')       => 'monospace-browser',
                __('settings-global-fontfamily-atkinson')          => 'atkinson',
                __('settings-global-fontfamily-ebgaramond')        => 'eb-garamond',
                __('settings-global-fontfamily-luciole')           => 'luciole'*/
            ],
            'default'     => 'sans-serif',
            'section'     => ['global', 'fonts']
        ];


        $default_settings['styles'] = [
            'title' => __('settings-footer-odysseystyles-title'),
        ];

        return $default_settings;
    }

    /**
     * Retrieves all theme settings stored in the database.
     *
     * @return array The id of the saved parameters associated with their values.
     */
    public static function saved(): array
    {
        $saved_settings   = [];
        $default_settings = self::default();

        foreach ($default_settings as $setting_id => $setting_data) {
            if (App::blog()->settings->odyssey->$setting_id) {
                if (isset($setting_data['type']) && $setting_data['type'] === 'checkbox') {
                    $saved_settings[$setting_id] = (bool) App::blog()->settings->odyssey->$setting_id;
                } elseif (isset($setting_data['type']) && $setting_data['type'] === 'select_int') {
                    $saved_settings[$setting_id] = (int) App::blog()->settings->odyssey->$setting_id;
                } else {
                    $saved_settings[$setting_id] = (string) App::blog()->settings->odyssey->$setting_id;
                }
            }
        }

        return $saved_settings;
    }
}
