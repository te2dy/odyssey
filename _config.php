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

l10n::set(__DIR__ . '/locales/' . dcCore::app()->lang . '/admin');

class adminConfigOrigineMini
{
    /**
     * Defines the sections of the page.
     */
    public static function page_sections()
    {
        $page_sections['global'] = [
            'name'         => __('section-global'),
            'sub_sections' => [
                'fonts'  => __('section-global-fonts'),
                'layout' => __('section-global-layout'),
            ]
        ];

        return $page_sections;
    }

    /**
     * Defines all the settings.
     */
    public static function default_settings()
    {
        $default_settings['global_font_size'] = [
            'title'       => __('settings-option-global-fontsize-title'),
            'description' => __('settings-option-global-fontsize-description'),
            'type'        => 'select_int',
            'choices'     => [
                __('settings-option-global-fontsize-80')          => 80,
                __('settings-option-global-fontsize-100-default') => 100,
                __('settings-option-global-fontsize-120')         => 120
            ],
            'default'     => 100,
            'section'     => ['global', 'fonts']
        ];

        $default_settings['global_font_family'] = [
            'title'       => __('settings-option-global-fontfamily-title'),
            'description' => __('settings-option-global-fontfamily-description'),
            'type'        => 'select',
            'choices'     => [
                __('settings-option-global-fontfamily-sansserif-default') => 'sans-serif',
                __('settings-option-global-fontfamily-serif')             => 'serif',
                __('settings-option-global-fontfamily-mono')              => 'monospace'
            ],
            'default'     => 'sans-serif',
            'section'     => ['global', 'fonts']
        ];

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

        return $default_settings;
    }

    /**
     *
     */
    public static function saved_settings()
    {
        $saved_settings   = [];
        $default_settings = self::default_settings();

        foreach ($default_settings as $setting_id => $setting_data) {
            if (dcCore::app()->blog->settings->originemini->settingExists($setting_id) === true) {
                switch ($setting_data['type']) {
                    case 'checkbox' :
                        $saved_settings[$setting_id] = (boolean) dcCore::app()->blog->settings->originemini->$setting_id;
                        break;

                    case 'select_int' :
                        $saved_settings[$setting_id] = (integer) dcCore::app()->blog->settings->originemini->$setting_id;
                        break;

                    default :
                        $saved_settings[$setting_id] = dcCore::app()->blog->settings->originemini->$setting_id;
                        break;
                }
            }
        }

        return $saved_settings;
    }

