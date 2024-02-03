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
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Network\Http;

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
                            'header_image',
                            'header_image2x',
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
                            }
                        }

                        // Saves the setting or drop it.
                        if (!empty($setting_data)) {
                            $setting_value = isset($setting_data['value']) ? $setting_data['value'] : '';
                            $setting_type  = isset($setting_data['type']) ? $setting_data['type'] : '';
                            $setting_label = Html::escapeHTML(My::settingsDefault($setting_id)['title']);
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

                if ($setting_id === 'global_font_family') {
                    echo '<p class=odyssey-font-preview id=odyssey-config-global-font-preview><strong>' . __('config-preview-font') . '</strong> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean iaculis egestas sapien, at pretium erat interdum ullamcorper. Aliquam facilisis dolor sit amet nibh imperdiet vestibulum. Aenean et elementum magna, eget blandit arcu. Morbi tellus tortor, gravida vitae rhoncus nec, scelerisque vitae odio. In nulla mi, efficitur interdum scelerisque ac, ultrices non tortor.</p>';
                } elseif ($setting_id === 'content_text_font') {
                    if ($setting_value === 'same' && isset($saved_settings['global_font_family'])) {
                        $attr = ' style=\'font-family:' . My::fontStack($saved_settings['global_font_family']) . '\';';
                    } else {
                        $attr = '';
                    }
                    echo '<p class=odyssey-font-preview id=odyssey-config-content-font-preview' . $attr . '><strong>' . __('config-preview-font') . '</strong> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean iaculis egestas sapien, at pretium erat interdum ullamcorper. Aliquam facilisis dolor sit amet nibh imperdiet vestibulum. Aenean et elementum magna, eget blandit arcu. Morbi tellus tortor, gravida vitae rhoncus nec, scelerisque vitae odio. In nulla mi, efficitur interdum scelerisque ac, ultrices non tortor.</p>';
                }

                break;

            case 'image' :
                $placeholder = isset($default_settings[$setting_id]['placeholder'])
                ? 'placeholder="' . $default_settings[$setting_id]['placeholder'] . '"'
                : '';

                if (!empty($setting_value) && $setting_value['url'] !== '') {
                    $image_src = $setting_value['url'];
                } else {
                    $image_src = '';
                }

                echo '<label for=', $setting_id, '>',
                $default_settings[$setting_id]['title'],
                '</label>',
                Form::field(
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

            case 'textarea' :
                $placeholder = isset($default_settings[$setting_id]['placeholder'])
                ? 'placeholder="' . $default_settings[$setting_id]['placeholder'] . '"'
                : '';

                echo '<label for=', $setting_id, '>',
                $default_settings[$setting_id]['title'],
                '</label>',
                Form::textArea(
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

        // Header image.
        if ($setting_id === 'header_image') {
            if (!empty($setting_value) && isset($setting_value['url'])) {
                $image_src = $setting_value['url'];
            } else {
                $image_src = '';
            }

            echo '<img alt="', __('header-image-preview-alt'), '" id=', $setting_id, '-src src="', $image_src, '">';

            if (isset($saved_settings['header_image2x'])) {
                echo '<p id=', $setting_id, '-retina>',
                __('header-image-retina-ready'),
                '</p>';
            }

            echo Form::hidden('header_image-url', $image_src);
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
        $settings_ignored = ['header_image2x', 'styles'];

        foreach (My::settingsDefault() as $setting_id => $setting_data) {
            if (!in_array($setting_id, $settings_ignored, true)) {
                // If a sub-section is set.
                if (isset($setting_data['section'][1])) {
                    $settings_render[$setting_data['section'][0]][$setting_data['section'][1]][] = $setting_id;
                } else {
                    $settings_render[$setting_data['section'][0]][] = $setting_id;
                }
            }
        }

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
     * // @param int $header_image_width The width if the header image.
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
        $css_media_contrast_array          = [];
        $css_media_motion_array            = [];
        $css_media_print_array             = [];

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
            $css_root_array[':root']['--font-family'] = My::fontStack($_POST['global_font_family']);
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

            /*
            if (isset($_POST['global_css_border_radius']) && $_POST['global_css_border_radius'] === '1') {
                $css_main_array['#site-image img']['border-radius'] = 'var(--border-radius)';
            }

            if (isset($header_image_width) && $header_image_width >= 100) {
                $css_main_array['#site-image img']['width'] = '100%';
            }
            */
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
        if (isset($_POST['content_postlist_altcolor']) && $_POST['content_postlist_altcolor'] === '1') {
            $css_root_dark_array[':root']['--color-background-even'] = '#000';

            $css_main_array[':is(.entry-list, .entry-list-excerpt) .post:nth-child(even)']['background-color'] = 'var(--color-background-even, #fff)';
        }

        // Post thumbnail
        if (isset($_POST['content_postlist_thumbnail']) && $_POST['content_postlist_thumbnail'] === '1') {
            $css_main_array['.entry-list-img']['aspect-ratio'] = '1/1';
            $css_main_array['.entry-list-img']['height']       = 'auto';
            $css_main_array['.entry-list-img']['object-fit']   = 'cover';
            $css_main_array['.entry-list-img']['overflow']     = 'hidden';
            $css_main_array['.entry-list-img']['width']        = '2rem';
        }

        // Link to reactions
        if (isset($_POST['content_postlist_reactions']) && $_POST['content_postlist_reactions'] === '1') {
            $css_main_array['.post-list-reaction-link']['margin-top'] = '.25rem';
        }

        // Content font family
        if (isset($_POST['content_text_font']) && $_POST['content_text_font'] !== 'same' && $_POST['global_font_family'] !== $_POST['content_text_font']) {
            $css_root_array[':root']['--font-family-content'] = My::fontStack($_POST['content_text_font']);
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
        if (isset($_POST['content_initial_letter']) && $_POST['content_initial_letter'] === '1') {
            $css_supports_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['-moz-initial-letter'] = '2';
            $css_supports_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['-webkit-initial-letter'] = '2';
            $css_supports_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['initial-letter'] = '2';
            $css_supports_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['margin-right'] = '.25rem';
        }

        // Private comments
        if (isset($_POST['reactions_private_comment']) && $_POST['reactions_private_comment'] !== 'disabled') {
            $css_main_array['#react-content .comment-private']['margin-top']      = '2rem';
            $css_main_array['#react-content .comment-private p']['margin-bottom'] = '0';
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
     * Saves the banner.
     *
     * The image is saved as an array which contains:
     * 'url'        => (string) The URL of the image.
     * 'max-width'  => (int) The maximum width of the image
     *                       (inferior or equal to the page width).
     * 'max-height' => (int) The maximum height of the image.
     *
     * @return void
     */
    public static function sanitizeHeaderImage($setting_id, $image_url, $page_width_unit, $page_width_value)
    {
        $default_settings = My::settingsDefault();
        $image_url        = $image_url ?: '';
        $page_width_unit  = $page_width_unit ?: '';
        $page_width_value = $page_width_value ?: '';

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
                $page_width_data = My::getContentWidth($page_width_unit, $page_width_value, true);

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
                    'width' => (int) $header_image_width,
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
                            'value' => $image_url_2x,
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
