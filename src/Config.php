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
use Dotclear\Core\Process;
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Helper\Network\Http;
use Dotclear\Helper\Html\Html;
// use Dotclear\Helper\Html\Form;
// use Dotclear\Helper\Html\Form\Select;

use form;

class Config extends Process
{
    public static function init(): bool
    {
        if (!self::status(My::checkContext(My::CONFIG))) {
            return false;
        }

        My::l10n('admin');

        return self::status();
    }

    /**
     * Processes the request(s).
     */
    public static function process(): bool
    {
        // Loads custon styles for the configurator page.
        App::behavior()->addBehavior(
            'adminPageHTMLHead',
            function () {
                echo My::cssLoad('/css/admin.min.css');
                echo My::jsLoad('/js/admin.min.js');
            }
        );

        if (!empty($_POST)) {
            try {

                // If the save button has been clicked.
                if (isset($_POST['save'])) {

                    /**
                     * This part saves each option in the database
                     * only if it is different than the default one.
                     *
                     * Custom styles to be inserted in the head of the blog
                     * will be saved later.
                     */
                    foreach (My::settingsDefault() as $setting_id => $setting_data) {
                        $specific_settings = [
                            'global_unit',
                            'global_page_width_value',
                            'styles'
                        ];

                        $setting_data = [];

                        // Saves non specific settings.
                        if (!in_array($setting_id, $specific_settings, true)) {
                            if (isset($_POST[$setting_id]) && $_POST[$setting_id] != My::settingsDefault($setting_id)['default']) {
                                $setting_data = self::sanitizeSetting(
                                    My::settingsDefault($setting_id)['type'],
                                    $setting_id,
                                    $_POST[$setting_id]
                                );
                            } elseif (!isset($_POST[$setting_id]) && My::settingsDefault($setting_id)['type'] === 'checkbox') {
                                $setting_data = self::sanitizeSetting(
                                    My::settingsDefault($setting_id)['type'],
                                    $setting_id,
                                    '0'
                                );
                            } else {
                                App::blog()->settings->odyssey->drop($setting_id);
                            }
                        } else {
                            // Saves each specific settings.
                            switch ($setting_id) {
                                case 'global_unit' :
                                case 'global_page_width_value' :
                                    $setting_data = self::sanitizePageWidth(
                                        $setting_id,
                                        $_POST['global_unit'],
                                        $_POST['global_page_width_value']
                                    );
                                    break;

                                case 'styles' :
                                    $setting_data = self::saveStyles();
                            }
                        }

                        // Saves the setting or drop it.
                        if (!empty($setting_data)) {
                            $setting_value = isset($setting_data['value']) ? $setting_data['value'] : '';
                            $setting_type  = isset($setting_data['type']) ? $setting_data['type'] : '';
                            $setting_label = My::settingsDefault($setting_id)['title'];
                            $setting_label = Html::clean($setting_label);

                            if ($setting_value !== '' && $setting_type !== '') {
                                App::blog()->settings->odyssey->put(
                                    $setting_id,
                                    $setting_value,
                                    $setting_type,
                                    $setting_label ?: '',
                                    true
                                );
                            } else {
                                App::blog()->settings->odyssey->drop($setting_id);
                            }
                        } else {
                            App::blog()->settings->odyssey->drop($setting_id);
                        }
                    }

                    // Refreshes blog.
                    App::blog()->triggerBlog();

                    // Resets template cache.
                    App::cache()->emptyTemplatesCache();

                    // Displays a success notice.
                    Notices::addSuccessNotice(__('settings-notice-saved'));

                    // Redirects to refresh form values.
                    App::backend()->url()->redirect('admin.blog.theme', ['conf' => '1']);
                } elseif (isset($_POST['reset'])) {

                    /**
                     * Reset button has been clicked.
                     * Drops all settings.
                     */
                    foreach (My::settingsDefault() as $setting_id => $setting_value) {
                        App::blog()->settings->odyssey->drop($setting_id);
                    }

                    // Displays a success notice.
                    Notices::addSuccessNotice(__('settings-notice-reset'));

                    // Redirects to refresh form values.
                    App::backend()->url()->redirect('admin.blog.theme', ['conf' => '1']);
                }
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        return true;
    }

    public static function settingRender($setting_id)
    {
        $default_settings = My::settingsDefault();
        $saved_settings   = self::settingsSaved();

        // Displays the default value of the parameter if it is not defined.
        if (isset($saved_settings[$setting_id])) {
            $setting_value = $saved_settings[$setting_id];
        } else {
            $setting_value = $default_settings[$setting_id]['default'];
        }

        echo '<p id=', $setting_id, '-input>';

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
                    $setting_value
                );
                break;

            default :
                $placeholder = isset($default_settings[$setting_id]['placeholder'])
                ? 'placeholder=' . My::attrValue($default_settings[$setting_id]['placeholder'])
                : '';

                echo '<label for=', $setting_id, '>',
                $default_settings[$setting_id]['title'],
                '</label>',
                Form::field(
                    $setting_id,
                    30,
                    255,
                    $setting_value,
                    '',
                    '',
                    false,
                    $placeholder
                );
        }

        echo '</p>';

        // Displays the description of the parameter as a note.
        if ($default_settings[$setting_id]['type'] === 'checkbox' || (isset($default_settings[$setting_id]['description']) && $default_settings[$setting_id]['description'] !== '')) {
            echo '<p class=form-note id=', $setting_id, '-description>',
            $default_settings[$setting_id]['description'];

            // If the parameter is a checkbox, displays its default value as a note.
            if ($default_settings[$setting_id]['type'] === 'checkbox') {
                if ($default_settings[$setting_id]['default'] === '1') {
                    echo ' ', __('settings-default-checked');
                } else {
                    echo ' ', __('settings-default-unchecked');
                }
            }

            echo '</p>';
        }
    }

