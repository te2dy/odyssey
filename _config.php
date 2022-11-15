<?php
/**
 * Origine Mini, a minimal theme for Dotclear.
 *
 * @copyright Teddy
 * @copyright GPL-3.0
 */

if (!defined('DC_RC_PATH')) {
    return;
}

/**
 * An array of default section where settings are put.
 *
 * @since origineConfig 2.0
 */
function origineMiniSettingsSections()
{
    $sections = [];

    $sections['global'] = [
        'name'         => __('settings-section-global-name'),
        'sub_sections' => [
            'fonts'    => __('settings-section-global-fonts-title'),
            'layout'   => __('settings-section-global-layout-title'),
            'colors'   => __('settings-section-global-colors-title'),
            'advanced' => __('settings-section-global-advance-title'),
        ]
    ];

    $sections['header'] = [
        'name'         => __('settings-section-header-name'),
        'sub_sections' => [
            'layout'  => __('settings-section-header-layout-title'),
            'content' => __('settings-section-header-content-title'),
            // 'logo'    => __('settings-section-header-logo-title'),
        ]
    ];

    $sections['content'] = [
        'name'         => __('settings-section-content-name'),
        'sub_sections' => [
            'post-list'       => __('settings-section-content-postlist-title'),
            'post'            => __('settings-section-content-post-title'),
            'text-formatting' => __('settings-section-content-textformatting-title'),
            'images'          => __('settings-section-content-images-title'),
            'author'          => __('settings-section-content-author-title'),
            'comments'        => __('settings-section-content-reactions-title'),
            'other'           => __('settings-section-content-other-title'),
        ]
    ];

    $sections['widgets'] = [
        'name'         => __('settings-section-widgets-name'),
        'sub_sections' => [
            'no-title' => '',
        ]
    ];

    $sections['footer'] = [
        'name'         => __('settings-section-footer-name'),
        'sub_sections' => [
            'no-title'     => '',
            'social-links' => __('settings-section-footer-sociallinks-title'),
        ]
    ];

    return $sections;
}

/**
 * An array of default settings of the plugin.
 *
 * @since origineConfig 2.0
 *
 * $default_settings = [
 *     'title'       => (string) The title of the setting,
 *     'description' => (string) The description of the setting,
 *     'type'        => (string) The type of the input (checkbox, string, select, select_int),
 *     'choices'     => [
 *         __('The name of the option') => 'the-id-of-the-option',
 *     ], only used with types "select" and "select_int"
 *     'default'     => (string) The default value of the setting,
 *     'section'     => (array) ['section', 'sub-section'] The section where to put the setting
 * ];
 */
