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
    public static function sections(): array
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
                'image'    => __('section-header-image'),
                'no-title' => ''
            ]
        ];

        $page_sections['content'] = [
            'name'         => __('section-content'),
            'sub_sections' => [
                'entry-list'      => __('section-content-postlist'),
                'post'            => __('section-content-post'),
                'page'            => __('section-content-page'),
                'text-formatting' => __('section-content-textformatting'),
                'reactions'       => __('section-content-reactions'),
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
    public static function default(): array
    {
        // Global settings.
        $default_settings['global_page_width_unit'] = [
            'title'       => __('settings-global-pagewidthunit-title'),
            'description' => __('settings-global-pagewidthunit-description'),
            'type'        => 'select',
            'choices'     => [
                __('settings-global-pagewidthunit-em-default') => 'em',
                __('settings-global-pagewidthunit-px')         => 'px'
            ],
            'default'     => 'em',
            'section'     => ['global', 'layout']
        ];

        $page_width_value_default = 30;

        if (dcCore::app()->blog->settings->odyssey->global_page_width_unit === 'px') {
            $page_width_value_default = 480;
        }

        $default_settings['global_page_width_value'] = [
            'title'       => __('settings-global-pagewidthvalue-title'),
            'description' => __('settings-global-pagewidthvalue-description'),
            'type'        => 'integer',
            'default'     => '',
            'placeholder' => $page_width_value_default,
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
                __('settings-global-fontfamily-ebgaramond')        => 'eb-garamond',
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

        $default_settings['global_css_custom'] = [
            'title'       => __('settings-global-csscustom-title'),
            'description' => __('settings-global-csscustom-description'),
            'type'        => 'textarea',
            'default'     => '',
            'section'     => ['global', 'colors']
        ];

        $default_settings['global_css_custom_mini'] = [
            'title'       => __('settings-global-csscustommini-title'),
            'type'        => 'text',
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
                __('settings-global-fontfamily-ebgaramond')       => 'eb-garamond',
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
                __('settings-content-postlisttype-content')       => 'content',
                __('settings-content-postlisttype-custom')        => 'custom'
            ],
            'default'     => 'short',
            'section'     => ['content', 'entry-list']
        ];

        $default_settings['content_post_list_custom'] = [
            'title'       => __('settings-content-postlistcustom-title'),
            'description' => __('settings-content-postlistcustom-description'),
            'type'        => 'text',
            'default'     => '',
            'placeholder' => '_entry-list-custom.html',
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

        $default_settings['content_post_template'] = [
            'title'       => __('settings-content-posttemplate-title'),
            'description' => __('settings-content-posttemplate-description'),
            'type'        => 'text',
            'default'     => '',
            'placeholder' => '_entry-post.html',
            'section'     => ['content', 'post']
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
            'type'        => 'select',
            'choices'     => [
                __('settings-content-imageswide-disabled-default') => 'disabled',
                __('settings-content-imageswide-postspages')       => 'posts-pages',
                __('settings-content-imageswide-always')           => 'always'
            ],
            'default'     => 'disabled',
            'section'     => ['content', 'post']
        ];

        $default_settings['content_images_wide_size'] = [
            'title'       => __('settings-content-imageswidesize-title'),
            'description' => __('settings-content-imageswidesize-description'),
            'type'        => 'text',
            'default'     => '',
            'placeholder' => '120',
            'section'     => ['content', 'post']
        ];

        $default_settings['content_image_custom_size'] = [
            'title'       => __('settings-content-imagecustomsizes-title'),
            'description' => __('settings-content-imagecustomsizes-description'),
            'type'        => 'text',
            'default'     => '',
            'section'     => ['content', 'post']
        ];

        $default_settings['content_page_template'] = [
            'title'       => __('settings-content-pagetemplate-title'),
            'description' => __('settings-content-pagetemplate-description'),
            'type'        => 'text',
            'default'     => '',
            'placeholder' => '_entry-page.html',
            'section'     => ['content', 'page']
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

        // Social links.
        $social_sites = odysseySettings::socialSites();

        foreach ($social_sites as $site_id) {

            // Defines the description of the setting.
            $setting_description = '';

            if (str_starts_with(__('settings-footer-sociallinks-' . $site_id . '-description'), 'settings-footer-sociallinks') === false ) {
                $setting_description = __('settings-footer-sociallinks-' . $site_id . '-description');
            }

            // Defines the placeholder of the setting.
            $setting_placeholder = '';

            switch ($site_id) {
                case 'whatsapp':
                    $setting_placeholder = '+1234567890';

                    break;
                case 'x':
                    $setting_placeholder = __('settings-footer-sociallinks-x-placeholder');
            }

            // Displays the setting.
            $default_settings['footer_social_links_' . $site_id] = [
                'title'       => __('settings-footer-sociallinks-' . $site_id . '-title'),
                'description' => $setting_description,
                'type'        => 'text',
                'default'     => '',
                'placeholder' => $setting_placeholder,
                'section'     => ['footer', 'social-links']
            ];
        }

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
        $default_settings = odysseySettings::default();

        foreach ($default_settings as $setting_id => $setting_data) {
            if (dcCore::app()->blog->settings->odyssey->$setting_id !== null) {
                if (isset($setting_data['type']) && $setting_data['type'] === 'checkbox') {
                    $saved_settings[$setting_id] = (bool) dcCore::app()->blog->settings->odyssey->$setting_id;
                } elseif (isset($setting_data['type']) && $setting_data['type'] === 'select_int') {
                    $saved_settings[$setting_id] = (int) dcCore::app()->blog->settings->odyssey->$setting_id;
                } else {
                    $saved_settings[$setting_id] = dcCore::app()->blog->settings->odyssey->$setting_id;
                }
            }
        }

        return $saved_settings;
    }

    /**
     * Returns the value of a theme setting.
     *
     * @param string $setting_id The setting id.
     *
     * @return mixed The value of the setting.
     */
    public static function value($setting_id = ''): mixed
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
