<?php
/**
 * Odyssey, a simple and customizable Dotclear theme.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2025 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

namespace Dotclear\Theme\odyssey;

use Dotclear\App;
use Dotclear\Core\Process;
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Core\Backend\ThemeConfig;
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
use Dotclear\Helper\Html\Form\Image;
use Dotclear\Helper\Html\Form\Img;
use Dotclear\Helper\Html\Form\Input;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Legend;
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
     * Processes the multiple requests.
     *
     * @return bool
     */
    public static function process(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
            try {
                if (isset($_POST['save'])) {
                    // If the save button has been clicked.
                    self::_saveSettings($_POST, $_FILES, __('settings-notice-saved'));
                } elseif (isset($_POST['save-config'])) {
                    // If the save configuration file button has been clicked.
                    self::_saveSettings($_POST, $_FILES, '', ['save-config' => 'create-file']);
                } elseif (isset($_POST['reset'])) {
                    // If the reset button has been clicked, removes all saved settings from the database.
                    App::blog()->settings->odyssey->dropAll();

                    // Removes the header image, custom CSS file and their folders.
                    Files::deltree(My::odysseyPublicFolder('path'));

                    App::blog()->triggerBlog();

                    App::cache()->emptyTemplatesCache();

                    Notices::addSuccessNotice(__('settings-notice-reset'));

                    App::backend()->url()->redirect(
                        'admin.blog.theme',
                        ['module' => My::id(), 'conf' => '1']
                    );
                } elseif (isset($_POST['import-config'])) {
                    // When the upload configuration file link is clicked, redirects to the upload page.
                    App::backend()->url()->redirect(
                        'admin.blog.theme',
                        ['module' => My::id(), 'conf' => '1', 'config-upload' => '1']
                    );
                } elseif (isset($_POST['config-upload-submit'])) {
                    // When a configuration file has been submitted, uploads it.
                    self::_uploadConfigFile($_FILES);
                } elseif (isset($_POST['config-upload-cancel'])) {
                    // Redirects if the cancel upload button has been clicked.
                    App::backend()->url()->redirect(
                        'admin.blog.theme',
                        ['module' => My::id(), 'conf' => '1']
                    );
                }
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET)) {
            try {
                if (isset($_GET['save-config']) && $_GET['save-config'] === 'create-file') {
                    // Creates a backup file.
                    self::_createBackupFile(self::settingsSaved());
                } elseif (isset($_GET['restore']) && $_GET['restore'] !== 'success') {
                    // Restores a configuration from a backup file listed from /var/odyssey/backups.
                    self::_restoreBackup($_GET['restore'] . '.json');
                } elseif (isset($_GET['restore_delete_file'])) {
                    // Deletes a configuration file.
                    $delete_file_name = $_GET['restore_delete_file'] . '.json';
                    $odyssey_folder   = My::odysseyVarFolder('path');
                    $backups_folder   = My::odysseyVarFolder('path', '/backups/');
                    $delete_file_path = $backups_folder . $delete_file_name;

                    if (Path::real($delete_file_path)) {
                        // Deletes the file and directories if empty.
                        unlink($delete_file_path);

                        if (Path::real($odyssey_folder)
                            && Path::real($backups_folder)
                            && empty(Files::getDirList($backups_folder)['files'])
                        ) {
                            Files::deltree($odyssey_folder);
                        }

                        Notices::addSuccessNotice(__('settings-notice-file-deleted'));

                        App::backend()->url()->redirect(
                            'admin.blog.theme',
                            ['module' => My::id(), 'conf' => '1']
                        );
                    }
                } elseif (isset($_GET['restore_delete_all'])) {
                    // Deletes all configuration files.
                    $odyssey_var_folder = My::odysseyVarFolder('path');

                    if (Path::real($odyssey_var_folder)) {
                        Files::deltree($odyssey_var_folder);
                    }

                    Notices::addSuccessNotice(__('settings-notice-files-deleted'));

                    App::backend()->url()->redirect(
                        'admin.blog.theme',
                        ['module' => My::id(), 'conf' => '1']
                    );
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
     * @param array  $redirect_params Parameters to add to the redirection after saving.
     *
     * @return void
     */
    private static function _saveSettings(
        array  $http_post,
        array  $http_files,
        string $notice_success = '',
        array  $redirect_params = []
    ): void
    {
        if (empty($http_post) || empty($http_files)) {
            return;
        }

        // Puts all $_POST et $_FILES variables in a new array to manipulate them.
        $new_settings  = [];
        $http_requests = array_merge($http_post, $http_files);

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
                    $new_settings
                );

                // Saves the setting in the database or drop it.
                if (!empty($setting)) {
                    App::blog()->settings->odyssey->put(
                        $setting['id'],
                        $setting['value'],
                        $setting['type']
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
            if ($setting_id === 'header_image') {
                // Saves header image.
                if (isset($new_settings['header_image-delete-action'])
                    && $new_settings['header_image-delete-action'] === 'true'
                ) {
                    // If the delete button has been clicked.
                    self::_deleteHeaderImage();
                } elseif (isset($setting['value'])
                    && isset($setting_value['tmp_name'])
                    && $setting_value['tmp_name']
                ) {
                    // If an image file has been submitted and the file exists, saves the image.
                    self::_saveHeaderImage($setting_id, $setting, $setting_value['tmp_name']);
                }
            } elseif ($setting_id === 'header_image2x') {
                // Saves header image for Retina displays.
                if (isset($new_settings['header_image'])
                    && isset($setting['value'])
                    && isset($setting_value['tmp_name'])
                    && $setting_value['tmp_name']
                ) {
                    // If a Retina image file has been submitted and the file exists, saves the image.
                    self::_saveHeaderImage($setting_id, $setting, $setting_value['tmp_name']);
                }
            } elseif ($setting_id === 'styles') {
                // Saves styles.
                $sanitized_styles       = self::_sanitizeStyles($sanitizedSettings);
                $sanitized_styles_value = $sanitized_styles['value'] ?? null;
                $sanitized_styles_type  = $sanitized_styles['type']  ?? null;

                if ($sanitized_styles_value && $sanitized_styles_type) {
                    App::blog()->settings->odyssey->put(
                        'styles',
                        $sanitized_styles_value,
                        $sanitized_styles_type
                    );

                    self::_stylesCustomFile($sanitized_styles_value);
                } else {
                    App::blog()->settings->odyssey->drop('styles');
                }
            }

            // If the /public/odyssey folder has empty subfolders, deletes it.
            if (!is_dir(My::odysseyPublicFolder('path', '/css'))
                && !is_dir(My::odysseyPublicFolder('path', '/img'))
            ) {
                Files::deltree(My::odysseyPublicFolder('path'));
            }
        }

        // Refreshes the blog.
        App::blog()->triggerBlog();

        // Resets template cache.
        App::cache()->emptyTemplatesCache();

        // Displays a success notice.
        if ($notice_success) {
            Notices::addSuccessNotice($notice_success);
        }

        // Redirects.
        $redirect_params = array_merge(
            [
                'module' => My::id(),
                'conf'   => '1'
            ],
            $redirect_params
        );

        App::backend()->url()->redirect('admin.blog.theme', $redirect_params);
    }

    /**
     * Sanitizes a setting to be saved.
     *
     * @param string $setting_id    The id of the current setting to sanitize.
     * @param array  $setting_data  The components of the current parameter.
     * @param mixed  $setting_value The value of the setting to be saved.
     * @param array  $new_settings  All new settings passed through the configurator form.
     *
     * @return array The sanitized setting value and type.
     */
    private static function _sanitizeSetting(
        string $setting_id,
        array  $setting_data,
        mixed  $setting_value,
        array  $new_settings
    ): array
    {
        $setting = [
            'id'    => $setting_id,
            'value' => null,
            'type'  => null
        ];

        if (!isset($setting_data['sanitizer'])) {
            // Generic sanitization.
            switch ($setting_data['type']) {
                case 'select':
                case 'select_int':
                    if ($setting_data['type'] === 'select_int') {
                        $setting_value = (int) $setting_value;
                    }

                    if (in_array($setting_value, $setting_data['choices'], true)
                        && $setting_value !== $setting_data['default']
                    ) {
                        $setting['value'] = $setting_value;
                        $setting['type']  = 'string';
                    }

                    break;
                case 'checkbox':
                    $setting_value = $setting_value ? true : false;

                    if ($setting_value === true && $setting_data['default'] === false) {
                        $setting['value'] = '1';
                        $setting['type']  = 'boolean';
                    } elseif ($setting_value === false && $setting_data['default'] === true) {
                        $setting['value'] = '0';
                        $setting['type']  = 'boolean';
                    }

                    break;
                default:
                    if ($setting_value && $setting_value != $setting_data['default']) {
                        $setting['value'] = filter_var(
                            $setting_value,
                            FILTER_SANITIZE_SPECIAL_CHARS
                        );

                        $setting['type'] = 'string';
                    }
            }

            if (isset($setting['id']) && isset($setting['value']) && isset($setting['type'])) {
                return $setting;
            }
        } else {
            // If a sanitizer function is defined.
            $params = [];

            $saved_settings = self::settingsSaved();

            $action_delete = false;

            if (isset($new_settings['header_image-delete-action']) && $new_settings['header_image-delete-action'] === 'true') {
                $action_delete = true;
            }

            $header_image_name    = $new_settings['header_image']['name'] ?? $saved_settings['header_image']['name'] ?? '';
            $header_image2x_img1x = $saved_settings['header_image2x']['img1x'] ?? null;

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

            $header_image_db_data   = $saved_settings['header_image']   ?? [];
            $header_image2x_db_data = $saved_settings['header_image2x'] ?? [];

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

            if (isset($setting['id'])
                && isset($setting['value'])
                && isset($setting['type'])
            ) {
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
    public static function _sanitizeHeaderImage(
        string $setting_id,
        array  $image_file   = [],
        array  $new_settings = []
    ): array
    {
        if (!empty($image_file)
            && isset($image_file['size'])
            && isset($image_file['tmp_name'])
            && isset($image_file['type'])
        ) {
            $image_size = (int) $image_file['size'];

            if ($image_size > 0
                && isset($image_file['error'])
                && $image_file['error'] === UPLOAD_ERR_OK
            ) {
                $file_name  = Files::tidyFileName($image_file['name']) ?: '';
                $file_path  = $image_file['tmp_name'];
                $file_type  = $image_file['type'];
                $file_url   = My::odysseyPublicFolder('url', '/img/' . $file_name);
                $image_data = [];

                $mime_types_supported = Files::mimeTypes();

                if ($file_name
                    && $file_path
                    && $file_type
                    && $file_url
                    && file_exists($file_path)
                    && str_starts_with($file_type, 'image/')
                    && in_array($file_type, $mime_types_supported, true)
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
                            if (isset($new_settings['global_page_width_value'])) {
                                $page_width = (int) $new_settings['global_page_width_value'];
                            } else {
                                $page_width = 30;
                            }

                            $page_width_unit = $new_settings['global_unit'] ?? 'em';

                            if ($page_width_unit === 'em') {
                                $page_width = $page_width * 16;
                            }

                            if ($header_image_width > $page_width) {
                                $header_image_width = 100;
                            } else {
                                $header_image_width = $header_image_width * 100 / $page_width;
                            }

                            $image_data = [
                                'name'     => $file_name,
                                'url'      => $file_url,
                                'width'    => (int) $header_image_width
                            ];

                            break;
                        case 'header_image2x':
                            if (isset($new_settings['header_image'])
                                && isset($new_settings['header_image']['name'])
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
     * Adds custom styles to the theme to apply the settings.
     *
     * @param array $settings All sanitized settings after being passed through the configurator form.
     *
     * @return array The styles.
     */
    public static function _sanitizeStyles(array $settings): array
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

        // Page width
        $page_width_data  = self::_sanitizePageWidth(
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
            if (isset($settings['global_color_text_custom'])
                && isset($default_settings['global_color_primary']['default'])
                && My::isHexColor($settings['global_color_text_custom'])
                && $settings['global_color_text_custom'] !== $default_settings['global_color_primary']['default']
            ) {
                $css_root_array[':root']['--color-text-main'] = $settings['global_color_text_custom'];
            }

            if (isset($settings['global_color_text_dark_custom'])
                && isset($default_settings['global_color_text_dark_custom']['default'])
                && My::isHexColor($settings['global_color_text_dark_custom'])
                && $settings['global_color_text_dark_custom'] !== $default_settings['global_color_text_dark_custom']['default']
            ) {
                $css_root_array[':root']['--color-text-main-dark'] = $settings['global_color_text_dark_custom'];
            }

            // Text secondary color
            if (isset($settings['global_color_text_secondary_custom'])
                && isset($default_settings['global_color_text_secondary_custom']['default'])
                && My::isHexColor($settings['global_color_text_secondary_custom'])
                && $settings['global_color_text_secondary_custom'] !== $default_settings['global_color_text_secondary_custom']['default']
            ) {
                $css_root_array[':root']['--color-text-secondary'] = $settings['global_color_text_secondary_custom'];
            }

            if (isset($settings['global_color_text_secondary_dark_custom'])
                && isset($default_settings['global_color_text_secondary_dark_custom']['default'])
                && My::isHexColor($settings['global_color_text_secondary_dark_custom'])
                && $settings['global_color_text_secondary_dark_custom'] !== $default_settings['global_color_text_secondary_dark_custom']['default']
            ) {
                $css_root_array[':root']['--color-text-secondary-dark'] = $settings['global_color_text_secondary_dark_custom'];
            }

            // Input color
            if (isset($settings['global_color_input_custom'])
                && isset($default_settings['global_color_input_custom']['default'])
                && My::isHexColor($settings['global_color_input_custom'])
                && $settings['global_color_input_custom'] !== $default_settings['global_color_input_custom']['default']
            ) {
                $css_root_array[':root']['--color-input-background'] = $settings['global_color_input_custom'];
            }

            if (isset($settings['global_color_input_dark_custom'])
                && isset($default_settings['global_color_input_dark_custom']['default'])
                && My::isHexColor($settings['global_color_input_dark_custom'])
                && $settings['global_color_input_dark_custom'] !== $default_settings['global_color_input_dark_custom']['default']
            ) {
                $css_root_array[':root']['--color-input-background-dark'] = $settings['global_color_input_dark_custom'];
            }

            // Border color
            if (isset($settings['global_color_border_custom'])
                && isset($default_settings['global_color_border_custom']['default'])
                && My::isHexColor($settings['global_color_border_custom'])
                && $settings['global_color_border_custom'] !== $default_settings['global_color_border_custom']['default']
            ) {
                $css_root_array[':root']['--color-border'] = $settings['global_color_border_custom'];
            }

            if (isset($settings['global_color_border_dark_custom'])
                && isset($default_settings['global_color_border_dark_custom']['default'])
                && My::isHexColor($settings['global_color_border_dark_custom'])
                && $settings['global_color_border_dark_custom'] !== $default_settings['global_color_border_dark_custom']['default']
            ) {
                $css_root_array[':root']['--color-border-dark'] = $settings['global_color_border_dark_custom'];
            }

            // Background color
            if (isset($settings['global_color_background_custom'])
                && isset($default_settings['global_color_background_custom']['default'])
                && My::isHexColor($settings['global_color_background_custom'])
                && $settings['global_color_background_custom'] !== $default_settings['global_color_background_custom']['default']
            ) {
                $css_root_array[':root']['--color-background'] = $settings['global_color_background_custom'];
            }

            if (isset($settings['global_color_background_dark_custom'])
                && isset($default_settings['global_color_background_dark_custom']['default'])
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
                && in_array($settings['global_color_primary'], $primary_colors_allowed, true)
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
                if (isset($settings['global_color_primary_custom'])
                    && isset($default_settings['global_color_primary_custom']['default'])
                    && My::isHexColor($settings['global_color_primary_custom'])
                    && $settings['global_color_primary_custom'] !== $default_settings['global_color_primary_custom']['default']
                ) {
                    $color_primary_light = $settings['global_color_primary_custom'];

                    $css_root_array[':root']['--color-primary'] = $color_primary_light;
                }

                if (isset($settings['global_color_primary_amplified_custom'])
                    && isset($default_settings['global_color_primary_amplified_custom']['default'])
                    && My::isHexColor($settings['global_color_primary_amplified_custom'])
                    && $settings['global_color_primary_amplified_custom'] !== $default_settings['global_color_primary_amplified_custom']['default']
                ) {
                    $color_primary_amplified_light = $settings['global_color_primary_amplified_custom'];

                    $css_root_array[':root']['--color-primary-amplified'] = $color_primary_amplified_light;
                }

                if (isset($settings['global_color_primary_dark_custom'])
                    && isset($default_settings['global_color_primary_dark_custom']['default'])
                    && My::isHexColor($settings['global_color_primary_dark_custom'])
                    && $settings['global_color_primary_dark_custom'] !== $default_settings['global_color_primary_dark_custom']['default']
                ) {
                    $color_primary_dark = $settings['global_color_primary_dark_custom'];

                    $css_root_dark_array[':root']['--color-primary-dark'] = $color_primary_dark;
                }

                if (isset($settings['global_color_primary_dark_amplified_custom'])
                    && isset($default_settings['global_color_primary_dark_amplified_custom']['default'])
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
            if ($settings['global_color_scheme'] === 'light') {
                $css_root_array[':root']['--color-background-dark']        = '#fafafa';
                $css_root_array[':root']['--color-text-main-dark']         = '#303030';
                $css_root_array[':root']['--color-text-secondary-dark']    = '#6c6f78';
                $css_root_array[':root']['--color-primary-dark']           = $color_primary_light;
                $css_root_array[':root']['--color-primary-dark-amplified'] = $color_primary_amplified_light;
                $css_root_array[':root']['--color-input-background-dark']  = '#f2f2f2';
                $css_root_array[':root']['--color-border-dark']            = '#ccc';
            } elseif ($settings['global_color_scheme'] === 'dark') {
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

        if (isset($settings['header_align'])
            && in_array($settings['header_align'], $header_align_allowed, true)
        ) {
            $css_root_array[':root']['--header-align'] = $settings['header_align'];
        }

        // Header image
        if (isset($settings['header_image']) && !empty($settings['header_image'])) {
            $css_main_array['#site-image']['width'] = '100%';

            $css_main_array['#site-image a']['display']       = 'inline-block';
            $css_main_array['#site-image a']['outline-width'] = '.168em';

            $css_main_array['#site-image img']['display'] = 'inline-block';
        }

        // Post list type
        if (isset($settings['content_postlist_type'])) {
            if ($settings['content_postlist_type'] === 'excerpt') {
                $css_main_array['.entry-list-excerpt .post']['margin']  = '1em -1rem';
                $css_main_array['.entry-list-excerpt .post']['padding'] = '1rem';

                $css_main_array['.entry-list-excerpt .post:first-child']['margin-top']   = '0';
                $css_main_array['.entry-list-excerpt .post:last-child']['margin-bottom'] = '0';

                $css_main_array['.entry-list-excerpt .entry-title']['font-size']    = '1.1rem';
                $css_main_array['.entry-list-excerpt .entry-title']['margin-block'] = '.5rem';

                $css_main_array['.entry-list-excerpt .post-excerpt']['margin-block'] = '.5rem';
            } elseif ($settings['content_postlist_type'] === 'content') {
                $css_main_array['.entry-list-content .post']['border-bottom'] = '.063em solid var(--color-border, #ccc)';
                $css_main_array['.entry-list-content .post']['margin-bottom'] = '4em';

                $css_main_array['.entry-list-content .post:last-child']['margin-bottom'] = '0';
                $css_main_array['.entry-list-content .post:last-child']['border-bottom'] = 'none';

                $css_main_array['.entry-list-content .entry-title']['font-size'] = '1.4em';

                $css_main_array['.entry-list-content .post-footer']['background-color'] = 'var(--color-input-background, #f2f2f2)';
                $css_main_array['.entry-list-content .post-footer']['border-radius']    = 'var(--border-radius, unset)';
                $css_main_array['.entry-list-content .post-footer']['margin-block']     = '2em 4em';
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
        if (isset($settings['content_postlist_thumbnail']) && $settings['content_postlist_thumbnail'] === '1') {
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
                    $css_root_array[':root']['--text-align']  = 'justify';

                    $css_media_array[':root']['--text-align'] = 'left';

                    $css_media_contrast_array[':root']['--text-align'] = 'left';
            }
        }

        // Line Height
        $line_height_allowed = [125, 175];

        if (isset($settings['content_line_height'])
            && in_array((int) $settings['content_line_height'], $line_height_allowed, true)
        ) {
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
            $css_supports_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['-moz-initial-letter'] = '2';
            $css_supports_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['-webkit-initial-letter'] = '2';
            $css_supports_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['initial-letter'] = '2';
            $css_supports_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['margin-right'] = '.25rem';
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

            if (isset($settings['content_postlist_thumbnail']) && $settings['content_postlist_thumbnail'] === '1') {
                if (isset($settings['content_postlist_type']) && $settings['content_postlist_type'] === 'one-line') {
                    $css_main_array['.entry-list-img']['transition']                          = 'var(--color-transition, unset)';
                    $css_main_array['.entry-list-img:not(:active, :focus, :hover)']['filter'] = 'grayscale(1)';
                }
            }
        }

        // Footer align
        $footer_align_allowed = ['center', 'right'];

        if (isset($settings['footer_enabled']) && $settings['footer_enabled'] === '1') {
            if (isset($settings['footer_align']) && in_array($settings['footer_align'], $footer_align_allowed, true)) {
                $css_root_array[':root']['--footer-align'] = $settings['footer_align'];
            }
        }

        // Displays Simple Icons styles if necessary.
        $simpleicons_styles = false;

        // Checks if a link has been set.
        foreach (My::socialSites() as $id => $data) {
            if (isset($settings['social_' . $id])) {
                if (isset($settings['reactions_other'])
                    && $settings['reactions_other'] !== 'disabled'
                    && isset($settings['reactions_other_' . $id])
                ) {
                    if (!empty(self::_sanitizeSocial('social_' . $id, $settings['social_' . $id]))) {
                        if (My::svgIcons($id)['author'] === 'simpleicons') {
                            $simpleicons_styles  = true;
                        }
                    }
                }
            }
        }

        // Other reactions
        if (isset($settings['reactions_other']) && $settings['reactions_other'] !== 'disabled') {
            $css_main_array['.reactions-button .social-icon-si']['fill'] = 'var(--color-primary, hsl(226, 80%, 45%))';
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

        if ($simpleicons_styles === true) {
            $css_main_array['.social-icon-si']['border']          = '0';
            $css_main_array['.social-icon-si']['stroke']          = 'none';
            $css_main_array['.social-icon-si']['stroke-linecap']  = 'round';
            $css_main_array['.social-icon-si']['stroke-linejoin'] = 'round';
            $css_main_array['.social-icon-si']['stroke-width']    = '0';
            $css_main_array['.social-icon-si']['width']           = '1rem';
            $css_main_array['.social-icon-si']['transition']      = 'var(--color-transition, unset)';

            $css_media_contrast_array['.reactions-button:is(:active, :focus, :hover) .reactions-button-icon.social-icon-si']['fill'] = 'var(--color-background)';
        }

        if ($footer_social_links === true) {
            $css_main_array['.footer-social-links']['list-style']                 = 'none';
            $css_main_array['.footer-social-links']['margin']                     = '0 0 1rem';
            $css_main_array['.footer-social-links']['padding-left']               = '0';

            $css_main_array['.footer-social-links li']['display']                 = 'inline-block';
            $css_main_array['.footer-social-links li']['margin']                  = '.25em';
            $css_main_array['.footer-social-links li:first-child']['margin-left'] = '0';
            $css_main_array['.footer-social-links li:last-child']['margin-right'] = '0';

            $css_main_array['.footer-social-links a']['display'] = 'inline-block';

            $css_main_array['.footer-social-links-icon-container']['align-items']      = 'center';
            $css_main_array['.footer-social-links-icon-container']['background-color'] = 'var(--color-input-background, #f2f2f2)';
            $css_main_array['.footer-social-links-icon-container']['display']          = 'flex';
            $css_main_array['.footer-social-links-icon-container']['justify-content']  = 'center';
            $css_main_array['.footer-social-links-icon-container']['width']            = '1.5rem';
            $css_main_array['.footer-social-links-icon-container']['height']           = '1.5rem';
            $css_main_array['.footer-social-links-icon-container']['transition']       = 'var(--color-transition, unset)';

            $css_media_contrast_array['.footer-social-links-icon-container']['border'] = '.063rem solid var(--color-border, #ccc)';

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

            $css_media_contrast_array['.footer-social-links a:is(:active, :focus, :hover) .footer-social-links-icon-container']['border-color'] = 'var(--color-primary-amplified, hsl(226, 95%, 50%))';

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

        if ($css) {
            return [
                'value' => $css,
                'type'  => 'string'
            ];
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
    public static function _saveHeaderImage(
        string  $setting_id,
        array   $image_data,
        ?string $img_file_path
    ): void
    {
        $img_folder_path = My::odysseyPublicFolder('path', '/img');

        switch ($setting_id) {
            case 'header_image':
                /**
                 * If an image file alrealdy exists, removes it
                 * and its folder; then, created the folder again
                 * to store the image later.
                 */
                if (is_dir($img_folder_path)) {
                    Files::deltree($img_folder_path);
                }

                Files::makeDir($img_folder_path, true);

                $image_name     = $image_data['value']['name'] ?? null;
                $image_path     = $img_folder_path . '/' . $image_name;
                $image_url      = My::odysseyPublicFolder('url', '/img/' . $image_name);

                if ($img_file_path && $image_name) {
                    move_uploaded_file($img_file_path, $image_path);
                }

                break;
            case 'header_image2x':
                $img_folder_path = My::odysseyPublicFolder('path', '/img');
                $img_folder_url  = My::odysseyPublicFolder('url', '/img');

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
        Files::deltree(My::odysseyPublicFolder('path', '/img'));
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
     * Prepares to save the page width option.
     *
     * @param ?string $unit       The unit used to define the width (px or em)
     * @param ?int    $value      The value of the page width.
     * @param ?string $setting_id The setting id.
     *
     * @return array The page width and its unit.
     */
    public static function _sanitizePageWidth(
        ?string $unit,
        ?int    $value,
        ?string $setting_id = null
    ): array
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
    public static function _sanitizeSocial(?string $setting_id, ?string $value): array
    {
        if (!$setting_id || !$value) {
            return [];
        }

        $id = str_replace('social_', '', $setting_id);

        $site_base = My::socialSites($id)['base'] ?? '';
        $site_type = My::socialSites($id)['type'] ?? '';

        switch ($site_type) {
            case 'phone-number':
                if (str_starts_with($value, $site_base) && is_numeric(substr($value, 1))) {
                    return [
                        'value' => '+' . str_replace('+', '', (int) $value),
                        'type'  => 'string'
                    ];
                }

                break;
            case 'url':
                if ($site_base !== '' && str_starts_with($value, $site_base)) {
                    return [
                        'value' => filter_var($value, FILTER_SANITIZE_URL),
                        'type'  => 'string'
                    ];
                } elseif (filter_var($value, FILTER_VALIDATE_URL) !== false) {
                    return [
                        'value' => filter_var($value, FILTER_SANITIZE_URL),
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
                } elseif (str_starts_with($value, 'https://signal.me/')
                    || str_starts_with($value, 'sgnl://signal.me/')
                    || str_starts_with($value, 'https://signal.group/')
                    || str_starts_with($value, 'sgnl://signal.group/')
                ) {
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
                } elseif (str_starts_with($value, 'https://wa.me/') || str_starts_with($value, 'whatsapp://wa.me/')) {
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
                } elseif (str_starts_with($value, 'https://x.com/') || str_starts_with($value, 'https://twitter.com/')) {
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
     * @param ?string $styles_custom All custom CSS rules.
     *
     * @return void
     */
    private static function _stylesCustomFile(?string $styles_custom): void
    {
        $css_default_path_file = My::odysseyThemeFolder('path', '/style.min.css');
        $css_path_folder       = My::id() . '/css';
        $css_custom_path_file  = $css_path_folder . '/style.min.css';

        if ($styles_custom) {
            $styles_default = '';

            // Gets default CSS content.
            if (file_exists($css_default_path_file)) {
                $styles_default = (string) file_get_contents($css_default_path_file) ?: '';
            }

            // Creates a custom CSS file in the public folder.
            if (is_writable(App::blog()->publicPath())) {
                if (!is_dir(My::odysseyPublicFolder('path', '/css'))) {
                    Files::makeDir(My::odysseyPublicFolder('path', '/css'), true);
                }

                ThemeConfig::writeCss(
                    $css_path_folder,
                    'style.min',
                    $styles_custom . $styles_default
                );

                // Creates a entry in the database that contains the CSS URL.
                App::blog()->settings->odyssey->put(
                    'styles_url',
                    My::odysseyPublicFolder('url', '/css/style.min.css'),
                    'string'
                );
            } else {
                if (file_exists($css_custom_path_file)) {
                    ThemeConfig::dropCss(
                        My::id() . '/css/',
                        'style.min'
                    );
                }

                App::blog()->settings->odyssey->drop('styles_url');
            }
        } else {
            // If there is no custom styles, deletes the CSS file if exists.
            if (file_exists(ThemeConfig::cssPath($css_custom_path_file))) {
                ThemeConfig::dropCss(
                    My::id() . '/css/',
                    'style.min'
                );
            }

            // Removes the CSS folder if it exists.
            if (Files::isDeletable(ThemeConfig::cssPath($css_path_folder))) {
                Files::deltree(ThemeConfig::cssPath($css_path_folder));
            }

            // Removes the database entry.
            App::blog()->settings->odyssey->drop('styles_url');
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
        if (isset($file_data['config-upload-file']['tmp_name'])
            && isset($file_data['config-upload-file']['type'])
            && isset($file_data['config-upload-file']['error'])
            && $file_data['config-upload-file']['error'] === UPLOAD_ERR_OK
        ) {
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
                    self::_saveSettings($settings_array, [], __('settings-notice-upload-success'));
                } else {
                    Notices::addErrorNotice(__('settings-notice-upload-file-not-valid'));

                    App::backend()->url()->redirect(
                        'admin.blog.theme',
                        ['module' => My::id(), 'conf' => '1', 'config-upload' => '1']
                    );
                }
            } else {
                // If the uploaded file is not a JSON file.
                Notices::addErrorNotice(__('settings-notice-upload-file-not-valid'));

                App::backend()->url()->redirect(
                    'admin.blog.theme',
                    ['module' => My::id(), 'conf' => '1', 'config-upload' => '1']
                );
            }
        } else {
            // If there is no file uploaded.
            Notices::addErrorNotice(__('settings-notice-upload-no-file'));

            App::backend()->url()->redirect(
                'admin.blog.theme',
                ['module' => My::id(), 'conf' => '1', 'config-upload' => '1']
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
        $restore_file_path    = Path::real(My::odysseyVarFolder('path', '/backups/' . $file_name));
        $restore_file_content = file_get_contents($restore_file_path);

        if ($restore_file_path && $restore_file_content && $restore_file_content !== '[]') {
            $settings_array = json_decode($restore_file_content, true);

            if (!empty($settings_array)) {
                // Drops all settings.
                App::blog()->settings->odyssey->dropAll();

                // Imports all settings.
                self::_saveSettings($settings_array, [], __('settings-notice-restore-success'));
            }
        } else {
            // If the file is empty.
            Notices::addErrorNotice(__('settings-notice-restore-error'));

            App::backend()->url()->redirect(
                'admin.blog.theme',
                ['module' => My::id(), 'conf' => '1']
            );
        }
    }

    /**
     * Creates a JSON backup file.
     *
     * The file will be uploaded in /var/odyssey/backups
     *
     * @param array $settings The name of the backup file.
     *
     * @return void
     */
    private static function _createBackupFile(array $settings = []): void
    {
        if (empty($settings)) {
            // If no custom setting has been set.
            Notices::addErrorNotice(__('settings-notice-save-fail'));
        } else {
            $backups_path = My::odysseyVarFolder('path', '/backups');

            // Creates the var/odyssey/backups folder if it doesn't exist.
            if (Path::real($backups_path) === false) {
                Files::makeDir($backups_path, true);
            }

            // Sets the name of the backup file with date and time.
            $time = str_replace(':', '', Date::str('%Y%m%d', time(), App::blog()->settings()->system->blog_timezone) . '-' . Date::str('%T', time(), App::blog()->settings()->system->blog_timezone));

            $file_name = Files::tidyFileName($time . '-settings');

            $backups_path .= '/' . $file_name . '.json';

            // Creates the JSON file.
            Files::putContent($backups_path, json_encode($settings));

            Notices::addNotice(
                'success',
                '<p>' . sprintf(__('settings-notice-save-success'), My::id(), '#odyssey-backups') . '</p>' .
                '<a class="button submit" href=' . My::displayAttr(urldecode(Page::getVF(My::odysseyVarFolder('vf', '/backups/' . $file_name . '.json'))), 'url') . ' download>' . __('settings-notice-save-success-link') . '</a>',
                ['divtag' => true]
            );

            App::backend()->url()->redirect('admin.blog.theme', ['module' => My::id(), 'conf' => '1']);
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
        $saved_settings   = self::settingsSaved();
        $setting_value    = $saved_settings[$setting_id] ?? $default_settings[$setting_id]['default'];
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
                            ->id($setting_id . '-description')
                            ->class('form-note');
                    } elseif ($checkbox_default !== '') {
                        $the_setting[] = (new Text('p', $checkbox_default))
                            ->id($setting_id . '-description')
                            ->class('form-note');
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
                                ->id('odyssey-config-global-font-preview')
                                ->class('odyssey-font-preview')
                                ->items([
                                    (new Text('strong', __('config-preview-font'))),
                                    (new Text(null, ' ' . $preview_string))
                                ]);
                        } else {
                            $the_setting[] = (new Para())
                                ->id('odyssey-config-content-font-preview')
                                ->class('odyssey-font-preview')
                                // ->extra($style)
                                ->items([
                                    (new Text('strong', __('config-preview-font'))),
                                    (new Text(null, ' ' . $preview_string))
                                ]);
                        }
                    }

                    if (isset($default_settings[$setting_id]['description']) && $default_settings[$setting_id]['description'] !== '') {
                        $the_setting[] = (new Text('p', $default_settings[$setting_id]['description']))
                            ->id($setting_id . '-description')
                            ->class('form-note');
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
                            ->id($setting_id . '-description')
                            ->class('form-note');
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
                            (new Color($setting_id, $setting_value)),
                            (new Input($setting_id . '-text', $setting_value))
                                ->placeholder($placeholder)
                                ->value($setting_value_input),
                            (new Button($setting_id . '-default-button', __('settings-colors-reset'))),
                            (new Hidden($setting_id . '-default-value', $default_settings[$setting_id]['default']))
                        ]);

                    break;
                case 'textarea' :
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
                        $the_setting[] = (new Text('p', $default_settings[$setting_id]['description']))
                            ->id($setting_id . '-description')
                            ->class('form-note');
                    }

                    break;
                case 'range' :
                    $range_default = [
                        'unit'  => 'em',
                        'value' => (int) My::settings()->$setting_id ?: (int) $default_settings[$setting_id]['default'],
                        'min'   => (int) $default_settings[$setting_id]['range']['min'],
                        'max'   => (int) $default_settings[$setting_id]['range']['max'],
                        'step'  => (int) $default_settings[$setting_id]['range']['step']
                    ];

                    if (My::settings()->global_unit === 'px') {
                        $range_default['unit'] = 'px';
                        $range_default['min']  = 480;
                        $range_default['max']  = 1280;
                        $range_default['step'] = 2;
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
                                ->value($range_default['value'])
                                ->min($range_default['min'])
                                ->max($range_default['max'])
                                ->step($range_default['step']),
                            (new Text(null, ' <output id=' . $setting_id . ' name=' . $setting_id . '-output>' . $range_default_output . '</output>'))
                        ]);

                    if (isset($default_settings[$setting_id]['description']) && $default_settings[$setting_id]['description'] !== '') {
                        $the_setting[] = (new Text('p', $default_settings[$setting_id]['description']))
                            ->id($setting_id . '-description')
                            ->class('form-note');
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
                                ->value($setting_value)
                        ]);

                    if (isset($default_settings[$setting_id]['description']) && $default_settings[$setting_id]['description'] !== '') {
                        $the_setting[] = (new Text('p', $default_settings[$setting_id]['description']))
                            ->id($setting_id . '-description')
                            ->class('form-note');
                    }
            }
        }

        // Header image.
        if ($setting_id === 'header_image') {
            $image_src = $setting_value['url'] ?? '';

            if (isset($saved_settings['header_image'])) {
                $the_setting[] = (new Para())
                    ->id('header_image-preview')
                    ->items([
                        (new Img(My::escapeURL($image_src), 'header_image-src'))
                            ->alt(__('header_image-preview-alt'))
                    ]);

                if (isset($saved_settings['header_image2x'])) {
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

            $header_image_file_name = $saved_settings['header_image']['name'] ?? '';

            $the_setting[] = (new Hidden('header_image-delete-action', "false"));
            $the_setting[] = (new Hidden('header_image-retina-text', __('header_image-retina-ready')));
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

        Page::openModule(
            My::name(),
            My::cssLoad('/css/admin.min.css') . My::jsLoad('/js/admin.min.js')
        );

        echo Notices::getNotices();

        // Add a form before the main form to upload a configuration file.
        if (isset($_GET['config-upload']) && $_GET['config-upload'] === '1') {
            $upload_form_fields = [];

            $upload_form_fields[] = (new Text('h3', __('settings-upload-title')));
            $upload_form_fields[] = (new Text('p', __('settings-upload-description')));

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
                ->action(App::backend()->url()->get('admin.blog.theme', ['module' => My::id(), 'conf' => '1', 'config-upload' => '1']))
                ->class('fieldset')
                ->enctype('multipart/form-data')
                ->method('post')
                ->fields($upload_form_fields)
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

        $settings_ignored = ['styles'];

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

        $fields[] = (new Text('p', sprintf(
            __('settings-page-intro'),
            My::name()
        )));
        $fields[] = (new Text(
            'p',
            sprintf(
                __('settings-page-forum-link'),
                'https://forum.dotclear.org/viewtopic.php?id=51635'
            )
        ));

        foreach ($settings_render as $section_id => $setting_data) {
            $settings_fields = [];

            foreach ($setting_data as $sub_section_id => $setting_id) {
                // Displays the name of the sub-section unless its ID is "no-title".
                if ($sub_section_id !== 'no-title') {
                    $settings_fields[] = (new Text('h4', My::settingsSections($section_id)['sub_sections'][$sub_section_id]))
                        ->id('section-' . $section_id . '-' . $sub_section_id);
                }

                if (isset($setting_id[0]) && $setting_id[0] === 'social_bluesky') {
                    $settings_fields[] = (new Text(
                        'p',
                        sprintf(
                            __('settings-social-notice'),
                            __('section-footer'),
                            __('section-reactions')
                        )
                    ));
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

        $fields[] = (new Hidden('page_width_em_min_default', '30'));
        $fields[] = (new Hidden('page_width_em_max_default', '80'));
        $fields[] = (new Hidden('page_width_em_step_default', '1'));
        $fields[] = (new Hidden('page_width_px_min_default', '480'));
        $fields[] = (new Hidden('page_width_px_max_default', '1280'));
        $fields[] = (new Hidden('page_width_px_step_default', '2'));
        $fields[] = (new Hidden('reset_warning', __('settings-reset-warning')));
        $fields[] = (new Hidden('config_remove_warning', __('settings-config-remove-warning')));
        $fields[] = (new Hidden('config_remove_all_warning', __('settings-config-remove-all-warning')));

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
        $backups_dir_path = My::odysseyVarFolder('path', '/backups/');

        if (is_dir($backups_dir_path)) {
            $backups_dir_data = Files::getDirList($backups_dir_path);

            if (!empty($backups_dir_data)) {
                $backups_dir_files = $backups_dir_data['files'] ?? [];

                if (!empty($backups_dir_files)) {
                    $file_list = [];

                    foreach ($backups_dir_files as $backup_path) {
                        $file_extension = Files::getExtension($backup_path);

                        if ($file_extension === 'json') {
                            $file_list[] = $backup_path;
                        }
                    }

                    if (!empty($file_list)) {
                        $table_fields = [];

                        foreach ($file_list as $file_path) {
                            $file_name_parts = explode('-', basename($file_path));

                            $file_name_date = $file_name_parts[0] ?? null;
                            $file_name_date = Date::str(App::blog()->settings()->system->date_format, strtotime($file_name_date));
                            $file_name_time = $file_name_parts[1] ?? null;
                            $file_name_time = Date::str(App::blog()->settings()->system->time_format, strtotime($file_name_time));
                            $file_datetime  = $file_name_date . ' à ' . $file_name_time;
                            $file_datetime  = sprintf(__('settings-backup-datetime'), $file_name_date, $file_name_time);

                            $file_name_without_extension = substr(basename($file_path), 0, -5);

                            $restore_url = App::backend()->url()->get(
                                'admin.blog.theme',
                                [
                                    'module'  => My::id(),
                                    'conf'    => '1',
                                    'restore' => My::escapeURL($file_name_without_extension)
                                ]
                            );

                            $download_url = Page::getVF(My::odysseyVarFolder('vf', '/backups/' . basename($backup_path)));

                            $delete_url = App::backend()->url()->get(
                                'admin.blog.theme',
                                [
                                    'module'              => My::id(),
                                    'conf'                => '1',
                                    'restore_delete_file' => My::escapeURL($file_name_without_extension)
                                ]
                            );

                            $table_fields[] = (new Tr())
                                ->class('line')
                                ->items([
                                    (new Td())
                                        ->text(sprintf(__('settings-backup-title'), Html::escapeHTML($file_datetime))),
                                    (new Td())
                                        ->items([
                                            (new Link())
                                                ->href($restore_url, false)
                                                ->text(__('settings-backup-restore-link')),
                                        ]),
                                    (new Td())
                                        ->items([
                                            (new Link())
                                                ->extra('download')
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
                            $backups_table_intro = sprintf(
                                __('settings-backups-count-multiple'),
                                count($file_list)
                            );
                        } else {
                            $backups_table_intro = __('settings-backups-count-one');
                        }

                        $delete_all_url = App::backend()->url()->get(
                            'admin.blog.theme',
                            [
                                'module'             => My::id(),
                                'conf'               => '1',
                                'restore_delete_all' => '1'
                            ]
                        );

                        $fields[] = (new Div('odyssey-backups'))
                            ->items([
                                (new Text('h3', __('settings-backups-title'))),
                                (new Text('p', $backups_table_intro)),
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
                                        ])
                            ]);
                    }
                }
            }
        }

        echo (new Form('theme-config-form'))
            ->action(App::backend()->url()->get('admin.blog.theme', ['module' => My::id(), 'conf' => '1']))
            ->enctype('multipart/form-data')
            ->method('post')
            ->fields($fields)
            ->render();

        Page::closeModule();
    }
}
