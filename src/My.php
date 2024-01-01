<?php
/**
 * Odyssey, a Dotclear theme.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2024 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

namespace Dotclear\Theme\odyssey;

use Dotclear\App;
use Dotclear\Module\MyTheme;

class My extends MyTheme
{
    public static function settingsSections(string $section_id = ''): array
    {
        $sections = [
            'global' => [
                'name'         => __('section-global'),
                'sub_sections' => [
                    'layout' => __('section-global-layout'),
                    'fonts'  => __('section-global-fonts')
                ]
            ],
            'header' => [
                'name'         => __('section-header'),
                'sub_sections' => [
                    'no-title' => ''
                ]
            ],
            'content' => [
                'name'         => __('section-content'),
                'sub_sections' => [
                    'postlist' => __('section-content-postlist'),
                    'text'     => __('section-content-text'),
                    'images'   => __('section-content-images')
                ]
            ],
            'reactions' => [
                'name'         => __('section-reactions'),
                'sub_sections' => [
                    'form' => __('section-reactions-form')
                ]
            ],
            'widgets' => [
                'name'         => __('section-widgets'),
                'sub_sections' => []
            ],
            'advanced' => [
                'name'         => __('section-advanced'),
                'sub_sections' => [
                    'metadata' => __('section-advanced-metadata')
                ]
            ]
        ];

        if ($section_id && isset($sections[$section_id])) {
            return $sections[$section_id];
        }

        return $sections;
    }

    public static function settingsDefault(string $setting_id = ''): array
    {
        $default_settings['global_unit'] = [
            'title'       => __('settings-global-unit-title'),
            'description' => __('settings-global-unit-description'),
            'type'        => 'select',
            'choices'     => [
                __('settings-global-unit-relative-default') => 'em',
                __('settings-global-unit-static')           => 'px'
            ],
            'default'     => 'em',
            'section'     => ['global', 'layout']
        ];

        $default_settings['global_page_width_value'] = [
            'title'       => __('settings-global-pagewidthvalue-title'),
            'description' => __('settings-global-pagewidthvalue-description'),
            'type'        => 'integer',
            'default'     => '',
            'placeholder' => !My::settingValue('global_unit') ? 30 : 480,
            'section'     => ['global', 'layout']
        ];

        $default_settings['global_font_family'] = [
            'title'       => __('settings-global-fontfamily-title'),
            'description' => __('settings-global-fontfamily-description'),
            'type'        => 'select',
            'choices'     => [
                __('settings-global-fontfamily-sansserif-default') => 'sans-serif',
                __('settings-global-fontfamily-serif')             => 'serif',
                __('settings-global-fontfamily-mono')              => 'monospace',
                __('settings-global-fontfamily-sansserifbrowser')  => 'sans-serif-browser',
                __('settings-global-fontfamily-serifbrowser')      => 'serif-browser',
                __('settings-global-fontfamily-monobrowser')       => 'monospace-browser',
                __('settings-global-fontfamily-atkinson')          => 'atkinson',
                __('settings-global-fontfamily-ebgaramond')        => 'eb-garamond',
                __('settings-global-fontfamily-luciole')           => 'luciole'
            ],
            'default'     => 'sans-serif',
            'section'     => ['global', 'fonts']
        ];

        $default_settings['global_font_size'] = [
            'title'       => __('settings-global-fontsize-title'),
            'description' => __('settings-global-fontsize-description'),
            'type'        => 'select_int',
            'choices'     => [
                __('settings-global-fontsize-80')          => 80,
                __('settings-global-fontsize-90')          => 90,
                __('settings-global-fontsize-100-default') => 100,
                __('settings-global-fontsize-110')         => 110,
                __('settings-global-fontsize-120')         => 120
            ],
            'default'     => 100,
            'section'     => ['global', 'fonts']
        ];

        $default_settings['global_font_antialiasing'] = [
            'title'       => __('settings-global-fontantialiasing-title'),
            'description' => __('settings-global-fontantialiasing-description'),
            'type'        => 'checkbox',
            'default'     => 0,
            'section'     => ['global', 'fonts']
        ];

        $default_settings['header_description'] = [
            'title'       => __('settings-header-description-title'),
            'description' => '',
            'type'        => 'checkbox',
            'default'     => 1,
            'section'     => ['header', 'no-title']
        ];

        $default_settings['content_postlist_altcolor'] = [
            'title'       => __('settings-content-postlistaltcolor-title'),
            'description' => __('settings-content-postlistaltcolor-description'),
            'type'        => 'checkbox',
            'default'     => 0,
            'section'     => ['content', 'postlist']
        ];

        $default_settings['content_postlist_thumbnail'] = [
            'title'       => __('settings-content-postlistthumbnail-title'),
            'description' => __('settings-content-postlistthumbnail-description'),
            'type'        => 'checkbox',
            'default'     => 1,
            'section'     => ['content', 'postlist']
        ];

        $default_settings['content_text_align'] = [
            'title'       => __('settings-content-textalign-title'),
            'description' => __('settings-content-textalign-description'),
            'type'        => 'select',
            'choices'     => [
                __('settings-content-textalign-left-default')       => 'left',
                __('settings-content-textalign-justify')            => 'justify',
                __('settings-content-textalign-justify-not-mobile') => 'justify-not-mobile'
            ],
            'default'     => 'left',
            'section'     => ['content', 'text']
        ];

        $default_settings['content_images_wide'] = [
            'title'       => __('settings-content-imageswide-title'),
            'description' => __('settings-content-imageswide-description'),
            'type'        => 'checkbox',
            'default'     => 0,
            'section'     => ['content', 'images']
        ];

        $default_settings['reactions_button'] = [
            'title'       => __('settings-reactions-button-title'),
            'description' => __('settings-reactions-button-description'),
            'type'        => 'checkbox',
            'default'     => 1,
            'section'     => ['reactions', 'form']
        ];

        if (App::plugins()->moduleExists('legacyMarkdown')) {
            $default_settings['reactions_markdown_notice'] = [
                'title'       => __('settings-reactions-markdownnotice-title'),
                'description' => __('settings-reactions-markdownnotice-description'),
                'type'        => 'checkbox',
                'default'     => 0,
                'section'     => ['reactions', 'form']
            ];
        }

        $default_settings['widgets_display'] = [
            'title'       => __('settings-widgets-display-title'),
            'description' => __('settings-widgets-display-description'),
            'type'        => 'checkbox',
            'default'     => 1,
            'section'     => ['widgets', 'no-title']
        ];

        $default_settings['advanced_minimal_social_meta'] = [
            'title'       => __('settings-advanced-minimalsocialmeta-title'),
            'description' => __('settings-advanced-minimalsocialmeta-description'),
            'type'        => 'checkbox',
            'default'     => 0,
            'section'     => ['advanced', 'metadata']
        ];

        $default_settings['advanced_json'] = [
            'title'       => __('settings-advanced-json-title'),
            'description' => __('settings-advanced-json-description'),
            'type'        => 'checkbox',
            'default'     => 0,
            'section'     => ['advanced', 'metadata']
        ];

        $default_settings['styles'] = [
            'title' => __('settings-footer-odysseystyles-title'),
        ];

        if ($setting_id && isset($default_settings[$setting_id])) {
            return $default_settings[$setting_id];
        }

        return $default_settings;
    }

    /**
     * Returns the value of a saved theme setting.
     *
     * @param string $setting_id The setting id.
     *
     * @return mixed The value of the setting.
     */
    public static function settingValue($setting_id = ''): mixed
    {
        return $setting_id ? App::blog()->settings->odyssey->$setting_id : '';
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
    public static function attrValue(string $value): string
    {
        return str_contains($value, ' ') === false ? $value : '"' . $value . '"';
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
    public static function stylesArrToStr($rules): string
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
