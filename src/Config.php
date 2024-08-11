<?php
/**
 * Odyssey, a simple and customizable Dotclear theme.
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
use Dotclear\Core\Backend\ThemeConfig;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Html\Form\Button;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Color;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Hidden;
use Dotclear\Helper\Html\Form\Image;
use Dotclear\Helper\Html\Form\Input;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Legend;
use Dotclear\Helper\Html\Form\Option;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Select;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Textarea;
use Dotclear\Helper\Network\Http;

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
     * Processes the requests.
     *
     * @return bool
     */
    public static function process(): bool
    {
        // Loads custom styles and scripts for the configurator page.
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
                     * This part saves each setting in the database
                     * only if there are different than the default one.
                     */
                    foreach (My::settingsDefault() as $setting_id => $setting_data) {
                        $specific_settings = [
                            'global_unit',
                            'global_page_width_value',
                            'header_image',
                            'header_image2x',
                            'styles'
                        ];

                        // Now, set the value of the setting and its type.
                        $setting_data = [];

                        if (!in_array($setting_id, $specific_settings, true) && !str_starts_with($setting_id, 'social_')) {
                            // Prepares non specific settings to save.
                            if (isset($_POST[$setting_id]) && $_POST[$setting_id] != My::settingsDefault($setting_id)['default']) {
                                $setting_data = self::sanitizeSetting(
                                    My::settingsDefault($setting_id)['type'],
                                    $setting_id,
                                    $_POST[$setting_id]
                                );
                            } elseif (!isset($_POST[$setting_id]) && My::settingsDefault($setting_id)['type'] === 'checkbox') {
                                // Prepares empty checkboxes to save.
                                $setting_data = self::sanitizeSetting(
                                    'checkbox',
                                    $setting_id,
                                    '0'
                                );
                            } else {
                                // Otherwise, deletes the value.
                                App::blog()->settings->odyssey->drop($setting_id);
                            }
                        } else {
                            // Saves each specific settings.
                            if (!str_starts_with($setting_id, 'social_')) {
                                switch ($setting_id) {
                                    case 'global_unit' :
                                    case 'global_page_width_value' :
                                        $setting_data = self::sanitizePageWidth(
                                            $_POST['global_unit'],
                                            $_POST['global_page_width_value'],
                                            $setting_id
                                        );

                                        break;
                                    case 'header_image':
                                    case 'header_image2x':
                                        $setting_data = self::sanitizeHeaderImage(
                                            $setting_id,
                                            $_POST['header_image'],
                                            $_POST['global_unit'],
                                            $_POST['global_page_width_value']
                                        );

                                        break;
                                    case 'styles' :
                                        $setting_data = self::saveStyles();

                                        // Dev
                                        if (isset($setting_data['value'])) {
                                            $styles_min = $setting_data['value'];
                                        }
                                }
                            } else {
                                $setting_data = self::sanitizeSocialLink($setting_id, $_POST[$setting_id]);
                            }
                        }

                        // Saves the setting data or drop it if it's empty.
                        if (!empty($setting_data)) {
                            $setting_value = isset($setting_data['value']) ? $setting_data['value'] : '';
                            $setting_type  = isset($setting_data['type']) ? $setting_data['type'] : '';
                            $setting_label = Html::clean(Html::escapeHTML(My::settingsDefault($setting_id)['title']));

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

                    $styles_min = isset($styles_min) ? $styles_min : '';

                    if ($styles_min) {
                        var_dump($styles_min);
                        ThemeConfig::canWriteCss(My::id() . '/css/', true);
                        ThemeConfig::writeCss(My::id() . '/css/', 'styles.min', $styles_min);
                    }

                    // Refreshes the blog.
                    App::blog()->triggerBlog();

                    // Resets template cache.
                    App::cache()->emptyTemplatesCache();

                    // Displays a success notice.
                    Notices::addSuccessNotice(__('settings-notice-saved'));

                    // Redirects to refresh form values.
                    // App::backend()->url()->redirect('admin.blog.theme', ['conf' => '1']);
                } elseif (isset($_POST['reset'])) {
                    App::blog()->settings->odyssey->dropAll();

                    App::blog()->triggerBlog();

                    App::cache()->emptyTemplatesCache();

                    Notices::addSuccessNotice(__('settings-notice-reset'));

                    App::backend()->url()->redirect('admin.blog.theme', ['conf' => '1']);
                }
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        return true;
    }

    /**
     * Renders the setting in the configurato page.
     *
     * @param string $setting_id The id of the setting to display.
     *
     * @return array The setting.
     */
    public static function settingRender(string $setting_id): array
    {
        $default_settings = My::settingsDefault();
        $saved_settings   = self::settingsSaved();

        // Displays the default value of the parameter if it is not defined.
        if (isset($saved_settings[$setting_id])) {
            $setting_value = $saved_settings[$setting_id];
        } else {
            $setting_value = $default_settings[$setting_id]['default'];
        }

        $the_setting = [];

        switch ($default_settings[$setting_id]['type']) {
            case 'checkbox' :
                $the_setting[] = (new Para())
                    ->id($setting_id . '-input')
                    ->items([
                        (new Checkbox($setting_id, $setting_value))
                            ->checked($setting_value)
                            ->label(
                                (new Label($default_settings[$setting_id]['title'], Label::OUTSIDE_TEXT_AFTER))
                                ->class('classic')
                            )
                        ]
                    );

                $checkbox_default = '';

                if ($default_settings[$setting_id]['type'] === 'checkbox') {
                    if ($default_settings[$setting_id]['default'] === true) {
                        $checkbox_default = ' ' . __('settings-default-checked');
                    } else {
                        $checkbox_default = ' ' . __('settings-default-unchecked');
                    }
                }

                if (isset($default_settings[$setting_id]['description']) && $default_settings[$setting_id]['description'] !== '') {
                    $the_setting[] = (new Para())
                        ->id($setting_id . '-description')
                        ->class('form-note')
                        ->items([
                            (new Text(null, $default_settings[$setting_id]['description'] . $checkbox_default))
                       ]);
                } elseif ($checkbox_default !== '') {
                    $the_setting[] = (new Para())
                        ->id($setting_id . '-description')
                        ->class('form-note')
                        ->items([
                            (new Text(null, $checkbox_default))
                       ]);
                }

                break;

            case 'select' :
            case 'select_int' :
                $combo = [];

                foreach ($default_settings[$setting_id]['choices'] as $name => $value) {
                    $combo[] = new Option($name, $value);
                }

                $the_setting[] = (new Para())
                    ->id($setting_id . '-input')
                    ->items([
                        (new Select($setting_id))
                            ->default((string) $setting_value)
                            ->items($combo)
                            ->label(new Label($default_settings[$setting_id]['title'], Label::OL_TF))
                        ]
                    );

                // Displays a preview for font changes.
                if ($setting_id === 'global_font_family' || $setting_id === 'content_text_font') {
                    $preview_string = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean iaculis egestas sapien, at pretium erat interdum ullamcorper. Aliquam facilisis dolor sit amet nibh imperdiet vestibulum. Aenean et elementum magna, eget blandit arcu. Morbi tellus tortor, gravida vitae rhoncus nec, scelerisque vitae odio. In nulla mi, efficitur interdum scelerisque ac, ultrices non tortor.';

                    if ($setting_id === 'global_font_family') {
                        $the_setting[] = (new Para())
                            ->id('odyssey-config-global-font-preview')
                            ->class('odyssey-font-preview')
                            ->items([
                                (new Text('strong', __('config-preview-font'))),
                                (new Text(null, ' ' . $preview_string))
                            ]);
                    } else {
                        if ($setting_value === 'same' && isset($saved_settings['global_font_family'])) {
                            $attr = ' style="font-family:' . My::fontStack($saved_settings['global_font_family']) . '";';
                        } else {
                            $attr = '';
                        }

                        $the_setting[] = (new Para())
                            ->id('odyssey-config-content-font-preview')
                            ->class('odyssey-font-preview')
                            ->items([
                                (new Text('strong', __('config-preview-font'))),
                                (new Text(null, ' ' . $preview_string))
                            ]);
                    }
                }

                if (isset($default_settings[$setting_id]['description']) && $default_settings[$setting_id]['description'] !== '') {
                    $the_setting[] = (new Para())
                        ->id($setting_id . '-description')
                        ->class('form-note')
                        ->items([
                            (new Text(null, $default_settings[$setting_id]['description']))
                        ]);
                }

                break;

            case 'image' :
                $placeholder = isset($default_settings[$setting_id]['placeholder'])
                ? $default_settings[$setting_id]['placeholder']
                : '';

                if (!empty($setting_value) && $setting_value['url'] !== '') {
                    $image_src = $setting_value['url'];
                } else {
                    $image_src = '';
                }

                $the_setting[] = (new Para())
                    ->id($setting_id . '-input')
                    ->items([
                        (new Input($setting_id))
                            ->label(
                                (new Label($default_settings[$setting_id]['title'], 2))
                                    ->for($setting_id)
                            )
                            ->maxlength(255)
                            ->placeholder($placeholder)
                            ->size(30)
                            ->value($image_src)
                    ]);

                if (isset($default_settings[$setting_id]['description']) && $default_settings[$setting_id]['description'] !== '') {
                    $the_setting[] = (new Para())
                        ->id($setting_id . '-description')
                        ->class('form-note')
                        ->items([
                            (new Text(null, $default_settings[$setting_id]['description']))
                        ]);
                }

                break;

            case 'color' :
                $placeholder = isset($default_settings[$setting_id]['placeholder'])
                ? My::attrValue($default_settings[$setting_id]['placeholder'])
                : '';

                $setting_value = $setting_value ?: $default_settings[$setting_id]['default'];

                $setting_value_input = $setting_value !== $default_settings[$setting_id]['default']
                ? $setting_value
                : '';

                $the_setting[] = (new Para())
                    ->id($setting_id . '-input')
                    ->class('odyssey-color-setting')
                    ->items([
                        (new Label($default_settings[$setting_id]['title'], 0, $setting_id))
                            ->extra('for=' . $setting_id . '-text'),
                        (new Color($setting_id, $setting_value)),
                        (new Input($setting_id . '-text', $setting_value))
                            ->placeholder($placeholder)
                            ->value($setting_value_input),
                        (new Button($setting_id . '-default-button', __('settings-colors-reset'))),
                        (new Hidden($setting_id . '-default-value', Html::escapeHTML($default_settings[$setting_id]['default'])))
                    ]);

                break;

            case 'textarea' :
                $placeholder = isset($default_settings[$setting_id]['placeholder'])
                ? 'placeholder=' . My::attrValue($default_settings[$setting_id]['placeholder'])
                : '';

                $the_setting[] = (new Para())
                    ->id($setting_id . '-input')
                    ->items([
                        (new Label($default_settings[$setting_id]['title'], 2))
                            ->for($setting_id),
                        (new Textarea($setting_id, $setting_value))
                            ->placeholder($placeholder)
                            ->cols(60)
                            ->rows(3)
                    ]);

                if (isset($default_settings[$setting_id]['description']) && $default_settings[$setting_id]['description'] !== '') {
                    $the_setting[] = (new Para())
                        ->id($setting_id . '-description')
                        ->class('form-note')
                        ->items([
                            (new Text(null, $default_settings[$setting_id]['description']))
                        ]);
                }

                break;

            default :
                $placeholder = isset($default_settings[$setting_id]['placeholder'])
                ? My::attrValue($default_settings[$setting_id]['placeholder'])
                : '';

                $the_setting[] = (new Para())
                    ->id($setting_id . '-input')
                    ->items([
                        (new Input($setting_id))
                            ->label(
                                (new Label($default_settings[$setting_id]['title'], 2))
                                    ->for($setting_id)
                            )
                            ->maxlength(255)
                            ->placeholder($placeholder)
                            ->size(30)
                            ->value($setting_value)
                    ]);

                if (isset($default_settings[$setting_id]['description']) && $default_settings[$setting_id]['description'] !== '') {
                    $the_setting[] = (new Para())
                        ->id($setting_id . '-description')
                        ->class('form-note')
                        ->items([
                            (new Text(null, $default_settings[$setting_id]['description']))
                        ]);
                }
        }

        // Header image.
        if ($setting_id === 'header_image') {
            if (!empty($setting_value) && isset($setting_value['url'])) {
                $image_src = $setting_value['url'];
            } else {
                $image_src = '';
            }


            $the_setting[] = (new Text(
                null,
                '<img alt="' . __('header-image-preview-alt') . '" id=' . $setting_id . '-src src="' . Html::escapeURL($image_src) . '">'
            ));


            if (isset($saved_settings['header_image2x'])) {
                $the_setting[] = (new Text('div', __('header-image-retina-ready')))
                    ->id($setting_id . '-retina');
            }

            $the_setting[] = (new Hidden($setting_id . '-url', Html::escapeURL($image_src)));
            $the_setting[] = (new Hidden($setting_id . '-retina-text', Html::escapeHTML(__('header-image-retina-ready'))));
        }

        return $the_setting;
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
     *
     * @return void The page.
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

        $settings_ignored = ['header_image2x', 'styles'];

        foreach (My::settingsDefault() as $setting_id => $setting_data) {
            if (!in_array($setting_id, $settings_ignored, true)) {
                // If a sub-section has been set.
                if (isset($setting_data['section'][1])) {
                    $settings_render[$setting_data['section'][0]][$setting_data['section'][1]][] = $setting_id;
                } else {
                    $settings_render[$setting_data['section'][0]][] = $setting_id;
                }
            }
        }

        // Adds settings in their section.
        $fields = [];

        foreach ($settings_render as $section_id => $setting_data) {
            $fields[] = (new Text('', '<h3>' . My::settingsSections($section_id)['name'] . '</h3>'))
                ->id('section-' . $section_id);
            $fields[] = (new Text('', '<div class=fieldset>'));

            foreach ($setting_data as $sub_section_id => $setting_id) {
                // Displays the name of the sub-section unless its ID is "no-title".
                if ($sub_section_id !== 'no-title') {
                    $fields[] = (new Text('h4', My::settingsSections($section_id)['sub_sections'][$sub_section_id]))
                        ->id('section-' . $section_id . '-' . $sub_section_id);
                }

                // Displays the parameter.
                foreach ($setting_id as $setting_id_value) {
                    if (is_array(self::settingRender($setting_id_value))) {
                        foreach (self::settingRender($setting_id_value) as $item) {
                            $fields[] = $item;
                        }
                    }
                }
            }

            $fields[] = (new Text(null, '</div>'));
        }

        $fields[] = (new Hidden('page_width_em_default', '30'));
        $fields[] = (new Hidden('page_width_px_default', '480'));

        $fields[] = (new Para())
            ->class('form-buttons')
            ->items([
                App::nonce()->formNonce(),
                (new Submit(null, __('settings-save-button-text')))
                    ->name('save'),
                (new Submit(null,  __('settings-reset-button-text')))
                    ->class('delete')
                    ->name('reset')
            ]);

        echo (new Form('theme-config-form'))
            ->action(App::backend()->url()->get('admin.blog.theme', ['conf' => '1']))
            ->method('post')
            ->fields($fields)
            ->render();
    }

    /**
     * Adds custom styles to the theme to apply the settings.
     *
     * @return array The styles.
     */
    public static function saveStyles(): array
    {
        $css_root_array                    = [];
        $css_root_dark_array               = [];
        $css_main_array                    = [];
        $css_supports_initial_letter_array = [];
        $css_media_array                   = [];
        $css_media_contrast_array          = [];
        $css_media_motion_array            = [];
        $css_media_print_array             = [];

        // Page width.
        if (isset($_POST['global_unit']) && isset($_POST['global_page_width_value'])) {
            $page_width_data = self::sanitizePageWidth($_POST['global_unit'], $_POST['global_page_width_value']);

            if (!empty($page_width_data)) {
                $css_root_array[':root']['--page-width'] = Html::escapeHTML($page_width_data['value'] . $page_width_data['unit']);
            }
        }

        // Font family.
        if (isset($_POST['global_font_family']) && $_POST['global_font_family'] !== 'system') {
            $css_root_array[':root']['--font-family'] = My::fontStack($_POST['global_font_family']);
        }

        // Font size.
        $font_size_allowed = [80, 90, 110, 120];

        if (isset($_POST['global_font_size']) && in_array((int) $_POST['global_font_size'], $font_size_allowed, true)) {
            $css_root_array[':root']['--font-size'] = My::removeZero((int) $_POST['global_font_size'] / 100) . 'em';
        }

        // Font antialiasing.
        if (isset($_POST['global_font_antialiasing']) && $_POST['global_font_antialiasing'] === 'on') {
            $css_main_array['body']['-moz-osx-font-smoothing'] = 'grayscale';
            $css_main_array['body']['-webkit-font-smoothing']  = 'antialiased';
            $css_main_array['body']['font-smooth']             = 'always';

            $css_media_contrast_array['body']['-moz-osx-font-smoothing'] = 'unset';
            $css_media_contrast_array['body']['-webkit-font-smoothing']  = 'unset';
            $css_media_contrast_array['body']['font-smooth']             = 'unset';

            $css_media_print_array['body']['-moz-osx-font-smoothing'] = 'unset';
            $css_media_print_array['body']['-webkit-font-smoothing']  = 'unset';
            $css_media_print_array['body']['font-smooth']             = 'unset';
        }

        // Main text color.
        if (isset($_POST['global_color_text_custom'])
            && isset($_POST['global_color_text_custom-default-value'])
            && self::isHexColor($_POST['global_color_text_custom'])
            && $_POST['global_color_text_custom'] !== $_POST['global_color_text_custom-default-value']
        ) {
            $css_root_array[':root']['--color-text-main'] = Html::escapeHTML($_POST['global_color_text_custom']);
        }

        if (isset($_POST['global_color_text_dark_custom'])
            && isset($_POST['global_color_text_dark_custom-default-value'])
            && self::isHexColor($_POST['global_color_text_dark_custom'])
            && $_POST['global_color_text_dark_custom'] !== $_POST['global_color_text_dark_custom-default-value']
        ) {
            $css_root_array[':root']['--color-text-main-dark'] = Html::escapeHTML($_POST['global_color_text_dark_custom']);
        }

        // Text secondary color.
        if (isset($_POST['global_color_text_secondary_custom'])
            && isset($_POST['global_color_text_secondary_custom-default-value'])
            && self::isHexColor($_POST['global_color_text_secondary_custom'])
            && $_POST['global_color_text_secondary_custom'] !== $_POST['global_color_text_secondary_custom-default-value']
        ) {
            $css_root_array[':root']['--color-text-secondary'] = Html::escapeHTML($_POST['global_color_text_secondary_custom']);
        }

        if (isset($_POST['global_color_text_secondary_dark_custom'])
            && isset($_POST['global_color_text_secondary_dark_custom-default-value'])
            && self::isHexColor($_POST['global_color_text_secondary_dark_custom'])
            && $_POST['global_color_text_secondary_dark_custom'] !== $_POST['global_color_text_secondary_dark_custom-default-value']
        ) {
            $css_root_array[':root']['--color-text-secondary-dark'] = Html::escapeHTML($_POST['global_color_text_secondary_dark_custom']);
        }

        // Input color.
        if (isset($_POST['global_color_input_custom'])
            && isset($_POST['global_color_input_custom-default-value'])
            && self::isHexColor($_POST['global_color_input_custom'])
            && $_POST['global_color_input_custom'] !== $_POST['global_color_input_custom-default-value']
        ) {
            $css_root_array[':root']['--color-input-background'] = Html::escapeHTML($_POST['global_color_input_custom']);
        }

        if (isset($_POST['global_color_input_dark_custom'])
            && isset($_POST['global_color_input_dark_custom-default-value'])
            && self::isHexColor($_POST['global_color_input_dark_custom'])
            && $_POST['global_color_input_dark_custom'] !== $_POST['global_color_input_dark_custom-default-value']
        ) {
            $css_root_array[':root']['--color-input-background-dark'] = Html::escapeHTML($_POST['global_color_input_dark_custom']);
        }

        // Border color.
        if (isset($_POST['global_color_border_custom'])
            && isset($_POST['global_color_border_custom-default-value'])
            && self::isHexColor($_POST['global_color_border_custom'])
            && $_POST['global_color_border_custom'] !== $_POST['global_color_border_custom-default-value']
        ) {
            $css_root_array[':root']['--color-border'] = Html::escapeHTML($_POST['global_color_border_custom']);
        }

        if (isset($_POST['global_color_border_dark_custom'])
            && isset($_POST['global_color_border_dark_custom-default-value'])
            && self::isHexColor($_POST['global_color_border_dark_custom'])
            && $_POST['global_color_border_dark_custom'] !== $_POST['global_color_border_dark_custom-default-value']
        ) {
            $css_root_array[':root']['--color-border-dark'] = Html::escapeHTML($_POST['global_color_border_dark_custom']);
        }

        // Background color.
        if (isset($_POST['global_color_background_custom'])
            && isset($_POST['global_color_background_custom-default-value'])
            && self::isHexColor($_POST['global_color_background_custom'])
            && $_POST['global_color_background_custom'] !== $_POST['global_color_background_custom-default-value']
        ) {
            $css_root_array[':root']['--color-background'] = Html::escapeHTML($_POST['global_color_background_custom']);
        }

        if (isset($_POST['global_color_background_dark_custom'])
            && isset($_POST['global_color_background_custom-default-value'])
            && self::isHexColor($_POST['global_color_background_dark_custom'])
            && $_POST['global_color_background_dark_custom'] !== $_POST['global_color_background_dark_custom-default-value']
        ) {
            $css_root_array[':root']['--color-background-dark'] = Html::escapeHTML($_POST['global_color_background_dark_custom']);
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
                'red'   => '0, 70%, 70%'
            ]
        ];

        if (isset($_POST['global_color_primary'])) {
            if ($_POST['global_color_primary'] !== 'custom' && in_array($_POST['global_color_primary'], $primary_colors_allowed, true)) {
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
            } elseif ($_POST['global_color_primary'] === 'custom') {
                if (isset($_POST['global_color_primary_custom'])
                    && isset($_POST['global_color_primary_custom-default-value'])
                    && self::isHexColor($_POST['global_color_primary_custom'])
                    && $_POST['global_color_primary_custom'] !== $_POST['global_color_primary_custom-default-value']
                ) {
                    $css_root_array[':root']['--color-primary'] = Html::escapeHTML($_POST['global_color_primary_custom']);
                }

                if (isset($_POST['global_color_primary_amplified_custom'])
                    && isset($_POST['global_color_primary_amplified_custom-default-value'])
                    && self::isHexColor($_POST['global_color_primary_amplified_custom'])
                    && $_POST['global_color_primary_amplified_custom'] !== $_POST['global_color_primary_amplified_custom-default-value']
                ) {
                    $css_root_array[':root']['--color-primary-amplified'] = Html::escapeHTML($_POST['global_color_primary_amplified_custom']);
                }

                if (isset($_POST['global_color_primary_dark_custom'])
                    && isset($_POST['global_color_primary_dark_custom-default-value'])
                    && self::isHexColor($_POST['global_color_primary_dark_custom'])
                    && $_POST['global_color_primary_dark_custom'] !== $_POST['global_color_primary_dark_custom-default-value']
                ) {
                    $css_root_dark_array[':root']['--color-primary-dark'] = Html::escapeHTML($_POST['global_color_primary_dark_custom']);
                }

                if (isset($_POST['global_color_primary_dark_amplified_custom'])
                    && isset($_POST['global_color_primary_dark_amplified_custom-default-value'])
                    && self::isHexColor($_POST['global_color_primary_dark_amplified_custom'])
                    && $_POST['global_color_primary_dark_amplified_custom'] !== $_POST['global_color_primary_dark_amplified_custom-default-value']
                ) {
                    $css_root_dark_array[':root']['--color-primary-dark-amplified'] = Html::escapeHTML($_POST['global_color_primary_dark_amplified_custom']);
                }
            }
        }

        // Transitions.
        if (isset($_POST['global_css_transition']) && $_POST['global_css_transition'] === 'on') {
            $css_root_array[':root']['--color-transition'] = 'all .2s ease-in-out';

            $css_media_motion_array[':root']['--color-transition'] = 'unset';
        }

        // Header alignment
        $header_align_allowed = ['left', 'right'];

        if (isset($_POST['header_align']) && in_array($_POST['header_align'], $header_align_allowed, true)) {
            $css_root_array[':root']['--header-align'] = $_POST['header_align'];
        }

        // Header banner
        if (isset($_POST['header_image']) && $_POST['header_image'] !== '') {
            $css_main_array['#site-image']['width'] = '100%';

            $css_media_contrast_array['#site-image a']['outline'] = 'inherit';
        }

        // Post list type
        if (isset($_POST['content_postlist_type']) && $_POST['content_postlist_type'] === 'excerpt') {
            $css_main_array['.entry-list-excerpt .post']['margin-inline'] = '-1rem';
            $css_main_array['.entry-list-excerpt .post']['padding']       = '1rem';

            $css_main_array['.entry-list-excerpt .entry-title']['font-size']    = '1.1rem';
            $css_main_array['.entry-list-excerpt .entry-title']['margin-block'] = '.5rem';

            $css_main_array['.entry-list-excerpt .post-excerpt']['margin-block'] = '.5rem';
        }

        // Alternate post color
        if (isset($_POST['content_postlist_altcolor']) && $_POST['content_postlist_altcolor'] === 'on') {
            $css_root_dark_array[':root']['--color-background-even'] = '#000';

            $css_main_array[':is(.entry-list, .entry-list-excerpt) .post:nth-child(even)']['background-color'] = 'var(--color-background-even, #fff)';
        }

        // Post thumbnail
        if (isset($_POST['content_postlist_thumbnail']) && $_POST['content_postlist_thumbnail'] === 'on') {
            if (isset($_POST['content_postlist_type']) && $_POST['content_postlist_type'] === 'excerpt') {
                $css_main_array['.post-list-excerpt']['display'] = 'block';

                $css_main_array['.entry-list-excerpt-img']['display']      = 'block';
                $css_main_array['.entry-list-excerpt-img']['margin-block'] = '1rem';
            }
        }

        // Link to reactions
        if (isset($_POST['content_postlist_reactions']) && $_POST['content_postlist_reactions'] === 'on') {
            $css_main_array['.post-list-reaction-link']['margin-top'] = '.25rem';
        }

        // Content font family
        if (isset($_POST['content_text_font']) && $_POST['content_text_font'] !== 'same' && $_POST['global_font_family'] !== $_POST['content_text_font']) {
            $css_root_array[':root']['--font-family-content'] = My::fontStack($_POST['content_text_font']);
        }

        // Content font size
        $font_size_allowed = [80, 90, 110, 120];

        if (isset($_POST['content_font_size']) && in_array((int) $_POST['content_font_size'], $font_size_allowed, true)) {
            $css_root_array[':root']['--content-font-size'] = My::removeZero($_POST['content_font_size'] / 100) . 'em';
        }

        // Text align
        if (isset($_POST['content_text_align'])) {
            switch ($_POST['content_text_align']) {
                case 'justify' :
                    $css_root_array[':root']['--text-align'] = 'justify';

                    $css_media_contrast_array[':root']['--text-align'] = 'left';

                    break;
                case 'justify-not-mobile' :
                    $css_root_array[':root']['--text-align']  = 'justify';

                    $css_media_array[':root']['--text-align'] = 'left';

                    $css_media_contrast_array[':root']['--text-align'] = 'left';
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
        if (isset($_POST['content_initial_letter']) && $_POST['content_initial_letter'] === 'on') {
            $css_supports_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['-moz-initial-letter'] = '2';
            $css_supports_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['-webkit-initial-letter'] = '2';
            $css_supports_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['initial-letter'] = '2';
            $css_supports_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['margin-right'] = '.25rem';
        }

        // Footer align
        $footer_align_allowed = ['center', 'right'];

        if (isset($_POST['footer_enabled']) && $_POST['footer_enabled'] === 'on') {
            if (isset($_POST['footer_align']) && in_array($_POST['footer_align'], $footer_align_allowed, true)) {
                $css_root_array[':root']['--footer-align'] = $_POST['footer_align'];
            }
        }

        // Displays Simple Icons styles if necessary.
        $simpleicons_styles = false;

        // Checks if a link has been set.
        foreach (My::socialSites() as $id => $data) {
            if (isset($_POST['social_' . $id]) && $_POST['social_' . $id] !== '') {
                if ((isset($_POST['reactions_other']) && $_POST['reactions_other'] !== 'disabled' && isset($_POST['reactions_other_' . $id]) && $_POST['reactions_other_' . $id] !== '')
                    || (isset($_POST['footer_social_' . $id]) && $_POST['footer_social_' . $id] !== '')
                ) {
                    if (!empty(self::sanitizeSocialLink('social_' . $id, $_POST['social_' . $id]))) {
                        if (My::svgIcons($id)['author'] === 'simpleicons') {
                            $simpleicons_styles  = true;
                        }
                    }
                }
            }
        }

        if ($simpleicons_styles === true) {
            $css_main_array['.social-icon-si']['border']          = '0';
            $css_main_array['.social-icon-si']['stroke']          = 'none';
            $css_main_array['.social-icon-si']['stroke-linecap']  = 'round';
            $css_main_array['.social-icon-si']['stroke-linejoin'] = 'round';
            $css_main_array['.social-icon-si']['stroke-width']    = '0';
            $css_main_array['.social-icon-si']['width']           = '1rem';
            $css_main_array['.social-icon-si']['transition']      = 'var(--color-transition, unset)';
        }

        // Other reactions
        if (isset($_POST['reactions_other']) && $_POST['reactions_other'] !== 'disabled') {
            $css_main_array['.reactions-button .social-icon-si']['fill'] = 'var(--color-primary, hsl(226, 80%, 45%))';
        }

        // Footer links
        $footer_social_links = false;
        $simpleicons_styles  = false;
        $feathericons_styles = false;

        if (isset($_POST['footer_feed']) && $_POST['footer_feed'] !== 'disabled') {
            $footer_social_links = true;
            $feathericons_styles = true;
        }

        foreach (My::socialSites() as $id => $data) {
            if (isset($_POST['social_' . $id]) && $_POST['social_' . $id] !== '') {
                if (isset($_POST['footer_social_' . $id]) && $_POST['footer_social_' . $id] !== '') {
                    if (!empty(self::sanitizeSocialLink('social_' . $id, $_POST['social_' . $id]))) {
                        $footer_social_links = true;

                        if (My::svgIcons($id)['author'] === 'simpleicons') {
                            $simpleicons_styles = true;
                        } elseif (My::svgIcons($id)['author'] === 'feathericons') {
                            $feathericons_styles = true;
                        }
                    }
                }
            }

            if ($footer_social_links === true && $simpleicons_styles === true && $feathericons_styles === true) {
                break;
            }
        }

        if ($footer_social_links === true) {
            $css_main_array['.footer-social-links']['margin-bottom'] = '1rem';

            $css_main_array['.footer-social-links']['list-style']                 = 'none';
            $css_main_array['.footer-social-links']['margin']                     = '0';
            $css_main_array['.footer-social-links']['padding-left']               = '0';
            $css_main_array['.footer-social-links li']['display']                 = 'inline-block';
            $css_main_array['.footer-social-links li']['margin']                  = '.25em';
            $css_main_array['.footer-social-links li:first-child']['margin-left'] = '0';
            $css_main_array['.footer-social-links li:last-child']['margin-right'] = '0';

            $css_main_array['.footer-social-links a']['display'] = 'inline-block';

            $css_main_array['.footer-social-links-icon-container']['align-items']      = 'center';
            $css_main_array['.footer-social-links-icon-container']['background-color'] = 'var(--color-input-background, #f2f2f2)';
            $css_main_array['.footer-social-links-icon-container']['bor'] = 'var(--color-input-background, #f2f2f2)';
            $css_main_array['.footer-social-links-icon-container']['display']          = 'flex';
            $css_main_array['.footer-social-links-icon-container']['justify-content']  = 'center';
            $css_main_array['.footer-social-links-icon-container']['width']            = '1.5rem';
            $css_main_array['.footer-social-links-icon-container']['height']           = '1.5rem';
            $css_main_array['.footer-social-links-icon-container']['transition']       = 'var(--color-transition, unset)';

            if ($simpleicons_styles === true) {
                $css_main_array['.footer-social-links-icon-container .footer-social-links-icon-si']['fill'] = 'var(--color-text-main, #303030)';

            }

            if ($feathericons_styles === true) {
                $css_main_array['.footer-social-links-icon-container .footer-social-links-icon-fi']['stroke'] = 'var(--color-text-main, #303030)';
            }

            $css_main_array['.footer-social-links a:active .footer-social-links-icon-container, .footer-social-links a:focus .footer-social-links-icon-container, .footer-social-links a:hover .footer-social-links-icon-container']['background-color'] = 'var(--color-primary, hsl(226, 80%, 45%))';

            $css_main_array['.footer-social-links a:active .footer-social-links-icon-container, .footer-social-links a:focus .footer-social-links-icon-container, .footer-social-links a:hover .footer-social-links-icon-container']['transition'] = 'var(--color-transition, unset)';

            $css_main_array['.footer-social-links a']['border-bottom'] = 'none';

            $css_main_array['.footer-social-links a:active, .footer-social-links a:focus, .footer-social-links a:hover']['border-bottom'] = 'none';

            if ($simpleicons_styles === true) {
                $css_main_array['.footer-social-links a:active .footer-social-links-icon-si, .footer-social-links a:focus .footer-social-links-icon-si, .footer-social-links a:hover .footer-social-links-icon-si']['fill'] = 'var(--color-background, #fcfcfd)';

                $css_main_array['.footer-social-links a:active .footer-social-links-icon-si, .footer-social-links a:focus .footer-social-links-icon-si, .footer-social-links a:hover .footer-social-links-icon-si']['transition'] = 'var(--color-transition, unset)';
            }

            if ($feathericons_styles === true) {
                $css_main_array['.footer-social-links a:active .footer-social-links-icon-fi, .footer-social-links a:focus .footer-social-links-icon-fi, .footer-social-links a:hover .footer-social-links-icon-fi']['stroke'] = 'var(--color-background, #fcfcfd)';

                $css_main_array['.footer-social-links a:active .footer-social-links-icon-fi, .footer-social-links a:focus .footer-social-links-icon-fi, .footer-social-links a:hover .footer-social-links-icon-fi']['transition'] = 'var(--color-transition, unset)';
            }
        }

        $css  = !empty($css_root_array) ? My::stylesArrToStr($css_root_array) : '';
        $css .= !empty($css_root_dark_array) ? '@media (prefers-color-scheme:dark){' . My::stylesArrToStr($css_root_dark_array) . '}' : '';
        $css .= !empty($css_main_array) ? My::stylesArrToStr($css_main_array) : '';
        $css .= !empty($css_supports_initial_letter_array) ? '@supports (initial-letter:2) or (-webkit-initial-letter:2) or (-moz-initial-letter:2){' . My::stylesArrToStr($css_supports_initial_letter_array) . '}' : '';
        $css .= !empty($css_media_array) ? '@media (max-width:34em){' . My::stylesArrToStr($css_media_array) . '}' : '';
        $css .= !empty($css_media_contrast_array) ? '@media (prefers-contrast:more){' . My::stylesArrToStr($css_media_contrast_array) . '}' : '';
        $css .= !empty($css_media_motion_array) ? '@media (prefers-reduced-motion:reduce){' . My::stylesArrToStr($css_media_motion_array) . '}' : '';
        $css .= !empty($css_media_print_array) ? '@media print{' . My::stylesArrToStr($css_media_print_array) . '}' : '';

        $css = htmlspecialchars($css, ENT_NOQUOTES);
        $css = str_replace('&gt;', '>', $css);

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
     * @param mixed  $setting_value The value of the setting.
     *
     * @return array The value of the setting and its type.
     */
    public static function sanitizeSetting(string $setting_type, string $setting_id, mixed $setting_value): array
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
            if ($setting_value === 'on' && $default_settings[$setting_id]['default'] !== true) {
                return [
                    'value' => '1',
                    'type'  => 'boolean'
                ];
            }

            if ($setting_value === '0' && $default_settings[$setting_id]['default'] !== false) {
                return [
                    'value' => '0',
                    'type'  => 'boolean'
                ];
            }
        }

        if ($setting_type === 'integer' && is_numeric($setting_value) && $setting_value !== $default_settings[$setting_id]['default']) {
            return [
                'value' => (int) $setting_value,
                'type'  => 'integer'
            ];
        }

        if ($setting_type === 'color') {
            if (preg_match('/#[A-Fa-f0-9]{6}/', $setting_value) === false) {
                $setting_value = $default_settings[$setting_id]['default'];
            }

            return [
                'value' => Html::escapeHTML(strtolower($setting_value)),
                'type'  => 'string'
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
     * Saves the banner.
     *
     * The image is saved as an array which contains:
     * 'url'        => (string) The URL of the image.
     * 'max-width'  => (int) The maximum width of the image
     *                       (inferior or equal to the page width).
     * 'max-height' => (int) The maximum height of the image.
     *
     * @param string $setting_id       The setting id.
     * @param string $image_url        The image URL.
     * @param string $page_width_unit  The page width unit (em or px).
     * @param string $page_width_value The page width value.
     *
     * @return array The image in an array.
     */
    public static function sanitizeHeaderImage(string $setting_id, string $image_url, string $page_width_unit, string $page_width_value): array
    {
        $default_settings = My::settingsDefault();
        $image_url        = Html::escapeURL($image_url) ?: '';
        $page_width_unit  = $page_width_unit ?: '';
        $page_width_value = $page_width_value ?: null;

        if ($image_url) {
            // Gets relative url and path of the public folder.
            $public_url  = App::blog()->settings->system->public_url;
            $public_path = App::blog()->public_path;

            // Converts the absolute URL in a relative one if necessary.
            $image_url = Html::stripHostURL($image_url);

            // Retrieves the image path.
            $image_path = $public_path . str_replace($public_url . '/', '/', $image_url);

            if (My::imageExists($image_path)) {
                // Gets the dimensions of the image.
                list($header_image_width) = getimagesize($image_path);

                /**
                 * Limits the maximum width value of the image if its superior to the page width,
                 * and sets its height proportionally.
                 */
                $page_width_data = self::sanitizePageWidth($page_width_unit, (int) $page_width_value);

                if (empty($page_width_data)) {
                    $page_width_data['unit']  = 'em';
                    $page_width_data['value'] = 30;
                }

                $page_width = $page_width_data['value'];

                if ($page_width_data['unit'] === 'em') {
                    $page_width = $page_width * 16;
                }

                if ($header_image_width > $page_width) {
                    $header_image_width = 100;
                } else {
                    $header_image_width = $header_image_width * 100 / $page_width;
                }

                // Sets the array which contains the image data.
                $image_data = [
                    'url'   => Html::sanitizeURL($image_url),
                    'width' => (int) $header_image_width
                ];

                if ($setting_id === 'header_image' && !empty($image_data)) {
                    // Prepares the setting to save in the database as an array.
                    return [
                        'value' => $image_data,
                        'type'  => 'array'
                    ];
                }

                // Builds the path to an hypothetical double sized image.
                $image_info    = Path::info($image_path);
                $image_path_2x = $image_info['dirname'] . '/' . $image_info['base'] . '-2x.' . $image_info['extension'];

                // If the double sized image exists.
                if (file_exists($image_path_2x)) {
                    $image_url_2x = str_replace($public_path, $public_url, $image_path_2x);

                    if (file_exists($image_path_2x) && getimagesize($image_path_2x) !== false) {
                        return [
                            'value' => Html::sanitizeURL($image_url_2x),
                            'type'  => 'string'
                        ];
                    }
                }
            }
        }

        return [];
    }

    /**
     * Prepares to save the page width option.
     *
     * @param string $unit       The unit used to define the width (px or em)
     * @param string $value      The value of the page width.
     * @param string $setting_id The setting id.
     *
     * @return array The page width and its unit.
     */
    public static function sanitizePageWidth(string $unit, string $value, $setting_id = null): array
    {
        $unit  = $unit ?: 'em';
        $value = $value ? (int) $value : 30;

        if (($unit === 'em' && ($value > 30 && $value <= 80))
            || ($unit === 'px' && ($value >= 480 && $value <= 1280))
        ) {
            if ($setting_id === 'global_unit') {
                return [
                    'value' => Html::escapeHTML($unit),
                    'type'  => 'string'
                ];
            }

            if ($setting_id === 'global_page_width_value') {
                return [
                    'value' => (int) $value,
                    'type'  => 'integer'
                ];
            }

            return [
                'unit'  => Html::escapeHTML($unit),
                'value' => (int) $value
            ];
        }

        return [];
    }

    /**
     * Prepares to save social links.
     *
     * @param string $setting_id The social setting id.
     * @param string $value      The value of the social setting.
     *
     * @return array The value of the setting and its type.
     */
    public static function sanitizeSocialLink(string $setting_id, string $value)
    {
        if ($value === '') {
            return [];
        }

        $id = str_replace('social_', '', $setting_id);

        $site_base = isset(My::socialSites($id)['base']) ? My::socialSites($id)['base'] : '';
        $site_type = isset(My::socialSites($id)['type']) ? My::socialSites($id)['type'] : '';

        switch ($site_type) {
            case 'phone-number':
                if (str_starts_with($value, $site_base) && is_numeric(substr($value, 1))) {
                    return [
                        'value' => Html::escapeHTML($value),
                        'type'  => 'string'
                    ];
                }

                break;
            case 'url':
                if ($site_base !== '' && str_starts_with($value, $site_base)) {
                    return [
                        'value' => Html::escapeURL($value),
                        'type'  => 'string'
                    ];
                } elseif (filter_var($value, FILTER_VALIDATE_URL) !== false) {
                    return [
                        'value' => Html::escapeURL($value),
                        'type'  => 'string'
                    ];
                }

                break;
            case 'username':
                if (str_starts_with($value, $site_base)) {
                    return [
                        'value' => Html::escapeHTML($value),
                        'type'  => 'string'
                    ];
                }

                break;
            case 'signal':
                if (str_starts_with($value, '+') && preg_match('/\+[0-9]+/', $value)) {
                    return [
                        'value' => 'https://signal.me/#p/' . Html::escapeURL($value),
                        'type'  => 'string',
                    ];
                } elseif (str_starts_with($value, 'https://signal.me/')
                    || str_starts_with($value, 'sgnl://signal.me/')
                    || str_starts_with($value, 'https://signal.group/')
                    || str_starts_with($value, 'sgnl://signal.group/')
                ) {
                    return [
                        'value' => Html::escapeURL($value),
                        'type'  => 'string'
                    ];
                }

                break;
            case 'whatsapp':
                if (str_starts_with($value, '+') && preg_match('/\+[0-9]+/', $value)) {
                    return [
                        'value' => 'https://wa.me/' . Html::escapeURL(substr($value, 1)),
                        'type'  => 'string'
                    ];
                } elseif (str_starts_with($value, 'https://wa.me/') || str_starts_with($value, 'whatsapp://wa.me/')) {
                    return [
                        'value' => Html::escapeURL($value),
                        'type'  => 'string'
                    ];
                }

                break;
            case 'x':
                if (preg_match('/\@[\w+]{0,15}/', $value)) {
                    return [
                        'value' => 'https://x.com/' . Html::escapeURL(substr($value, 1)),
                        'type'  => 'string'
                    ];
                } elseif (str_starts_with($value, 'https://x.com/') || str_starts_with($value, 'https://twitter.com/')) {
                    return [
                        'value' => Html::escapeURL($value),
                        'type'  => 'string'
                    ];
                }
        }

        return [];
    }

    /**
     * Checks if the input is an Hex color code.
     *
     * @param string $color The Hex color code.
     *
     * @return bool
     */
    public static function isHexColor(string $color): bool
    {
        if (preg_match('/#[A-Fa-f0-9]{6}/', $color) !== false) {
            return true;
        }

        return false;
    }
}