    /**
     * Converts an array of styles to a string without spaces and line breaks.
     *
     * @param array $rules An array of CSS rules.
     *
     * @return string $css All the CSS in a single line.
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

    public static function setting_rendering($setting_id = '')
    {
        $default_settings = self::default_settings();
        $saved_settings   = self::saved_settings();

        if ($setting_id !== '' && array_key_exists($setting_id, $default_settings)) {
            echo '<p>';

            // If the value of the setting is not set, defines the default value.
            if (isset($saved_settings[$setting_id])) { // Here, the value of the setting.
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
                        '<label class=classic for=' . $setting_id . '>',
                        $default_settings[$setting_id]['title'],
                        '</label>';

                    break;

                case 'select' :
                case 'select_int' :
                    echo '<label for=' . $setting_id . '>',
                        $default_settings[$setting_id]['title'],
                        '</label>',
                        form::combo(
                            $setting_id,
                            $default_settings[$setting_id]['choices'],
                            strval($setting_value)
                        );

                    break;

                default :
                    echo '<label for=' . $setting_id . '>',
                        $default_settings[$setting_id]['title'],
                        '</label>',
                        form::field(
                            $setting_id,
                            30,
                            255,
                            $setting_value
                        );

                    break;
            }

            echo '</p>';

            // If the setting has a description, displays it as a note.
            if ($default_settings[$setting_id]['type'] === 'checkbox' || (isset($default_settings[$setting_id]['description']) && $default_settings[$setting_id]['description'] !== '')) {
                echo '<p class=form-note>', $default_settings[$setting_id]['description'];

                // Displays the default value if the option is a checkbox.
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

    public static function save_settings()
    {
        if (!empty($_POST)) {
            $default_settings = self::default_settings();
            $saved_settings   = self::saved_settings();

            try {
                dcCore::app()->blog->settings->addNamespace('originemini');

                if (isset($_POST['save'])) {
                    foreach ($default_settings as $setting_id => $setting_value) {
                        if (isset($_POST[$setting_id])) {
                            $drop          = false;
                            $setting_value = '';
                            $setting_type  = isset($default_settings[$setting_id]['type']) ? $default_settings[$setting_id]['type'] : '';
                            $setting_title = isset($default_settings[$setting_id]['title']) ? $default_settings[$setting_id]['title'] : '';

                            if ($_POST[$setting_id] && $_POST[$setting_id] != $default_settings[$setting_id]['default']) {
                                if ($setting_type === 'select') {
                                    // Checks if the input value is proposed by the setting.
                                    if (in_array($_POST[$setting_id], $default_settings[$setting_id]['choices'])) {
                                        $setting_value = $_POST[$setting_id];
                                    }

                                    $setting_type = 'string';
                                } elseif ($setting_type === 'select_int') {
                                    // Checks if the input value is proposed by the setting.
                                    if (in_array($_POST[$setting_id], $default_settings[$setting_id]['choices'])) {
                                        $setting_value = (int) $_POST[$setting_id];
                                    }

                                    $setting_type = 'integer';
                                }

                            // If the value is equal to the default value, drop the setting.
                            } elseif ($_POST[$setting_id] == $default_settings[$setting_id]['default']) {
                                $drop = true;
                            }

                            if ($drop === false) {
                                dcCore::app()->blog->settings->originemini->put(
                                    $setting_id,
                                    $setting_value,
                                    $setting_type,
                                    $setting_title,
                                    true // ????
                                );
                            } else {
                                dcCore::app()->blog->settings->originemini->drop($setting_id);
                            }
                        }
                    }

                    dcPage::addSuccessNotice(__('config-updated'));
                } if (isset($_POST['reset'])) {
                    foreach ($default_settings as $setting_id => $setting_value) {
                        dcCore::app()->blog->settings->originemini->drop($setting_id);
                    }

                    dcPage::addSuccessNotice(__('config-reset'));
                }

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
                http::redirect(dcCore::app()->adminurl->get('admin.blog.theme', ['conf' => '1']) . '&module=origine-mini');
            } catch (Exception $e) {
                dcCore::app()->error->add($e->getMessage());
            }
        }
    }

    public static function page_rendering()
    {
        /**
         * Creates an array which will contain all the settings and their title following the pattern below.
         *
         * $sections_with_settings_id = [
         *     'section_id_1' => [
         *         'sub_section_id_1' => ['option_id_1', 'option_id_2'],
         *         'sub_section_id_2' => ['option_id_3', 'option_id_4'],
         *         […]
         *     ],
         * ];
         */
        $sections_with_settings_id = [];

        // Gets all setting sections.
        $sections = self::page_sections();
        $settings = self::default_settings();

        // Puts titles in the settings array.
        foreach($sections as $section_id => $section_data) {
            $sections_with_settings_id[$section_id] = [];
        }

        // Puts all settings in their sections.
        foreach($settings as $setting_id => $setting_data) {
            if ($setting_id !== 'styles') {
                // If a sub section has been set.
                if (isset($setting_data['section'][1])) {
                    $sections_with_settings_id[$setting_data['section'][0]][$setting_data['section'][1]][] = $setting_id;
                } elseif (isset($setting_data['section'][0])) {
                    $sections_with_settings_id[$setting_data['section'][0]][] = $setting_id;
                }
            }
        }

        // Removes titles when there are associated with any setting.
        // $sections_with_settings_id = array_filter($sections_with_settings_id);
        ?>

        <form id=module_config action="<?php echo dcCore::app()->adminurl->get('admin.blog.theme', ['module' => 'origine-mini', 'conf' => '1']); ?>" method=post enctype=multipart/form-data>
            <?php
            // Displays the title of each sections and put the settings inside.
            foreach ($sections_with_settings_id as $section_id => $section_data) {
                echo '<h3>', $sections[$section_id]['name'], '</h3>',
                     '<div class=fieldset>';

                foreach ($section_data as $sub_section_id => $setting_id) {
                    // Shows the sub section name except if its ID is "no-title".
                    if ($sub_section_id !== 'no-title') {
                        echo '<h4>', $sections[$section_id]['sub_sections'][$sub_section_id], '</h4>';
                    }

                    // Displays the option.
                    foreach ($setting_id as $setting_id_value) {
                        self::setting_rendering($setting_id_value);
                    }
                }

                echo '</div>';
            }
            ?>

            <p>
                <?php echo dcCore::app()->formNonce(); ?>
                <input name=save type=submit value="<?php echo __('admin-save-button-text'); ?>">
                <input class=delete name=reset value="<?php echo __('admin-reset-button-text'); ?>" type=submit>
            </p>
        </form>

        <?php
    }
}

adminConfigOrigineMini::save_settings();
var_dump(adminConfigOrigineMini::saved_settings());
adminConfigOrigineMini::page_rendering();