    /**
     * Retrieves all theme settings stored in the database.
     *
     * @return array The id of the saved parameters associated with their values.
     */
    public static function settingsSaved(): array
    {
        $saved_settings   = [];
        $default_settings = My::settingsDefault();

        foreach ($default_settings as $setting_id => $setting_data) {
            if (App::blog()->settings->odyssey->$setting_id !== null) {
                if (isset($setting_data['type']) && $setting_data['type'] === 'checkbox') {
                    $saved_settings[$setting_id] = (bool) App::blog()->settings->odyssey->$setting_id;
                } elseif (isset($setting_data['type']) && $setting_data['type'] === 'select_int') {
                    $saved_settings[$setting_id] = (int) App::blog()->settings->odyssey->$setting_id;
                } else {
                    $saved_settings[$setting_id] = App::blog()->settings->odyssey->$setting_id;
                }
            }
        }

        return $saved_settings;
    }

    /**
     * Renders the page.
     */
    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        // Creates an array to put all the settings in their sections.
        $settings_render = [];

        // Adds sections.
        foreach (My::settingsSections() as $section_id => $section_data) {
            $settings_render[$section_id] = [];
        }

        // Adds settings in their section.
        foreach (My::settingsDefault() as $setting_id => $setting_data) {
            if ($setting_id !== 'styles') {
                // If a sub-section is set.
                if (isset($setting_data['section'][1])) {
                    $settings_render[$setting_data['section'][0]][$setting_data['section'][1]][] = $setting_id;
                } else {
                    $settings_render[$setting_data['section'][0]][] = $setting_id;
                }
            }
        }

        echo '<p>' . __('settings-intro') . '</p>';

        echo '<form action="', App::backend()->url()->get('admin.blog.theme', ['conf' => '1']), '" enctype=multipart/form-data id=theme-config-form method=post>';

        // Displays the setting.
        foreach ($settings_render as $section_id => $setting_data) {
            echo '<h3 id=section-', $section_id, '>',
            My::settingsSections($section_id)['name'],
            '</h3>',
            '<div class=fieldset>';

            foreach ($setting_data as $sub_section_id => $setting_id) {
                // Displays the name of the sub-section unless its ID is "no-title".
                if ($sub_section_id !== 'no-title') {
                    echo '<h4 id=section-', $section_id, '-', $sub_section_id, '>',
                    My::settingsSections($section_id)['sub_sections'][$sub_section_id],
                    '</h4>';
                }

                // Displays the parameter.
                foreach ($setting_id as $setting_id_value) {
                    self::settingRender($setting_id_value);
                }
            }

            echo '</div>';
        }

        // Hidden inputs.
        echo form::hidden('page_width_em_default', '30');
        echo form::hidden('page_width_px_default', '480');