function origineMiniSettingsDefault()
{
    $default_settings = [];

    // Global.
    $default_settings['global_page_width'] = [
        'title'       => __('settings-option-global-pagewidth-title'),
        'description' => __('settings-option-global-pagewidth-description'),
        'type'        => 'select_int',
        'choices'     => [
            __('settings-option-global-pagewidth-title-30-default') => 30,
            __('settings-option-global-pagewidth-title-35')         => 35,
            __('settings-option-global-pagewidth-title-40')         => 40
        ],
        'default'     => 'system',
        'section'     => ['global', 'layout']
    ];

    $content_font_family_default = 'sans-serif';
    $content_font_family_choices = [
        __('settings-option-global-fontfamily-sansserif-default') => 'sans-serif',
        __('settings-option-global-fontfamily-serif')             => 'serif',
        __('settings-option-global-fontfamily-mono')              => 'monospace'
    ];

    $default_settings['global_font_family'] = [
        'title'       => __('settings-option-global-fontfamily-title'),
        'description' => __('settings-option-global-fontfamily-description'),
        'type'        => 'select',
        'choices'     => $content_font_family_choices,
        'default'     => $content_font_family_default,
        'section'     => ['global', 'fonts']
    ];

    $default_settings['global_font_size'] = [
        'title'       => __('settings-option-global-fontsize-title'),
        'description' => __('settings-option-global-fontsize-description'),
        'type'                => 'select_int',
        'choices'     => [
            __('settings-option-global-fontsize-80')          => 80,
            __('settings-option-global-fontsize-90')          => 90,
            __('settings-option-global-fontsize-100-default') => 100,
            __('settings-option-global-fontsize-110')         => 110,
            __('settings-option-global-fontsize-120')         => 120
        ],
        'default'     => 100,
        'section'     => ['global', 'fonts']
    ];

    $global_color_secondary_default = 'blue';
    $global_color_secondary_choices = [
        __('settings-option-global-secondarycolor-blue-default') => 'blue',
        __('settings-option-global-secondarycolor-gray')         => 'gray',
        __('settings-option-global-secondarycolor-green')        => 'green',
        __('settings-option-global-secondarycolor-red')          => 'red'
    ];

    ksort($global_color_secondary_choices);

    $default_settings['global_color_secondary'] = [
        'title'       => __('settings-option-global-secondarycolor-title'),
        'description' => __('settings-option-global-secondarycolor-description'),
        'type'        => 'select',
        'choices'     => $global_color_secondary_choices,
        'default'     => $global_color_secondary_default,
        'section'     => ['global', 'colors']
    ];

    $default_settings['global_css_transition'] = [
        'title'       => __('settings-option-global-colortransition-title'),
        'description' => __('settings-option-global-colortransition-description'),
        'type'        => 'checkbox',
        'default'     => 0,
        'section'     => ['global', 'colors']
    ];

    $default_settings['global_css_links_underline'] = [
        'title'       => __('settings-option-global-linksunderline-title'),
        'description' => __('settings-option-global-linksunderline-description'),
        'type'        => 'checkbox',
        'default'     => 0,
        'section'     => ['global', 'colors']
    ];

    $default_settings['global_css_border_radius'] = [
        'title'       => __('settings-option-global-roundcorner-title'),
        'description' => __('settings-option-global-roundcorner-description'),
        'type'        => 'checkbox',
        'default'     => 0,
        'section'     => ['global', 'colors']
    ];

    $default_settings['global_meta_social'] = [
        'title'       => __('settings-option-global-minimalsocialmarkups-title'),
        'description' => __('settings-option-global-minimalsocialmarkups-description'),
        'type'        => 'checkbox',
        'default'     => 0,
        'section'     => ['global', 'advanced']
    ];

    $default_settings['global_meta_generator'] = [
        'title'       => __('settings-option-global-metagenerator-title'),
        'description' => __('settings-option-global-metagenerator-description'),
        'type'        => 'checkbox',
        'default'     => 0,
        'section'     => ['global', 'advanced']
    ];

    $default_settings['header_description'] = [
        'title'       => __('settings-option-header-description-title'),
        'description' => '',
        'type'        => 'checkbox',
        'default'     => 0,
        'section'     => ['header', 'content']
    ];

    $content_postlisttype_choices = [
        __('settings-option-content-postlisttype-oneline-default')  => 'short',
        __('settings-option-content-postlisttype-extended')         => 'extended'
    ];

    $default_settings['content_post_list_type'] = [
        'title'       => __('settings-option-content-postlisttype-title'),
        'description' => '',
        'type'        => 'select',
        'choices'     => $content_postlisttype_choices,
        'default'     => 'standard',
        'section'     => ['content', 'post-list']
    ];

    $default_settings['content_post_list_time'] = [
        'title'       => __('settings-option-content-postlisttime-title'),
        'description' => '',
        'type'        => 'checkbox',
        'default'     => 0,
        'section'     => ['content', 'post-list']
    ];

    $content_text_font_family_choices = [
        __('settings-option-content-fontfamily-title-same-default') => 'same',
        __('settings-option-global-fontfamily-serif')               => 'serif',
        __('settings-option-global-fontfamily-sansserif')           => 'sans-serif',
        __('settings-option-global-fontfamily-mono')                => 'monospace'
    ];

    $default_settings['content_text_font'] = [
        'title'       => __('settings-option-content-fontfamily-title'),
        'description' => '',
        'type'        => 'select',
        'choices'     => $content_text_font_family_choices,
        'default'     => 'same',
        'section'     => ['content', 'text-formatting']
    ];

    $default_settings['content_text_align'] = [
        'title'       => __('settings-option-content-textalign-title'),
        'description' => '',
        'type'        => 'select',
        'choices'     => [
            __('settings-option-content-textalign-left-default')     => 'left',
            __('settings-option-content-textalign-justify')          => 'justify',
            __('settings-option-content-textalign-justifynotmobile') => 'justify_not_mobile'
        ],
        'default'     => 'left',
        'section'     => ['content', 'text-formatting']
    ];

    $default_settings['content_hyphens'] = [
        'title'       => __('settings-option-content-hyphens-title'),
        'description' => '',
        'type'        => 'select',
        'choices'     => [
            __('settings-option-content-hyphens-disabled-default') => 'disabled',
            __('settings-option-content-hyphens-enabled')          => 'enabled',
            __('settings-option-content-hyphens-enablednotmobile') => 'enabled_not_mobile'
        ],
        'default'     => 'disabled',
        'section'     => ['content', 'text-formatting']
    ];

    $default_settings['content_images_wide'] = [
        'title'       => __('settings-option-content-imageswide-title'),
        'description' => __('settings-option-content-imageswide-description'),
        'type'        => 'checkbox',
        'default'     => 0,
        'section'     => ['content', 'images']
    ];

    $default_settings['content_post_time'] = [
        'title'       => __('settings-option-content-posttime-title'),
        'description' => '',
        'type'        => 'checkbox',
        'default'     => 0,
        'section'     => ['content', 'other']
    ];

    $default_settings['content_post_intro'] = [
        'title'       => __('settings-option-content-postintro-title'),
        'description' => __('settings-option-content-postintro-description'),
        'type'        => 'checkbox',
        'default'     => 0,
        'section'     => ['content', 'other']
    ];

    $default_settings['content_separator'] = [
        'title'       => __('settings-option-content-separator-title'),
        'description' => sprintf(__('settings-option-content-separator-description'), '|'),
        'type'        => 'text',
        'default'     => '|',
        'section'     => ['content', 'other']
    ];

    $default_settings['content_post_list_comment_link'] = [
        'title'       => __('settings-option-content-postlistcommentlink-title'),
        'description' => '',
        'type'        => 'checkbox',
        'default'     => 0,
        'section'     => ['content', 'comments']
    ];

    $default_settings['content_comment_links'] = [
        'title'       => __('settings-option-content-postcommentfeed-title'),
        'description' => '',
        'type'        => 'checkbox',
        'default'     => 1,
        'section'     => ['content', 'comments']
    ];

    $default_settings['content_post_email_author'] = [
        'title'       => __('settings-option-content-privatecomment-title'),
        'description' => sprintf(__('settings-option-content-postlistcommentlink-description'), 'https://plugins.dotaddict.org/dc2/details/signal'),
        'type'        => 'select',
        'choices'     => [
            __('settings-option-content-postlistcommentlink-no-default') => 'disabled',
            __('settings-option-content-postlistcommentlink-open')       => 'comments_open',
            __('settings-option-content-postlistcommentlink-always')     => 'always'
        ],
        'default'     => 'disabled',
        'section'     => ['content', 'comments']
    ];

    // Widgets.
    $default_settings['widgets_nav_position'] = [
        'title'       => __('settings-option-widgets-navposition-title'),
        'description' => '',
        'type'        => 'select',
        'choices'     => [
            __('settings-option-widgets-navposition-top')            => 'header_content',
            __('settings-option-widgets-navposition-bottom-default') => 'content_footer',
            __('settings-option-widgets-navposition-disabled')       => 'disabled'
        ],
        'default'     => 'content_footer',
        'section'     => ['widgets', 'no-title']
    ];

    $default_settings['widgets_search_form'] = [
        'title'       => __('settings-option-widgets-searchform-title'),
        'description' => '',
        'type'        => 'checkbox',
        'default'     => 0,
        'section'     => ['widgets', 'no-title']
    ];

    $default_settings['widgets_extra_enabled'] = [
        'title'       => __('settings-option-widgets-extra-title'),
        'description' => '',
        'type'        => 'checkbox',
        'default'     => 1,
        'section'     => ['widgets', 'no-title']
    ];

    // Footer.
    $default_settings['footer_enabled'] = [
        'title'       => __('settings-option-footer-activation-title'),
        'description' => __('settings-option-footer-activation-description'),
        'type'        => 'checkbox',
        'default'     => 1,
        'section'     => ['footer', 'no-title']
    ];

    $default_settings['footer_credits'] = [
        'title'       => __('settings-option-footer-credits-title'),
        'description' => '',
        'type'        => 'checkbox',
        'default'     => 1,
        'section'     => ['footer', 'no-title']
    ];

    $default_settings['footer_social_links_diaspora'] = [
        'title'       => __('settings-option-footer-sociallinks-diaspora-title'),
        'description' => '',
        'type'        => 'text',
        'default'     => '',
        'section'     => ['footer', 'social-links']
    ];

    $default_settings['footer_social_links_discord'] = [
        'title'       => __('settings-option-footer-sociallinks-discord-title'),
        'description' => '',
        'type'        => 'text',
        'default'     => '',
        'section'     => ['footer', 'social-links']
    ];

    $default_settings['footer_social_links_facebook'] = [
        'title'       => __('settings-option-footer-sociallinks-facebook-title'),
        'description' => '',
        'type'        => 'text',
        'default'     => '',
        'section'     => ['footer', 'social-links']
    ];

    $default_settings['footer_social_links_github'] = [
        'title'       => __('settings-option-footer-sociallinks-github-title'),
        'description' => '',
        'type'        => 'text',
        'default'     => '',
        'section'     => ['footer', 'social-links']
    ];

    $default_settings['footer_social_links_mastodon'] = [
        'title'       => __('settings-option-footer-sociallinks-mastodon-title'),
        'description' => '',
        'type'        => 'text',
        'default'     => '',
        'section'     => ['footer', 'social-links']
    ];

    $default_settings['footer_social_links_signal'] = [
        'title'       => __('settings-option-footer-sociallinks-signal-title'),
        'description' => '',
        'type'        => 'text',
        'default'     => '',
        'section'     => ['footer', 'social-links']
    ];

    $default_settings['footer_social_links_tiktok'] = [
        'title'       => __('settings-option-footer-sociallinks-tiktok-title'),
        'description' => '',
        'type'        => 'text',
        'default'     => '',
        'section'     => ['footer', 'social-links']
    ];

    $default_settings['footer_social_links_twitter'] = [
        'title'       => __('settings-option-footer-sociallinks-twitter-title'),
        'description' => '',
        'type'        => 'text',
        'default'     => '',
        'section'     => ['footer', 'social-links'],
    ];

    $default_settings['footer_social_links_whatsapp'] = [
        'title'       => __('settings-option-footer-sociallinks-whatsapp-title'),
        'description' => '',
        'type'        => 'text',
        'default'     => '',
        'section'     => ['footer', 'social-links']
    ];

    $default_settings['css'] = [
        'title'       => __('settings-option-footer-origineministyles-title'),
        'description' => '',
        'type'        => 'text',
        'default'     => '',
        'section'     => []
    ];

    return $default_settings;
}

