<?php
/**
 * Origine Mini, a minimal theme for Dotclear.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2023 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

class OrigineMiniSettings
{
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
    public static function default()
    {
        // Global settings.
        $default_settings['global_page_width'] = [
            'title'       => __('settings-global-pagewidth-title'),
            'description' => __('settings-global-pagewidth-description'),
            'type'        => 'select_int',
            'choices'     => [
                __('settings-global-pagewidth-30-default') => 30,
                __('settings-global-pagewidth-35')         => 35,
                __('settings-global-pagewidth-40')         => 40
            ],
            'default'     => 30,
            'section'     => ['global', 'layout']
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
                __('settings-global-fontfamily-luciole')           => 'luciole'
            ],
            'default'     => 'sans-serif',
            'section'     => ['global', 'fonts']
        ];

        $default_settings['global_font_antialiasing'] = [
            'title'       => __('settings-global-fontantialiasing-title'),
            'description' => __('settings-global-fontantialiasing-description'),
            'type'        => 'checkbox',
            'default'     => 0,
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

        $global_color_background_choices = [
            __('settings-global-backgroundcolor-none-default') => 'none',
            __('settings-global-backgroundcolor-beige')        => 'beige',
            __('settings-global-backgroundcolor-blue')         => 'blue',
            __('settings-global-backgroundcolor-gray')         => 'gray',
            __('settings-global-backgroundcolor-green')        => 'green',
            __('settings-global-backgroundcolor-red')          => 'red'
        ];

        ksort($global_color_primary_choices);

        $default_settings['global_color_background'] = [
            'title'       => __('settings-global-backgroundcolor-title'),
            'description' => __('settings-global-backgroundcolor-description'),
            'type'        => 'select',
            'choices'     => $global_color_background_choices,
            'default'     => 'none',
            'section'     => ['global', 'colors']
        ];

        $default_settings['global_css_transition'] = [
            'title'       => __('settings-global-colortransition-title'),
            'description' => __('settings-global-colortransition-description'),
            'type'        => 'checkbox',
            'default'     => 0,
            'section'     => ['global', 'colors']
        ];

        $default_settings['global_css_links_underline'] = [
            'title'       => __('settings-global-linksunderline-title'),
            'description' => __('settings-global-linksunderline-description'),
            'type'        => 'checkbox',
            'default'     => 0,
            'section'     => ['global', 'colors']
        ];

        $default_settings['global_css_border_radius'] = [
            'title'       => __('settings-global-roundcorner-title'),
            'description' => __('settings-global-roundcorner-description'),
            'type'        => 'checkbox',
            'default'     => 0,
            'section'     => ['global', 'colors']
        ];

        if (dcCore::app()->plugins->moduleExists('socialMeta')) {
            $plugin_social_url = dcCore::app()->adminurl->get('admin.plugin.socialMeta');
        } else {
            $plugin_social_url = dcCore::app()->adminurl->get('admin.plugins');
        }

        $default_settings['global_meta_social'] = [
            'title'       => __('settings-global-minimalsocialmarkups-title'),
            'description' => sprintf(__('settings-global-minimalsocialmarkups-description'), $plugin_social_url),
            'type'        => 'checkbox',
            'default'     => 0,
            'section'     => ['global', 'advanced']
        ];

        $default_settings['global_meta_home_description'] = [
            'title'       => __('settings-global-metahomedescription-title'),
            'description' => __('settings-global-metahomedescription-description'),
            'type'        => 'textarea',
            'default'     => '',
            'section'     => ['global', 'advanced']
        ];

        $default_settings['global_js'] = [
            'title'       => __('settings-global-js-title'),
            'description' => __('settings-global-js-description'),
            'type'        => 'checkbox',
            'default'     => 0,
            'section'     => ['global', 'advanced']
        ];

        $default_settings['global_meta_generator'] = [
            'title'       => __('settings-global-metagenerator-title'),
            'description' => __('settings-global-metagenerator-description'),
            'type'        => 'checkbox',
            'default'     => 0,
            'section'     => ['global', 'advanced']
        ];

        // Header settings.
        $default_settings['header_description'] = [
            'title'       => __('settings-header-description-title'),
            'description' => '',
            'type'        => 'checkbox',
            'default'     => 0,
            'section'     => ['header', 'no-title']
        ];

        $default_settings['header_image'] = [
            'title'       => __('settings-header-image-title'),
            'description' => __('settings-header-image-description'),
            'type'        => 'image',
            'placeholder' => dcCore::app()->blog->settings->system->public_url . '/' . __('settings-header-image-placeholder'),
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
            'title'       => __('settings-header-layout-title'),
            'description' => '',
            'type'        => 'select',
            'choices'     => [
                __('settings-header-imageposition-top-default') => 'top',
                __('settings-header-imageposition-bottom')      => 'bottom',
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

        // Content settings.
        $default_settings['content_text_font'] = [
            'title'       => __('settings-content-fontfamily-title'),
            'description' => '',
            'type'        => 'select',
            'choices'     => [
                __('settings-content-fontfamily-same-default')    => 'same',
                __('settings-global-fontfamily-sansserif')        => 'sans-serif',
                __('settings-global-fontfamily-serif')            => 'serif',
                __('settings-global-fontfamily-mono')             => 'monospace',
                __('settings-global-fontfamily-sansserifbrowser') => 'sans-serif-browser',
                __('settings-global-fontfamily-serifbrowser')     => 'serif-browser',
                __('settings-global-fontfamily-monobrowser')      => 'monospace-browser',
                __('settings-global-fontfamily-atkinson')         => 'atkinson',
                __('settings-global-fontfamily-luciole')          => 'luciole'
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
            'description' => '',
            'type'        => 'select',
            'choices'     => [
                __('settings-content-textalign-left-default')     => 'left',
                __('settings-content-textalign-justify')          => 'justify',
                __('settings-content-textalign-justifynotmobile') => 'justify_not_mobile'
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
                __('settings-content-hyphens-enablednotmobile') => 'enabled_not_mobile'
            ],
            'default'     => 'disabled',
            'section'     => ['content', 'text-formatting']
        ];

        $default_settings['content_initial_letter'] = [
            'title'       => __('settings-content-initialletter-title'),
            'description' => __('settings-content-initialletter-description'),
            'type'        => 'checkbox',
            'default'     => 0,
            'section'     => ['content', 'text-formatting']
        ];

        $default_settings['content_post_list_type'] = [
            'title'       => __('settings-content-postlisttype-title'),
            'description' => '',
            'type'        => 'select',
            'choices'     => [
                __('settings-content-postlisttype-short-default') => 'short',
                __('settings-content-postlisttype-excerpt')       => 'excerpt',
                __('settings-content-postlisttype-content')       => 'content'
            ],
            'default'     => 'title',
            'section'     => ['content', 'entry-list']
        ];

        $default_settings['content_post_list_time'] = [
            'title'       => __('settings-content-postlisttime-title'),
            'description' => '',
            'type'        => 'checkbox',
            'default'     => 0,
            'section'     => ['content', 'entry-list']
        ];

        $default_settings['content_post_list_reaction_link'] = [
            'title'       => __('settings-content-postlistreactionlink-title'),
            'description' => '',
            'type'        => 'select',
            'choices'     => [
                __('settings-content-postlistreactionlink-disabled-default') => 'disabled',
                __('settings-content-postlistreactionlink-whenexist')        => 'when_exist',
                __('settings-content-postlistreactionlink-always')           => 'always'
            ],
            'default'     => 'disabled',
            'section'     => ['content', 'entry-list']
        ];

        $default_settings['content_post_time'] = [
            'title'       => __('settings-content-posttime-title'),
            'description' => '',
            'type'        => 'checkbox',
            'default'     => 0,
            'section'     => ['content', 'post']
        ];

        $default_settings['content_post_intro'] = [
            'title'       => __('settings-content-postintro-title'),
            'description' => __('settings-content-postintro-description'),
            'type'        => 'checkbox',
            'default'     => 0,
            'section'     => ['content', 'post']
        ];

        $default_settings['content_links_underline'] = [
            'title'       => __('settings-content-linksunderline-title'),
            'description' => '',
            'type'        => 'checkbox',
            'default'     => 1,
            'section'     => ['content', 'post']
        ];

        $default_settings['content_images_wide'] = [
            'title'       => __('settings-content-imageswide-title'),
            'description' => __('settings-content-imageswide-description'),
            'type'        => 'checkbox',
            'default'     => 0,
            'section'     => ['content', 'post']
        ];

        $default_settings['content_commentform_hide'] = [
            'title'       => __('settings-content-commentformhide-title'),
            'description' => __('settings-content-commentformhide-description'),
            'type'        => 'checkbox',
            'default'     => 0,
            'section'     => ['content', 'reactions']
        ];

        $default_settings['content_reaction_feed'] = [
            'title'       => __('settings-content-postreactionfeed-title'),
            'description' => '',
            'type'        => 'checkbox',
            'default'     => 1,
            'section'     => ['content', 'reactions']
        ];

        $default_settings['content_trackback_link'] = [
            'title'       => __('settings-content-posttrackbacklink-title'),
            'description' => '',
            'type'        => 'checkbox',
            'default'     => 1,
            'section'     => ['content', 'reactions']
        ];

        if (dcCore::app()->plugins->moduleExists('signal')) {
            $plugin_signal_url = dcCore::app()->adminurl->get('admin.plugin.signal');
        } else {
            $plugin_signal_url = dcCore::app()->adminurl->get('admin.plugins');
        }

        $default_settings['content_post_email_author'] = [
            'title'       => __('settings-content-privatecomment-title'),
            'description' => sprintf(__('settings-content-postlistcommentlink-description'), $plugin_signal_url),
            'type'        => 'select',
            'choices'     => [
                __('settings-content-postlistcommentlink-no-default') => 'disabled',
                __('settings-content-postlistcommentlink-open')       => 'comments_open',
                __('settings-content-postlistcommentlink-always')     => 'always'
            ],
            'default'     => 'disabled',
            'section'     => ['content', 'reactions']
        ];

        $default_settings['content_separator'] = [
            'title'       => __('settings-content-separator-title'),
            'description' => sprintf(__('settings-content-separator-description'), '|'),
            'type'        => 'text',
            'default'     => '|',
            'section'     => ['content', 'other']
        ];

        // Widgets settings.
        if (dcCore::app()->plugins->moduleExists('widgets')) {
            $default_settings['widgets_nav_position'] = [
                'title'       => sprintf(__('settings-widgets-navposition-title'), dcCore::app()->adminurl->get('admin.plugin.widgets')),
                'description' => '',
                'type'        => 'select',
                'choices'     => [
                    __('settings-widgets-navposition-top')            => 'header_content',
                    __('settings-widgets-navposition-bottom-default') => 'content_footer',
                    __('settings-widgets-navposition-disabled')       => 'disabled'
                ],
                'default'     => 'content_footer',
                'section'     => ['widgets', 'no-title']
            ];

            $default_settings['widgets_search_form'] = [
                'title'       => __('settings-widgets-searchform-title'),
                'description' => __('settings-widgets-searchform-description'),
                'type'        => 'checkbox',
                'default'     => 0,
                'section'     => ['widgets', 'no-title']
            ];

            $default_settings['widgets_extra_enabled'] = [
                'title'       => sprintf(__('settings-widgets-extra-title'), dcCore::app()->adminurl->get('admin.plugin.widgets')),
                'description' => __('settings-widgets-extra-description'),
                'type'        => 'checkbox',
                'default'     => 1,
                'section'     => ['widgets', 'no-title']
            ];
        }

        // Footer settings.
        $default_settings['footer_enabled'] = [
            'title'       => __('settings-footer-activation-title'),
            'description' => __('settings-footer-activation-description'),
            'type'        => 'checkbox',
            'default'     => 1,
            'section'     => ['footer', 'no-title']
        ];

        $default_settings['footer_credits'] = [
            'title'       => __('settings-footer-credits-title'),
            'description' => __('settings-footer-credits-description'),
            'type'        => 'checkbox',
            'default'     => 1,
            'section'     => ['footer', 'no-title']
        ];

        $default_settings['footer_social_links_diaspora'] = [
            'title'       => __('settings-footer-sociallinks-diaspora-title'),
            'description' => '',
            'type'        => 'text',
            'default'     => '',
            'section'     => ['footer', 'social-links']
        ];

        $default_settings['footer_social_links_discord'] = [
            'title'       => __('settings-footer-sociallinks-discord-title'),
            'description' => '',
            'type'        => 'text',
            'default'     => '',
            'section'     => ['footer', 'social-links']
        ];

        $default_settings['footer_social_links_facebook'] = [
            'title'       => __('settings-footer-sociallinks-facebook-title'),
            'description' => '',
            'type'        => 'text',
            'default'     => '',
            'section'     => ['footer', 'social-links']
        ];

        $default_settings['footer_social_links_github'] = [
            'title'       => __('settings-footer-sociallinks-github-title'),
            'description' => '',
            'type'        => 'text',
            'default'     => '',
            'section'     => ['footer', 'social-links']
        ];

        $default_settings['footer_social_links_mastodon'] = [
            'title'       => __('settings-footer-sociallinks-mastodon-title'),
            'description' => '',
            'type'        => 'text',
            'default'     => '',
            'section'     => ['footer', 'social-links']
        ];

        $default_settings['footer_social_links_signal'] = [
            'title'       => __('settings-footer-sociallinks-signal-title'),
            'description' => '',
            'type'        => 'text',
            'default'     => '',
            'placeholder' => '+1234567890',
            'section'     => ['footer', 'social-links']
        ];

        $default_settings['footer_social_links_tiktok'] = [
            'title'       => __('settings-footer-sociallinks-tiktok-title'),
            'description' => '',
            'type'        => 'text',
            'default'     => '',
            'section'     => ['footer', 'social-links']
        ];

        $default_settings['footer_social_links_twitter'] = [
            'title'       => __('settings-footer-sociallinks-twitter-title'),
            'description' => '',
            'type'        => 'text',
            'default'     => '',
            'placeholder' => __('settings-footer-sociallinks-twitter-placeholder'),
            'section'     => ['footer', 'social-links']
        ];

        $default_settings['footer_social_links_whatsapp'] = [
            'title'       => __('settings-footer-sociallinks-whatsapp-title'),
            'description' => '',
            'type'        => 'text',
            'default'     => '',
            'placeholder' => '+1234567890',
            'section'     => ['footer', 'social-links']
        ];

        $default_settings['styles'] = [
            'title' => __('settings-footer-origineministyles-title'),
        ];

        return $default_settings;
    }
}