        echo '<p>',
        App::nonce()->getFormNonce(),
        '<input name=save type=submit value="', __('settings-save-button-text'), '"> ',
        '<input class=delete name=reset value="', __('settings-reset-button-text'), '" type=submit>',
        '</p>',
        '</form>';
    }

    /**
     * Adds custom styles to the theme to apply the settings.
     *
     * @param int $header_image_width The width if the header image.
     *
     * @return void
     */
    public static function saveStyles(): array
    {
        $css_root_array                    = [];
        $css_root_dark_array               = [];
        $css_main_array                    = [];
        $css_supports_initial_letter_array = [];
        $css_media_array                   = [];

        $default_settings = My::settingsDefault();

        // Page width.
        if (isset($_POST['global_unit']) && isset($_POST['global_page_width_value'])) {
            $page_width_unit  = $_POST['global_unit'];
            $page_width_value = $_POST['global_page_width_value'];

            if ($page_width_unit === 'px' && $page_width_value === '') {
                $page_width_value = '480';
            }

            $page_width_data = My::getContentWidth($page_width_unit, $page_width_value);

            if (!empty($page_width_data)) {
                $css_root_array[':root']['--page-width'] = $page_width_data['value'] . $page_width_data['unit'];
            }
        }

        // Font family.
        if (isset($_POST['global_font_family'])) {
            if ($_POST['global_font_family'] === 'serif') {
                $css_root_array[':root']['--font-family'] = '"Iowan Old Style", "Apple Garamond", Baskerville, "Times New Roman", "Droid Serif", Times, "Source Serif Pro", serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"';
            } elseif ($_POST['global_font_family'] === 'monospace') {
                $css_root_array[':root']['--font-family'] = 'Menlo, Consolas, Monaco, "Liberation Mono", "Lucida Console", monospace';
            } elseif ($_POST['global_font_family'] === 'sans-serif-browser') {
                $css_root_array[':root']['--font-family'] = 'sans-serif';
            } elseif ($_POST['global_font_family'] === 'serif-browser') {
                $css_root_array[':root']['--font-family'] = 'serif';
            } elseif ($_POST['global_font_family'] === 'monospace-browser') {
                $css_root_array[':root']['--font-family'] = 'monospace';
            } elseif ($_POST['global_font_family'] === 'atkinson') {
                $css_root_array[':root']['--font-family'] = '"Atkinson Hyperlegible", sans-serif';

                $css_main_array[0]['@font-face']['font-family'] = '"Atkinson Hyperlegible"';
                $css_main_array[0]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/Atkinson-Hyperlegible-Regular-102a.woff2") format("woff2")';
                $css_main_array[0]['@font-face']['font-style']  = 'normal';
                $css_main_array[0]['@font-face']['font-weight'] = '400';

                $css_main_array[1]['@font-face']['font-family'] = '"Atkinson Hyperlegible"';
                $css_main_array[1]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/Atkinson-Hyperlegible-Italic-102a.woff2") format("woff2")';
                $css_main_array[1]['@font-face']['font-style']  = 'italic';
                $css_main_array[1]['@font-face']['font-weight'] = '400';

                $css_main_array[2]['@font-face']['font-family'] = '"Atkinson Hyperlegible"';
                $css_main_array[2]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/Atkinson-Hyperlegible-Bold-102a.woff2") format("woff2")';
                $css_main_array[2]['@font-face']['font-style']  = 'normal';
                $css_main_array[2]['@font-face']['font-weight'] = '700';

                $css_main_array[3]['@font-face']['font-family'] = '"Atkinson Hyperlegible"';
                $css_main_array[3]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/Atkinson-Hyperlegible-BoldItalic-102a.woff2") format("woff2")';
                $css_main_array[3]['@font-face']['font-style']  = 'italic';
                $css_main_array[3]['@font-face']['font-weight'] = '700';
            } elseif ($_POST['global_font_family'] === 'eb-garamond') {
                $css_root_array[':root']['--font-family'] = '"EB Garamond", serif';

                $css_main_array[0]['@font-face']['font-family'] = '"EB Garamond"';
                $css_main_array[0]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/EBGaramond-Regular.ttf") format("truetype")';
                $css_main_array[0]['@font-face']['font-style']  = 'normal';
                $css_main_array[0]['@font-face']['font-weight'] = '400';

                $css_main_array[1]['@font-face']['font-family'] = '"EB Garamond"';
                $css_main_array[1]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/EBGaramond-Italic.ttf") format("truetype")';
                $css_main_array[1]['@font-face']['font-style']  = 'italic';
                $css_main_array[1]['@font-face']['font-weight'] = '400';

                $css_main_array[2]['@font-face']['font-family'] = '"EB Garamond"';
                $css_main_array[2]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/EBGaramond-Bold.ttf") format("truetype")';
                $css_main_array[2]['@font-face']['font-style']  = 'normal';
                $css_main_array[2]['@font-face']['font-weight'] = '700';

                $css_main_array[3]['@font-face']['font-family'] = '"EB Garamond"';
                $css_main_array[3]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/EBGaramond-BoldItalic.ttf") format("truetype")';
                $css_main_array[3]['@font-face']['font-style']  = 'italic';
                $css_main_array[3]['@font-face']['font-weight'] = '700';
            } elseif ($_POST['global_font_family'] === 'luciole') {
                $css_root_array[':root']['--font-family'] = 'Luciole, sans-serif';

                $css_main_array[0]['@font-face']['font-family'] = '"Luciole"';
                $css_main_array[0]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/Luciole-Regular.ttf") format("truetype")';
                $css_main_array[0]['@font-face']['font-style']  = 'normal';
                $css_main_array[0]['@font-face']['font-weight'] = '400';

                $css_main_array[1]['@font-face']['font-family'] = '"Luciole"';
                $css_main_array[1]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/Luciole-Regular-Italic.ttf") format("truetype")';
                $css_main_array[1]['@font-face']['font-style']  = 'italic';
                $css_main_array[1]['@font-face']['font-weight'] = '400';

                $css_main_array[2]['@font-face']['font-family'] = '"Luciole"';
                $css_main_array[2]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/Luciole-Bold.ttf") format("truetype")';
                $css_main_array[2]['@font-face']['font-style']  = 'normal';
                $css_main_array[2]['@font-face']['font-weight'] = '700';

                $css_main_array[3]['@font-face']['font-family'] = '"Luciole"';
                $css_main_array[3]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/Luciole-Bold-Italic.ttf") format("truetype")';
                $css_main_array[3]['@font-face']['font-style']  = 'italic';
                $css_main_array[3]['@font-face']['font-weight'] = '700';
            }
        }

        // Font size.
        $font_size_allowed = [80, 90, 110, 120];

        if (isset($_POST['global_font_size']) && in_array((int) $_POST['global_font_size'], $font_size_allowed, true)) {
            $css_root_array[':root']['--font-size'] = My::removeZero($_POST['global_font_size'] / 100) . 'em';
        }

        // Font antialiasing.
        if (isset($_POST['global_font_antialiasing']) && $_POST['global_font_antialiasing'] === '1') {
            $css_main_array['body']['-moz-osx-font-smoothing'] = 'grayscale';
            $css_main_array['body']['-webkit-font-smoothing']  = 'antialiased';
            $css_main_array['body']['font-smooth']             = 'always';

            /*
            $css_media_contrast_array['body']['-moz-osx-font-smoothing'] = 'unset';
            $css_media_contrast_array['body']['-webkit-font-smoothing']  = 'unset';
            $css_media_contrast_array['body']['font-smooth']             = 'unset';

            $css_media_print_array['body']['-moz-osx-font-smoothing'] = 'unset';
            $css_media_print_array['body']['-webkit-font-smoothing']  = 'unset';
            $css_media_print_array['body']['font-smooth']             = 'unset';
            */
        }

        // Primary color.
        $primary_colors_allowed = ['gray', 'green', 'red'];

        $primary_colors = [
            'light' => [
                'gray'  => '0, 0%, 10%',
                'green' => '120, 75%, 30%',
                'red'   => '0, 90%, 45%'
            ],
            'light-amplified' => [
                'gray'  => '0, 0%, 28%',
                'green' => '120, 60%, 40%',
                'red'   => '0, 100%, 55%'
            ],
            'dark' => [
                'gray'  => '0, 0%, 99%',
                'green' => '120, 60%, 80%',
                'red'   => '0, 70%, 85%'
            ],
            'dark-amplified' => [
                'gray'  => '0, 0%, 80%',
                'green' => '120, 50%, 60%',
                'red'   => '0, 70, 70%'
            ]
        ];

        // Primary color.
        if (isset($_POST['global_color_primary']) && in_array($_POST['global_color_primary'], $primary_colors_allowed, true)) {

            // Light.
            $css_root_array[':root']['--color-primary'] = 'hsl(' . $primary_colors['light'][$_POST['global_color_primary']] . ')';

            // Light & amplified.
            if (isset($primary_colors['light-amplified'][$_POST['global_color_primary']])) {
                $css_root_array[':root']['--color-primary-amplified'] = 'hsl(' . $primary_colors['light-amplified'][$_POST['global_color_primary']] . ')';
            }

            // Dark.
            if (isset($primary_colors['dark'][$_POST['global_color_primary']])) {
                $css_root_array[':root']['--color-primary-dark'] = 'hsl(' . $primary_colors['dark'][$_POST['global_color_primary']] . ')';
            }

            // Dark & amplified.
            if (isset($primary_colors['dark-amplified'][$_POST['global_color_primary']])) {
                $css_root_array[':root']['--color-primary-dark-amplified'] = 'hsl(' . $primary_colors['dark-amplified'][$_POST['global_color_primary']] . ')';
            }
        }

        // Transitions.
        if (isset($_POST['global_css_transition']) && $_POST['global_css_transition'] === '1') {
            $css_root_array[':root']['--color-transition'] = 'all .2s ease-in-out';

            /*
            $css_media_motion_array['a']['transition'] = 'unset';

            $css_media_motion_array['a:active, a:hover']['transition'] = 'unset';

            $css_media_motion_array['input[type="submit"], .form-submit, .button']['transition'] = 'unset';

            $css_media_motion_array['input[type="submit"]:hover, .button:hover, .form-submit:hover']['transition'] = 'unset';
            */
        }

        // Header alignment
        $header_align_allowed = ['left', 'right'];

        if (isset($_POST['header_align']) && in_array($_POST['header_align'], $header_align_allowed, true)) {
            $css_root_array[':root']['--header-align'] = $_POST['header_align'];
        }

        // Alternate post color
        if (isset($_POST['content_postlist_altcolor']) && $_POST['content_postlist_altcolor'] === '1') {
            $css_root_dark_array[':root']['--color-background-even'] = '#000';

            $css_main_array['.entry-list .post:nth-child(even)']['background-color'] = 'var(--color-background-even, #fff)';
        }

        // Content font family
        if (isset($_POST['content_text_font']) && $_POST['content_text_font'] !== 'same' && $_POST['global_font_family'] !== $_POST['content_text_font']) {
            switch ($_POST['content_text_font']) {
                case 'sans-serif' :
                    $css_root_array[':root']['--font-family-content'] = '-apple-system, BlinkMacSystemFont, "Avenir Next", Avenir, "Segoe UI", "Helvetica Neue", Helvetica, Ubuntu, Roboto, Noto, Arial, sans-serif';
                    break;
                case 'serif' :
                    $css_root_array[':root']['--font-family-content'] = '"Iowan Old Style", "Apple Garamond", Baskerville, "Times New Roman", "Droid Serif", Times, "Source Serif Pro", serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"';
                    break;
                case 'monospace' :
                    $css_root_array[':root']['--font-family-content'] = 'Menlo, Consolas, Monaco, "Liberation Mono", "Lucida Console", monospace';
                    break;
                case 'sans-serif-browser' :
                    $css_root_array[':root']['--font-family-content'] = 'sans-serif';
                    break;
                case 'serif-browser' :
                    $css_root_array[':root']['--font-family-content'] = 'serif';
                    break;
                case 'monospace-browser' :
                    $css_root_array[':root']['--font-family-content'] = 'monospace';
                    break;
                case 'atkinson' :
                    $css_root_array[':root']['--font-family-content'] = '"Atkinson Hyperlegible", sans-serif';

                    $css_main_array[4]['@font-face']['font-family'] = '"Atkinson Hyperlegible"';
                    $css_main_array[4]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/Atkinson-Hyperlegible-Regular-102a.woff2") format("woff2")';
                    $css_main_array[4]['@font-face']['font-style']  = 'normal';
                    $css_main_array[4]['@font-face']['font-weight'] = '400';

                    $css_main_array[5]['@font-face']['font-family'] = '"Atkinson Hyperlegible"';
                    $css_main_array[5]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/Atkinson-Hyperlegible-Italic-102a.woff2") format("woff2")';
                    $css_main_array[5]['@font-face']['font-style']  = 'italic';
                    $css_main_array[5]['@font-face']['font-weight'] = '400';

                    $css_main_array[6]['@font-face']['font-family'] = '"Atkinson Hyperlegible"';
                    $css_main_array[6]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/Atkinson-Hyperlegible-Bold-102a.woff2") format("woff2")';
                    $css_main_array[6]['@font-face']['font-style']  = 'normal';
                    $css_main_array[6]['@font-face']['font-weight'] = '700';

                    $css_main_array[7]['@font-face']['font-family'] = '"Atkinson Hyperlegible"';
                    $css_main_array[7]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/Atkinson-Hyperlegible-BoldItalic-102a.woff2") format("woff2")';
                    $css_main_array[7]['@font-face']['font-style']  = 'italic';
                    $css_main_array[7]['@font-face']['font-weight'] = '700';
                    break;
                case 'eb-garamond' :
                    $css_root_array[':root']['--font-family-content'] = '"EB Garamond", serif';

                    $css_main_array[4]['@font-face']['font-family'] = '"EB Garamond"';
                    $css_main_array[4]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/EBGaramond-Regular.ttf") format("truetype")';
                    $css_main_array[4]['@font-face']['font-style']  = 'normal';
                    $css_main_array[4]['@font-face']['font-weight'] = '400';

                    $css_main_array[5]['@font-face']['font-family'] = '"EB Garamond"';
                    $css_main_array[5]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/EBGaramond-Italic.ttf") format("truetype")';
                    $css_main_array[5]['@font-face']['font-style']  = 'italic';
                    $css_main_array[5]['@font-face']['font-weight'] = '400';

                    $css_main_array[6]['@font-face']['font-family'] = '"EB Garamond"';
                    $css_main_array[6]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/EBGaramond-Bold.ttf") format("truetype")';
                    $css_main_array[6]['@font-face']['font-style']  = 'normal';
                    $css_main_array[6]['@font-face']['font-weight'] = '700';

                    $css_main_array[7]['@font-face']['font-family'] = '"EB Garamond"';
                    $css_main_array[7]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/EBGaramond-BoldItalic.ttf") format("truetype")';
                    $css_main_array[7]['@font-face']['font-style']  = 'italic';
                    $css_main_array[7]['@font-face']['font-weight'] = '700';
                    break;
                case 'luciole' :
                    $css_root_array[':root']['--font-family-content'] = 'Luciole, sans-serif';

                    $css_main_array[4]['@font-face']['font-family'] = '"Luciole"';
                    $css_main_array[4]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/Luciole-Regular.ttf") format("truetype")';
                    $css_main_array[4]['@font-face']['font-style']  = 'normal';
                    $css_main_array[4]['@font-face']['font-weight'] = '400';

                    $css_main_array[5]['@font-face']['font-family'] = '"Luciole"';
                    $css_main_array[5]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/Luciole-Regular-Italic.ttf") format("truetype")';
                    $css_main_array[5]['@font-face']['font-style']  = 'italic';
                    $css_main_array[5]['@font-face']['font-weight'] = '400';

                    $css_main_array[6]['@font-face']['font-family'] = '"Luciole"';
                    $css_main_array[6]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/Luciole-Bold.ttf") format("truetype")';
                    $css_main_array[6]['@font-face']['font-style']  = 'normal';
                    $css_main_array[6]['@font-face']['font-weight'] = '700';

                    $css_main_array[7]['@font-face']['font-family'] = '"Luciole"';
                    $css_main_array[7]['@font-face']['src']         = 'url("' . My::themeURL() . '/fonts/Luciole-Bold-Italic.ttf") format("truetype")';
                    $css_main_array[7]['@font-face']['font-style']  = 'italic';
                    $css_main_array[7]['@font-face']['font-weight'] = '700';
            }
        }

        // Text align
        if (isset($_POST['content_text_align'])) {
            switch ($_POST['content_text_align']) {
                case 'justify' :
                    $css_root_array[':root']['--text-align'] = 'justify';
                    break;
                case 'justify-not-mobile' :
                    $css_root_array[':root']['--text-align']  = 'justify';
                    $css_media_array[':root']['--text-align'] = 'left';
            }
        }

        // Line Height
        $line_height_allowed = [125, 175];

        if (isset($_POST['content_line_height']) && in_array((int) $_POST['content_line_height'], $line_height_allowed, true)) {
            $css_root_array[':root']['--text-line-height'] = (int) $_POST['content_line_height'] / 100;
        }

        // Hyphenation.
        if (isset($_POST['content_hyphens']) && $_POST['content_hyphens'] !== 'disabled') {
            $css_main_array['.content-text']['-webkit-hyphens'] = 'auto';
            $css_main_array['.content-text']['-ms-hyphens']     = 'auto';
            $css_main_array['.content-text']['hyphens']         = 'auto';

            $css_main_array['.content-text']['-webkit-hyphenate-limit-chars'] = '5 2 2';
            $css_main_array['.content-text']['-moz-hyphenate-limit-chars']    = '5 2 2';
            $css_main_array['.content-text']['-ms-hyphenate-limit-chars']     = '5 2 2';
            $css_main_array['.content-text']['hyphenate-limit-chars']         = '5 2 2';

            $css_main_array['.content-text']['-webkit-hyphenate-limit-lines'] = '2';
            $css_main_array['.content-text']['-moz-hyphenate-limit-lines']    = '2';
            $css_main_array['.content-text']['-ms-hyphenate-limit-lines']     = '2';
            $css_main_array['.content-text']['hyphenate-limit-lines']         = '2';

            $css_main_array['.content-text']['-webkit-hyphenate-limit-last'] = 'always';
            $css_main_array['.content-text']['-moz-hyphenate-limit-last']    = 'always';
            $css_main_array['.content-text']['-ms-hyphenate-limit-last']     = 'always';
            $css_main_array['.content-text']['hyphenate-limit-last']         = 'always';

            /*
            $css_media_contrast_array['.content-text']['-webkit-hyphens'] = 'unset';
            $css_media_contrast_array['.content-text']['-ms-hyphens']     = 'unset';
            $css_media_contrast_array['.content-text']['hyphens']         = 'unset';

            $css_media_contrast_array['.content-text']['-webkit-hyphenate-limit-chars'] = 'unset';
            $css_media_contrast_array['.content-text']['-moz-hyphenate-limit-chars']    = 'unset';
            $css_media_contrast_array['.content-text']['-ms-hyphenate-limit-chars']     = 'unset';
            $css_media_contrast_array['.content-text']['hyphenate-limit-chars']         = 'unset';

            $css_media_contrast_array['.content-text']['-webkit-hyphenate-limit-lines'] = 'unset';
            $css_media_contrast_array['.content-text']['-moz-hyphenate-limit-lines']    = 'unset';
            $css_media_contrast_array['.content-text']['-ms-hyphenate-limit-lines']     = 'unset';
            $css_media_contrast_array['.content-text']['hyphenate-limit-lines']         = 'unset';

            $css_media_contrast_array['.content-text']['-webkit-hyphenate-limit-last'] = 'unset';
            $css_media_contrast_array['.content-text']['-moz-hyphenate-limit-last']    = 'unset';
            $css_media_contrast_array['.content-text']['-ms-hyphenate-limit-last']     = 'unset';
            $css_media_contrast_array['.content-text']['hyphenate-limit-last']         = 'unset';
            */

            if ($_POST['content_hyphens'] === 'enabled-not-mobile') {
                $css_media_array['.content-text']['-webkit-hyphens'] = 'unset';
                $css_media_array['.content-text']['-ms-hyphens']     = 'unset';
                $css_media_array['.content-text']['hyphens']         = 'unset';

                $css_media_array['.content-text']['-webkit-hyphenate-limit-chars'] = 'unset';
                $css_media_array['.content-text']['-moz-hyphenate-limit-chars']    = 'unset';
                $css_media_array['.content-text']['-ms-hyphenate-limit-chars']     = 'unset';
                $css_media_array['.content-text']['hyphenate-limit-chars']         = 'unset';

                $css_media_array['.content-text']['-webkit-hyphenate-limit-lines'] = 'unset';
                $css_media_array['.content-text']['-moz-hyphenate-limit-lines']    = 'unset';
                $css_media_array['.content-text']['-ms-hyphenate-limit-lines']     = 'unset';
                $css_media_array['.content-text']['hyphenate-limit-lines']         = 'unset';

                $css_media_array['.content-text']['-webkit-hyphenate-limit-last'] = 'unset';
                $css_media_array['.content-text']['-moz-hyphenate-limit-last']    = 'unset';
                $css_media_array['.content-text']['-ms-hyphenate-limit-last']     = 'unset';
                $css_media_array['.content-text']['hyphenate-limit-last']         = 'unset';
            }
        }

        // Initial letter
        if (isset($_POST['content_initial_letter']) && $_POST['content_initial_letter'] === '1') {
            $css_supports_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['-moz-initial-letter'] = '2';
            $css_supports_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['-webkit-initial-letter'] = '2';
            $css_supports_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['initial-letter'] = '2';
            $css_supports_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['margin-right'] = '.25rem';
        }

        $css  = !empty($css_root_array) ? My::stylesArrToStr($css_root_array) : '';
        $css .= !empty($css_root_dark_array) ? '@media (prefers-color-scheme:dark){' . My::stylesArrToStr($css_root_dark_array) . '}' : '';
        $css .= !empty($css_main_array) ? My::stylesArrToStr($css_main_array) : '';
        $css .= !empty($css_supports_initial_letter_array) ? '@supports (initial-letter:2) or (-webkit-initial-letter:2) or (-moz-initial-letter:2){' . My::stylesArrToStr($css_supports_initial_letter_array) . '}' : '';
        $css .= !empty($css_media_array) ? '@media (max-width:34em){' . My::stylesArrToStr($css_media_array) . '}' : '';

        $css = htmlspecialchars($css, ENT_NOQUOTES);
        $css = str_replace('&gt;', ">", $css);

        if ($css) {
            return [
                'value' => $css,
                'type'  => 'string'
            ];
        }

        return [];
    }

    /**
     * Prepares the value of a setting to be saved depending on its type.
     *
     * @param string $setting_type  The type of the setting (integer, checkbox, etc.).
     * @param string $setting_id    The id of the setting.
     * @param string $setting_value The value of the setting.
     *
     * @return array The value of the setting and its type.
     */
    public static function sanitizeSetting($setting_type, $setting_id, $setting_value): array
    {
        $default_settings = My::settingsDefault();

        if ($setting_type === 'select' && in_array($setting_value, $default_settings[$setting_id]['choices'])) {
            return [
                'value' => Html::escapeHTML($setting_value),
                'type'  => 'string'
            ];
        }

        if ($setting_type === 'select_int' && in_array((int) $setting_value, $default_settings[$setting_id]['choices'], true)) {
            return [
                'value' => (int) $setting_value,
                'type'  => 'integer'
            ];
        }

        if ($setting_type === 'checkbox') {
            if ($setting_value === '1' && $default_settings[$setting_id]['default'] !== '1') {
                return [
                    'value' => '1',
                    'type'  => 'boolean'
                ];
            }

            if ($setting_value === '0' && $default_settings[$setting_id]['default'] !== '0') {
                return [
                    'value' => '0',
                    'type'  => 'boolean'
                ];
            }
        }

        if ($setting_type === 'integer' && is_numeric($setting_value) && $setting_value != $default_settings[$setting_id]['default']) {
            return [
                'value' => (int) $setting_value,
                'type'  => 'integer'
            ];
        }

        if ($setting_value != $default_settings[$setting_id]['default']) {
            return [
                'value' => Html::escapeHTML($setting_value),
                'type'  => 'string'
            ];
        }

        return [];
    }

    /**
     * Prepares to save the page width option.
     *
     * @param string  $setting_id The setting id.
     * @param string  $unit       The unit used to define the width (px or em)
     * @param integer $value      The value of the page width.
     *
     * @return array The page width and its unit.
     */
    public static function sanitizePageWidth($setting_id, $unit, $value): array
    {
        $unit  = $unit ?: 'px';
        $value = $value ? (int) $value : 30;
        $data  = My::getContentWidth($unit, $value);

        if ($setting_id === 'global_unit' && isset($data['unit'])) {
            var_dump("ok");
            return [
                'value' => Html::escapeHTML($data['unit']),
                'type'  => 'string'
            ];
        }

        if ($setting_id === 'global_page_width_value' && isset($data['value'])) {
            return [
                'value' => Html::escapeHTML($data['value']),
                'type'  => 'integer'
            ];
        }

        return [];
    }
}
