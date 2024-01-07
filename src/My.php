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
use Dotclear\Helper\File\Files;

class My extends MyTheme
{
    public static function settingsSections(string $section_id = ''): array
    {
        $sections = [
            'global' => [
                'name'         => __('section-global'),
                'sub_sections' => [
                    'layout' => __('section-global-layout'),
                    'fonts'  => __('section-global-fonts'),
                    'colors' => __('section-global-colors')
                ]
            ],
            'header' => [
                'name'         => __('section-header'),
                'sub_sections' => [
                    'no-title' => '',
                    'image'    => __('section-header-image')
                ]
            ],
            'content' => [
                'name'         => __('section-content'),
                'sub_sections' => [
                    'postlist'        => __('section-content-postlist'),
                    'text-formatting' => __('section-content-textformatting'),
                    'images'          => __('section-content-images')
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
            'footer' => [
                'name'         => __('section-footer'),
                'sub_sections' => []
            ],
            'advanced' => [
                'name'         => __('section-advanced'),
                'sub_sections' => [
                    'seo' => __('section-advanced-seo')
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
            'placeholder' => !My::settingValue('global_unit') ? '30' : '480',
            'section'     => ['global', 'layout']
        ];

        $default_settings['global_font_family'] = [
            'title'       => __('settings-global-fontfamily-title'),
            'description' => __('settings-global-fontfamily-description'),
            'type'        => 'select',
            'choices'     => [
                __('settings-global-fontfamily-sansserif-default')  => 'sans-serif',
                __('settings-global-fontfamily-transitional')       => 'transitional',
                __('settings-global-fontfamily-oldstyle')           => 'old-style',
                __('settings-global-fontfamily-humanist')           => 'humanist',
                __('settings-global-fontfamily-geometrichumanist')  => 'geometric-humanist',
                __('settings-global-fontfamily-classicalhumanist')  => 'classical-humanist',
                __('settings-global-fontfamily-neogrotesque')       => 'neo-grotesque',
                __('settings-global-fontfamily-monospaceslabserif') => 'monospace-slab-serif',
                __('settings-global-fontfamily-monospacecode')      => 'monospace-code',
                __('settings-global-fontfamily-industrial')         => 'industrial',
                __('settings-global-fontfamily-roundedsans')        => 'rounded-sans',
                __('settings-global-fontfamily-slabserif')          => 'slab-serif',
                __('settings-global-fontfamily-antique')            => 'antique',
                __('settings-global-fontfamily-didone')             => 'didone',
                __('settings-global-fontfamily-handwritten')        => 'handwritten'
                // __('settings-global-fontfamily-atkinson')          => 'atkinson',
                // __('settings-global-fontfamily-ebgaramond')        => 'eb-garamond',
                // __('settings-global-fontfamily-luciole')           => 'luciole'
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
            'default'     => '0',
            'section'     => ['global', 'fonts']
        ];

        $global_color_primary_choices = [
            __('settings-global-primarycolor-blue-default') => 'blue',
            __('settings-global-primarycolor-gray')         => 'gray',
            __('settings-global-primarycolor-green')        => 'green',
            __('settings-global-primarycolor-red')          => 'red'
        ];

        ksort($global_color_primary_choices);

        $default_settings['global_color_primary'] = [
            'title'       => __('settings-global-primarycolor-title'),
            'description' => __('settings-global-primarycolor-description'),
            'type'        => 'select',
            'choices'     => $global_color_primary_choices,
            'default'     => 'blue',
            'section'     => ['global', 'colors']
        ];

        $default_settings['global_css_transition'] = [
            'title'       => __('settings-global-colortransition-title'),
            'description' => __('settings-global-colortransition-description'),
            'type'        => 'checkbox',
            'default'     => '0',
            'section'     => ['global', 'colors']
        ];

        $default_settings['header_align'] = [
            'title'       => __('settings-header-align-title'),
            'description' => '',
            'type'        => 'select',
            'choices'     => [
                __('settings-header-align-left')           => 'left',
                __('settings-header-align-center-default') => 'center',
                __('settings-header-align-right')          => 'right'
            ],
            'default'     => 'center',
            'section'     => ['header', 'no-title']
        ];

        $default_settings['header_description'] = [
            'title'       => __('settings-header-description-title'),
            'description' => '',
            'type'        => 'checkbox',
            'default'     => '1',
            'section'     => ['header', 'no-title']
        ];

        $default_settings['header_post_full'] = [
            'title'       => __('settings-header-postfull-title'),
            'description' => __('settings-header-postfull-description'),
            'type'        => 'checkbox',
            'default'     => '0',
            'section'     => ['header', 'no-title']
        ];

        $default_settings['header_image'] = [
            'title'       => __('settings-header-image-title'),
            'description' => __('settings-header-image-description'),
            'type'        => 'image',
            'placeholder' => App::blog()->settings()->system->public_url . '/' . __('settings-header-image-placeholder'),
            'default'     => '',
            'section'     => ['header', 'image']
        ];

        $default_settings['header_image2x'] = [
            'title'       => '',
            'description' => '',
            'type'        => 'text',
            'default'     => '',
            'section'     => ['header', 'image']
        ];

        $default_settings['header_image_position'] = [
            'title'       => __('settings-header-imageposition-title'),
            'description' => '',
            'type'        => 'select',
            'choices'     => [
                __('settings-header-imageposition-top-default') => 'top',
                __('settings-header-imageposition-bottom')      => 'bottom'
            ],
            'default'     => 'top',
            'section'     => ['header', 'image']
        ];

        $default_settings['header_image_description'] = [
            'title'       => __('settings-header-imagedescription-title'),
            'description' => __('settings-header-imagedescription-description'),
            'type'        => 'text',
            'default'     => '',
            'section'     => ['header', 'image']
        ];

        $default_settings['content_postlist_type'] = [
            'title'       => __('settings-content-postlisttype-title'),
            'description' => '',
            'type'        => 'select',
            'choices'     => [
                __('settings-content-postlisttype-oneline-default') => 'one-line',
                __('settings-content-postlisttype-excerpt')         => 'excerpt',
            ],
            'default'     => 'one-line',
            'section'     => ['content', 'postlist']
        ];

        $default_settings['content_postlist_altcolor'] = [
            'title'       => __('settings-content-postlistaltcolor-title'),
            'description' => __('settings-content-postlistaltcolor-description'),
            'type'        => 'checkbox',
            'default'     => '0',
            'section'     => ['content', 'postlist']
        ];

        $default_settings['content_postlist_thumbnail'] = [
            'title'       => __('settings-content-postlistthumbnail-title'),
            'description' => __('settings-content-postlistthumbnail-description'),
            'type'        => 'checkbox',
            'default'     => '1',
            'section'     => ['content', 'postlist']
        ];

        $default_settings['content_postlist_reactions'] = [
            'title'       => __('settings-content-postlistreactions-title'),
            'description' => '',
            'type'        => 'checkbox',
            'default'     => '0',
            'section'     => ['content', 'postlist']
        ];

        $default_settings['content_text_font'] = [
            'title'       => __('settings-content-fontfamily-title'),
            'description' => '',
            'type'        => 'select',
            'choices'     => [
                __('settings-content-fontfamily-same-default')      => 'same',
                __('settings-global-fontfamily-sansserif')          => 'sans-serif',
                __('settings-global-fontfamily-transitional')       => 'transitional',
                __('settings-global-fontfamily-oldstyle')           => 'old-style',
                __('settings-global-fontfamily-humanist')           => 'humanist',
                __('settings-global-fontfamily-geometrichumanist')  => 'geometric-humanist',
                __('settings-global-fontfamily-classicalhumanist')  => 'classical-humanist',
                __('settings-global-fontfamily-neogrotesque')       => 'neo-grotesque',
                __('settings-global-fontfamily-monospaceslabserif') => 'monospace-slab-serif',
                __('settings-global-fontfamily-monospacecode')      => 'monospace-code',
                __('settings-global-fontfamily-industrial')         => 'industrial',
                __('settings-global-fontfamily-roundedsans')        => 'rounded-sans',
                __('settings-global-fontfamily-slabserif')          => 'slab-serif',
                __('settings-global-fontfamily-antique')            => 'antique',
                __('settings-global-fontfamily-didone')             => 'didone',
                __('settings-global-fontfamily-handwritten')        => 'handwritten'
                // __('settings-global-fontfamily-atkinson')           => 'atkinson',
                // __('settings-global-fontfamily-ebgaramond')         => 'eb-garamond',
                // __('settings-global-fontfamily-luciole')            => 'luciole'
            ],
            'default'     => 'same',
            'section'     => ['content', 'text-formatting']
        ];

        $default_settings['content_line_height'] = [
            'title'       => __('settings-content-lineheight-title'),
            'description' => '',
            'type'        => 'select_int',
            'choices'     => [
                __('settings-content-lineheight-small')            => 125,
                __('settings-content-lineheight-standard-default') => 150,
                __('settings-content-lineheight-big')              => 175
            ],
            'default'     => 150,
            'section'     => ['content', 'text-formatting']
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
            'section'     => ['content', 'text-formatting']
        ];

        $default_settings['content_hyphens'] = [
            'title'       => __('settings-content-hyphens-title'),
            'description' => '',
            'type'        => 'select',
            'choices'     => [
                __('settings-content-hyphens-disabled-default') => 'disabled',
                __('settings-content-hyphens-enabled')          => 'enabled',
                __('settings-content-hyphens-enablednotmobile') => 'enabled-not-mobile'
            ],
            'default'     => 'disabled',
            'section'     => ['content', 'text-formatting']
        ];

        $default_settings['content_initial_letter'] = [
            'title'       => __('settings-content-initialletter-title'),
            'description' => __('settings-content-initialletter-description'),
            'type'        => 'checkbox',
            'default'     => '0',
            'section'     => ['content', 'text-formatting']
        ];

        $default_settings['content_images_wide'] = [
            'title'       => __('settings-content-imageswide-title'),
            'description' => __('settings-content-imageswide-description'),
            'type'        => 'checkbox',
            'default'     => '0',
            'section'     => ['content', 'images']
        ];

        $default_settings['reactions_button'] = [
            'title'       => __('settings-reactions-button-title'),
            'description' => __('settings-reactions-button-description'),
            'type'        => 'checkbox',
            'default'     => '1',
            'section'     => ['reactions', 'form']
        ];

        if (App::plugins()->moduleExists('legacyMarkdown')) {
            $default_settings['reactions_markdown_notice'] = [
                'title'       => __('settings-reactions-markdownnotice-title'),
                'description' => '',
                'type'        => 'checkbox',
                'default'     => '0',
                'section'     => ['reactions', 'form']
            ];
        }

        $default_settings['widgets_display'] = [
            'title'       => __('settings-widgets-display-title'),
            'description' => __('settings-widgets-display-description'),
            'type'        => 'checkbox',
            'default'     => '1',
            'section'     => ['widgets', 'no-title']
        ];

        $default_settings['footer_enabled'] = [
            'title'       => __('settings-footer-activation-title'),
            'description' => __('settings-footer-activation-description'),
            'type'        => 'checkbox',
            'default'     => '1',
            'section'     => ['footer', 'no-title']
        ];

        $default_settings['footer_credits'] = [
            'title'       => __('settings-footer-credits-title'),
            'description' => __('settings-footer-credits-description'),
            'type'        => 'checkbox',
            'default'     => '1',
            'section'     => ['footer', 'no-title']
        ];

        $default_settings['advanced_meta_description'] = [
            'title'       => __('settings-advanced-metadescription-title'),
            'description' => __('settings-advanced-metadescription-description'),
            'type'        => 'textarea',
            'default'     => '',
            'section'     => ['advanced', 'seo']
        ];

        $default_settings['advanced_meta_social'] = [
            'title'       => __('settings-advanced-metasocial-title'),
            'description' => __('settings-advanced-metasocial-description'),
            'type'        => 'checkbox',
            'default'     => '0',
            'section'     => ['advanced', 'seo']
        ];

        /*
        $default_settings['advanced_json'] = [
            'title'       => __('settings-advanced-json-title'),
            'description' => __('settings-advanced-json-description'),
            'type'        => 'checkbox',
            'default'     => '0',
            'section'     => ['advanced', 'seo']
        ];
        */

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

    /**
     * Displays the theme relative URL
     *
     * @return string The URL.
     */
    public static function themeURL(): string
    {
        return App::blog()->settings->system->themes_url . '/odyssey';
    }

    /**
     * Checks if a file path returns a valid image.
     *
     * @param array $path The path to the image.
     *
     * @return bool true if the image exists.
     */
    public static function imageExists($path): bool
    {
        // Extensions allowed for image files in Dotclear.
        $img_ext_allowed = [
            'bmp',
            'gif',
            'ico',
            'jpeg',
            'jpg',
            'jpe',
            'png',
            'svg',
            'tiff',
            'tif',
            'webp',
            'xbm'
        ];

        // Returns true if the file exists and is an allowed type of image.
        if (file_exists($path)
            && in_array(
                strtolower(files::getExtension($path)),
                $img_ext_allowed,
                true
            )
            && substr(mime_content_type($path), 0, 6) === 'image/'
        ) {
            return true;
        }

        return false;
    }

    /**
     * Returns the fonts to be used as styles in CSS
     *
     * @param string $fontname The name of the font to return the CSS rule.
     *
     * @return The font rule.
     */
    public static function fontStack($fontname = ''): string
    {
        if (!$fontname) {
            return '';
        }

        $emoji = ', "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"';

        switch ($fontname) {
            case 'sans-serif' :
                return 'system-ui, sans-serif' . $emoji;
            case 'transitional' :
                return 'Charter, "Bitstream Charter", "Sitka Text", Cambria, serif' . $emoji;
            case 'old-style' :
                return '"Iowan Old Style", "Palatino Linotype", "URW Palladio L", P052, serif' . $emoji;
            case 'humanist' :
                return 'Seravek, "Gill Sans Nova", Ubuntu, Calibri, "DejaVu Sans", source-sans-pro, sans-serif' . $emoji;
            case 'geometric-humanist' :
                return 'Avenir, Montserrat, Corbel, "URW Gothic", source-sans-pro, sans-serif' . $emoji;
            case 'classical-humanist' :
                return 'Optima, Candara, "Noto Sans", source-sans-pro, sans-serif' . $emoji;
            case 'neo-grotesque' :
                return 'Inter, Roboto, "Helvetica Neue", "Arial Nova", "Nimbus Sans", Arial, sans-serif' . $emoji;
            case 'monospace-slab-serif' :
                return '"Nimbus Mono PS", "Courier New", monospace' . $emoji;
            case 'monospace-code' :
                return 'ui-monospace, "Cascadia Code", "Source Code Pro", Menlo, Consolas, "DejaVu Sans Mono", monospace' . $emoji;
            case 'industrial' :
                return 'Bahnschrift, "DIN Alternate", "Franklin Gothic Medium", "Nimbus Sans Narrow", sans-serif-condensed, sans-serif' . $emoji;
            case 'rounded-sans' :
                return 'ui-rounded, "Hiragino Maru Gothic ProN", Quicksand, Comfortaa, Manjari, "Arial Rounded MT", "Arial Rounded MT Bold", Calibri, source-sans-pro, sans-serif' . $emoji;
            case 'slab-serif' :
                return 'Rockwell, "Rockwell Nova", "Roboto Slab", "DejaVu Serif", "Sitka Small", serif' . $emoji;
            case 'antique' :
                return 'Superclarendon, "Bookman Old Style", "URW Bookman", "URW Bookman L", "Georgia Pro", Georgia, serif' . $emoji;
            case 'didone' :
                return 'Didot, "Bodoni MT", "Noto Serif Display", "URW Palladio L", P052, Sylfaen, serif' . $emoji;
            case 'handwritten' :
                return '"Segoe Print", "Bradley Hand", Chilanka, TSCu_Comic, casual, cursive' . $emoji;
            default :
                return '';
        }
    }
}