/**
 * Converts an array to CSS without spaces and line breaks.
 *
 * @param array $rules An array of CSS rules.
 *
 * @return string $css All the CSS in a single line.
 */
function origineMiniArrayToCSS($rules)
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
 * Displays the input of a setting.
 *
 * @param string $setting_id       The ID of the setting to display.
 * @param array  $default_settings All default settings.
 * @param array  $settings         All values of settings set by the user.
 *
 * @return void
 */
function origineMiniSettingDisplay($setting_id = '', $default_settings = [], $settings = [])
{
    if ($setting_id !== '' && !empty($settings) && !empty($default_settings) && array_key_exists($setting_id, $default_settings) === true) {
        echo '<p>';

        if ($default_settings[$setting_id]['type'] === 'checkbox') {
            echo form::checkbox(
                 $setting_id,
                 true,
                 $settings[$setting_id]
            ),
            '<label class=classic for=' . $setting_id . '>',
            $default_settings[$setting_id]['title'],
            '</label>';
        } elseif ($default_settings[$setting_id]['type'] === 'select' || $default_settings[$setting_id]['type'] === 'select_int') {
            echo '<label for=' . $setting_id . '>',
                 $default_settings[$setting_id]['title'],
                 '</label>',
                 form::combo(
                     $setting_id,
                     $default_settings[$setting_id]['choices'],
                     strval($settings[$setting_id])
                 );
        } elseif ($default_settings[$setting_id]['type'] === 'text') {
            echo '<label for=' . $setting_id . '>',
                 $default_settings[$setting_id]['title'],
                 '</label>',
                 form::field(
                     $setting_id,
                     30,
                     255,
                     $settings[$setting_id]
                 );
        }

        echo '</p>';

        // If the setting has a description, displays it as a note.
        if ($default_settings[$setting_id]['type'] === 'checkbox' || (isset($default_settings[$setting_id]['description']) && $default_settings[$setting_id]['description'] !== '')) {
            echo '<p class=form-note>', $default_settings[$setting_id]['description'];

            if ($default_settings[$setting_id]['type'] === 'checkbox') {
                if ($default_settings[$setting_id]['default'] === 1) {
                    echo ' ', __('option-default-checked');
                } else {
                    echo ' ', __('option-default-unchecked');
                }
            }

            echo '</p>';
        }
    }
}

