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
use Dotclear\Module\MyTheme;

class My extends MyTheme
{
    public static function settingsSections(string $section_id = ''): array
    {
        $sections = [
            'global' => [
                'name'         => __('section-global'),
                'sub_sections' => [
                    'units' => __('section-global-units'),
                    'fonts' => __('section-global-fonts')
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
                    'images'   => __('section-content-images')
                ]
            ],
            'reactions' => [
                'name'         => __('section-reactions'),
                'sub_sections' => [
                    'form' => __('section-reactions-form')
                ]
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
                __('settings-global-unit-relative-default') => 'relative',
                __('settings-global-unit-static')           => 'static'
            ],
            'default'     => 'relative',
            'section'     => ['global', 'no-title']
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
}
