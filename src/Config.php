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
use Dotclear\Core\Process;
use Dotclear\Core\Backend\Notices;
use Dotclear\Helper\Network\Http;
use Dotclear\Helper\Html\Html;
// use Dotclear\Helper\Html\Form;
// use Dotclear\Helper\Html\Form\Select;

require_once 'OdysseyUtils.php';
use OdysseyUtils as odUtils;

use form;

class Config extends Process
{
    public static function init(): bool
    {
        if (!self::status(My::checkContext(My::CONFIG))) {
            return false;
        }

        My::l10n('admin');

        App::backend()->sections = [
            'global' => [
                'name'         => __('section-global'),
                'sub_sections' => [
                    'fonts' => __('section-global-fonts')
                ]
            ]
        ];

        App::backend()->settings = My::settings_default();

        return self::status();
    }

    /**
     * Processes the request(s).
     */
    public static function process(): bool
    {
        if (!empty($_POST)) {
            try {
                foreach (App::backend()->settings as $setting_id => $setting_data) {
                    $specific_settings = ['styles'];

                    if (!in_array($setting_id, $specific_settings, true)) {
                        if ($_POST[$setting_id] != App::backend()->settings[$setting_id]['default']) {
                            $setting_data = self::sanitizeSetting($setting_type, $setting_id, $_POST[$setting_id]);
                        } else {
                            /**
                             * If the value is equal to the default value,
                             * removes the parameter.
                             */
                            App::blog()->settings->odyssey->drop($setting_id);
                        }
                    } else {
                        switch ($setting_id) {
                            case 'styles':
                                $setting_data = self::saveStyles();
                                break;
                        }
                    }

                    App::blog()->settings->odyssey->put(
                        $setting_id,
                        $setting_data['value'],
                        $setting_data['type'],
                        $setting_label,
                        true
                    );
                }

                // Blog refresh
                App::blog()->triggerBlog();

                // Template cache reset
                App::cache()->emptyTemplatesCache();

                // Not working.
                Notices::addSuccessNotice(__('Theme configuration updated.'));

                // Redirects to refresh form values.
                App::backend()->url()->redirect('admin.blog.theme', ['conf' => '1']);
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        return true;
    }

    public static function settingRender($setting_id)
    {
        $default_settings = App::backend()->settings;
        $saved_settings   = self::settingsSaved();

        // Displays the default value of the parameter if it is not defined.
        if (isset($saved_settings[$setting_id])) {
            $setting_value = $saved_settings[$setting_id];
        } else {
            $setting_value = $default_settings[$setting_id]['default'];
        }

        echo '<p id=', $setting_id, '-input>';

        switch ($default_settings[$setting_id]['type']) {
            case 'select' :
                echo '<label for=', $setting_id, '>',
                $default_settings[$setting_id]['title'],
                '</label>',
                form::combo(
                    $setting_id,
                    $default_settings[$setting_id]['choices'],
                    $setting_value
                );
        }

        echo '</p>';

        // Displays the description of the parameter as a note.
        if ($default_settings[$setting_id]['type'] === 'checkbox' || (isset($default_settings[$setting_id]['description']) && $default_settings[$setting_id]['description'] !== '')) {
            echo '<p class=form-note id=', $setting_id, '-description>',
            $default_settings[$setting_id]['description'];

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

    /**
     * Retrieves all theme settings stored in the database.
     *
     * @return array The id of the saved parameters associated with their values.
     */
    public static function settingsSaved(): array
    {
        $saved_settings   = [];
        $default_settings = odysseySettings::default();

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
        foreach (App::backend()->sections as $section_id => $section_data) {
            $settings_render[$section_id] = [];
        }

        // Adds settings in their section.
        foreach (App::backend()->settings as $setting_id => $setting_data) {
            if ($setting_id !== 'styles') {
                // If a sub-section is set.
                if (isset($setting_data['section'][1])) {
                    $settings_render[$setting_data['section'][0]][$setting_data['section'][1]][] = $setting_id;
                } else {
                    $settings_render[$setting_data['section'][0]][] = $setting_id;
                }
            }
        }

        // Displays the setting.
        foreach ($settings_render as $section_id => $setting_data) {
            echo '<h3 id=section-', $section_id, '>',
            App::backend()->sections[$section_id]['name'],
            '</h3>',
            '<div class=fieldset>';

            foreach ($setting_data as $sub_section_id => $setting_id) {
                // Displays the name of the sub-section unless its ID is "no-title".
                if ($sub_section_id !== 'no-title') {
                    echo '<h4 id=section-', $section_id, '-', $sub_section_id, '>',
                    App::backend()->sections[$section_id]['sub_sections'][$sub_section_id],
                    '</h4>';
                }

                // Displays the parameter.
                foreach ($setting_id as $setting_id_value) {
                    self::settingRender($setting_id_value);
                }
            }

            echo '</div>';
        }
    }

    /**
     * Adds custom styles to the theme to apply the settings.
     *
     * @param int $header_image_width The width if the header image.
     *
     * @return void
     */
    public static function saveStyles()
    {
        $css = '';

        $css_root_array = [];
        $css_main_array = [];

        $default_settings = App::backend()->settings;

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
                $themes_url = App::blog()->settings->system->themes_url;

                $css_root_array[':root']['--font-family'] = '"Atkinson Hyperlegible", sans-serif';

                $css_main_array[0]['@font-face']['font-family'] = '"Atkinson Hyperlegible"';
                $css_main_array[0]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/Atkinson-Hyperlegible-Regular-102a.woff2") format("woff2")';
                $css_main_array[0]['@font-face']['font-style']  = 'normal';
                $css_main_array[0]['@font-face']['font-weight'] = '400';

                $css_main_array[1]['@font-face']['font-family'] = '"Atkinson Hyperlegible"';
                $css_main_array[1]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/Atkinson-Hyperlegible-Italic-102a.woff2") format("woff2")';
                $css_main_array[1]['@font-face']['font-style']  = 'italic';
                $css_main_array[1]['@font-face']['font-weight'] = '400';

                $css_main_array[2]['@font-face']['font-family'] = '"Atkinson Hyperlegible"';
                $css_main_array[2]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/Atkinson-Hyperlegible-Bold-102a.woff2") format("woff2")';
                $css_main_array[2]['@font-face']['font-style']  = 'normal';
                $css_main_array[2]['@font-face']['font-weight'] = '700';

                $css_main_array[3]['@font-face']['font-family'] = '"Atkinson Hyperlegible"';
                $css_main_array[3]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/Atkinson-Hyperlegible-BoldItalic-102a.woff2") format("woff2")';
                $css_main_array[3]['@font-face']['font-style']  = 'italic';
                $css_main_array[3]['@font-face']['font-weight'] = '700';
            } elseif ($_POST['global_font_family'] === 'eb-garamond') {
                $themes_url = dcCore::app()->blog->settings->system->themes_url;

                $css_root_array[':root']['--font-family'] = '"EB Garamond", serif';

                $css_main_array[0]['@font-face']['font-family'] = '"EB Garamond"';
                $css_main_array[0]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/EBGaramond-Regular.ttf") format("truetype")';
                $css_main_array[0]['@font-face']['font-style']  = 'normal';
                $css_main_array[0]['@font-face']['font-weight'] = '400';

                $css_main_array[1]['@font-face']['font-family'] = '"EB Garamond"';
                $css_main_array[1]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/EBGaramond-Italic.ttf") format("truetype")';
                $css_main_array[1]['@font-face']['font-style']  = 'italic';
                $css_main_array[1]['@font-face']['font-weight'] = '400';

                $css_main_array[2]['@font-face']['font-family'] = '"EB Garamond"';
                $css_main_array[2]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/EBGaramond-Bold.ttf") format("truetype")';
                $css_main_array[2]['@font-face']['font-style']  = 'normal';
                $css_main_array[2]['@font-face']['font-weight'] = '700';

                $css_main_array[3]['@font-face']['font-family'] = '"EB Garamond"';
                $css_main_array[3]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/EBGaramond-BoldItalic.ttf") format("truetype")';
                $css_main_array[3]['@font-face']['font-style']  = 'italic';
                $css_main_array[3]['@font-face']['font-weight'] = '700';
            } elseif ($_POST['global_font_family'] === 'luciole') {
                $themes_url = dcCore::app()->blog->settings->system->themes_url;

                $css_root_array[':root']['--font-family'] = 'Luciole, sans-serif';

                $css_main_array[0]['@font-face']['font-family'] = '"Luciole"';
                $css_main_array[0]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/Luciole-Regular.ttf") format("truetype")';
                $css_main_array[0]['@font-face']['font-style']  = 'normal';
                $css_main_array[0]['@font-face']['font-weight'] = '400';

                $css_main_array[1]['@font-face']['font-family'] = '"Luciole"';
                $css_main_array[1]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/Luciole-Regular-Italic.ttf") format("truetype")';
                $css_main_array[1]['@font-face']['font-style']  = 'italic';
                $css_main_array[1]['@font-face']['font-weight'] = '400';

                $css_main_array[2]['@font-face']['font-family'] = '"Luciole"';
                $css_main_array[2]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/Luciole-Bold.ttf") format("truetype")';
                $css_main_array[2]['@font-face']['font-style']  = 'normal';
                $css_main_array[2]['@font-face']['font-weight'] = '700';

                $css_main_array[3]['@font-face']['font-family'] = '"Luciole"';
                $css_main_array[3]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/Luciole-Bold-Italic.ttf") format("truetype")';
                $css_main_array[3]['@font-face']['font-style']  = 'italic';
                $css_main_array[3]['@font-face']['font-weight'] = '700';
            }
        }

        $css .= !empty($css_root_array) ? odUtils::stylesArrayToString($css_root_array) : '';
        $css .= !empty($css_main_array) ? odUtils::stylesArrayToString($css_main_array) : '';

        if (!empty($css)) {
            return [
                'value' => str_replace('&gt;', ">", htmlspecialchars($css, ENT_NOQUOTES)),
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
        $default_settings = odysseySettings::default();

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
}
