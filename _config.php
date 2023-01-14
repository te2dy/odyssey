<?php
/**
 * Origine Mini, a minimal theme for Dotclear.
 *
 * This file sets up the theme configuration page and settings.
 *
 * @author Teddy
 * @copyright GPL-3.0
 */

if (!defined('DC_RC_PATH')) {
    return;
}

l10n::set(__DIR__ . '/locales/' . dcCore::app()->lang . '/admin');

dcCore::app()->addBehavior('adminPageHTMLHead', ['adminConfigOrigineMini', 'load_styles_scripts']);

class adminConfigOrigineMini
{
    /**
     * Loads styles and scripts of the theme configurator.
     *
     * @return void
     */
    public static function load_styles_scripts()
    {
        echo dcPage::cssLoad(dcCore::app()->blog->settings->system->themes_url . '/origine-mini/css/admin.min.css'),
        dcPage::jsLoad(dcCore::app()->blog->settings->system->themes_url . '/origine-mini/js/admin.min.js');
    }
    /**
     * Defines the sections in which the theme settings will be sorted.
     *
     * The sections and sub-sections are placed in an array following this pattern:
     * $page_sections['section_id'] = [
     *     'name'         => 'The name of this section',
     *     'sub_sections' => [
     *         'sub_section_1_id' => 'The name of this subsection',
     *         'sub_section_2_id' => …
     *     ]
     * ];
     *
     * @return array Sections and sub-sections.
     */
    public static function page_sections()
    {
        $page_sections['global'] = [
            'name'         => __('section-global'),
            'sub_sections' => [
                'layout'   => __('section-global-layout'),
                'fonts'    => __('section-global-fonts'),
                'colors'   => __('section-global-colors'),
                'advanced' => __('section-global-advance')
            ]
        ];

        $page_sections['header'] = [
            'name'         => __('section-header'),
            'sub_sections' => [
                'image'    => __('section-image'),
                'no-title' => ''
            ]
        ];

        $page_sections['content'] = [
            'name'         => __('section-content'),
            'sub_sections' => [
                'post-list'       => __('section-content-postlist'),
                'post'            => __('section-content-post'),
                'text-formatting' => __('section-content-textformatting'),
                'other'           => __('section-content-other')
            ]
        ];

        $page_sections['widgets'] = [
            'name'         => __('section-widgets'),
            'sub_sections' => [
                'no-title' => ''
            ]
        ];

        $page_sections['footer'] = [
            'name'         => __('section-footer'),
            'sub_sections' => [
                'no-title'     => '',
                'social-links' => __('section-footer-sociallinks')
            ]
        ];

        return $page_sections;
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
    public static function default_settings()
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
                __('settings-global-fontfamily-mono')              => 'monospace'
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

        if (dcCore::app()->plugins->moduleExists('socialMeta') === true) {
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

        $default_settings['header_banner'] = [
            'title'       => __('settings-header-banner-title'),
            'description' => '',
            'type'        => 'image',
            'placeholder' => html::escapeURL(dcCore::app()->blog->settings->system->public_url . '/' . __('settings-header-banner-placeholder')),
            'default'     => '',
            'section'     => ['header', 'image']
        ];

        $default_settings['header_banner2'] = [
            'title'       => __('settings-header-banner2-title'),
            'description' => '',
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
                __('settings-content-fontfamily-same-default') => 'same',
                __('settings-global-fontfamily-serif')         => 'serif',
                __('settings-global-fontfamily-sansserif')     => 'sans-serif',
                __('settings-global-fontfamily-mono')          => 'monospace'
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

        $default_settings['content_post_list_type'] = [
            'title'       => __('settings-content-postlisttype-title'),
            'description' => '',
            'type'        => 'select',
            'choices'     => [
                __('settings-content-postlisttype-oneline-default')  => 'short',
                __('settings-content-postlisttype-extended')         => 'extended'
            ],
            'default'     => 'short',
            'section'     => ['content', 'post-list']
        ];

        $default_settings['content_post_list_time'] = [
            'title'       => __('settings-content-postlisttime-title'),
            'description' => '',
            'type'        => 'checkbox',
            'default'     => 0,
            'section'     => ['content', 'post-list']
        ];

        $default_settings['content_post_list_reaction_link'] = [
            'title'       => __('settings-content-postlistreactionlink-title'),
            'description' => '',
            'type'        => 'checkbox',
            'default'     => 0,
            'section'     => ['content', 'post-list']
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

        $default_settings['content_reaction_feed'] = [
            'title'       => __('settings-content-postreactionfeed-title'),
            'description' => '',
            'type'        => 'checkbox',
            'default'     => 1,
            'section'     => ['content', 'post']
        ];

        $default_settings['content_trackback_link'] = [
            'title'       => __('settings-content-posttrackbacklink-title'),
            'description' => '',
            'type'        => 'checkbox',
            'default'     => 1,
            'section'     => ['content', 'post']
        ];

        if (dcCore::app()->plugins->moduleExists('signal') === true) {
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
            'section'     => ['content', 'post']
        ];

        $default_settings['content_separator'] = [
            'title'       => __('settings-content-separator-title'),
            'description' => sprintf(__('settings-content-separator-description'), '|'),
            'type'        => 'text',
            'default'     => '|',
            'section'     => ['content', 'other']
        ];

        // Widgets settings.
        if (dcCore::app()->plugins->moduleExists('widgets') === true) {
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

    /**
     * Retrieves all theme settings stored in the database.
     *
     * @return array The id of the saved parameters associated with their values.
     */
    public static function saved_settings()
    {
        $saved_settings   = [];
        $default_settings = self::default_settings();

        foreach ($default_settings as $setting_id => $setting_data) {
            if (dcCore::app()->blog->settings->originemini->$setting_id !== null) {
                if (isset($setting_data['type']) && $setting_data['type'] === 'checkbox') {
                    $saved_settings[$setting_id] = (boolean) dcCore::app()->blog->settings->originemini->$setting_id;
                } elseif (isset($setting_data['type']) && $setting_data['type'] === 'select_int') {
                    $saved_settings[$setting_id] = (integer) dcCore::app()->blog->settings->originemini->$setting_id;
                } else {
                    $saved_settings[$setting_id] = dcCore::app()->blog->settings->originemini->$setting_id;
                }
            }
        }

        return $saved_settings;
    }

    /**
     * Converts a style array into a minified style string.
     *
     * @param array $rules An array of styles.
     *
     * @return string $css The minified styles.
     */
    public static function styles_array_to_string($rules)
    {
        $css = '';

        foreach ($rules as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $selector   = $key;
                $properties = $value;

                $css .= str_replace(', ', ',', $selector) . '{';

                if (is_array($properties) && !empty($properties)) {
                    foreach ($properties as $property => $rule) {
                        if ($rule !== '') {
                            $css .= $property . ':' . str_replace(', ', ',', $rule) . ';';
                        }
                    }
                }

                $css .= '}';
            }
        }

        return $css;
    }

    /**
     * Displays each parameter according to its type.
     *
     * @param strong $setting_id The id of the setting to display.
     *
     * @return void The parameter.
     */
    public static function setting_rendering($setting_id = '')
    {
        $default_settings = self::default_settings();
        $saved_settings   = self::saved_settings();

        if ($setting_id !== '' && array_key_exists($setting_id, $default_settings)) {
            echo '<p id=' . $setting_id . '-input>';

            // Displays the default value of the parameter if it is not defined.
            if (isset($saved_settings[$setting_id])) {
                $setting_value = $saved_settings[$setting_id];
            } else {
                $setting_value = isset($default_settings[$setting_id]['default']) ? $default_settings[$setting_id]['default'] : '';
            }

            switch ($default_settings[$setting_id]['type']) {
                case 'checkbox' :
                    echo form::checkbox(
                         $setting_id,
                         true,
                         $setting_value
                    ),
                    '<label class=classic for=', $setting_id, '>',
                    $default_settings[$setting_id]['title'],
                    '</label>';

                    break;

                case 'select' :
                case 'select_int' :
                    echo '<label for=', $setting_id, '>',
                    $default_settings[$setting_id]['title'],
                    '</label>',
                    form::combo(
                        $setting_id,
                        $default_settings[$setting_id]['choices'],
                        strval($setting_value)
                    );

                    break;

                case 'textarea' :
                    $placeholder = isset($default_settings[$setting_id]['placeholder']) ? 'placeholder="' . $default_settings[$setting_id]['placeholder'] . '"' : '';

                    echo '<label for=', $setting_id, '>',
                    $default_settings[$setting_id]['title'],
                    '</label>',
                    form::textArea(
                        $setting_id,
                        60,
                        3,
                        $setting_value,
                        '',
                        '',
                        false,
                        $placeholder
                    );

                    break;

                case 'image' :
                    $placeholder = isset($default_settings[$setting_id]['placeholder']) ? 'placeholder="' . $default_settings[$setting_id]['placeholder'] . '"' : '';

                    if (!empty($setting_value) && $setting_value['url'] !== '') {
                        $image_src = $setting_value['url'];
                    } else {
                        $image_src = '';
                    }

                    echo '<label for=', $setting_id, '>',
                    $default_settings[$setting_id]['title'],
                    '</label>',
                    '<img alt="" id=', $setting_id, '-src src="', $image_src, '">',
                    form::field(
                        $setting_id,
                        30,
                        255,
                        $image_src,
                        '',
                        '',
                        false,
                        $placeholder
                    );

                    break;

                default :
                    $placeholder = isset($default_settings[$setting_id]['placeholder']) ? 'placeholder="' . $default_settings[$setting_id]['placeholder'] . '"' : '';

                    echo '<label for=', $setting_id, '>',
                    $default_settings[$setting_id]['title'],
                    '</label>',
                    form::field(
                        $setting_id,
                        30,
                        255,
                        $setting_value,
                        '',
                        '',
                        false,
                        $placeholder
                    );

                    break;
            }

            echo '</p>';

            // Displays the description of the parameter as a note.
            if ($default_settings[$setting_id]['type'] === 'checkbox' || (isset($default_settings[$setting_id]['description']) && $default_settings[$setting_id]['description'] !== '')) {
                echo '<p class=form-note id=', $setting_id, '-description>', $default_settings[$setting_id]['description'];

                // If the parameter is a checkbox, displays its default value as a note.
                if ($default_settings[$setting_id]['type'] === 'checkbox') {
                    if ($default_settings[$setting_id]['default'] === 1) {
                        echo ' ', __('settings-default-checked');
                    } else {
                        echo ' ', __('settings-default-unchecked');
                    }
                }

                echo '</p>';
            }
        }
    }

    /**
     * Saves the settings to the database.
     *
     * If the parameter value is equal to the default value, the parameter is removed from the database.
     *
     * @return void
     */
    public static function save_settings()
    {
        if (!empty($_POST)) {
            $default_settings = self::default_settings();
            $saved_settings   = self::saved_settings();

            try {
                dcCore::app()->blog->settings->addNamespace('originemini');

                if (isset($_POST['save'])) {
                    foreach ($default_settings as $setting_id => $setting_value) {
                        $ignore_setting_id = ['styles', 'header_banner', 'header_banner2'];

                        if (!in_array($setting_id, $ignore_setting_id, true)) {
                            if (isset($_POST[$setting_id])) {
                                $drop          = false;
                                $setting_value = '';
                                $setting_type  = isset($default_settings[$setting_id]['type']) ? $default_settings[$setting_id]['type'] : 'string';
                                $setting_title = isset($default_settings[$setting_id]['title']) ? $default_settings[$setting_id]['title'] : '';

                                // If the parameter has a new value that is different from the default (and is not an unchecked checkbox).
                                if ($_POST[$setting_id] != $default_settings[$setting_id]['default']) {
                                    if ($setting_type === 'select') {
                                        // Checks if the input value is proposed by the setting.
                                        if (in_array($_POST[$setting_id], $default_settings[$setting_id]['choices'])) {
                                            $setting_value = $_POST[$setting_id];
                                        } else {
                                            $drop = true;
                                        }

                                        $setting_type = 'string';
                                    } elseif ($setting_type === 'select_int') {
                                        // Checks if the input value is proposed by the setting.
                                        if (in_array((int) $_POST[$setting_id], $default_settings[$setting_id]['choices'], true)) {
                                            $setting_value = (int) $_POST[$setting_id];
                                        } else {
                                            $drop = true;
                                        }

                                        $setting_type = 'integer';
                                    } elseif ($setting_type === 'checkbox') {
                                        if ($_POST[$setting_id] === '1' && $default_settings[$setting_id]['default'] !== '1') {
                                            $setting_value = true;
                                            $setting_type  = 'boolean';
                                        }
                                    } else {
                                        $setting_value = html::escapeHTML($_POST[$setting_id]);
                                    }

                                // If the value is equal to the default value, removes the parameter.
                                } elseif ($_POST[$setting_id] == $default_settings[$setting_id]['default']) {
                                    $drop = true;
                                }

                                if ($drop === false) {
                                    dcCore::app()->blog->settings->originemini->put(
                                        $setting_id,
                                        $setting_value,
                                        $setting_type,
                                        html::clean($setting_title),
                                        true
                                    );
                                } else {
                                    dcCore::app()->blog->settings->originemini->drop($setting_id);
                                }

                            // For unchecked checkboxes (= no POST request), does a specific action.
                            } elseif (!isset($_POST[$setting_id]) && $default_settings[$setting_id]['type'] === 'checkbox') {
                                $setting_title = isset($default_settings[$setting_id]['title']) ? $default_settings[$setting_id]['title'] : '';

                                if ($default_settings[$setting_id]['default'] !== 0) {
                                    dcCore::app()->blog->settings->originemini->put(
                                        $setting_id,
                                        false,
                                        'boolean',
                                        html::clean($setting_title),
                                        true
                                    );
                                } else {
                                    dcCore::app()->blog->settings->originemini->drop($setting_id);
                                }
                            } else {
                                dcCore::app()->blog->settings->originemini->drop($setting_id);
                            }

                        /**
                         * Saves the banner.
                         *
                         * The image is saved as an array which contains:
                         * 'url'        => (string) The URL of the image.
                         * 'max-width'  => (int) The maximum width of the image (inferior or equal to the page width).
                         * 'max-height' => (int) The maximum height of the image.
                         */
                        } elseif ($setting_id === 'header_banner') {

                            // If an URL is set.
                            if (isset($_POST['header_banner'])) {

                                // Gets relative url and path of the public folder.
                                $public_url  = dcCore::app()->blog->settings->system->public_url;
                                $public_path = dcCore::app()->blog->public_path;

                                // The URL of the image.
                                $image_url = $_POST['header_banner'];

                                // Converts the absolute URL in a relative one if necessary.
                                $image_url = substr($image_url, strpos($image_url, $public_url));

                                // Retrieves the image path.
                                $image_path = $public_path . str_replace($public_url . '/', '/', $image_url);

                                // If the file exists and is an image.
                                if (file_exists($image_path) === true && getimagesize($image_path) !== false) {

                                    // Gets the dimensions of the image.
                                    list($banner_width) = getimagesize($image_path);

                                    /**
                                     * Limits the maximum width value of the image if its superior to the page width,
                                     * and sets its height proportionally.
                                     */
                                    if (isset($_POST['global_page_width'])) {
                                        $page_width = intval($_POST['global_page_width']) * 16;
                                    } else {
                                        $page_width = 480;
                                    }

                                    if ($banner_width > $page_width) {
                                        $banner_width = 100;
                                    } else {
                                        $banner_width = $banner_width * 100 / $page_width;
                                    }

                                    // Sets the array which contains the image data.
                                    $image_data = [
                                        'url'    => html::escapeURL($image_url),
                                        'width'  => intval($banner_width),
                                    ];

                                    // Saves the setting in the database as an array.
                                    dcCore::app()->blog->settings->originemini->put(
                                        'header_banner',
                                        $image_data,
                                        'array',
                                        html::clean($setting_title),
                                        true
                                    );

                                    // Builds the path to an hypothetical double sized image.
                                    $image_info    = path::info($image_path);
                                    $image_path_2x = $image_info['dirname'] . '/' . $image_info['base'] . '-2x.' . $image_info['extension'];

                                    if (file_exists($image_path_2x) === false) {
                                        $image_path_2x = '';
                                    }

                                    // If the double sized image exists.
                                    if ($image_path_2x !== '') {
                                        $image_url_2x = str_replace($public_path, $public_url, $image_path_2x);

                                        if (file_exists($image_path_2x) !== false && getimagesize($image_path_2x) !== false) {
                                            dcCore::app()->blog->settings->originemini->put(
                                                'header_banner2',
                                                html::escapeURL($image_url_2x),
                                                'string',
                                                html::clean($setting_title),
                                                true
                                            );
                                        }
                                    } else {
                                        dcCore::app()->blog->settings->originemini->drop('header_banner2');
                                    }
                                } else {
                                    dcCore::app()->blog->settings->originemini->drop('header_banner');
                                    dcCore::app()->blog->settings->originemini->drop('header_banner2');
                                }
                            } else {
                                dcCore::app()->blog->settings->originemini->drop('header_banner');
                                dcCore::app()->blog->settings->originemini->drop('header_banner2');
                            }
                        }
                    }

                    dcPage::addSuccessNotice(__('settings-config-updated'));
                } if (isset($_POST['reset'])) {
                    foreach ($default_settings as $setting_id => $setting_value) {
                        dcCore::app()->blog->settings->originemini->drop($setting_id);
                    }

                    dcPage::addSuccessNotice(__('settings-config-reset'));
                }

                // Puts styles in the database.
                self::add_theme_styles($banner_width);

                // Refreshes the blog.
                dcCore::app()->blog->triggerBlog();

                // Resets template cache.
                dcCore::app()->emptyTemplatesCache();

                /**
                 * Redirects to refresh form values.
                 *
                 * With the parameters ['module' => 'origine-mini', 'conf' => '1'],
                 * the & is interpreted as &amp; causing a wrong redirect.
                 */
                http::redirect(dcCore::app()->adminurl->get('admin.blog.theme', ['module' => 'origine-mini']) . '&conf=1');
            } catch (Exception $e) {
                dcCore::app()->error->add($e->getMessage());
            }
        }
    }

    /**
     * Adds custom styles to the theme to apply the settings.
     *
     * @return void
     */
    public static function add_theme_styles($banner_width)
    {
        if (isset($_POST['save'])) {
            $css = '';

            $css_root_array           = [];
            $css_root_media_array     = [];
            $css_main_array           = [];
            $css_media_array          = [];
            $css_media_contrast_array = [];
            $css_media_motion_array   = [];
            $css_media_print_array    = [];

            $default_settings = self::default_settings();

            // Page width.
            $page_width_allowed = [35, 40];

            if (isset($_POST['global_page_width']) && in_array((int) $_POST['global_page_width'], $page_width_allowed, true)) {
                $css_root_array[':root']['--page-width'] = $_POST['global_page_width'] . 'em';
            }

            // Font size.
            $font_size_allowed = [80, 90, 110, 120];

            if (isset($_POST['global_font_size']) && in_array((int) $_POST['global_font_size'], $font_size_allowed, true)) {
                $css_root_array[':root']['--font-size'] = ($_POST['global_font_size'] / 100) . 'em';
            }

            // Font family.
            if (isset($_POST['global_font_family']) && $_POST['global_font_family'] === 'serif') {
                $css_root_array[':root']['--font-family'] = '"Iowan Old Style", "Apple Garamond", Baskerville, "Times New Roman", "Droid Serif", Times, "Source Serif Pro", serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"';
            } elseif ($_POST['global_font_family'] === 'monospace') {
                $css_root_array[':root']['--font-family'] = 'Menlo, Consolas, Monaco, "Liberation Mono", "Lucida Console", monospace';
            }

            // Font family.
            if (isset($_POST['global_font_antialiasing']) && $_POST['global_font_antialiasing'] === '1') {
                $css_root_array['body']['-moz-osx-font-smoothing'] = 'grayscale';
                $css_root_array['body']['-webkit-font-smoothing']  = 'antialiased';
                $css_root_array['body']['font-smooth']             = 'always';

                $css_media_contrast_array['body']['-moz-osx-font-smoothing'] = 'unset';
                $css_media_contrast_array['body']['-webkit-font-smoothing']  = 'unset';
                $css_media_contrast_array['body']['font-smooth']             = 'unset';

                $css_media_print_array['body']['-moz-osx-font-smoothing'] = 'unset';
                $css_media_print_array['body']['-webkit-font-smoothing']  = 'unset';
                $css_media_print_array['body']['font-smooth']             = 'unset';
            }

            // Primary color.
            $primary_colors_allowed = ['gray', 'green', 'red'];

            $primary_colors = [
                'light' => [
                    'gray'  => '#1a1a1a',
                    'green' => '#138613',
                    'red'   => '#e61919',
                ],
                'dark' => [
                    'gray'  => '#fcfcfc',
                    'green' => '#adebad',
                    'red'   => '#f7baba',
                ]
            ];

            if (isset($_POST['global_color_primary']) && in_array($_POST['global_color_primary'], $primary_colors_allowed, true)) {
                $css_root_array[':root']['--color-primary'] = $primary_colors['light'][$_POST['global_color_primary']];

                $css_root_media_array[':root']['--color-primary-dark'] = $primary_colors['dark'][$_POST['global_color_primary']];
            }

            // Transitions.
            if (isset($_POST['global_css_transition']) && $_POST['global_css_transition'] === '1') {
                $css_main_array['a']['transition'] = 'all .2s ease-in-out';

                $css_main_array['a:active, a:hover']['transition'] = 'all .2s ease-in-out';

                $css_main_array['input[type="submit"], .form-submit, .button']['transition'] = 'all .2s ease-in-out';

                $css_main_array['input[type="submit"]:hover, .button:hover, .form-submit:hover']['transition'] = 'all .2s ease-in-out';

                $css_media_motion_array['a']['transition'] = 'unset';

                $css_media_motion_array['a:active, a:hover']['transition'] = 'unset';

                $css_media_motion_array['input[type="submit"], .form-submit, .button']['transition'] = 'unset';

                $css_media_motion_array['input[type="submit"]:hover, .button:hover, .form-submit:hover']['transition'] = 'unset';
            }

            // Links underline.
            if (isset($_POST['global_css_links_underline']) && $_POST['global_css_links_underline'] === '1') {
                $css_root_array[':root']['--link-text-decoration']       = 'underline';
                $css_root_array[':root']['--link-text-decoration-style'] = 'dotted';

                $css_root_array['.button']['text-decoration']       = 'none';
                $css_root_array['.button']['text-decoration-style'] = 'none';
            }

            // Border radius.
            if (isset($_POST['global_css_border_radius']) && $_POST['global_css_border_radius'] === '1') {
                $css_root_array[':root']['--border-radius'] = '.168rem';
            }

            // JS.
            if (isset($_POST['global_js']) && $_POST['global_js'] === '1') {
                if (isset($_POST['content_trackback_link']) && $_POST['content_trackback_link'] === '1') {
                    $css_main_array['#trackback-url']['color'] = 'var(--color-primary, #1742cf)';

                    $css_main_array['#trackback-url:is(:active, :focus, :hover)']['cursor']                = 'pointer';
                    $css_main_array['#trackback-url:is(:active, :focus, :hover)']['filter']                = 'brightness(1.25)';
                    $css_main_array['#trackback-url:is(:active, :focus, :hover)']['text-decoration']       = 'underline';
                    $css_main_array['#trackback-url:is(:active, :focus, :hover)']['text-decoration-style'] = 'solid';

                    $css_main_array['#trackback-url-copied']['display'] = 'none';
                }
            }

            // Header banner
            if (isset($_POST['header_banner'])) {
                $css_main_array['#site-banner']['width'] = '100%';

                if (isset($banner_width) && $banner_width < 100) {
                    $css_main_array['#site-banner img']['width'] = intval($banner_width) . '%';
                } else {
                    $css_main_array['#site-banner img']['width'] = '100%';
                }
            }

            // Blog description.
            if (isset($_POST['header_description']) && $_POST['header_description'] === '1') {
                $css_main_array['#site-identity']['align-items'] = 'center';
                $css_main_array['#site-identity']['column-gap']  = '.5rem';
                $css_main_array['#site-identity']['display']     = 'flex';
                $css_main_array['#site-identity']['flex-wrap']   = 'wrap';
                $css_main_array['#site-identity']['row-gap']     = '.5rem';

                $css_main_array['#site-description']['font-size']   = '.8em';
                $css_main_array['#site-description']['font-style']  = 'italic';
                $css_main_array['#site-description']['font-weight'] = 'normal';
                $css_main_array['#site-description']['margin']      = '0';
            }

            // Content font family.
            if (isset($_POST['content_text_font']) && $_POST['content_text_font'] !== 'same' && $_POST['global_font_family'] !== $_POST['content_text_font']) {
                if ($_POST['content_text_font'] === 'sans-serif') {
                    $css_main_array['.content-text']['font-family'] = '-apple-system, BlinkMacSystemFont, "Avenir Next", Avenir, "Segoe UI", "Helvetica Neue", Helvetica, Ubuntu, Roboto, Noto, Arial, sans-serif';
                } elseif ($_POST['content_text_font'] === 'serif') {
                    $css_main_array['.content-text']['font-family'] = '"Iowan Old Style", "Apple Garamond", Baskerville, "Times New Roman", "Droid Serif", Times, "Source Serif Pro", serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"';
                } elseif ($_POST['content_text_font'] === 'monospace') {
                    $css_main_array['.content-text']['font-family'] = 'Menlo, Consolas, Monaco, "Liberation Mono", "Lucida Console", monospace';
                }
            }

            // Line Height
            $line_height_allowed = [125, 175];

            if (isset($_POST['content_line_height']) && in_array((int) $_POST['content_line_height'], $line_height_allowed, true)) {
                $css_root_array[':root']['--text-line-height'] = ((int) $_POST['content_line_height']) / 100;
            }

            // Text align
            if (isset($_POST['content_text_align']) && ($_POST['content_text_align'] === 'justify' || $_POST['content_text_align'] === 'justify_not_mobile')) {
                $css_root_array[':root']['--text-align'] = 'justify';

                $css_media_contrast_array[':root']['--text-align'] = 'left';

                if ($_POST['content_text_align'] === 'justify_not_mobile') {
                    $css_media_array[':root']['--text-align'] = 'left';
                }
            }

            // Hyphenation.
            if (isset($_POST['content_hyphens']) && $_POST['content_hyphens'] !== 'disabled') {
                $css_main_array['.content-text']['-webkit-hyphens'] = 'auto';
                $css_main_array['.content-text']['-moz-hyphens']    = 'auto';
                $css_main_array['.content-text']['-ms-hyphens']     = 'auto';
                $css_main_array['.content-text']['hyphens']         = 'auto';

                $css_main_array['.content-text']['-webkit-hyphenate-limit-chars'] = '5 2 2';
                $css_main_array['.content-text']['-moz-hyphenate-limit-chars']    = '5 2 2';
                $css_main_array['.content-text']['-ms-hyphenate-limit-chars']     = '5 2 2';

                $css_main_array['.content-text']['-moz-hyphenate-limit-lines'] = '2';
                $css_main_array['.content-text']['-ms-hyphenate-limit-lines']  = '2';
                $css_main_array['.content-text']['hyphenate-limit-lines']      = '2';

                $css_main_array['.content-text']['-webkit-hyphenate-limit-last'] = 'always';
                $css_main_array['.content-text']['-moz-hyphenate-limit-last']    = 'always';
                $css_main_array['.content-text']['-ms-hyphenate-limit-last']     = 'always';
                $css_main_array['.content-text']['hyphenate-limit-last']         = 'always';

                $css_media_contrast_array['.content-text']['-webkit-hyphens'] = 'unset';
                $css_media_contrast_array['.content-text']['-moz-hyphens']    = 'unset';
                $css_media_contrast_array['.content-text']['-ms-hyphens']     = 'unset';
                $css_media_contrast_array['.content-text']['hyphens']         = 'unset';

                $css_media_contrast_array['.content-text']['-webkit-hyphenate-limit-chars'] = 'unset';
                $css_media_contrast_array['.content-text']['-moz-hyphenate-limit-chars']    = 'unset';
                $css_media_contrast_array['.content-text']['-ms-hyphenate-limit-chars']     = 'unset';

                $css_media_contrast_array['.content-text']['-moz-hyphenate-limit-lines'] = 'unset';
                $css_media_contrast_array['.content-text']['-ms-hyphenate-limit-lines']  = 'unset';
                $css_media_contrast_array['.content-text']['hyphenate-limit-lines']      = 'unset';

                $css_media_contrast_array['.content-text']['-webkit-hyphenate-limit-last'] = 'unset';
                $css_media_contrast_array['.content-text']['-moz-hyphenate-limit-last']    = 'unset';
                $css_media_contrast_array['.content-text']['-ms-hyphenate-limit-last']     = 'unset';
                $css_media_contrast_array['.content-text']['hyphenate-limit-last']         = 'unset';

                if ($_POST['content_hyphens'] === 'enabled_not_mobile') {
                    $css_media_array['.content-text']['-webkit-hyphens'] = 'unset';
                    $css_media_array['.content-text']['-moz-hyphens']    = 'unset';
                    $css_media_array['.content-text']['-ms-hyphens']     = 'unset';
                    $css_media_array['.content-text']['hyphens']         = 'unset';

                    $css_media_array['.content-text']['-webkit-hyphenate-limit-chars'] = 'unset';
                    $css_media_array['.content-text']['-moz-hyphenate-limit-chars']    = 'unset';
                    $css_media_array['.content-text']['-ms-hyphenate-limit-chars']     = 'unset';

                    $css_media_array['.content-text']['-moz-hyphenate-limit-lines'] = 'unset';
                    $css_media_array['.content-text']['-ms-hyphenate-limit-lines']  = 'unset';
                    $css_media_array['.content-text']['hyphenate-limit-lines']      = 'unset';

                    $css_media_array['.content-text']['-webkit-hyphenate-limit-last'] = 'unset';
                    $css_media_array['.content-text']['-moz-hyphenate-limit-last']    = 'unset';
                    $css_media_array['.content-text']['-ms-hyphenate-limit-last']     = 'unset';
                    $css_media_array['.content-text']['hyphenate-limit-last']         = 'unset';
                }
            }

            // Post introduction.
            if (isset($_POST['content_post_intro']) && $_POST['content_post_intro'] === '1') {
                $css_main_array['#post-intro']['border-block']  = '.063rem solid var(--color-border, #c2c7d6)';
                $css_main_array['#post-intro']['font-weight']   = '700';
                $css_main_array['#post-intro']['margin-bottom'] = '2rem';

                $css_main_array['#post-intro strong']['font-weight'] = '900';
            }

            // Content links.
            if (!isset($_POST['content_links_underline'])) {
                $css_root_array[':root']['--content-link-text-decoration-line']      = 'none';
                $css_root_array[':root']['--content-link-text-decoration-style']     = 'unset';
                $css_root_array[':root']['--content-link-text-decoration-thickness'] = '.063rem';
            }

            // Link to reactions in the post list.
            if (isset($_POST['content_post_list_reaction_link']) && $_POST['content_post_list_reaction_link'] === '1') {
                $css_main_array['.post-list .post']['flex-wrap'] = 'wrap';

                if (!isset($_POST['content_post_list_type']) || (isset($_POST['content_post_list_type']) && $_POST['content_post_list_type'] !== 'extended')) {
                    $css_main_array['.post-reaction-link']['flex-basis'] = '100%';

                    $css_media_array['.post-reaction-link']['order'] = '3';
                } else {
                    $css_main_array['.post-reaction-link']['display']    = 'inline-block';
                    $css_main_array['.post-reaction-link']['flex-basis'] = '100%';
                    $css_main_array['.post-reaction-link']['margin-top'] = '.5rem';
                }
            }

            // Private comments.
            if (isset($_POST['content_post_email_author']) && $_POST['content_post_email_author'] !== 'disabled') {
                $css_main_array['.comment-private']['margin-bottom'] = '2rem';
            }

            // Sets the order of the blog elements.
            $structure_order = [2 => '',];

            if (isset($_POST['widgets_nav_position']) && $_POST['widgets_nav_position'] === 'header_content') {
                $structure_order[2] = '--order-widgets-nav';
            }

            if ($structure_order[2] === '') {
                $structure_order[2] = '--order-content';
            } else {
                $structure_order[] = '--order-content';
            }

            if (isset($_POST['widgets_nav_position']) && $_POST['widgets_nav_position'] === 'content_footer') {
                $structure_order[] = '--order-widgets-nav';
            }

            if (isset($_POST['widgets_extra_enabled']) && $_POST['widgets_extra_enabled'] === '1') {
                $structure_order[] = '--order-widgets-extra';
            }

            if (isset($_POST['footer_enabled']) && $_POST['footer_enabled'] === '1') {
                $structure_order[] = '--order-footer';
            }

            if (array_search('--order-content', $structure_order) !== 2) {
                $css_root_array[':root']['--order-content'] = array_search('--order-content', $structure_order);
            }

            if (in_array('--order-widgets-nav', $structure_order, true) && array_search('--order-widgets-nav', $structure_order) !== 3) {
                $css_root_array[':root']['--order-widgets-nav'] = array_search('--order-widgets-nav', $structure_order);
            }

            if (in_array('--order-widgets-extra', $structure_order, true) && array_search('--order-widgets-extra', $structure_order) !== 4) {
                $css_root_array[':root']['--order-widgets-extra'] = array_search('--order-widgets-extra', $structure_order);
            }

            if (in_array('--order-footer', $structure_order, true) && array_search('--order-footer', $structure_order) !== 5) {
                $css_root_array[':root']['--order-footer'] = array_search('--order-footer', $structure_order);
            }

            // Social links.
            if (
                (isset($_POST['footer_social_links_diaspora']) && $_POST['footer_social_links_diaspora'] !== '')
                || (isset($_POST['footer_social_links_discord']) && $_POST['footer_social_links_discord'] !== '')
                || (isset($_POST['footer_social_links_facebook']) && $_POST['footer_social_links_facebook'] !== '')
                || (isset($_POST['footer_social_links_github']) && $_POST['footer_social_links_github'] !== '')
                || (isset($_POST['footer_social_links_mastodon']) && $_POST['footer_social_links_mastodon'] !== '')
                || (isset($_POST['footer_social_links_signal']) && $_POST['footer_social_links_signal'] !== '')
                || (isset($_POST['footer_social_links_tiktok']) && $_POST['footer_social_links_tiktok'] !== '')
                || (isset($_POST['footer_social_links_twitter']) && $_POST['footer_social_links_twitter'] !== '')
                || (isset($_POST['footer_social_links_whatsapp']) && $_POST['footer_social_links_whatsapp'] !== '')
            ) {
                $css_main_array['.footer-social-links']['margin-bottom'] = '1rem';

                $css_main_array['.footer-social-links ul']['list-style']                 = 'none';
                $css_main_array['.footer-social-links ul']['margin']                     = '0';
                $css_main_array['.footer-social-links ul']['padding-left']               = '0';
                $css_main_array['.footer-social-links ul li']['display']                 = 'inline-block';
                $css_main_array['.footer-social-links ul li']['margin']                  = '.25em';
                $css_main_array['.footer-social-links ul li:first-child']['margin-left'] = '0';
                $css_main_array['.footer-social-links ul li:last-child']['margin-right'] = '0';

                $css_main_array['.footer-social-links a']['display'] = 'inline-block';

                $css_main_array['.footer-social-links-icon-container']['align-items']      = 'center';
                $css_main_array['.footer-social-links-icon-container']['background-color'] = 'var(--color-input-background, #f1f2f4)';
                $css_main_array['.footer-social-links-icon-container']['border-radius']    = 'var(--border-radius, unset)';
                $css_main_array['.footer-social-links-icon-container']['display']          = 'flex';
                $css_main_array['.footer-social-links-icon-container']['justify-content']  = 'center';
                $css_main_array['.footer-social-links-icon-container']['width']            = '1.5rem';
                $css_main_array['.footer-social-links-icon-container']['height']           = '1.5rem';

                $css_main_array['.footer-social-links-icon']['border']          = '0';
                $css_main_array['.footer-social-links-icon']['fill']            = 'var(--color-text-main, #2e3038)';
                $css_main_array['.footer-social-links-icon']['stroke']          = 'none';
                $css_main_array['.footer-social-links-icon']['stroke-linecap']  = 'round';
                $css_main_array['.footer-social-links-icon']['stroke-linejoin'] = 'round';
                $css_main_array['.footer-social-links-icon']['stroke-width']    = '0';
                $css_main_array['.footer-social-links-icon']['width']           = '1rem';

                $css_main_array['.footer-social-links a:active .footer-social-links-icon-container, .footer-social-links a:focus .footer-social-links-icon-container, .footer-social-links a:hover .footer-social-links-icon-container']['background-color'] = 'var(--color-primary, #1742cf)';

                $css_main_array['.footer-social-links a']['border-bottom'] = 'none';

                $css_main_array['.footer-social-links a:active, .footer-social-links a:focus, .footer-social-links a:hover']['border-bottom'] = 'none';

                $css_main_array['.footer-social-links a:active .footer-social-links-icon, .footer-social-links a:focus .footer-social-links-icon, .footer-social-links a:hover .footer-social-links-icon']['fill'] = 'var(--color-background, #fcfcfd)';

                if (isset($_POST['global_css_transition']) && $_POST['global_css_transition'] === true) {
                    $css_main_array['.footer-social-links-icon-container']['transition'] = 'all .2s ease-in-out';

                    $css_main_array['.footer-social-links-icon']['transition'] = 'all .2s ease-in-out';

                    $css_main_array['.footer-social-links a:active .footer-social-links-icon-container, .footer-social-links a:focus .footer-social-links-icon-container, .footer-social-links a:hover .footer-social-links-icon-container']['transition'] = 'all .2s ease-in-out';

                    $css_main_array['.footer-social-links a:active .footer-social-links-icon, .footer-social-links a:focus .footer-social-links-icon, .footer-social-links a:hover .footer-social-links-icon']['transition'] = 'all .2s ease-in-out';
                }

                $css_media_contrast_array['.footer-social-links-icon-container']['border'] = '1px solid var(--color-border, #c2c7d6)';
            }

            $css .= !empty($css_root_array) ? self::styles_array_to_string($css_root_array) : '';
            $css .= !empty($css_root_media_array) ? '@media (prefers-color-scheme:dark){' . self::styles_array_to_string($css_root_media_array) . '}' : '';
            $css .= !empty($css_main_array) ? self::styles_array_to_string($css_main_array) : '';
            $css .= !empty($css_media_array) ? '@media (max-width:34em){' . self::styles_array_to_string($css_media_array) . '}' : '';
            $css .= !empty($css_media_contrast_array) ? '@media (prefers-contrast:more),(-ms-high-contrast:active),(-ms-high-contrast:black-on-white){' . self::styles_array_to_string($css_media_contrast_array) . '}' : '';
            $css .= !empty($css_media_motion_array) ? '@media (prefers-reduced-motion:reduce){' . self::styles_array_to_string($css_media_motion_array) . '}' : '';
            $css .= !empty($css_media_print_array) ? '@media print{' . self::styles_array_to_string($css_media_print_array) . '}' : '';

            if (!empty($css)) {
                dcCore::app()->blog->settings->originemini->put(
                    'styles',
                    htmlspecialchars($css, ENT_NOQUOTES),
                    'string',
                    $default_settings['styles']['title'],
                    true
                );
            } else {
                dcCore::app()->blog->settings->originemini->drop('styles');
            }
        } else {
            dcCore::app()->blog->settings->originemini->drop('styles');
        }
    }

    /**
     * Displays the theme configuration page.
     *
     * @return void
     */
    public static function page_rendering()
    {
        /**
         * Creates a table that contains all the parameters and their titles according to the following pattern:
         *
         * $sections_with_settings_id = [
         *     'section_1_id' => [
         *         'sub_section_1_id' => ['setting_1_id', 'option_2_id'],
         *         'sub_section_2_id' => …
         *     ]
         * ];
         */
        $sections_with_settings_id = [];

        $sections = self::page_sections();
        $settings = self::default_settings();

        // Puts titles in the setting array.
        foreach($sections as $section_id => $section_data) {
            $sections_with_settings_id[$section_id] = [];
        }

        // Puts all parameters in their section.
        foreach($settings as $setting_id => $setting_data) {
            if ($setting_id !== 'header_banner2' && $setting_id !== 'styles') {
                // If a sub-section is set.
                if (isset($setting_data['section'][1])) {
                    $sections_with_settings_id[$setting_data['section'][0]][$setting_data['section'][1]][] = $setting_id;
                } else {
                    $sections_with_settings_id[$setting_data['section'][0]][] = $setting_id;
                }
            }
        }

        // Removes the titles if they are not associated with any parameter.
        $sections_with_settings_id = array_filter($sections_with_settings_id);
        ?>

        <form action="<?php echo dcCore::app()->adminurl->get('admin.blog.theme', ['module' => 'origine-mini', 'conf' => '1']); ?>" enctype=multipart/form-data id=theme_config method=post>
            <?php
            // Displays the title of each section and places the corresponding parameters under each one.
            foreach ($sections_with_settings_id as $section_id => $section_data) {
                echo '<h3 id=section-', $section_id, '>', $sections[$section_id]['name'], '</h3>',
                '<div class=fieldset>';

                foreach ($section_data as $sub_section_id => $setting_id) {
                    // Displays the name of the sub-section unless its ID is "no-title".
                    if ($sub_section_id !== 'no-title') {
                        echo '<h4 id=section-', $section_id, '-', $sub_section_id, '>', $sections[$section_id]['sub_sections'][$sub_section_id], '</h4>';
                    }

                    // Displays the parameter.
                    foreach ($setting_id as $setting_id_value) {
                        self::setting_rendering($setting_id_value);
                    }
                }

                echo '</div>';
            }
            ?>

            <p>
                <?php echo dcCore::app()->formNonce(); ?>

                <input name=save type=submit value="<?php echo __('settings-save-button-text'); ?>">

                <input class=delete name=reset value="<?php echo __('settings-reset-button-text'); ?>" type=submit>
            </p>

            <div class=warning-msg id=origine-mini-message-js>
                <p><?php echo __('settings-scripts-message-intro'); ?></p>

                <p><?php printf(__('settings-scripts-message-csp'), __('settings-scripts-message-csp-href'), __('settings-scripts-message-csp-title')); ?></p>

                <p><?php echo __('settings-scripts-message-hash-intro'); ?></p>

                <?php
                /**
                 * Displays the list of script hashes if they are loaded.
                 *
                 * @see /_prepend.php
                 */
                if (dcCore::app()->blog->settings->originemini->js_hash !== null) {
                    $hashes = dcCore::app()->blog->settings->originemini->js_hash;

                    if (!empty($hashes)) {
                        echo '<ul>';

                        foreach ($hashes as $script_id => $hash) {
                            $hash = '<code>' . $hash . '</code>';

                            echo '<li id=hash-', $script_id, '>';

                            if ($script_id === 'searchform') {
                                echo __('settings-scripts-message-hash-searchform'), '<br>', $hash;
                            } elseif ($script_id === 'trackbackurl') {
                                echo __('settings-scripts-message-hash-trackbackurl'), '<br>', $hash;
                            } elseif ($script_id === 'imagewide') {
                                echo __('settings-scripts-message-hash-imagewide'), '<br>', $hash;
                            }

                            echo '</li>';
                        }

                        echo '</ul>';
                    }
                }
                ?>

                <p><?php printf(__('settings-scripts-message-example'), 'https://open-time.net/post/2022/08/15/CSP-mon-amour-en-public', 'fr', 'CSP mon amour en public'); ?></p>

                <p><?php echo __('settings-scripts-message-note'); ?></p>
            </div>
        </form>

        <?php
    }
}

adminConfigOrigineMini::save_settings();
adminConfigOrigineMini::page_rendering();
