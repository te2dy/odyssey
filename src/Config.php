<?php
/**
 * Odyssey, a simple and customizable Dotclear theme.
 *
 * @author    Teddy
 * @copyright 2022-2026 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

namespace Dotclear\Theme\odyssey;

use Dotclear\App;
use Dotclear\Helper\Date;
use Dotclear\Helper\File\Files;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Html\Form\Button;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Color;
use Dotclear\Helper\Html\Form\Div;
use Dotclear\Helper\Html\Form\Fieldset;
use Dotclear\Helper\Html\Form\File;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Hidden;
use Dotclear\Helper\Html\Form\Img;
use Dotclear\Helper\Html\Form\Input;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Link;
use Dotclear\Helper\Html\Form\Option;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Select;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Textarea;
use Dotclear\Helper\Process\TraitProcess;

class Config
{
    use TraitProcess;

    private static array $redirect_query = ['module' => 'odyssey', 'conf' => '1'];

    /**
     * Inits the process.
     *
     * @return bool
     */
    public static function init(): bool
    {
        if (!self::status(My::checkContext(My::CONFIG))) {
            return false;
        }

        My::l10n('admin');

        return self::status();
    }

    /**
     * Processes the multiple requests.
     *
     * @return bool
     */
    public static function process(): bool
    {
        // Loads custom styles and scripts for the configurator page.
        App::behavior()->addBehavior(
            'adminPageHTMLHead',
            function () {
                echo My::cssLoad('admin.min.css'),
                My::jsLoad('admin.min.js');

                // To support textarea color syntax.
                if (App::auth()->prefs()->interface->colorsyntax) {
                    echo App::backend()->page()->jsLoadCodeMirror(
                        App::auth()->prefs()->interface->colorsyntax_theme ?: 'default',
                        true,
                        ['css']
                    );
                }
            }
        );

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
            try {
                if (isset($_POST['save'])) {
                    // If the save button has been clicked.
                    self::_saveSettings($_POST, $_FILES, __('settings-notice-saved'));
                }

                if (isset($_POST['save-config'])) {
                    // If the save configuration file button has been clicked.
                    self::_saveSettings($_POST, $_FILES, '', ['save-config' => 'create-file']);
                }

                if (isset($_POST['reset'])) {
                    // If the reset button has been clicked, removes all saved settings from the database.
                    App::blog()->settings->odyssey->dropAll();

                    // Removes the header image, custom CSS file and their folders.
                    Files::deltree(My::publicFolder('path'));

                    // Clears caches.
                    My::refreshBlog();

                    App::backend()->notices()->addSuccessNotice(__('settings-notice-reset'));

                    App::backend()->url()->redirect('admin.blog.theme', self::$redirect_query);
                }

                if (isset($_POST['import-config'])) {
                    // When the upload configuration file link is clicked, redirects to the upload page.
                    App::backend()->url()->redirect('admin.blog.theme', array_merge(self::$redirect_query, ['config-upload' => '1']));
                }

                if (isset($_POST['config-upload-submit'])) {
                    // When a configuration file has been submitted, uploads it.
                    self::_uploadConfigFile($_FILES);
                }

                if (isset($_POST['config-upload-cancel'])) {
                    // Redirects if the cancel upload button has been clicked.
                    App::backend()->url()->redirect('admin.blog.theme', self::$redirect_query);
                }
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET)) {
            try {
                if (isset($_GET['save-config']) && $_GET['save-config'] === 'create-file') {
                    // Creates a backup file.
                    self::_createBackupFile();
                }

                if (isset($_GET['restore']) && $_GET['restore'] !== 'success') {
                    // Restores a configuration from a backup file listed from /var/odyssey/backups.
                    self::_restoreBackup($_GET['restore']);
                }

                if (isset($_GET['restore_delete_file'])) {
                    // Deletes a configuration file.
                    $delete_file_name = Files::tidyFileName($_GET['restore_delete_file']) . '.json';
                    $odyssey_folder   = My::varFolder('path');
                    $backups_folder   = $odyssey_folder . '/backups/';
                    $delete_file_path = $backups_folder . $delete_file_name;

                    if (Path::real($delete_file_path)) {
                        // Deletes the file and directories if they are empty.
                        unlink($delete_file_path);

                        if (Path::real($backups_folder) && empty(Files::getDirList($backups_folder)['files'])) {
                            Files::deltree($odyssey_folder);
                        }

                        App::backend()->notices()->addSuccessNotice(__('settings-notice-file-deleted'));

                        App::backend()->url()->redirect('admin.blog.theme', self::$redirect_query);
                    }
                }

                if (isset($_GET['restore_delete_all'])) {
                    // Deletes all configuration files.
                    Files::deltree(My::varFolder('path'));

                    App::backend()->notices()->addSuccessNotice(__('settings-notice-files-deleted'));

                    App::backend()->url()->redirect('admin.blog.theme', self::$redirect_query);
                }
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        return true;
    }

    /**
     * Saves all the settings in the database.
     *
     * @param array  $http_post       All parameters sent with the theme configurator form.
     * @param array  $http_files      All files uploaded from the theme configurator form.
     * @param string $notice_success  A text displayed after successful parameter saving.
     * @param array  $redirect_params Parameters to add to the redirection URL after saving.
     * @param bool   $from_json       If we are currently importing a JSON configuration file.
     *
     * @return void
     */
    private static function _saveSettings(
        array  $http_post,
        array  $http_files      = [],
        string $notice_success  = '',
        array  $redirect_params = [],
        bool   $from_json       = false
    ): void
    {
        if (empty($http_post)) {
            return;
        }

        // Merges post and files data.
        $http_requests = array_merge($http_post, $http_files);

        // Puts all $_POST et $_FILES variables in a new array to use them all later.
        $new_settings = [];

        foreach ($http_requests as $setting_id => $setting_value) {
            $new_settings[$setting_id] = $setting_value;
        }

        // To put all sanitized settings later.
        $sanitizedSettings = [];

        $default_settings = My::settingsDefault();

        foreach ($default_settings as $setting_id => $setting_data) {
            $setting_value = $new_settings[$setting_id] ?? null;

            // Sanitizes the setting.
            if ($setting_id !== 'styles') {
                $setting = self::_sanitizeSetting(
                    $setting_id,
                    $setting_data,
                    $setting_value,
                    $new_settings,
                    $from_json
                );

                // Saves the setting in the database or drop it.
                if (!empty($setting)) {
                    App::blog()->settings->odyssey->put(
                        $setting['id'],
                        $setting['value'],
                        $setting['type'],
                        $setting['label']
                    );

                    // Saves the sanitized setting to save styles later.
                    $sanitizedSettings[$setting_id] = $setting['value'];
                } else {
                    App::blog()->settings->odyssey->drop($setting_id);
                }

                // Updates $new_settings array for other actions.
                if (isset($setting['value']) && $setting['value'] !== '') {
                    $new_settings[$setting_id] = $setting['value'];
                } else {
                    unset($new_settings[$setting_id]);
                }
            }

            // Other actions:
            switch ($setting_id) {
                case 'header_image':
                    // Saves header image.
                    if (isset($new_settings['header_image-delete-action'])
                        && $new_settings['header_image-delete-action'] === 'true'
                    ) {
                        // If the delete button has been clicked.
                        self::_deleteHeaderImage();
                    } elseif (isset($setting['value'], $setting_value['tmp_name']) && $setting_value['tmp_name']) {
                        // If an image file has been submitted and the file exists, saves the image.
                        self::_saveHeaderImage($setting_id, $setting, $setting_value['tmp_name']);
                    }

                    break;
                case 'header_image2x':
                    // Saves header image for Retina displays.
                    if (isset($new_settings['header_image'], $setting['value'], $setting_value['tmp_name']) && $setting_value['tmp_name']) {
                        // If a Retina image file has been submitted and the file exists, saves the image.
                        self::_saveHeaderImage($setting_id, $setting, $setting_value['tmp_name']);
                    }

                    break;
                case 'styles':
                    // Saves styles.
                    $styles_custom = $sanitizedSettings['styles_custom'] ?? '';

                    $sanitized_styles       = self::_sanitizeStyles($sanitizedSettings);
                    $sanitized_styles_value = $sanitized_styles['value'] ?? '';
                    $sanitized_styles_type  = $sanitized_styles['type']  ?? null;

                    if ($sanitized_styles_value && $sanitized_styles_type) {
                        App::blog()->settings->odyssey->put(
                            'styles',
                            $sanitized_styles_value,
                            $sanitized_styles_type,
                            $default_settings['styles']['label'],
                            true
                        );
                    } else {
                        App::blog()->settings->odyssey->drop('styles');
                    }

                    if ($sanitized_styles_value || $styles_custom) {
                        self::_customStylesFile($sanitized_styles_value, $styles_custom);
                    }
            }

            // If the /public/odyssey folder has empty subfolders, deletes it.
            if (!is_dir(My::publicFolder('path', '/css')) && !is_dir(My::publicFolder('path', '/img'))) {
                Files::deltree(My::publicFolder('path'));
            }
        }

        // Clears caches.
        My::refreshBlog();

        // Displays a success notice.
        if ($notice_success) {
            App::backend()->notices()->addSuccessNotice($notice_success);
        }

        // Redirects.
        App::backend()->url()->redirect('admin.blog.theme', array_merge(self::$redirect_query, $redirect_params));
    }

    /**
     * Sanitizes a setting to be saved.
     *
     * @param string $setting_id    The id of the current setting to sanitize.
     * @param array  $setting_data  The components of the current parameter.
     * @param mixed  $setting_value The value of the setting to be saved.
     * @param array  $new_settings  All new settings passed through the configurator form.
     * @param bool   $from_json     If we are currently importing a JSON configuration file.
     *
     * @return array The sanitized setting value and type.
     */
    private static function _sanitizeSetting(
        string $setting_id,
        array  $setting_data,
        mixed  $setting_value,
        array  $new_settings,
        bool   $from_json = false
    ): array
    {
        $setting = [
            'id'    => $setting_id,
            'value' => null,
            'type'  => null,
            'label' => $setting_data['label'] ?? null
        ];

        if (!isset($setting_data['sanitizer'])) {
            // Generic sanitization.
            switch ($setting_data['type']) {
                case 'select':
                case 'select_int':
                    $setting_type = 'string';

                    if ($setting_data['type'] === 'select_int') {
                        $setting_value = (int) $setting_value;
                        $setting_type  = 'integer';
                    }

                    if (in_array($setting_value, $setting_data['choices'], true)
                        && $setting_value !== $setting_data['default']
                    ) {
                        $setting['value'] = $setting_value;
                        $setting['type']  = $setting_type;
                    }

                    break;
                case 'checkbox':
                    if ($from_json === false || ($from_json === true && $setting_value !== null)) {
                        $setting_value = $setting_value ? true : false;
                    } else {
                        /**
                         * If we are importing a configuration file and the value is null,
                         * returns null instead of false.
                         */
                        $setting_value = null;
                    }

                    if ($setting_value === true && $setting_data['default'] === false) {
                        $setting['value'] = '1';
                        $setting['type']  = 'boolean';
                    } elseif ($setting_value === false && $setting_data['default'] === true) {
                        $setting['value'] = '0';
                        $setting['type']  = 'boolean';
                    }

                    break;
                case 'range':
                    $setting_value = (int) $setting_value;

                    $setting_min     = $setting_data['range']['min'];
                    $setting_max     = $setting_data['range']['max'];
                    $setting_step    = $setting_data['range']['step'];
                    $setting_default = $setting_data['default'];

                    if ($setting_value >= $setting_min
                        && $setting_value <= $setting_max
                        && $setting_value % $setting_step === 0
                        && $setting_value !== $setting_default
                    ) {
                        $setting['value'] = $setting_value;
                        $setting['type']  = 'integer';
                    }

                    break;
                default:
                    if ($setting_value && $setting_value != $setting_data['default']) {
                        $setting['value'] = filter_var($setting_value, FILTER_SANITIZE_SPECIAL_CHARS);
                        $setting['type']  = 'string';
                    }
            }

            if (isset($setting['id'], $setting['value'], $setting['type'])) {
                return $setting;
            }
        } else {
            // If a custom sanitizer function has been defined, prepare parameters to call the function.
            $params = [];

            $action_delete = false;

            if (isset($new_settings['header_image-delete-action']) && $new_settings['header_image-delete-action'] === 'true') {
                $action_delete = true;
            }

            $header_image_name    = $new_settings['header_image']['name'] ?? My::settings()->header_image['name'] ?? '';
            $header_image2x_img1x = My::settings()->header_image2x['img1x'] ?? null;

            switch ($setting_id) {
                case 'global_page_width_value':
                    $params = [
                        $new_settings['global_unit'] ?? 'em',
                        $setting_value,
                        $setting_id
                    ];

                    break;
                case 'header_image':
                    $header_image_file_data = [];

                    if (isset($new_settings[$setting_id]['tmp_name']) && $new_settings[$setting_id]['tmp_name']) {
                        $header_image_file_data = $new_settings[$setting_id];
                    }

                    if (!empty($header_image_file_data)) {
                        // If an image file has been submitted.
                        $params = [
                            $setting_id,
                            $setting_value,
                            $new_settings
                        ];
                    } elseif ($header_image_name && empty($header_image_file_data)) {
                        // No image is submitted and an image already exists in the database.
                        $params = [
                            $setting_id,
                            $setting_value,
                            $new_settings
                        ];
                    }

                    break;
                case 'header_image2x':
                    if (isset($new_settings[$setting_id]['tmp_name']) && $new_settings[$setting_id]['tmp_name']) {
                        $header_image2x_file_data = $new_settings[$setting_id];
                    } else {
                        $header_image2x_file_data = [];
                    }

                    if (!empty($header_image2x_file_data)) {
                        // If an image is submitted within the form.
                        $params = [
                            $setting_id,
                            $setting_value,
                            $new_settings
                        ];
                    }

                    break;
                case 'styles_custom':
                    $params = [$setting_value];
            }

            if (str_starts_with($setting_id, 'social_')) {
                $params = [$setting_id, $setting_value];
            }

            $setting_callback = [];

            if (!empty($params)) {
                $setting_callback = call_user_func_array(
                    [self::class, '_' . $setting_data['sanitizer']],
                    $params
                );
            }

            $header_image_db_data   = My::settings()->header_image   ?? [];
            $header_image2x_db_data = My::settings()->header_image2x ?? [];

            if (empty($setting_callback)
                && $setting_id === 'header_image'
                && !empty($header_image_db_data)
                && $action_delete === false
            ) {
                // If the header image value passed is null but an image already exists.
                $setting_callback['value'] = $header_image_db_data;
                $setting_callback['type']  = 'array';
            }

            if (empty($setting_callback)
                && $setting_id === 'header_image2x'
                && !empty($header_image2x_db_data)
                && $action_delete === false
                && $header_image2x_img1x === $header_image_name
            ) {
                /**
                 * If the Retina image value passed is null but an image already exists
                 * and corresponds to the main image.
                 */
                $setting_callback['value'] = $header_image2x_db_data;
                $setting_callback['type']  = 'array';
            }

            $setting['value'] = $setting_callback['value'] ?? null;
            $setting['type']  = $setting_callback['type']  ?? null;

            if (isset($setting['id'], $setting['value'], $setting['type'])) {
                return $setting;
            }
        }

        return [];
    }

    /**
     * Sanitizes header image.
     *
     * @param string $setting_id   The id of the current setting.
     * @param array  $image_file   The image file data.
     * @param array  $new_settings All new settings passed through the configurator form.
     *
     * @return array The sanitized header image parameters.
     */
    private static function _sanitizeHeaderImage(
        string $setting_id,
        array  $image_file   = [],
        array  $new_settings = []
    ): array
    {
        if (!empty($image_file) && isset($image_file['size'], $image_file['tmp_name'], $image_file['type'])) {
            $image_size = (int) $image_file['size'];

            if ($image_size > 0
                && isset($image_file['error'])
                && $image_file['error'] === UPLOAD_ERR_OK
            ) {
                $file_name  = Files::tidyFileName($image_file['name']) ?: '';
                $file_path  = $image_file['tmp_name'];
                $file_type  = $image_file['type'];
                $file_url   = My::publicFolder('url', '/img/' . $file_name);
                $image_data = [];

                $mime_types_supported = Files::mimeTypes();

                if ($file_name
                    && $file_path
                    && $file_type
                    && $file_url
                    && file_exists($file_path)
                    && str_starts_with($file_type, 'image/')
                    && in_array($file_type, $mime_types_supported, true)
                    && mime_content_type($file_path) === $file_type
                ) {
                    switch ($setting_id) {
                        case 'header_image':
                            // Gets the dimensions of the image.
                            list($header_image_width) = getimagesize($file_path);

                            /**
                             * Limits the maximum width value of the image
                             * if its superior to the page width,
                             * and sets its height proportionally.
                             */

                            $page_width_value = $new_settings['global_page_width_value'] ? (int) $new_settings['global_page_width_value'] : 480;
                            $page_width_unit  = $new_settings['global_unit'] ?? 'px';
                            $page_width_value = $page_width_unit === 'em' ? $page_width_value * 16 : $page_width_value;

                            if ($header_image_width > $page_width_value) {
                                $header_image_width = $page_width_value;
                            }

                            $image_data = [
                                'name'     => $file_name,
                                'url'      => $file_url,
                                'width'    => (int) $header_image_width
                            ];

                            break;
                        case 'header_image2x':
                            if (isset($new_settings['header_image'], $new_settings['header_image']['name'])
                                && $new_settings['header_image']['name'] !== $file_name
                            ) {
                                $img1x = $new_settings['header_image']['name'] ?? null;

                                if ($img1x) {
                                    $image_data = [
                                        'name'  => $file_name,
                                        'url'   => $file_url,
                                        'img1x' => $img1x
                                    ];
                                }
                            }
                    }

                    if (!empty($image_data)) {
                        // Prepares the setting to save in the database as an array.
                        return [
                            'value' => $image_data,
                            'type'  => 'array'
                        ];
                    }
                }
            }
        }

        return [];
    }

    /**
     * Sanitizes styles entered in the configurator.
     *
     * @param string $css Custom CSS.
     *
     * @return array The sanitized styles parameters.
     */
    private static function _sanitizeCSS(?string $css): array
    {
        if (!$css) {
            return [];
        }

        $css = strip_tags($css);
        $css = htmlspecialchars($css, ENT_HTML5 | ENT_NOQUOTES | ENT_SUBSTITUTE, 'utf-8');
        $css = str_replace('&gt;', '>', $css);

        return [
            'value' => $css,
            'type'  => 'string'
        ];
    }

    /**
     * Adds custom styles to the theme to apply the settings.
     *
     * @param array $settings All sanitized settings after being passed through the configurator form.
     *
     * @return array The styles.
     */
    private static function _sanitizeStyles(array $settings): array
    {
        $css_root_array                       = [];
        $css_root_dark_array                  = [];
        $css_main_array                       = [];
        $css_supported_initial_letter_array   = [];
        $css_unsupported_initial_letter_array = [];
        $css_media_array                      = [];
        $css_media_contrast_array             = [];
        $css_media_motion_array               = [];
        $css_media_print_array                = [];

        $default_settings = My::settingsDefault();

        // Page width
        $page_width_data = self::_sanitizePageWidth(
            $settings['global_unit'] ?? 'em',
            isset($settings['global_page_width_value']) ? (int) $settings['global_page_width_value'] : 30
        );

        $page_width_value = $page_width_data['value'] ?? null;
        $page_width_unit  = $page_width_data['unit']  ?? null;

        if ($page_width_value && $page_width_unit) {
            $css_root_array[':root']['--page-width'] = $page_width_value . $page_width_unit;
        }

        // Font family
        if (isset($settings['global_font_family']) && $settings['global_font_family'] !== 'system') {
            $css_root_array[':root']['--font-family'] = My::fontStack($settings['global_font_family']);
        }

        // Font size
        $font_size_allowed = [80, 90, 110, 120];

        if (isset($settings['global_font_size'])
            && in_array((int) $settings['global_font_size'], $font_size_allowed, true)
        ) {
            $css_root_array[':root']['--font-size'] = My::removeZero((int) $settings['global_font_size'] / 100) . 'em';
        }

        // Font antialiasing
        if (isset($settings['global_font_antialiasing']) && $settings['global_font_antialiasing'] === '1') {
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

        if (isset($settings['global_color_primary']) && $settings['global_color_primary'] === 'custom') {
            // Main text color.
            if (isset($settings['global_color_text_custom'], $default_settings['global_color_primary']['default'])
                && My::isHexColor($settings['global_color_text_custom'])
                && $settings['global_color_text_custom'] !== $default_settings['global_color_primary']['default']
            ) {
                $css_root_array[':root']['--color-text-main'] = $settings['global_color_text_custom'];
            }

            if (isset($settings['global_color_text_dark_custom'], $default_settings['global_color_text_dark_custom']['default'])
                && My::isHexColor($settings['global_color_text_dark_custom'])
                && $settings['global_color_text_dark_custom'] !== $default_settings['global_color_text_dark_custom']['default']
            ) {
                $css_root_array[':root']['--color-text-main-dark'] = $settings['global_color_text_dark_custom'];
            }

            // Text secondary color
            if (isset($settings['global_color_text_secondary_custom'], $default_settings['global_color_text_secondary_custom']['default'])
                && My::isHexColor($settings['global_color_text_secondary_custom'])
                && $settings['global_color_text_secondary_custom'] !== $default_settings['global_color_text_secondary_custom']['default']
            ) {
                $css_root_array[':root']['--color-text-secondary'] = $settings['global_color_text_secondary_custom'];
            }

            if (isset($settings['global_color_text_secondary_dark_custom'], $default_settings['global_color_text_secondary_dark_custom']['default'])
                && My::isHexColor($settings['global_color_text_secondary_dark_custom'])
                && $settings['global_color_text_secondary_dark_custom'] !== $default_settings['global_color_text_secondary_dark_custom']['default']
            ) {
                $css_root_array[':root']['--color-text-secondary-dark'] = $settings['global_color_text_secondary_dark_custom'];
            }

            // Input color
            if (isset($settings['global_color_input_custom'], $default_settings['global_color_input_custom']['default'])
                && My::isHexColor($settings['global_color_input_custom'])
                && $settings['global_color_input_custom'] !== $default_settings['global_color_input_custom']['default']
            ) {
                $css_root_array[':root']['--color-input-background'] = $settings['global_color_input_custom'];
            }

            if (isset($settings['global_color_input_dark_custom'], $default_settings['global_color_input_dark_custom']['default'])
                && My::isHexColor($settings['global_color_input_dark_custom'])
                && $settings['global_color_input_dark_custom'] !== $default_settings['global_color_input_dark_custom']['default']
            ) {
                $css_root_array[':root']['--color-input-background-dark'] = $settings['global_color_input_dark_custom'];
            }

            // Border color
            if (isset($settings['global_color_border_custom'], $default_settings['global_color_border_custom']['default'])
                && My::isHexColor($settings['global_color_border_custom'])
                && $settings['global_color_border_custom'] !== $default_settings['global_color_border_custom']['default']
            ) {
                $css_root_array[':root']['--color-border'] = $settings['global_color_border_custom'];
            }

            if (isset($settings['global_color_border_dark_custom'], $default_settings['global_color_border_dark_custom']['default'])
                && My::isHexColor($settings['global_color_border_dark_custom'])
                && $settings['global_color_border_dark_custom'] !== $default_settings['global_color_border_dark_custom']['default']
            ) {
                $css_root_array[':root']['--color-border-dark'] = $settings['global_color_border_dark_custom'];
            }

            // Background color
            if (isset($settings['global_color_background_custom'], $default_settings['global_color_background_custom']['default'])
                && My::isHexColor($settings['global_color_background_custom'])
                && $settings['global_color_background_custom'] !== $default_settings['global_color_background_custom']['default']
            ) {
                $css_root_array[':root']['--color-background'] = $settings['global_color_background_custom'];
            }

            if (isset($settings['global_color_background_dark_custom'], $default_settings['global_color_background_dark_custom']['default'])
                && My::isHexColor($settings['global_color_background_dark_custom'])
                && $settings['global_color_background_dark_custom'] !== $default_settings['global_color_background_dark_custom']['default']
            ) {
                $css_root_array[':root']['--color-background-dark'] = $settings['global_color_background_dark_custom'];
            }
        }

        // Primary color
        $primary_colors_allowed = ['gray', 'green', 'red'];

        $primary_colors = [
            'light' => [
                'blue'  => 'hsl(226, 80%, 45%)',
                'gray'  => 'hsl(0, 0%, 10%)',
                'green' => 'hsl(120, 75%, 30%)',
                'red'   => 'hsl(0, 90%, 45%)'
            ],
            'light-amplified' => [
                'blue'  => 'hsl(226, 95%, 50%)',
                'gray'  => 'hsl(0, 0%, 28%)',
                'green' => 'hsl(120, 60%, 40%)',
                'red'   => 'hsl(0, 100%, 55%)'
            ],
            'dark' => [
                'blue'  => 'hsl(226, 80%, 70%)',
                'gray'  => 'hsl(0, 0%, 99%)',
                'green' => 'hsl(120, 60%, 80%)',
                'red'   => 'hsl(0, 70%, 85%)'
            ],
            'dark-amplified' => [
                'blue'  => 'hsl(226, 95%, 80%)',
                'gray'  => 'hsl(0, 0%, 80%)',
                'green' => 'hsl(120, 50%, 60%)',
                'red'   => 'hsl(0, 70%, 70%)'
            ]
        ];

        // Variables for the custom color scheme option.
        $color_primary_light           = $primary_colors['light']['blue'];
        $color_primary_amplified_light = $primary_colors['light-amplified']['blue'];
        $color_primary_dark            = $primary_colors['dark']['blue'];
        $color_primary_amplified_dark  = $primary_colors['dark-amplified']['blue'];

        if (isset($settings['global_color_primary'])) {
            if ($settings['global_color_primary'] !== 'custom'
                && in_array($settings['global_color_primary'], $primary_colors_allowed)
            ) {
                // Light
                $color_primary_light = $primary_colors['light'][$settings['global_color_primary']];

                $css_root_array[':root']['--color-primary'] = $color_primary_light;

                // Light & amplified
                if (isset($primary_colors['light-amplified'][$settings['global_color_primary']])) {
                    $color_primary_amplified_light = $primary_colors['light-amplified'][$settings['global_color_primary']];

                    $css_root_array[':root']['--color-primary-amplified'] = $color_primary_amplified_light;
                }

                // Dark
                if (isset($primary_colors['dark'][$settings['global_color_primary']])) {
                    $color_primary_dark = $primary_colors['dark'][$settings['global_color_primary']];

                    $css_root_array[':root']['--color-primary-dark'] = $color_primary_dark;
                }

                // Dark & amplified
                if (isset($primary_colors['dark-amplified'][$settings['global_color_primary']])) {
                    $color_primary_amplified_dark = $primary_colors['dark-amplified'][$settings['global_color_primary']];

                    $css_root_array[':root']['--color-primary-dark-amplified'] = $color_primary_amplified_dark;
                }
            } elseif ($settings['global_color_primary'] === 'custom') {
                if (isset($settings['global_color_primary_custom'], $default_settings['global_color_primary_custom']['default'])
                    && My::isHexColor($settings['global_color_primary_custom'])
                    && $settings['global_color_primary_custom'] !== $default_settings['global_color_primary_custom']['default']
                ) {
                    $color_primary_light = $settings['global_color_primary_custom'];

                    $css_root_array[':root']['--color-primary'] = $color_primary_light;
                }

                if (isset($settings['global_color_primary_amplified_custom'], $default_settings['global_color_primary_amplified_custom']['default'])
                    && My::isHexColor($settings['global_color_primary_amplified_custom'])
                    && $settings['global_color_primary_amplified_custom'] !== $default_settings['global_color_primary_amplified_custom']['default']
                ) {
                    $color_primary_amplified_light = $settings['global_color_primary_amplified_custom'];

                    $css_root_array[':root']['--color-primary-amplified'] = $color_primary_amplified_light;
                }

                if (isset($settings['global_color_primary_dark_custom'], $default_settings['global_color_primary_dark_custom']['default'])
                    && My::isHexColor($settings['global_color_primary_dark_custom'])
                    && $settings['global_color_primary_dark_custom'] !== $default_settings['global_color_primary_dark_custom']['default']
                ) {
                    $color_primary_dark = $settings['global_color_primary_dark_custom'];

                    $css_root_dark_array[':root']['--color-primary-dark'] = $color_primary_dark;
                }

                if (isset($settings['global_color_primary_dark_amplified_custom'], $default_settings['global_color_primary_dark_amplified_custom']['default'])
                    && My::isHexColor($settings['global_color_primary_dark_amplified_custom'])
                    && $settings['global_color_primary_dark_amplified_custom'] !== $default_settings['global_color_primary_dark_amplified_custom']['default']
                ) {
                    $color_primary_amplified_dark = $settings['global_color_primary_dark_amplified_custom'];

                    $css_root_dark_array[':root']['--color-primary-dark-amplified'] = $color_primary_amplified_dark;
                }
            }
        }

        // Color scheme
        if (isset($settings['global_color_scheme'])) {
            switch ($settings['global_color_scheme']) {
                case 'light':
                    $css_root_array[':root']['--color-background-dark']        = '#fafafa';
                    $css_root_array[':root']['--color-text-main-dark']         = '#303030';
                    $css_root_array[':root']['--color-text-secondary-dark']    = '#6c6f78';
                    $css_root_array[':root']['--color-primary-dark']           = $color_primary_light;
                    $css_root_array[':root']['--color-primary-dark-amplified'] = $color_primary_amplified_light;
                    $css_root_array[':root']['--color-input-background-dark']  = '#f2f2f2';
                    $css_root_array[':root']['--color-border-dark']            = '#ccc';

                    break;
                case 'dark':
                    $css_root_array[':root']['--color-background']        = '#16161d';
                    $css_root_array[':root']['--color-text-main']         = '#ccc';
                    $css_root_array[':root']['--color-text-secondary']    = '#969696';
                    $css_root_array[':root']['--color-primary']           = $color_primary_dark;
                    $css_root_array[':root']['--color-primary-amplified'] = $color_primary_amplified_dark;
                    $css_root_array[':root']['--color-input-background']  = '#2b2a33';
                    $css_root_array[':root']['--color-border']            = '#ccc';
            }
        }

        // Transitions
        if (isset($settings['global_css_transition']) && $settings['global_css_transition'] === '1') {
            $css_root_array[':root']['--color-transition'] = 'all .2s ease-in-out';

            $css_media_motion_array[':root']['--color-transition'] = 'unset';
        }

        // Links underline
        if (isset($settings['global_links_underline']) && $settings['global_links_underline'] === '1') {
            $css_root_array[':root']['--link-text-decoration']       = 'underline';
            $css_root_array[':root']['--link-text-decoration-style'] = 'dotted';
        }

        // Border radius
        if (isset($settings['global_border_radius']) && $settings['global_border_radius'] === '1') {
            $css_root_array[':root']['--border-radius'] = '.168em';
        }

        // Header alignment
        $header_align_allowed = ['left', 'right'];

        if (isset($settings['header_align']) && in_array($settings['header_align'], $header_align_allowed, true)) {
            $css_root_array[':root']['--header-align'] = $settings['header_align'];
        }

        // Header image
        if (isset($settings['header_image']) && !empty($settings['header_image'])) {
            $css_main_array['#site-image']['width'] = '100%';

            $css_main_array['#site-image a']['display']       = 'inline-block';
            $css_main_array['#site-image a']['outline-width'] = '.168em';

            $css_main_array['#site-image img']['border-radius'] = 'var(--border-radius, unset)';
            $css_main_array['#site-image img']['display']       = 'inline-block';
        }

        // Burger Menu
        if (isset($settings['header_menu_burger']) && $settings['header_menu_burger'] === '1') {
            if (!isset($settings['header_post_full'])) {
                $css_root_array[':root']['--header-short-direction'] = 'column';
            }

            $css_main_array['#site-nav details']['display'] = 'inline-block';

            $css_main_array['#site-nav summary']['list-style-type'] = 'none';

            $css_main_array['#site-nav summary::after']['content']     = '" ☰"';
            $css_main_array['#site-nav summary::after']['display']     = 'inline-block';
            $css_main_array['#site-nav summary::after']['margin-left'] = '.1em';

            $css_main_array['#site-nav details[open] summary::after']['content'] = '" ×"';

            $css_main_array['#site-nav-content']['margin-top'] = '1em';

            $css_main_array['#site-nav li a']['background-color'] = 'transparent';
            $css_main_array['#site-nav li a']['border']           = '.063rem solid var(--color-primary, #1641ce)';
            $css_main_array['#site-nav li a']['border-radius']    = 'var(--border-radius, unset)';
            $css_main_array['#site-nav li a']['box-sizing']       = 'border-box';
            $css_main_array['#site-nav li a']['display']          = 'inline-block';
            $css_main_array['#site-nav li a']['line-height']      = '1.5';
            $css_main_array['#site-nav li a']['max-width']        = '100%';
            $css_main_array['#site-nav li a']['padding']          = '.25em .5em';
            $css_main_array['#site-nav li a']['transition']       = 'var(--color-transition, unset)';

            $css_main_array['#site-nav li.active a']['text-decoration'] = 'none';

            $css_main_array['#site-nav li.active a']['border-color'] = 'var(--color-primary, hsl(226, 80%, 45%))';
            $css_main_array['#site-nav li.active a']['color']        = 'var(--color-primary, hsl(226, 80%, 45%))';
            $css_main_array['#site-nav li.active a']['cursor']       = 'pointer';

            $css_main_array['#site-nav li a:is(:active, :focus, :hover), #site-nav li.active a']['background-color'] = 'var(--color-primary-amplified, hsl(226, 95%, 50%))';
            $css_main_array['#site-nav li a:is(:active, :focus, :hover), #site-nav li.active a']['border-color']     = 'var(--color-primary-amplified, hsl(226, 95%, 50%))';
            $css_main_array['#site-nav li a:is(:active, :focus, :hover), #site-nav li.active a']['color']            = 'var(--color-background, #fafafa)';

            $css_main_array['#site-nav li a:is(:active, :focus, :hover), #site-nav li.active a']['text-decoration']  = 'none';
            $css_main_array['#site-nav li a:is(:active, :focus, :hover), #site-nav li.active a']['transition']       = 'var(--color-transition, unset)';
        }

        // Breadcrumb
        $breadcrumb_align_allowed = ['left', 'right'];

        if (isset($settings['header_breadcrumb_align']) && in_array($settings['header_breadcrumb_align'], $breadcrumb_align_allowed, true)) {
            $css_root_array[':root']['--breadcrumb-align'] = $settings['header_breadcrumb_align'];
        }

        // Post list type
        if (isset($settings['content_postlist_type'])) {
            switch ($settings['content_postlist_type']) {
                case 'excerpt':
                    $css_main_array['.entry-list-excerpt .post']['margin']  = '1em -1rem';
                    $css_main_array['.entry-list-excerpt .post']['padding'] = '1rem';

                    $css_main_array['.entry-list-excerpt .post:first-child']['margin-top']   = '0';
                    $css_main_array['.entry-list-excerpt .post:last-child']['margin-bottom'] = '0';

                    $css_main_array['.entry-list-excerpt .entry-title']['font-size']    = '1.1rem';
                    $css_main_array['.entry-list-excerpt .entry-title']['margin-block'] = '.5rem';

                    $css_main_array['.entry-list-excerpt .post-excerpt']['margin-block'] = '.5rem';

                    break;
                case 'content':
                    $css_main_array['.entry-list-content .post']['border-bottom']  = '.063em solid var(--color-border, #ccc)';
                    $css_main_array['.entry-list-content .post']['margin-bottom']  = '4em';
                    $css_main_array['.entry-list-content .post']['padding-bottom'] = '2em';

                    $css_main_array['.entry-list-content .post:last-child']['margin-bottom'] = '0';
                    $css_main_array['.entry-list-content .post:last-child']['border-bottom'] = 'none';

                    $css_main_array['.entry-list-content .entry-title']['font-size'] = '1.4em';

                    $css_main_array['.entry-list-content .post-footer']['background-color'] = 'var(--color-input-background, #f2f2f2)';
                    $css_main_array['.entry-list-content .post-footer']['border-radius']    = 'var(--border-radius, unset)';
                    $css_main_array['.entry-list-content .post-footer']['margin-block']     = '2em';
                    $css_main_array['.entry-list-content .post-footer']['padding']          = '1em';

                    $css_main_array['.content-info + .entry-list-content']['margin-top'] = '4em;';
            }
        }

        // Alternate post color
        if (isset($settings['content_postlist_altcolor']) && $settings['content_postlist_altcolor'] === '1') {
            $css_main_array[':is(.entry-list, .entry-list-excerpt) .post:nth-child(even)']['background-color'] = 'var(--color-input-background, #f2f2f2)';
            $css_main_array[':is(.entry-list, .entry-list-excerpt) .post:nth-child(even)']['border-radius'] = 'var(--border-radius, unset)';
        }

        // Post thumbnail
        if (!isset($settings['content_postlist_thumbnail'])) {
            if (isset($settings['content_postlist_type']) && $settings['content_postlist_type'] === 'excerpt') {
                $css_main_array['.post-list-excerpt']['display'] = 'block';

                $css_main_array['.entry-list-excerpt-img']['display']      = 'block';
                $css_main_array['.entry-list-excerpt-img']['margin-block'] = '1rem';

                if (isset($settings['content_images_grayscale']) && $settings['content_images_grayscale'] === '1') {
                    $css_main_array['.entry-list-excerpt-img']['transition']                          = 'var(--color-transition, unset)';
                    $css_main_array['.entry-list-excerpt-img:not(:active, :focus, :hover)']['filter'] = 'grayscale(1)';
                }
            }
        }

        // Link to reactions
        if (isset($settings['content_postlist_reactions']) && $settings['content_postlist_reactions'] === '1') {
            if (!isset($settings['content_postlist_type'])) {
                $css_main_array['.post-list-reaction-link']['margin-top'] = '.5rem';
            }
        }

        // Content font family
        if (isset($settings['content_text_font'])
            && $settings['content_text_font'] !== 'same'
            && $settings['global_font_family'] !== $settings['content_text_font']
        ) {
            $css_root_array[':root']['--font-family-content'] = My::fontStack($settings['content_text_font']);
        }

        // Content font size
        $font_size_allowed = [80, 90, 110, 120];

        if (isset($settings['content_font_size'])
            && in_array((int) $settings['content_font_size'], $font_size_allowed, true)
        ) {
            $css_root_array[':root']['--content-font-size'] = My::removeZero((int) $settings['content_font_size'] / 100) . 'em';
        }

        // Text align
        if (isset($settings['content_text_align'])) {
            switch ($settings['content_text_align']) {
                case 'justify' :
                    $css_root_array[':root']['--text-align'] = 'justify';

                    $css_media_contrast_array[':root']['--text-align'] = 'left';

                    break;
                case 'justify-not-mobile' :
                    $css_root_array[':root']['--text-align'] = 'justify';

                    $css_media_array[':root']['--text-align'] = 'left';

                    $css_media_contrast_array[':root']['--text-align'] = 'left';
            }
        }

        // Line Height
        $line_height_allowed = [125, 175];

        if (isset($settings['content_line_height']) && in_array((int) $settings['content_line_height'], $line_height_allowed, true)) {
            $css_root_array[':root']['--text-line-height'] = (int) $settings['content_line_height'] / 100;
        }

        // Hyphenation.
        if (isset($settings['content_hyphens']) && $settings['content_hyphens'] !== 'disabled') {
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

            if ($settings['content_hyphens'] === 'enabled-not-mobile') {
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
        if (isset($settings['content_initial_letter']) && $settings['content_initial_letter'] === '1') {
            $css_supported_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['-moz-initial-letter'] = '2';
            $css_supported_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['-webkit-initial-letter'] = '2';
            $css_supported_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['initial-letter'] = '2';
            $css_supported_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['margin-right'] = '.25rem';

            if (isset($settings['content_line_height']) && in_array((int) $settings['content_line_height'], [125, 175], true)) {
                $content_line_height = (int) $settings['content_line_height'] / 100;
            } else {
                $content_line_height = 1.5;
            }

            $css_unsupported_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['float']     = 'left';
            $css_unsupported_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['font-size'] = $content_line_height * 2 . 'em';
        }

        // Wide images
        if (isset($settings['content_images_wide']) && $settings['content_images_wide'] === '1') {
            $css_main_array['body']['overflow-x'] = 'hidden';

            $css_main_array['.odyssey-img-wide']['display']     = 'block';
            $css_main_array['.odyssey-img-wide']['margin-left'] = '50%';
            $css_main_array['.odyssey-img-wide']['transform']   = 'translateX(-50%)';
            $css_main_array['.odyssey-img-wide']['max-width']   = '95vw';
        }

        // Grayscale images
        if (isset($settings['content_images_grayscale']) && $settings['content_images_grayscale'] === '1') {
            $css_main_array['.content-text img']['transition']                          = 'var(--color-transition, unset)';
            $css_main_array['.content-text img:not(:active, :focus, :hover)']['filter'] = 'grayscale(1)';

            if (isset($settings['content_postlist_thumbnail']) && $settings['content_postlist_thumbnail'] === '1'
                && isset($settings['content_postlist_type']) && $settings['content_postlist_type'] === 'one-line'
            ) {
                $css_main_array['.entry-list-img']['transition']                          = 'var(--color-transition, unset)';
                $css_main_array['.entry-list-img:not(:active, :focus, :hover)']['filter'] = 'grayscale(1)';
            }
        }

        // Footer align
        if (!isset($settings['footer_enabled'])) {
            if (isset($settings['footer_align']) && in_array($settings['footer_align'], ['center', 'right'], true)) {
                $css_root_array[':root']['--footer-align'] = $settings['footer_align'];
            }
        }

        // Displays Simple Icons styles if necessary.
        $simpleicons_styles = false;

        // Checks if a link has been set.
        foreach (My::socialSites() as $id => $data) {
            if (isset($settings['social_' . $id])) {
                if (isset($settings['reactions_other'], $settings['reactions_other_' . $id])
                    && $settings['reactions_other'] !== 'disabled'
                ) {
                    if (!empty(self::_sanitizeSocial('social_' . $id, $settings['social_' . $id]))) {
                        if (My::svgIcons($id)['creator'] === 'simpleicons') {
                            $simpleicons_styles  = true;
                        }
                    }
                }
            }
        }

        // Other reactions
        if (isset($settings['reactions_other']) && $settings['reactions_other'] !== 'disabled') {
            $css_main_array['.reactions-button .social-icon-si']['fill'] = 'var(--color-primary, hsl(226, 80%, 45%))';
            $css_main_array['.reactions-button:is(:focus-visible) .reactions-button-icon.social-icon-si']['fill'] = 'var(--color-background, #fafafa)';
        }

        // Footer links
        $footer_social_links = false;
        $feathericons_styles = false;

        if (isset($settings['footer_feed']) && $settings['footer_feed'] !== 'disabled') {
            $footer_social_links = true;
            $feathericons_styles = true;
        }

        foreach (My::socialSites() as $id => $data) {
            if (isset($settings['social_' . $id])) {
                if (!isset($settings['footer_social_' . $id])) {
                    if (!empty(self::_sanitizeSocial('social_' . $id, $settings['social_' . $id]))) {
                        $footer_social_links = true;

                        if (My::svgIcons($id)['creator'] === 'simpleicons') {
                            $simpleicons_styles = true;
                        } elseif (My::svgIcons($id)['creator'] === 'feathericons') {
                            $feathericons_styles = true;
                        }
                    }
                }
            }

            if ($footer_social_links && $simpleicons_styles && $feathericons_styles) {
                break;
            }
        }

        if ($simpleicons_styles) {
            $css_main_array['.social-icon-si']['border']          = '0';
            $css_main_array['.social-icon-si']['stroke']          = 'none';
            $css_main_array['.social-icon-si']['stroke-linecap']  = 'round';
            $css_main_array['.social-icon-si']['stroke-linejoin'] = 'round';
            $css_main_array['.social-icon-si']['stroke-width']    = '0';
            $css_main_array['.social-icon-si']['width']           = '1rem';
            $css_main_array['.social-icon-si']['transition']      = 'var(--color-transition, unset)';

            $css_media_contrast_array['.reactions-button:is(:active, :focus, :hover) .reactions-button-icon.social-icon-si']['fill'] = 'var(--color-background, #fafafa)';
        }

        if ($footer_social_links) {
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
            $css_main_array['.footer-social-links-icon-container']['border-radius']    = 'var(--border-radius, unset)';
            $css_main_array['.footer-social-links-icon-container']['display']          = 'flex';
            $css_main_array['.footer-social-links-icon-container']['justify-content']  = 'center';
            $css_main_array['.footer-social-links-icon-container']['width']            = '1.5rem';
            $css_main_array['.footer-social-links-icon-container']['height']           = '1.5rem';
            $css_main_array['.footer-social-links-icon-container']['transition']       = 'var(--color-transition, unset)';

            $css_media_contrast_array['.footer-social-links-icon-container']['border'] = '.063rem solid var(--color-border, #ccc)';

            if ($simpleicons_styles) {
                $css_main_array['.footer-social-links-icon-container .footer-social-links-icon-si']['fill'] = 'var(--color-text-main, #303030)';
            }

            if ($feathericons_styles) {
                $css_main_array['.footer-social-links-icon-container .footer-social-links-icon-fi']['stroke'] = 'var(--color-text-main, #303030)';
            }

            $css_main_array['.footer-social-links a:active .footer-social-links-icon-container, .footer-social-links a:focus .footer-social-links-icon-container, .footer-social-links a:hover .footer-social-links-icon-container']['background-color'] = 'var(--color-primary, hsl(226, 80%, 45%))';

            $css_main_array['.footer-social-links a:active .footer-social-links-icon-container, .footer-social-links a:focus .footer-social-links-icon-container, .footer-social-links a:hover .footer-social-links-icon-container']['transition'] = 'var(--color-transition, unset)';

            $css_main_array['.footer-social-links a']['border-bottom'] = 'none';

            $css_main_array['.footer-social-links a:active, .footer-social-links a:focus, .footer-social-links a:hover']['border-bottom'] = 'none';

            $css_media_contrast_array['.footer-social-links a:is(:active, :focus, :hover) .footer-social-links-icon-container']['border-color'] = 'var(--color-primary-amplified, hsl(226, 95%, 50%))';

            if ($simpleicons_styles) {
                $css_main_array['.footer-social-links a:active .footer-social-links-icon-si, .footer-social-links a:focus .footer-social-links-icon-si, .footer-social-links a:hover .footer-social-links-icon-si']['fill'] = 'var(--color-background, #fafafa)';

                $css_main_array['.footer-social-links a:active .footer-social-links-icon-si, .footer-social-links a:focus .footer-social-links-icon-si, .footer-social-links a:hover .footer-social-links-icon-si']['transition'] = 'var(--color-transition, unset)';
            }

            if ($feathericons_styles) {
                $css_main_array['.footer-social-links a:active .footer-social-links-icon-fi, .footer-social-links a:focus .footer-social-links-icon-fi, .footer-social-links a:hover .footer-social-links-icon-fi']['stroke'] = 'var(--color-background, #fafafa)';

                $css_main_array['.footer-social-links a:active .footer-social-links-icon-fi, .footer-social-links a:focus .footer-social-links-icon-fi, .footer-social-links a:hover .footer-social-links-icon-fi']['transition'] = 'var(--color-transition, unset)';
            }
        }

        $css  = !empty($css_root_array) ? My::stylesArrToStr($css_root_array) : '';
        $css .= !empty($css_root_dark_array) ? '@media (prefers-color-scheme:dark){' . My::stylesArrToStr($css_root_dark_array) . '}' : '';
        $css .= !empty($css_main_array) ? My::stylesArrToStr($css_main_array) : '';
        $css .= !empty($css_supported_initial_letter_array) ? '@supports (initial-letter:2) or (-webkit-initial-letter:2) or (-moz-initial-letter:2){' . My::stylesArrToStr($css_supported_initial_letter_array) . '}' : '';
        $css .= !empty($css_unsupported_initial_letter_array) ? '@supports not (initial-letter:2){' . My::stylesArrToStr($css_unsupported_initial_letter_array) . '}' : '';
        $css .= !empty($css_media_array) ? '@media (max-width:34em){' . My::stylesArrToStr($css_media_array) . '}' : '';
        $css .= !empty($css_media_contrast_array) ? '@media (prefers-contrast:more){' . My::stylesArrToStr($css_media_contrast_array) . '}' : '';
        $css .= !empty($css_media_motion_array) ? '@media (prefers-reduced-motion:reduce){' . My::stylesArrToStr($css_media_motion_array) . '}' : '';
        $css .= !empty($css_media_print_array) ? '@media print{' . My::stylesArrToStr($css_media_print_array) . '}' : '';

        $css = My::cssMinify($css);

        if ($css) {
            return self::_sanitizeCSS($css);
        }

        return [];
    }

    /**
     * Saves header image.
     *
     * @param string  $setting_id    The id of the current setting.
     * @param array   $image_file    The image data.
     * @param ?string $img_file_path The image temporary path.
     *
     * @return void
     */
    private static function _saveHeaderImage(string $setting_id, array $image_data, ?string $img_file_path): void
    {
        $img_folder_path = My::publicFolder('path', '/img');

        switch ($setting_id) {
            case 'header_image':
                /**
                 * If an image file alrealdy exists, removes it
                 * and its folder; then, created the folder again
                 * to store the image later.
                 */
                Files::deltree($img_folder_path);
                Files::makeDir($img_folder_path, true);

                $image_name     = $image_data['value']['name'] ?? null;
                $image_path     = $img_folder_path . '/' . $image_name;
                $image_url      = My::publicFolder('url', '/img/' . $image_name);

                if ($img_file_path && $image_name) {
                    move_uploaded_file($img_file_path, $image_path);
                }

                break;
            case 'header_image2x':
                $img_folder_path = My::publicFolder('path', '/img');
                $img_folder_url  = My::publicFolder('url', '/img');

                $image2x_path_tmp = $img_file_path;
                $image2x_name     = $image_data['value']['name'] ?? null;
                $image2x_path     = $img_folder_path . '/' . $image2x_name;
                $image2x_url      = $img_folder_url  . '/' . $image2x_name;

                $header_image_name = App::backend()->header_image['name'] ?? '';

                if ($image2x_path_tmp && $image2x_name && $image2x_name !== $header_image_name) {
                    move_uploaded_file($image2x_path_tmp, $image2x_path);
                }
        }
    }

    /**
     * Deletes the /img folder in /public/odyssey.
     *
     * @return void
     */
    private static function _deleteHeaderImage(): void
    {
        Files::deltree(My::publicFolder('path', '/img'));
    }

    /**
     * Prepares to save the page width option.
     *
     * @param ?string $unit       The unit used to define the width (px or em)
     * @param ?int    $value      The value of the page width.
     * @param ?string $setting_id The setting id.
     *
     * @return array The page width and its unit.
     */
    private static function _sanitizePageWidth(?string $unit, ?int $value, ?string $setting_id = null): array
    {
        $units_allowed = ['em', 'px'];

        $unit  = in_array($unit, $units_allowed, true) ? $unit : 'em';
        $value = $value ?: 30;

        if (($unit === 'em' && ($value > 30 && $value <= 80))
            || ($unit === 'px' && ($value >= 480 && $value <= 1280))
        ) {
            if ($setting_id === 'global_unit') {
                return [
                    'value' => $unit,
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
                'unit'  => $unit,
                'value' => (int) $value
            ];
        }

        return [];
    }

    /**
     * Prepares to save social links.
     *
     * @param ?string $setting_id The social setting id.
     * @param ?string $value      The value of the social setting.
     *
     * @return array The value of the setting and its type.
     */
    private static function _sanitizeSocial(?string $setting_id, ?string $value): array
    {
        if (!$setting_id || !$value) {
            return [];
        }

        $id = str_replace('social_', '', $setting_id);

        $site_base = My::socialSites($id)['base'] ?? '';
        $site_type = My::socialSites($id)['type'] ?? '';

        switch ($site_type) {
            case 'email':
                if (str_contains($value, '@') && str_contains($value, '.') && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return [
                        'value' => filter_var($value, FILTER_VALIDATE_EMAIL),
                        'type'  => 'string'
                    ];
                }

                break;
            case 'matrix':
                if (str_starts_with($value, 'https://matrix.to/#/') && filter_var($value, FILTER_VALIDATE_URL)) {
                    return [
                        'value' => filter_var($value, FILTER_SANITIZE_URL),
                        'type'  => 'string'
                    ];
                }

                break;
            case 'phone-number':
                if (str_starts_with($value, $site_base) && is_numeric(substr($value, 1))) {
                    return [
                        'value' => '+' . str_replace('+', '', (int) $value),
                        'type'  => 'string'
                    ];
                }

                break;
            case 'signal':
                if (str_starts_with($value, '+') && preg_match('/\+[0-9]+/', $value)) {
                    return [
                        'value' => My::escapeURL('https://signal.me/#p/' . $value),
                        'type'  => 'string',
                    ];
                } elseif (filter_var($value, FILTER_VALIDATE_URL)
                    && (str_starts_with($value, 'https://signal.me/')
                    || str_starts_with($value, 'sgnl://signal.me/')
                    || str_starts_with($value, 'https://signal.group/')
                    || str_starts_with($value, 'sgnl://signal.group/')
                )) {
                    return [
                        'value' => filter_var($value, FILTER_SANITIZE_URL),
                        'type'  => 'string'
                    ];
                }

                break;
            case 'telegram':
                if (preg_match('/\@[\w+]{0,15}/', $value)) {
                    return [
                        'value' => filter_var('https://t.me/' . substr($value, 1), FILTER_SANITIZE_URL),
                        'type'  => 'string'
                    ];
                } elseif (filter_var($value, FILTER_VALIDATE_URL) && str_starts_with($value, 'https://t.me/')) {
                    return [
                        'value' => filter_var($value, FILTER_SANITIZE_URL),
                        'type'  => 'string'
                    ];
                }

                break;
            case 'url':
                if ($site_base && str_starts_with($value, $site_base) && filter_var($value, FILTER_VALIDATE_URL)) {
                    return [
                        'value' => filter_var($value, FILTER_SANITIZE_URL),
                        'type'  => 'string'
                    ];
                } elseif (filter_var($value, FILTER_VALIDATE_URL)) {
                    return [
                        'value' => filter_var($value, FILTER_SANITIZE_URL),
                        'type'  => 'string'
                    ];
                }

                break;
            case 'whatsapp':
                if (str_starts_with($value, '+') && preg_match('/\+[0-9]+/', $value)) {
                    return [
                        'value' => filter_var('https://wa.me/' . substr($value, 1), FILTER_SANITIZE_URL),
                        'type'  => 'string'
                    ];
                } elseif (filter_var($value, FILTER_VALIDATE_URL)
                    && (str_starts_with($value, 'https://wa.me/') || str_starts_with($value, 'whatsapp://wa.me/'))
                ) {
                    return [
                        'value' => filter_var($value, FILTER_SANITIZE_URL),
                        'type'  => 'string'
                    ];
                }

                break;
            case 'x':
                if (preg_match('/\@[\w+]{0,15}/', $value)) {
                    return [
                        'value' => filter_var('https://x.com/' . substr($value, 1), FILTER_SANITIZE_URL),
                        'type'  => 'string'
                    ];
                } elseif (filter_var($value, FILTER_VALIDATE_URL)
                    && (str_starts_with($value, 'https://x.com/') || str_starts_with($value, 'https://twitter.com/'))
                ) {
                    return [
                        'value' => filter_var($value, FILTER_SANITIZE_URL),
                        'type'  => 'string'
                    ];
                }
        }

        return [];
    }

    /**
     * Saves a custom CSS in the public folder of Dotclear, or deletes it.
     *
     * @param string $css_options Styles from configurator options.
     * @param string $css_custom  Styles entered in the textarea.
     *
     * @return void
     */
    private static function _customStylesFile(string $css_options, string $css_custom): void
    {
        $css_theme_file_path = My::themeFolder('path', '/style.min.css');

        if (Path::real($css_theme_file_path)) {
            // If the theme stylesheet is readable.
            $css  = $css_options;
            $css .= file_get_contents($css_theme_file_path) ?: '';
            $css  = trim($css);
            $css .= My::cssMinify($css_custom) . PHP_EOL;

            $css_folder    = My::id() . '/css';
            $css_file_name = 'style.min';

            // Deletes the previous CSS file if it exists.
            App::backend()->themeConfig()->dropCss($css_folder, $css_file_name);

            if ($css && App::backend()->themeConfig()->canWriteCss($css_folder, true)) {
                // Creates the CSS file.
                App::backend()->themeConfig()->writeCss($css_folder, $css_file_name, $css);
            } else {
                // Deletes the CSS folder.
                Files::deltree(App::backend()->themeConfig()->cssPath($css_folder));
            }
        }
    }

    /**
     * Uploads a configuration from a JSON file.
     *
     * @param array $file_data The JSON file data from $_FILES.
     *
     * @return void
     */
    private static function _uploadConfigFile(array $file_data = []): void
    {
        if (isset($file_data['config-upload-file']['tmp_name'], $file_data['config-upload-file']['type'], $file_data['config-upload-file']['error']) && $file_data['config-upload-file']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_path  = $file_data['config-upload-file']['tmp_name'];
            $file_type      = $file_data['config-upload-file']['type'];
            $json_content   = file_get_contents($file_tmp_path);
            $settings_array = [];

            if ($file_type === 'application/json' && $json_content) {
                // Puts all configuration file settings in an array.
                $settings_array = json_decode($json_content, true);

                if (!empty($settings_array)) {
                    // First, drops all settings to clean the database.
                    App::blog()->settings->odyssey->dropAll();

                    // Then, imports all settings.
                    self::_saveSettings($settings_array, [], __('settings-notice-upload-success'), [], true);
                } else {
                    App::backend()->notices()->addErrorNotice(__('settings-notice-upload-file-not-valid'));

                    App::backend()->url()->redirect(
                        'admin.blog.theme',
                        array_merge(self::$redirect_query, ['config-upload' => '1'])
                    );
                }
            } else {
                // If the uploaded file is not a JSON file.
                App::backend()->notices()->addErrorNotice(__('settings-notice-upload-file-not-valid'));

                App::backend()->url()->redirect(
                    'admin.blog.theme',
                    array_merge(self::$redirect_query, ['config-upload' => '1'])
                );
            }
        } else {
            // If there is no file uploaded.
            App::backend()->notices()->addErrorNotice(__('settings-notice-upload-no-file'));

            App::backend()->url()->redirect(
                'admin.blog.theme',
                array_merge(self::$redirect_query, ['config-upload' => '1'])
            );
        }
    }

    /**
     * Restores a configuration from a backup file.
     *
     * @param string $file_name The name of the backup file.
     *
     * @return void
     */
    private static function _restoreBackup(string $file_name): void
    {
        $restore_file_path    = Path::real(My::varFolder('path', '/backups/' . $file_name . '.json'));
        $restore_file_content = file_get_contents($restore_file_path);

        if ($restore_file_path && $restore_file_content && $restore_file_content !== '[]') {
            $settings_array = json_decode($restore_file_content, true);

            if (!empty($settings_array)) {
                // Drops all settings.
                App::blog()->settings->odyssey->dropAll();

                // Imports all settings.
                self::_saveSettings($settings_array, [], __('settings-notice-restore-success'), [], true);
            }
        } else {
            // If the file is empty.
            App::backend()->notices()->addErrorNotice(__('settings-notice-restore-error'));

            App::backend()->url()->redirect('admin.blog.theme', self::$redirect_query);
        }
    }

    /**
     * Creates a JSON backup file.
     *
     * The file will be uploaded in /var/odyssey/backups.
     *
     * @return void
     */
    private static function _createBackupFile(): void
    {
        // Retrieves all custom settings to put in the file.
        $custom_settings = [];

        foreach (My::settingsDefault() as $setting_id => $setting_data) {
            if (My::settings()->$setting_id !== null) {
                $custom_settings[$setting_id] = My::settings()->$setting_id;
            }
        }

        if (count($custom_settings) === 0) {
            // If there is no custom settings.
            App::backend()->notices()->addErrorNotice(__('settings-notice-save-fail'));
        } else {
            $backups_path = My::varFolder('path', '/backups');

            // Creates the /var/odyssey/backups folder if it doesn't exist.
            Files::makeDir($backups_path, true);

            // Sets the name of the backup file with date and time.
            $time = str_replace(':', '', Date::str('%Y%m%d', time(), App::blog()->settings()->system->blog_timezone) . '-' . Date::str('%T', time(), App::blog()->settings()->system->blog_timezone));

            $file_name = Files::tidyFileName($time . '-settings');

            $backups_path .= '/' . $file_name . '.json';

            // Creates the JSON file and saves it in the backups folder.
            Files::putContent($backups_path, json_encode($custom_settings));

            $notice  = '<p>' . sprintf(__('settings-notice-save-success'), My::id(), '#odyssey-backups') . '</p>';
            $notice .= '<p>' . __('settings-notice-save-success-warning') . '</p>';
            $notice .= '<a class="button submit" href=' . My::displayAttr(urldecode(App::backend()->page()->getVF(My::varFolder('vf', '/backups/' . $file_name . '.json'))), 'url') . ' download=' . My::displayAttr($file_name) . '>';
            $notice .= __('settings-notice-save-success-link');
            $notice .= '</a>';

            App::backend()->notices()->addNotice('success', $notice, ['divtag' => true]);

            App::backend()->url()->redirect('admin.blog.theme', self::$redirect_query);
        }
    }

    /**
     * Renders the setting in the configurator page.
     *
     * @param string $setting_id The id of the setting to display.
     *
     * @return array The setting.
     */
    public static function settingRender(string $setting_id): array
    {
        $default_settings = My::settingsDefault();
        $setting_value    = My::settings()->$setting_id ?? $default_settings[$setting_id]['default'];
        $placeholder      = $default_settings[$setting_id]['placeholder'] ?? '';
        $the_setting      = [];

        // If the setting does not exist.
        if (!empty(My::settingsDefault($setting_id))) {
            switch ($default_settings[$setting_id]['type']) {
                case 'checkbox' :
                    $the_setting[] = (new Para())
                        ->id($setting_id . '-input')
                        ->items([
                            (new Checkbox($setting_id, (bool) $setting_value))
                                ->label(
                                    (new Label($default_settings[$setting_id]['title'], 3))
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
                        $the_setting[] = (new Text('p', $default_settings[$setting_id]['description'] . $checkbox_default))
                            ->class('form-note')
                            ->id($setting_id . '-description');
                    } elseif ($checkbox_default !== '') {
                        $the_setting[] = (new Text('p', $checkbox_default))
                            ->class('form-note')
                            ->id($setting_id . '-description');
                    }

                    break;
                case 'select' :
                case 'select_int' :
                    $combo = [];

                    foreach ($default_settings[$setting_id]['choices'] as $name => $value) {
                        $combo[] = new Option($name, Html::escapeHTML($value));
                    }

                    $the_setting[] = (new Para())
                        ->id($setting_id . '-input')
                        ->items([
                            (new Select($setting_id))
                                ->default((string) Html::escapeHTML($setting_value))
                                ->items($combo)
                                ->label(
                                    new Label($default_settings[$setting_id]['title'], 2)
                                )
                            ]
                        );

                    // Displays a preview for font changes.
                    if ($setting_id === 'global_font_family' || $setting_id === 'content_text_font') {
                        $preview_string = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean iaculis egestas sapien, at pretium erat interdum ullamcorper. Aliquam facilisis dolor sit amet nibh imperdiet vestibulum. Aenean et elementum magna, eget blandit arcu. Morbi tellus tortor, gravida vitae rhoncus nec, scelerisque vitae odio. In nulla mi, efficitur interdum scelerisque ac, ultrices non tortor.';

                        if ($setting_id === 'global_font_family') {
                            $the_setting[] = (new Para())
                                ->class('odyssey-font-preview')
                                ->id('odyssey-config-global-font-preview')
                                ->items([
                                    new Text('strong', __('config-preview-font')),
                                    new Text(null, ' ' . $preview_string)
                                ]);
                        } else {
                            $the_setting[] = (new Para())
                                ->class('odyssey-font-preview')
                                ->id('odyssey-config-content-font-preview')
                                ->items([
                                    new Text('strong', __('config-preview-font')),
                                    new Text(null, ' ' . $preview_string)
                                ]);
                        }
                    }

                    if (isset($default_settings[$setting_id]['description']) && $default_settings[$setting_id]['description'] !== '') {
                        $the_setting[] = (new Text('p', $default_settings[$setting_id]['description']))
                            ->class('form-note')
                            ->id($setting_id . '-description');
                    }

                    break;
                case 'image' :
                    $the_setting[] = (new Para())
                        ->id($setting_id . '-input')
                        ->items([
                            (new File($setting_id))
                                ->label(
                                    (new Label($default_settings[$setting_id]['title'], 2))
                                        ->for($setting_id)
                                )
                        ]);

                    if (isset($default_settings[$setting_id]['description']) && $default_settings[$setting_id]['description'] !== '') {
                        $the_setting[] = (new Text('p', $default_settings[$setting_id]['description']))
                            ->class('form-note')
                            ->id($setting_id . '-description');
                    }

                    break;
                case 'color' :
                    $setting_value_input = $setting_value !== $default_settings[$setting_id]['default']
                    ? $setting_value
                    : '';

                    $the_setting[] = (new Para())
                        ->id($setting_id . '-input')
                        ->class('odyssey-color-setting')
                        ->items([
                            (new Label($default_settings[$setting_id]['title'], 0))
                                ->extra('for=' . $setting_id . '-text'),
                            new Color($setting_id, Html::escapeHTML($setting_value)),
                            (new Input($setting_id . '-text'))
                                ->placeholder($placeholder)
                                ->value(Html::escapeHTML($setting_value_input)),
                            new Button($setting_id . '-default-button', __('settings-colors-reset')),
                            new Hidden($setting_id . '-default-value', $default_settings[$setting_id]['default'])
                        ]);

                    break;
                case 'textarea' :
                    // Expands CSS textarea.
                    $rows = $setting_id !== 'styles_custom' ? 3 : 10;

                    $the_setting[] = (new Para())
                        ->id($setting_id . '-input')
                        ->items([
                            (new Label($default_settings[$setting_id]['title'], 2))
                                ->for($setting_id),
                            (new Textarea($setting_id, Html::escapeHTML($setting_value)))
                                ->cols(60)
                                ->placeholder($placeholder)
                                ->rows($rows)
                        ]);

                    if (isset($default_settings[$setting_id]['description']) && $default_settings[$setting_id]['description'] !== '') {
                        $the_setting[] = (new Text('p', $default_settings[$setting_id]['description']))
                            ->class('form-note')
                            ->id($setting_id . '-description');
                    }

                    break;
                case 'range' :
                    $range_default = [
                        'max'   => (int) $default_settings[$setting_id]['range']['max'],
                        'min'   => (int) $default_settings[$setting_id]['range']['min'],
                        'step'  => (int) $default_settings[$setting_id]['range']['step'],
                        'unit'  => $default_settings[$setting_id]['range']['unit'],
                        'value' => (int) My::settings()->$setting_id ?: (int) $default_settings[$setting_id]['default']
                    ];

                    if ($setting_id === 'global_page_width_value' && My::settings()->global_unit === 'px') {
                        // Specific values for page width in px.
                        $range_default['max']  = 1280;
                        $range_default['min']  = 480;
                        $range_default['step'] = 2;
                        $range_default['unit'] = 'px';
                    }

                    $range_default_output = sprintf(
                        __('settings-input-width-range-output'),
                        '<span id=' . $setting_id . '-output-value>' . $range_default['value'] . '</span>',
                        '<span id=' . $setting_id . '-output-unit>' . $range_default['unit'] . '</span>'
                    );

                    $the_setting[] = (new Para())
                        ->id($setting_id . '-input')
                        ->items([
                            (new Label($default_settings[$setting_id]['title'], 2))
                                ->for($setting_id),
                            (new Input($setting_id, 'range'))
                                ->max($range_default['max'])
                                ->min($range_default['min'])
                                ->step($range_default['step'])
                                ->value($range_default['value']),
                            (new Div(null, 'output'))
                                ->id($setting_id . '-output')
                                ->items([new Text(null, $range_default_output)])
                        ]);

                    if (isset($default_settings[$setting_id]['description']) && $default_settings[$setting_id]['description'] !== '') {
                        $the_setting[] = (new Text('p', $default_settings[$setting_id]['description']))
                            ->class('form-note')
                            ->id($setting_id . '-description');
                    }

                    break;
                default :
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
                                ->value(Html::escapeHTML($setting_value))
                        ]);

                    if (isset($default_settings[$setting_id]['description']) && $default_settings[$setting_id]['description'] !== '') {
                        $the_setting[] = (new Text('p', $default_settings[$setting_id]['description']))
                            ->class('form-note')
                            ->id($setting_id . '-description');
                    }
            }
        }

        // Header image.
        if ($setting_id === 'header_image') {
            $image_src = $setting_value['url'] ?? '';

            if (My::settings()->header_image) {
                $image2x_src = My::settings()->header_image2x['url'] ?? null;
                $srcset      = '';

                if ($image2x_src) {
                    $srcset = 'srcset="' . My::escapeURL($image_src) . ' 1x, ' . My::escapeURL($image2x_src) . ' 2x" sizes=100vw';
                }

                $the_setting[] = (new Para())
                    ->id('header_image-preview')
                    ->items([
                        (new Img(My::escapeURL($image_src), 'header_image-src'))
                            ->alt(__('header_image-preview-alt'))
                            ->extra($srcset)
                    ]);

                if (My::settings()->header_image2x) {
                    $the_setting[] = (new Text('p', __('header_image-retina-ready')))
                        ->id('header_image-retina');
                }
            }

            $the_setting[] = (new Para())
                ->id('header_image-delete')
                ->items([
                    (new Button('header_image-delete-button', __('header_image-delete-button')))
                        ->class('delete')
                ]);

            $header_image_file_name = My::settings()->header_image['name'] ?? '';

            $the_setting[] = new Hidden('header_image-delete-action', 'false');
            $the_setting[] = new Hidden('header_image-retina-text', Html::escapeHTML(__('header_image-retina-ready')));
        }

        return $the_setting;
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

        echo App::backend()->notices()->getNotices();

        // Add a form before the main form to upload a configuration file.
        if (isset($_GET['config-upload']) && $_GET['config-upload'] === '1') {
            $upload_form_fields = [];

            $upload_form_fields[] = new Text('h3', __('settings-upload-title'));
            $upload_form_fields[] = new Text('p', __('settings-upload-description'));

            $upload_form_fields[] = (new Para())
                ->class('form-buttons')
                ->items([
                    (new File('config-upload-file', __('settings-file-import-input-button-text')))
                        ->name('config-upload-file'),
                ]);

            $upload_form_fields[] = (new Para())
                ->class('form-buttons')
                ->items([
                    App::nonce()->formNonce(),
                    (new Submit(null, __('settings-upload-submit')))
                        ->name('config-upload-submit'),
                    (new Submit(null, __('settings-upload-cancel')))
                        ->class('delete')
                        ->name('config-upload-cancel')
                ]);

            echo (new Form('theme-config-upload'))
                ->action(App::backend()->url()->get('admin.blog.theme', array_merge(self::$redirect_query, ['config-upload' => '1'])))
                ->class('fieldset')
                ->enctype('multipart/form-data')
                ->fields($upload_form_fields)
                ->method('post')
                ->render();
        }

        /**
         * Starting to create the main form.
         *
         * Creates an array to put all the settings in their sections.
         */
        $settings_render = [];

        // Adds sections.
        foreach (My::settingsSections() as $section_id => $section_data) {
            $settings_render[$section_id] = [];
        }

        foreach (My::settingsDefault() as $setting_id => $setting_data) {
            if ($setting_id !== 'styles') {
                if (isset($setting_data['section'][1])) {
                    // If a sub-section has been set.
                    $settings_render[$setting_data['section'][0]][$setting_data['section'][1]][] = $setting_id;
                } else {
                    $settings_render[$setting_data['section'][0]]['untitled'][] = $setting_id;
                }
            }
        }

        // Adds settings in their section.
        $fields = [];

        $fields[] = new Text('p', sprintf(__('settings-page-intro'), My::name()));

        $fields[] = new Text(
            'p',
            sprintf(
                __('settings-page-forum-link'),
                'https://github.com/te2dy/odyssey/issues',
                'mailto:contact.teddy@laposte.net',
                'https://matrix.to/#/#dotclear:matrix.org',
                'https://dotclear.org/forum'
            )
        );

        $theme_dev_mode = false;

        if ((bool) App::auth()->prefs()->interface->themeeditordevmode
            && App::plugins()->moduleExists('tidyAdmin')
        ) {
            $theme_dev_mode = true;
        }

        $theme_update_locked = false;

        if ((bool) App::themes()->getDefine(App::blog()->settings()->system->theme)->updLocked()
            && App::plugins()->moduleExists('themeEditor')
        ) {
            $theme_update_locked = true;
        }

        $no_public_css = (bool) App::blog()->settings()->system->no_public_css;

        if ($theme_update_locked || $theme_dev_mode || !$no_public_css) {
            $theme_info = [];

            if ($theme_update_locked && $theme_dev_mode) {
                $theme_info[] = (new Text('p', sprintf(
                    __('settings-theme-warning-lockedanddev'),
                    My::name(),
                    My::displayAttr(App::backend()->url()->get('admin.plugin.themeEditor')),
                    My::displayAttr(App::backend()->url()->get('admin.plugin.tidyAdmin', ['part' => 'options']))
                )));
            } elseif ($theme_update_locked) {
                $theme_info[] = (new Text('p', sprintf(
                    __('settings-theme-warning-locked'),
                    My::name(),
                    My::displayAttr(App::backend()->url()->get('admin.plugin.themeEditor'))
                )));
            } elseif ($theme_dev_mode) {
                $theme_info[] = new Text('p', sprintf(
                    __('settings-theme-warning-dev'),
                    My::displayAttr(App::backend()->url()->get('admin.plugin.tidyAdmin', ['part' => 'options']))
                ));
            }

            if (!$no_public_css) {
                $blogpref_url  = My::displayAttr(App::backend()->url()->get('admin.blog.pref'));

                $theme_info[] = new Text('p', sprintf(__('settings-mediacss-warning'), $blogpref_url, My::name()));
            }

            $fields[] = (new Div())
                ->class('info')
                ->items($theme_info);
        }

        foreach ($settings_render as $section_id => $setting_data) {
            $settings_fields = [];

            foreach ($setting_data as $sub_section_id => $setting_id) {
                // Displays the name of the sub-section unless its ID is "no-title".
                if ($sub_section_id !== 'untitled') {
                    $settings_fields[] = (new Text('h4', My::settingsSections($section_id)['sub_sections'][$sub_section_id]))
                        ->id('section-' . $section_id . '-' . $sub_section_id);
                }

                if (isset($setting_id[0]) && $setting_id[0] === 'social_bluesky') {
                    $settings_fields[] = new Text(
                        'p',
                        sprintf(
                            __('settings-social-notice'),
                            __('section-footer'),
                            __('section-reactions')
                        )
                    );
                }

                // Displays the parameter.
                foreach ($setting_id as $setting_id_value) {
                    if (is_array(self::settingRender($setting_id_value))) {
                        foreach (self::settingRender($setting_id_value) as $item) {
                            $settings_fields[] = $item;
                        }
                    }
                }
            }

            $fields[] = (new Text('h3', My::settingsSections($section_id)['name']))
                ->id('section-' . $section_id);
            $fields[] = (new Fieldset())
                ->items($settings_fields);
        }

        $fields[] = new Hidden('page_width_em_min_default', '30');
        $fields[] = new Hidden('page_width_em_max_default', '80');
        $fields[] = new Hidden('page_width_em_step_default', '1');
        $fields[] = new Hidden('page_width_px_min_default', '480');
        $fields[] = new Hidden('page_width_px_max_default', '1280');
        $fields[] = new Hidden('page_width_px_step_default', '2');
        $fields[] = new Hidden('reset_warning', __('settings-reset-warning'));
        $fields[] = new Hidden('config_restore_warning', __('settings-config-restore-warning'));
        $fields[] = new Hidden('config_remove_warning', __('settings-config-remove-warning'));
        $fields[] = new Hidden('config_remove_all_warning', __('settings-config-remove-all-warning'));

        $fields[] = (new Para())
            ->class('form-buttons')
            ->items([
                App::nonce()->formNonce(),
                (new Submit(null, __('settings-save-button-text')))
                    ->name('save'),
                (new Submit(null,  __('settings-reset-button-text')))
                    ->class('delete')
                    ->id('odyssey-reset')
                    ->name('reset'),
                (new Submit(null, __('settings-create-file-button-text')))
                    ->class('button modal')
                    ->name('save-config'),
                (new Submit(null,  __('settings-upload-file-button-text')))
                    ->class('button modal')
                    ->name('import-config')
            ]);

        // Displays theme configuration backups.
        $backups_dir_path  = My::varFolder('path', '/backups');
        $backups_dir_data  = is_dir($backups_dir_path) ? Files::getDirList($backups_dir_path) : null;
        $backups_dir_files = [];

        if ($backups_dir_data) {
            if (isset($backups_dir_data['files']) && !empty($backups_dir_data['files'])) {
                $backups_dir_files = $backups_dir_data['files'];
            }

            $file_list = [];

            foreach ($backups_dir_files as $backup_path) {
                $file_extension = Files::getExtension($backup_path);

                if ($file_extension && $file_extension === 'json') {
                    $file_datatime = str_replace('-', '', basename($backup_path, '-settings.json'));

                    $file_list[$file_datatime] = $backup_path;
                }
            }

            ksort($file_list);

            if (!empty($file_list)) {
                $table_fields = [];

                foreach ($file_list as $file_path) {
                    $file_name       = basename($file_path);
                    $file_name_parts = explode('-', $file_name);
                    $file_name_date  = $file_name_parts[0] ?? null;

                    if ($file_name_date) {
                        $file_name_date = Date::str(App::blog()->settings()->system->date_format, strtotime($file_name_date));
                    }

                    $file_name_time = $file_name_parts[1] ?? null;

                    if ($file_name_time) {
                        $file_name_time = Date::str(App::blog()->settings()->system->time_format, strtotime($file_name_time));
                    }

                    $file_datetime = sprintf(__('settings-backup-datetime'), $file_name_date, $file_name_time);

                    $file_name_without_extension = substr($file_name, 0, -5);

                    $restore_url = App::backend()->url()->get(
                        'admin.blog.theme',
                        array_merge(self::$redirect_query, ['restore' => My::escapeURL($file_name_without_extension)])
                    );

                    $download_url = App::backend()->page()->getVF(My::varFolder('vf', '/backups/' . $file_name));

                    $delete_url = App::backend()->url()->get(
                        'admin.blog.theme',
                        array_merge(self::$redirect_query, ['restore_delete_file' => My::escapeURL($file_name_without_extension)])
                    );

                    $table_fields[] = (new Tr())
                        ->class('line')
                        ->items([
                            (new Td())
                                ->text(sprintf(__('settings-backup-title'), Html::escapeHTML($file_datetime))),
                            (new Td())
                                ->items([
                                    (new Link())
                                        ->class('odyssey-backups-restore')
                                        ->href($restore_url, false)
                                        ->text(__('settings-backup-restore-link')),
                                ]),
                            (new Td())
                                ->items([
                                    (new Link())
                                        ->download($file_name)
                                        ->href(My::escapeURL($download_url))
                                        ->text(__('settings-backup-download-link'))
                                ]),
                            (new Td())
                                ->items([
                                    (new Link())
                                        ->class('odyssey-backups-remove')
                                        ->href($delete_url)
                                        ->text(__('settings-backup-delete-link'))
                                ])
                        ]);
                }

                if (count($file_list) > 1) {
                    $backups_table_intro = sprintf(__('settings-backups-count-multiple'), count($file_list));
                } else {
                    $backups_table_intro = __('settings-backups-count-one');
                }

                $delete_all_url = App::backend()->url()->get(
                    'admin.blog.theme',
                    array_merge(self::$redirect_query, ['restore_delete_all' => '1'])
                );

                $fields[] = (new Div('odyssey-backups'))
                    ->items([
                        new Text('h3', __('settings-backups-title')),
                        new Text('p', $backups_table_intro),
                        (new Table())
                            ->class('settings rch rch-thead')
                            ->items([
                                (new Tbody())
                                    ->items($table_fields)
                            ]),
                        (new Para())
                            ->items([
                                (new Link())
                                    ->id('odyssey-backups-remove-all')
                                    ->href($delete_all_url)
                                    ->text(__('settings-backup-delete-all-link'))
                            ]),
                        new Text('p', sprintf(__('settings-backups-explanations'), My::id())),
                        new Text('p', sprintf(__('settings-backups-warning'), My::name()))
                    ]);
            }
        }

        echo (new Form('theme-config-form'))
            ->action(App::backend()->url()->get('admin.blog.theme', self::$redirect_query))
            ->enctype('multipart/form-data')
            ->fields($fields)
            ->method('post')
            ->render();

        if (App::auth()->prefs()->interface->colorsyntax) {
            echo App::backend()->page()->jsRunCodeMirror([
                [
                    'name'  => 'styles_custom',
                    'id'    => 'styles_custom',
                    'mode'  => 'css',
                    'theme' => App::auth()->prefs()->interface->colorsyntax_theme ?: 'default',
                ]
            ]);
        }
    }
}