$default_settings = origineMiniSettingsDefault();

// Adds all default settings values if necessary.
foreach($default_settings as $setting_id => $setting_data) {
    if (!dcCore::app()->blog->settings->originemini->$setting_id) {
        if ($setting_data['type'] === 'checkbox') {
            $setting_type = 'boolean';
        } elseif ($setting_data['type'] === 'select_int') {
            $setting_type = 'integer';
        } else {
            $setting_type = 'string';
        }

        dcCore::app()->blog->settings->originemini->put(
            $setting_id,
            $setting_data['default'],
            $setting_type,
            $setting_data['title'],
            false
        );
    }
}

// An array or all settings.
$settings = [];

foreach($default_settings as $setting_id => $setting_data) {
    if ($setting_data['type'] === 'checkbox') {
        $settings[$setting_id] = (boolean) dcCore::app()->blog->settings->originemini->$setting_id;
    } elseif ($setting_data['type'] === 'select_int') {
        $settings[$setting_id] = (integer) dcCore::app()->blog->settings->originemini->$setting_id;
    } else {
        $settings[$setting_id] = dcCore::app()->blog->settings->originemini->$setting_id;
    }
}

/**
 * Saves settings.
 */
if (!empty($_POST)) {
    try {
        if (isset($_POST['save']) !== false) {
            dcCore::app()->blog->settings->addNamespace('originemini');

            // Ignores styles.
            $settings_to_ignore = ['css'];

            // Saves options.
            foreach ($settings as $id => $value) {
                if (!in_array($id, $settings_to_ignore)) {
                    if ($default_settings[$id]['type'] === 'checkbox') {
                        if (!empty($_POST[$id]) && intval($_POST[$id]) === 1) {
                            dcCore::app()->blog->settings->originemini->put($id, true);
                        } else {
                            dcCore::app()->blog->settings->originemini->put($id, false);
                        }
                    } elseif (isset($_POST[$id])) {
                        dcCore::app()->blog->settings->originemini->put($id, trim(html::escapeHTML($_POST[$id])));
                    }
                }
            }

            dcPage::addSuccessNotice(__('Theme configuration has been successfully updated.'));

        // Resets options.
        } elseif (isset($_POST['default']) !== false) {
            foreach($default_settings as $setting_id => $setting_data) {
                if ($setting_data['type'] === 'checkbox') {
                    $setting_type = 'boolean';
                } elseif ($setting_data['type'] === 'select_int') {
                    $setting_type = 'integer';
                } else {
                    $setting_type = 'string';
                }

                dcCore::app()->blog->settings->originemini->put(
                    $setting_id,
                    $setting_data['default'],
                    $setting_type,
                    $setting_data['title'],
                    true
                );
            }

            dcPage::addSuccessNotice(__('Theme configuration has been successfully reset.'));
        }

        dcCore::app()->blog->triggerBlog();

        $css                    = '';
        $css_root_array         = [];
        $css_root_media_array   = [];
        $css_main_array         = [];
        $css_media_array        = [];
        $css_media_motion_array = [];

        // Page width.
        $page_width_allowed = [35, 40];

        if (isset($_POST['global_page_width']) && in_array(intval($_POST['global_page_width']), $page_width_allowed, true) === true) {
            $css_root_array[':root']['--page-width'] = $_POST['global_page_width'] . 'em';
        }

        // Font family.
        if (isset($_POST['global_font_family']) && $_POST['global_font_family'] === 'serif') {
            $css_root_array[':root']['--font-family'] = '"Iowan Old Style", "Apple Garamond", Baskerville, "Times New Roman", "Droid Serif", Times, "Source Serif Pro", serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"';
        } elseif ($_POST['global_font_family'] === 'mono') {
            $css_root_array[':root']['--font-family'] = 'Menlo, Consolas, Monaco, "Liberation Mono", "Lucida Console", monospace';
        }

        // Font size.
        if (isset($_POST['global_font_size']) === true && (int) $_POST['global_font_size'] !== 100) {
            $css_root_array[':root']['--font-size'] = ($_POST['global_font_size'] / 100) . 'em';
        }

        // Primary color.
        $primary_colors_allowed = ['blue', 'gray', 'green', 'red'];

        $primary_colors = [
            'light' => [
                'gray' => [
                    '--color-primary'          => '#1a1a1a',
                    '--color-background'       => '#fcfcfc',
                    '--color-text-main'        => '#333333',
                    '--color-text-secondary'   => '#808080',
                    '--color-border'           => '#cccccc',
                    '--color-input-background' => '#f2f2f2'
                ],

                'green' => [
                    '--color-primary'          => '#138613',
                    '--color-background'       => '#fcfcfc',
                    '--color-text-main'        => '#2e382e',
                    '--color-text-secondary'   => '#676d7e',
                    '--color-border'           => '#cccccc',
                    '--color-input-background' => '#f1f4f1'
                ],

                'red' => [
                    '--color-primary'          => '#e61919',
                    '--color-background'       => '#fdfcfc',
                    '--color-text-main'        => '#382e2e',
                    '--color-text-secondary'   => '#867979',
                    '--color-border'           => '#cccccc',
                    '--color-input-background' => '#f3f2f2'
                ]
            ],
            'dark' => [
                'blue'  => '#94c9ff',
                'gray'  => '#fcfcfc',
                'green' => '#adebad',
                'red'   => '#f7baba',
            ]
        ];

        if (isset($_POST['global_color_secondary']) && $_POST['global_color_secondary'] !== 'blue') {
            if (in_array($_POST['global_color_secondary'], $primary_colors_allowed) === true) {
                foreach ($primary_colors['light'][$_POST['global_color_secondary']] as $key => $value) {
                    $css_root_array[':root'][$key] = $value;
                }

                $css_root_media_array[':root']['--color-primary-dark'] = $primary_colors['dark'][$_POST['global_color_secondary']];
            } else {
                foreach ($primary_colors['light']['blue'] as $key => $value) {
                    $css_root_array[':root'][$key] = $value;
                }

                $css_root_media_array[':root']['--color-primary-dark'] = $primary_colors['dark']['blue'];
            }
        }

        // Transitions.
        if (isset($_POST['global_css_transition']) && $_POST['global_css_transition'] === '1') {
            $css_main_array['a']['transition']                 = 'all .2s ease-in-out';
            $css_main_array['a:active, a:hover']['transition'] = 'all .2s ease-in-out';

            $css_main_array['input[type="submit"], .form-submit, .button']['transition'] = 'all .2s ease-in-out';

            $css_main_array['input[type="submit"]:hover, .button:hover, .form-submit:hover']['transition'] = 'all .2s ease-in-out';

            $css_media_motion_array['a']['transition']                 = 'none';
            $css_media_motion_array['a:active, a:hover']['transition'] = 'none';

            $css_media_motion_array['input[type="submit"], .form-submit, .button']['transition']                   = 'none';
            $css_media_motion_array['input[type="submit"]:hover, .button:hover, .form-submit:hover']['transition'] = 'none';
        }

        // Links underline.
        if (isset($_POST['global_css_links_underline']) && $_POST['global_css_links_underline'] === '1') {
            $css_main_array[':root']['--link-text-decoration'] = 'underline dotted';
        }

        // Border radius.
        if (isset($_POST['global_css_border_radius']) && $_POST['global_css_border_radius'] === '1') {
            $css_main_array['#site-title, .button, .footer-social-links-icon-container, .post-selected, button, code, input, pre, textarea']['border-radius'] = '.168rem';
        }

        // Blog description.
        if (isset($_POST['header_description']) && $_POST['header_description'] === '1') {
            $css_main_array['#site-description']['font-size']     = '1em';
            $css_main_array['#site-description']['margin-bottom'] = '0';
            $css_main_array['#site-description']['flex-basis'] = '100%';

            $css_media_array['#site-title']['order']            = '1';
            $css_media_array['#site-description']['margin-top'] = '.25rem';
            $css_media_array['#site-description']['order']      = '2';
            $css_media_array['#site-header nav']['order']       = '3';
        }

        // Font family of content.
        if (isset($_POST['content_text_font']) && $_POST['content_text_font'] !== 'same' && $_POST['global_font_family'] !== $_POST['content_text_font']) {
            if ($_POST['content_text_font'] === 'serif') {
                $css_main_array['.content-text']['font-family'] = '"Iowan Old Style", "Apple Garamond", Baskerville, "Times New Roman", "Droid Serif", Times, "Source Serif Pro", serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"';
            } elseif ($_POST['content_text_font'] === 'sans-serif') {
                $css_main_array['.content-text']['font-family'] = '-apple-system, BlinkMacSystemFont, "Avenir Next", Avenir, "Segoe UI", "Helvetica Neue", Helvetica, Ubuntu, Roboto, Noto, Arial, sans-serif';
            } else {
                $css_main_array['.content-text']['font-family'] = 'Menlo, Consolas, Monaco, "Liberation Mono", "Lucida Console", monospace';
            }
        }

        // Text align.
        if (isset($_POST['content_text_align']) && ($_POST['content_text_align'] === 'justify' || $_POST['content_text_align'] === 'justify_not_mobile')) {
            $css_root_array[':root']['--text-align'] = 'justify';

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
            $css_main_array['#post-single-excerpt']['border-block'] = '.063rem solid var(--color-border, #c2c7d6)';
            $css_main_array['#post-single-excerpt']['font-weight']  = '700';
            $css_main_array['#post-single-excerpt']['margin']       = '2rem 0';
            $css_main_array['#post-single-excerpt']['padding']      = '.5rem 0';

            $css_main_array['#post-single-excerpt p']['margin']     = '.5rem 0';

            $css_main_array['#post-single-excerpt strong']['font-weight'] = '900';
        }

        $css .= !empty($css_root_array) ? origineMiniArrayToCSS($css_root_array) : '';
        $css .= !empty($css_root_media_array) ? origineMiniArrayToCSS($css_root_media_array) : '';
        $css .= !empty($css_main_array) ? origineMiniArrayToCSS($css_main_array) : '';
        $css .= !empty($css_media_array) ? '@media (max-width: 34em){' . origineMiniArrayToCSS($css_media_array) . '}' : '';
        $css .= !empty($css_media_motion_array) ? '@media (prefers-reduced-motion:reduce){' . origineMiniArrayToCSS($css_media_motion_array) . '}' : '';

        dcCore::app()->blog->settings->originemini->put('styles', htmlspecialchars($css, ENT_NOQUOTES));

        // Clears template cache.
        if (dcCore::app()->blog->settings->system->tpl_use_cache === true) {
            dcCore::app()->emptyTemplatesCache();
        }

        http::redirect(html::escapeURL(dcCore::app()->adminurl->get('admin.blog.theme') . '?conf=1'));
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
}
?>


<?php
/**
 * Creates an array which will contain all the settings and there title following the pattern below.
 *
 * $setting_page_content = [
 *     'section_id_1' => [
 *         'sub_section_id_1' => ['option_id_1', 'option_id_2'],
 *         'sub_section_id_2' => ['option_id_3', 'option_id_4'],
 *         […]
 *     ],
 * ];
 */
$setting_page_content = [];

// Gets all setting sections.
$sections         = origineMiniSettingsSections();
$default_settings = origineMiniSettingsDefault();

// Puts titles in the settings array.
foreach($sections as $section_id => $section_data) {
    $setting_page_content[$section_id] = [];
}

$settings_to_ignore = ['css_origine', 'css_origine_mini'];

// Puts all settings in their sections.
foreach($default_settings as $setting_id => $setting_data) {
    if ($setting_id !== 'css') {
        if (isset($setting_data['section']) === true && is_array($setting_data['section']) === true) {
            if (isset($setting_data['section'][1]) === true) {
                $setting_page_content[$setting_data['section'][0]][$setting_data['section'][1]][] = $setting_id;
            } else {
                $setting_page_content[$setting_data['section'][0]][] = $setting_id;
            }
        } elseif (isset($setting_data['section']) === true && is_string($setting_data['section']) === true) {
            $setting_page_content[$setting_data['section'][0]][] = $setting_id;
        }
    }
}

// Removes titles when there are associated with any setting.
$setting_page_content = array_filter($setting_page_content);

// Displays the title of each sections and put the settings inside.
foreach ($setting_page_content as $title_id => $section_content) {
    echo '<h3>', $sections[$title_id]['name'], '</h3>';

    foreach ($section_content as $sub_section_id => $setting_id) {
        echo '<div class=fieldset>';

        // Shows the sub section name, except if its ID is "no-title".
        if (is_string($sub_section_id) && $sub_section_id !== 'no-title') {
            echo '<h4>', $sections[$title_id]['sub_sections'][$sub_section_id], '</h4>';
        }

        // Displays the option.
        if (is_string($setting_id)) {
            echo origineMiniSettingDisplay($setting_id, $default_settings, $settings);
        } else {
            foreach ($setting_id as $setting_id_value) {
                echo origineMiniSettingDisplay($setting_id_value, $default_settings, $settings);
            }
        }

        echo '</div>';
    }
}
?>

<p>
    <input class=delete name=default value="<?php echo __('admin-reset-button-text'); ?>" type=submit>
</p>
