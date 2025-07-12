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
        $default_settings = My::settingsDefault();

        $specific_settings = [
            'global_page_width_value',
            'header_image',
            'header_image2x',
            'styles'
        ];

        $header_image_name = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
            try {
                // If the save button has been clicked.
                if (isset($_POST['save']) || isset($_POST['save-config'])) {
                    $styles_custom = '';

                    /**
                     * This part saves each setting in the database
                     * only if there are different than the default one.
                     */
                    foreach ($default_settings as $setting_id => $setting_data) {
                        // Now, set the value of the setting and its type.
                        $setting_data  = [];
                        $setting_value = $_POST[$setting_id] ?? null;
                        $setting_type  = $default_settings[$setting_id]['type'] ?? null;

                        if (array_key_exists($setting_id, $default_settings)
                            && !in_array($setting_id, $specific_settings, true)
                            && !str_starts_with($setting_id, 'social_')
                        ) {
                            // Prepares non specific settings to save.
                            if ($setting_value != $default_settings[$setting_id]['default']) {
                                // Prepares data if the value if different than the default one.
                                $setting_data = self::sanitizeSetting($setting_type, $setting_id, $setting_value);
                            } else {
                                // Otherwise, deletes the value.
                                App::blog()->settings->odyssey->drop($setting_id);
                            }
                        } elseif (!str_starts_with($setting_id, 'social_')) {
                            // Prepares value for each specific settings.
                            switch ($setting_id) {
                                case 'global_page_width_value' :
                                    $setting_data = self::sanitizePageWidth(
                                        $_POST['global_unit'],
                                        $_POST['global_page_width_value'],
                                        $setting_id
                                    );

                                    break;
                                case 'header_image' :
                                    $image_file = $_FILES[$setting_id] ?? [];
                                    $image_size = $image_file['size'] ?? 0;

                                    if (!empty($image_file) && $image_size > 0) {
                                        $setting_data = self::sanitizeHeaderImage(
                                            $image_file,
                                            $setting_id,
                                            $_POST['global_unit'],
                                            $_POST['global_page_width_value']
                                        );

                                        $img_folder_path = My::odysseyPublicFolder('path', '/img/');

                                        if (!is_dir($img_folder_path)) {
                                            mkdir($img_folder_path);
                                        } else {
                                            My::deleteDirectory($img_folder_path, true);
                                        }

                                        $image_path_tmp = $setting_data['value']['path_tmp'] ?? null;
                                        $image_name     = $setting_data['value']['name'] ?? null;
                                        $image_path     = $img_folder_path . $image_name;
                                        $image_url      = My::odysseyPublicFolder('url', '/img/' . $image_name);

                                        $header_image_name = $image_name;

                                        if ($image_path_tmp && $image_name) {
                                            if (move_uploaded_file($image_path_tmp, $image_path)) {
                                                unset($setting_data['value']['path_tmp']);

                                                $setting_data['value']['url'] = $image_url;
                                            } else {
                                                $setting_data = [];
                                            }
                                        }
                                    }

                                    if ($_POST['header_image-delete-action'] === "true") {
                                        App::blog()->settings->odyssey->drop($setting_id);
                                        App::blog()->settings->odyssey->drop('header_image2x');

                                        My::deleteDirectory(My::odysseyPublicFolder('path', '/img/'));
                                    }

                                    break;
                                case 'header_image2x' :
                                    $image2x_file = $_FILES[$setting_id] ?? [];
                                    $image2x_size = $image2x_file['size'] ?? 0;

                                    if (!empty($image2x_file) && (int) $image2x_size > 0) {
                                        $setting_data = self::sanitizeHeaderImage(
                                            $image2x_file,
                                            $setting_id,
                                            $_POST['global_unit'],
                                            $_POST['global_page_width_value']
                                        );

                                        $img_folder_path = My::odysseyPublicFolder('path', '/img/');

                                        $image2x_path_tmp = $setting_data['value']['path_tmp'] ?? null;
                                        $image2x_name     = $setting_data['value']['name'] ?? null;
                                        $image2x_path     = $img_folder_path . $image2x_name;
                                        $image2x_url      = My::odysseyPublicFolder('url', '/img/' . $image2x_name);

                                        $header_image2x_name = $image2x_name;

                                        if ($image2x_path_tmp && $image2x_name && $header_image2x_name !== $header_image_name) {
                                            if (move_uploaded_file($image2x_path_tmp, $image2x_path)) {
                                                unset($setting_data['value']['path_tmp']);

                                                $setting_data['value']['url'] = $image2x_url;
                                            } else {
                                                $setting_data = [];
                                            }
                                        }
                                    }

                                    break;
                                case 'styles' :
                                    $setting_data  = self::saveStyles();
                                    $styles_custom = $setting_data['value'] ?? '';
                            }
                        } else {
                            // The rest should be social links only.
                            $setting_data = self::sanitizeSocialLink($setting_id, $setting_value);
                        }

                        // Saves the setting data or drop it if empty.
                        if (!empty($setting_data)) {
                            $setting_value = $setting_data['value'] ?? null;
                            $setting_type  = $setting_data['type']  ?? null;
                            $setting_label = Html::clean(Html::escapeHTML($default_settings[$setting_id]['title']));

                            if ($setting_type) {
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
                        } elseif ($setting_id !== 'header_image' && $setting_id !== 'header_image2x') {
                            App::blog()->settings->odyssey->drop($setting_id);
                        }
                    }

                    // Creates a CSS file if necessary.
                    self::_stylesCustomFile($styles_custom);

                    // Refreshes the blog.
                    App::blog()->triggerBlog();

                    // Resets template cache.
                    App::cache()->emptyTemplatesCache();

                    // Displays a success notice.
                    if (isset($_POST['save'])) {
                        Notices::addSuccessNotice(__('settings-notice-saved'));
                    }

                    // Redirects.
                    $redirect_params = [
                        'module' => My::id(),
                        'conf' => '1'
                    ];

                    if (isset($_POST['save-config'])) {
                        $redirect_params['save-config'] = 'create-file';
                    }

                    App::backend()->url()->redirect('admin.blog.theme', $redirect_params);
                } elseif (isset($_POST['reset'])) {
                    // Remove all saved settings from the database.
                    App::blog()->settings->odyssey->dropAll();

                    // Removes the header image and its folder.
                    Files::deltree(My::odysseyPublicFolder('path', '/img/'));

                    // Removes the custom CSS file if it exists.
                    $css_custom_path = My::odysseyPublicFolder('path', '/css/style.min.css');

                    if (file_exists($css_custom_path)) {
                        ThemeConfig::dropCss(
                            My::id() . '/css/',
                            'style.min'
                        );
                    }

                    // Removes the "css" subfolder in the "public" folder if it exists.
                    $css_path_rel_folder = My::id() . '/css';

                    if (Files::isDeletable(ThemeConfig::cssPath($css_path_rel_folder))) {
                        Files::deltree(ThemeConfig::cssPath($css_path_rel_folder));
                    }

                    App::blog()->triggerBlog();
                    App::cache()->emptyTemplatesCache();
                    Notices::addSuccessNotice(__('settings-notice-reset'));
                    App::backend()->url()->redirect('admin.blog.theme', ['module' => My::id(), 'conf' => '1']);
                } elseif (isset($_POST['import-config'])) {
                    // When a configuration file is uploaded, redirects to the upload page.
                    App::backend()->url()->redirect('admin.blog.theme', ['module' => My::id(), 'conf' => '1', 'config-upload' => '1']);
                } elseif (isset($_POST['config-upload-submit'])) {
                    // When a configuration file has been submitted, uploads it.
                    if (!empty($_FILES['config-upload-file']) && $_FILES['config-upload-file']['error'] === UPLOAD_ERR_OK) {
                        $file_tmp_path = $_FILES['config-upload-file']['tmp_name'];
                        $file_type     = $_FILES['config-upload-file']['type'];

                        $json_content   = file_get_contents($file_tmp_path);
                        $settings_array = [];
                        $styles_custom  = '';

                        if ($file_type === 'application/json' && $json_content) {
                            $settings_array = json_decode($json_content, true);

                            if (!empty($settings_array)) {
                                // Drops all settings.
                                App::blog()->settings->odyssey->dropAll();

                                // Imports all settings.
                                foreach ($settings_array as $setting_id => $setting_value) {
                                    if (array_key_exists($setting_id, $default_settings)
                                        && !in_array($setting_id, $specific_settings, true)
                                        && !str_starts_with($setting_id, 'social_')
                                    ) {
                                        // Prepares non specific settings to save.
                                        $setting_type  = $default_settings[$setting_id]['type'] ?? null;

                                        if ($setting_value != $default_settings[$setting_id]['default']) {
                                            // Prepares data if the value if different than the default one.
                                            $setting_data = self::sanitizeSetting($setting_type, $setting_id, $setting_value);
                                        } else {
                                            // Otherwise, deletes the value.
                                            App::blog()->settings->odyssey->drop($setting_id);
                                        }
                                    } elseif (!str_starts_with($setting_id, 'social_')) {
                                        // Prepares value for each specific settings.
                                        switch ($setting_id) {
                                            case 'global_page_width_value' :
                                                $setting_data = self::sanitizePageWidth(
                                                    $settings_array['global_unit'] ?? $default_settings['global_unit']['default'],
                                                    $settings_array['global_page_width_value'] ?? $default_settings['global_page_width_value']['default'],
                                                    $setting_id
                                                );

                                                break;
                                            case 'header_image' :
                                            case 'header_image2x' :
                                                $setting_data['value'] = [];

                                                $setting_data['value']['name'] = isset($setting_value['name']) ? Files::tidyFileName($setting_value['name']) : null;

                                                if ($setting_id !== 'header_image2x') {
                                                    $setting_data['value']['width'] = $setting_value['width'] ?? null;

                                                    if (isset($setting_data['value']['width']) && ((int) $setting_data['value']['width'] <= 0 || (int) $setting_data['value']['width'] > 100)) {
                                                        $setting_data['value']['width'] = null;
                                                    }
                                                }

                                                $setting_data['value']['url'] = $setting_value['url'] ?? null;

                                                $img_folder_url = My::odysseyPublicFolder('url', '/img');

                                                if (isset($setting_data['value']['url']) && !str_starts_with($setting_data['value']['url'], $img_folder_url)) {
                                                    $setting_data['value']['url'] = null;
                                                }

                                                if (!isset($setting_data['value']['name']) || !isset($setting_data['value']['name']) || !isset($setting_data['value']['name'])) {
                                                    $setting_data = [];
                                                }

                                                $setting_data['type'] = 'array';

                                                break;
                                            case 'styles' :
                                                $setting_data  = self::saveStyles();
                                                $styles_custom = $settings_array['styles'] ?? '';
                                        }
                                    } else {
                                        // The rest should be social links only.
                                        $setting_data = self::sanitizeSocialLink($setting_id, $setting_value);
                                    }

                                    if (!empty($setting_data)) {
                                        $setting_value = $setting_data['value'] ?? null;

                                        $setting_type  = $setting_data['type']  ?? null;
                                        $setting_label = Html::clean(Html::escapeHTML($default_settings[$setting_id]['title']));

                                        if ($setting_type) {
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

                                self::_stylesCustomFile($styles_custom);

                                App::blog()->triggerBlog();
                                App::cache()->emptyTemplatesCache();
                                Notices::addSuccessNotice(__('settings-notice-upload-success'));
                                App::backend()->url()->redirect('admin.blog.theme', ['module' => My::id(), 'conf' => '1']);
                            } else {
                                Notices::addErrorNotice(__('settings-notice-upload-file-not-valid'));
                                App::backend()->url()->redirect('admin.blog.theme', ['module' => My::id(), 'conf' => '1', 'config-upload' => '1']);
                            }
                        } else {
                            // If the uploaded file is not a JSON file.
                            Notices::addErrorNotice(__('settings-notice-upload-file-not-valid'));
                            App::backend()->url()->redirect('admin.blog.theme', ['module' => My::id(), 'conf' => '1', 'config-upload' => '1']);
                        }
                    } else {
                        // If there is no file uploaded.
                        Notices::addErrorNotice(__('settings-notice-upload-no-file'));
                        App::backend()->url()->redirect('admin.blog.theme', ['module' => My::id(), 'conf' => '1', 'config-upload' => '1']);
                    }
                } elseif (isset($_POST['config-upload-cancel'])) {
                    // Redirects if the cancel upload button is clicked.
                    App::backend()->url()->redirect('admin.blog.theme', ['module' => My::id(), 'conf' => '1']);
                }
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET)) {
            try {
                if (isset($_GET['save-config']) && $_GET['save-config'] === 'create-file') {
                    // Creates a backup file.
                    $path = My::odysseyVarFolder('path', '/backups/');

                    // Creates the var/odyssey/backups folder if it doesn't exist.
                    if (Path::real($path) === false) {
                        Files::makeDir($path, true);
                    }

                    // Sets the name of the backup file.
                    $time = str_replace(':', '', Date::str('%Y%m%d', time(), App::blog()->settings()->system->blog_timezone) . '-' . Date::str('%T', time(), App::blog()->settings()->system->blog_timezone));

                    $file_name = Files::tidyFileName($time . '-settings');

                    $path .= $file_name . '.json';

                    // Puts all the settings in an array.
                    $saved_settings = [];

                    foreach (self::settingsSaved() as $setting_id => $setting_value) {
                        $saved_settings[$setting_id] = $setting_value;
                    }

                    if (!empty($saved_settings)) {
                        Files::putContent($path, json_encode($saved_settings));

                        Notices::addNotice(
                            'success',
                            '<p>' . sprintf(__('settings-notice-save-success'), My::id(), '#odyssey-backups') . '</p>' .
                            '<a class="button submit" href=' . My::escapeAttr(Html::escapeURL(urldecode(Page::getVF(My::odysseyVarFolder('vf', '/backups/' . $file_name . '.json'))))) . ' download>' . __('settings-notice-save-success-link') . '</a>',
                            ['divtag' => true]
                        );
                    } else {
                        // If no custom option has been set.
                        Notices::addErrorNotice(__('settings-notice-save-fail'));
                    }

                    App::backend()->url()->redirect('admin.blog.theme', ['module' => My::id(), 'conf' => '1']);
                } elseif (isset($_GET['restore']) && $_GET['restore'] !== 'success') {
                    // Restores a configuration from a backup file listed from /var/odyssey/backups.
                    $restore_file_name    = $_GET['restore'] . '.json';
                    $restore_file_path    = My::odysseyVarFolder('path', '/backups/' . $restore_file_name);
                    $restore_file_content = file_get_contents($restore_file_path);

                    $settings_array = [];

                    if ($restore_file_content && $restore_file_content !== '[]') {
                        $settings_array = json_decode($restore_file_content, true);
                        $styles_custom  = '';

                        if (!empty($settings_array)) {
                            // Drops all settings.
                            App::blog()->settings->odyssey->dropAll();

                            // Imports all settings.
                            foreach ($settings_array as $setting_id => $setting_value) {
                                if (array_key_exists($setting_id, $default_settings)
                                    && !in_array($setting_id, $specific_settings, true)
                                    && !str_starts_with($setting_id, 'social_')
                                ) {
                                    $setting_type  = $default_settings[$setting_id]['type'] ?? null;

                                    if ($setting_value != $default_settings[$setting_id]['default']) {
                                        // Prepares data if the value if different than the default one.
                                        $setting_data = self::sanitizeSetting($setting_type, $setting_id, $setting_value);
                                    } else {
                                        // Otherwise, deletes the value.
                                        App::blog()->settings->odyssey->drop($setting_id);
                                    }
                                } else {
                                    // Prepares value for each specific settings.
                                    if (!str_starts_with($setting_id, 'social_')) {
                                        switch ($setting_id) {
                                            case 'global_page_width_value' :
                                                $setting_data = self::sanitizePageWidth(
                                                    $settings_array['global_unit'] ?? $default_settings['global_unit']['default'],
                                                    $settings_array['global_page_width_value'] ?? $default_settings['global_page_width_value']['default'],
                                                    $setting_id
                                                );

                                                break;
                                            case 'header_image' :
                                            case 'header_image2x' :
                                                $setting_data['value'] = [];

                                                $setting_data['value']['name'] = isset($setting_value['name']) ? Files::tidyFileName($setting_value['name']) : null;

                                                if ($setting_id !== 'header_image2x') {
                                                    $setting_data['value']['width'] = $setting_value['width'] ?? null;

                                                    if (isset($setting_data['value']['width']) && ((int) $setting_data['value']['width'] <= 0 || (int) $setting_data['value']['width'] > 100)) {
                                                        $setting_data['value']['width'] = null;
                                                    }
                                                }

                                                $setting_data['value']['url'] = $setting_value['url'] ?? null;

                                                $img_folder_url = My::odysseyPublicFolder('url', '/img');

                                                if (isset($setting_data['value']['url']) && !str_starts_with($setting_data['value']['url'], $img_folder_url)) {
                                                    $setting_data['value']['url'] = null;
                                                }

                                                if (!isset($setting_data['value']['name']) || !isset($setting_data['value']['name']) || !isset($setting_data['value']['name'])) {
                                                    $setting_data = [];
                                                }

                                                $setting_data['type'] = 'array';

                                                break;
                                            case 'styles' :
                                                $setting_data  = self::saveStyles();
                                                $styles_custom = $settings_array['styles'] ?? '';
                                        }
                                    } else {
                                        // The rest should be social links only.
                                        $setting_data = self::sanitizeSocialLink($setting_id, $setting_value);
                                    }
                                }

                                if (!empty($setting_data)) {
                                    $setting_value = $setting_data['value'] ?? null;
                                    $setting_type  = $setting_data['type']  ?? null;
                                    $setting_label = Html::clean(Html::escapeHTML($default_settings[$setting_id]['title']));

                                    if ($setting_type) {
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

                            self::_stylesCustomFile($styles_custom);

                            App::blog()->triggerBlog();
                            App::cache()->emptyTemplatesCache();
                            Notices::addSuccessNotice(__('settings-notice-restore-success'));
                            App::backend()->url()->redirect('admin.blog.theme', ['module' => My::id(), 'conf' => '1']);
                        }
                    } else {
                        // If the file is empty.
                        Notices::addErrorNotice(__('settings-notice-restore-error'));
                        App::backend()->url()->redirect('admin.blog.theme', ['module' => My::id(), 'conf' => '1']);
                    }
                } elseif (isset($_GET['restore_delete_file'])) {
                    // Deletes a configuration file.

                    $delete_file_name = $_GET['restore_delete_file'] . '.json';
                    $odyssey_folder   = My::odysseyVarFolder('path');
                    $backups_folder   = My::odysseyVarFolder('path', '/backups/');
                    $delete_file_path = Path::real($backups_folder . $delete_file_name);

                    if ($delete_file_path) {
                        // Deletes the file and directories if empty.
                        unlink($delete_file_path);

                        if (Path::real($odyssey_folder)
                            && Path::real($backups_folder)
                            && empty(Files::getDirList($backups_folder)['files'])
                        ) {
                            Files::deltree($odyssey_folder);
                        }

                        Notices::addSuccessNotice(__('settings-notice-file-deleted'));
                        App::backend()->url()->redirect('admin.blog.theme', ['module' => My::id(), 'conf' => '1']);
                    }
                } elseif (isset($_GET['restore_delete_all'])) {
                    // Deletes all configuration files.
                    $odyssey_folder = Path::real(My::odysseyVarFolder('path'));

                    if ($odyssey_folder) {
                        Files::deltree($odyssey_folder);
                    }

                    Notices::addSuccessNotice(__('settings-notice-files-deleted'));
                    App::backend()->url()->redirect('admin.blog.theme', ['module' => My::id(), 'conf' => '1']);
                }
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        return true;
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
                                ->checked((bool) $setting_value)
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
                        'value' => (int) My::settingValue($setting_id) ?: (int) $default_settings[$setting_id]['default'],
                        'min'   => (int) $default_settings[$setting_id]['range']['min'],
                        'max'   => (int) $default_settings[$setting_id]['range']['max'],
                        'step'  => (int) $default_settings[$setting_id]['range']['step']
                    ];

                    if (My::settingValue('global_unit') === 'px') {
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
                        (new Img(Html::escapeURL($image_src), 'header_image-src'))
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

            $the_setting[] = (new Hidden('header_image-defined', $setting_value ? "true" : "false"));
            $the_setting[] = (new Hidden('header_image-delete-action', "false"));
            $the_setting[] = (new Hidden('header_image-url', $image_src));
            $the_setting[] = (new Hidden('header_image-retina-text', __('header_image-retina-ready')));
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

        $fields[] = (new Text('p', __('settings-page-intro')));
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
                                    'restore' => Html::escapeURL($file_name_without_extension)
                                ]
                            );

                            $download_url = Page::getVF(My::odysseyVarFolder('vf', '/backups/' . basename($backup_path)));

                            $delete_url = App::backend()->url()->get(
                                'admin.blog.theme',
                                [
                                    'module'              => My::id(),
                                    'conf'                => '1',
                                    'restore_delete_file' => Html::escapeURL($file_name_without_extension)
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
                                                ->href(Html::escapeURL($download_url))
                                                ->extra('download')
                                                ->text(__('settings-backup-download-link'))
                                        ]),
                                    (new Td())
                                        ->items([
                                            (new Link())
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

        // Page width
        $global_unit = $_POST['global_unit']             ?? null;
        $page_width  = $_POST['global_page_width_value'] ?? null;

        if ($global_unit) {
            $page_width_data  = self::sanitizePageWidth($global_unit, $page_width);
            $page_width_value = $page_width_data['value'] ?? null;
            $page_width_unit  = $page_width_data['unit']  ?? null;

            if ($page_width_value && $page_width_unit) {
                $css_root_array[':root']['--page-width'] = $page_width_value . $page_width_unit;
            }
        }

        // Font family
        if (isset($_POST['global_font_family']) && $_POST['global_font_family'] !== 'system') {
            $css_root_array[':root']['--font-family'] = My::fontStack($_POST['global_font_family']);
        }

        // Font size
        $font_size_allowed = [80, 90, 110, 120];

        if (isset($_POST['global_font_size']) && in_array((int) $_POST['global_font_size'], $font_size_allowed, true)) {
            $css_root_array[':root']['--font-size'] = My::removeZero((int) $_POST['global_font_size'] / 100) . 'em';
        }

        // Font antialiasing
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

        if (isset($_POST['global_color_primary']) && $_POST['global_color_primary'] === 'custom') {
            // Main text color.
            if (isset($_POST['global_color_text_custom'])
                && isset($_POST['global_color_text_custom-default-value'])
                && self::isHexColor($_POST['global_color_text_custom'])
                && $_POST['global_color_text_custom'] !== $_POST['global_color_text_custom-default-value']
            ) {
                $css_root_array[':root']['--color-text-main'] = $_POST['global_color_text_custom'];
            }

            if (isset($_POST['global_color_text_dark_custom'])
                && isset($_POST['global_color_text_dark_custom-default-value'])
                && self::isHexColor($_POST['global_color_text_dark_custom'])
                && $_POST['global_color_text_dark_custom'] !== $_POST['global_color_text_dark_custom-default-value']
            ) {
                $css_root_array[':root']['--color-text-main-dark'] = $_POST['global_color_text_dark_custom'];
            }

            // Text secondary color
            if (isset($_POST['global_color_text_secondary_custom'])
                && isset($_POST['global_color_text_secondary_custom-default-value'])
                && self::isHexColor($_POST['global_color_text_secondary_custom'])
                && $_POST['global_color_text_secondary_custom'] !== $_POST['global_color_text_secondary_custom-default-value']
            ) {
                $css_root_array[':root']['--color-text-secondary'] = $_POST['global_color_text_secondary_custom'];
            }

            if (isset($_POST['global_color_text_secondary_dark_custom'])
                && isset($_POST['global_color_text_secondary_dark_custom-default-value'])
                && self::isHexColor($_POST['global_color_text_secondary_dark_custom'])
                && $_POST['global_color_text_secondary_dark_custom'] !== $_POST['global_color_text_secondary_dark_custom-default-value']
            ) {
                $css_root_array[':root']['--color-text-secondary-dark'] = $_POST['global_color_text_secondary_dark_custom'];
            }

            // Input color
            if (isset($_POST['global_color_input_custom'])
                && isset($_POST['global_color_input_custom-default-value'])
                && self::isHexColor($_POST['global_color_input_custom'])
                && $_POST['global_color_input_custom'] !== $_POST['global_color_input_custom-default-value']
            ) {
                $css_root_array[':root']['--color-input-background'] = $_POST['global_color_input_custom'];
            }

            if (isset($_POST['global_color_input_dark_custom'])
                && isset($_POST['global_color_input_dark_custom-default-value'])
                && self::isHexColor($_POST['global_color_input_dark_custom'])
                && $_POST['global_color_input_dark_custom'] !== $_POST['global_color_input_dark_custom-default-value']
            ) {
                $css_root_array[':root']['--color-input-background-dark'] = $_POST['global_color_input_dark_custom'];
            }

            // Border color
            if (isset($_POST['global_color_border_custom'])
                && isset($_POST['global_color_border_custom-default-value'])
                && self::isHexColor($_POST['global_color_border_custom'])
                && $_POST['global_color_border_custom'] !== $_POST['global_color_border_custom-default-value']
            ) {
                $css_root_array[':root']['--color-border'] = $_POST['global_color_border_custom'];
            }

            if (isset($_POST['global_color_border_dark_custom'])
                && isset($_POST['global_color_border_dark_custom-default-value'])
                && self::isHexColor($_POST['global_color_border_dark_custom'])
                && $_POST['global_color_border_dark_custom'] !== $_POST['global_color_border_dark_custom-default-value']
            ) {
                $css_root_array[':root']['--color-border-dark'] = $_POST['global_color_border_dark_custom'];
            }

            // Background color
            if (isset($_POST['global_color_background_custom'])
                && isset($_POST['global_color_background_custom-default-value'])
                && self::isHexColor($_POST['global_color_background_custom'])
                && $_POST['global_color_background_custom'] !== $_POST['global_color_background_custom-default-value']
            ) {
                $css_root_array[':root']['--color-background'] = $_POST['global_color_background_custom'];
            }

            if (isset($_POST['global_color_background_dark_custom'])
                && isset($_POST['global_color_background_custom-default-value'])
                && self::isHexColor($_POST['global_color_background_dark_custom'])
                && $_POST['global_color_background_dark_custom'] !== $_POST['global_color_background_dark_custom-default-value']
            ) {
                $css_root_array[':root']['--color-background-dark'] = $_POST['global_color_background_dark_custom'];
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

        if (isset($_POST['global_color_primary'])) {
            if ($_POST['global_color_primary'] !== 'custom' && in_array($_POST['global_color_primary'], $primary_colors_allowed, true)) {
                // Light
                $color_primary_light = $primary_colors['light'][$_POST['global_color_primary']];

                $css_root_array[':root']['--color-primary'] = $color_primary_light;

                // Light & amplified
                if (isset($primary_colors['light-amplified'][$_POST['global_color_primary']])) {
                    $color_primary_amplified_light = $primary_colors['light-amplified'][$_POST['global_color_primary']];

                    $css_root_array[':root']['--color-primary-amplified'] = $color_primary_amplified_light;
                }

                // Dark
                if (isset($primary_colors['dark'][$_POST['global_color_primary']])) {
                    $color_primary_dark = $primary_colors['dark'][$_POST['global_color_primary']];

                    $css_root_array[':root']['--color-primary-dark'] = $color_primary_dark;
                }

                // Dark & amplified
                if (isset($primary_colors['dark-amplified'][$_POST['global_color_primary']])) {
                    $color_primary_amplified_dark = $primary_colors['dark-amplified'][$_POST['global_color_primary']];

                    $css_root_array[':root']['--color-primary-dark-amplified'] = $color_primary_amplified_dark;
                }
            } elseif ($_POST['global_color_primary'] === 'custom') {
                if (isset($_POST['global_color_primary_custom'])
                    && isset($_POST['global_color_primary_custom-default-value'])
                    && self::isHexColor($_POST['global_color_primary_custom'])
                    && $_POST['global_color_primary_custom'] !== $_POST['global_color_primary_custom-default-value']
                ) {
                    $color_primary_light = $_POST['global_color_primary_custom'];

                    $css_root_array[':root']['--color-primary'] = $color_primary_light;
                }

                if (isset($_POST['global_color_primary_amplified_custom'])
                    && isset($_POST['global_color_primary_amplified_custom-default-value'])
                    && self::isHexColor($_POST['global_color_primary_amplified_custom'])
                    && $_POST['global_color_primary_amplified_custom'] !== $_POST['global_color_primary_amplified_custom-default-value']
                ) {
                    $color_primary_amplified_light = $_POST['global_color_primary_amplified_custom'];

                    $css_root_array[':root']['--color-primary-amplified'] = $color_primary_amplified_light;
                }

                if (isset($_POST['global_color_primary_dark_custom'])
                    && isset($_POST['global_color_primary_dark_custom-default-value'])
                    && self::isHexColor($_POST['global_color_primary_dark_custom'])
                    && $_POST['global_color_primary_dark_custom'] !== $_POST['global_color_primary_dark_custom-default-value']
                ) {
                    $color_primary_dark = $_POST['global_color_primary_dark_custom'];

                    $css_root_dark_array[':root']['--color-primary-dark'] = $color_primary_dark;
                }

                if (isset($_POST['global_color_primary_dark_amplified_custom'])
                    && isset($_POST['global_color_primary_dark_amplified_custom-default-value'])
                    && self::isHexColor($_POST['global_color_primary_dark_amplified_custom'])
                    && $_POST['global_color_primary_dark_amplified_custom'] !== $_POST['global_color_primary_dark_amplified_custom-default-value']
                ) {
                    $color_primary_amplified_dark = $_POST['global_color_primary_dark_amplified_custom'];

                    $css_root_dark_array[':root']['--color-primary-dark-amplified'] = $color_primary_amplified_dark;
                }
            }
        }

        // Color scheme
        if (isset($_POST['global_color_scheme'])) {
            if ($_POST['global_color_scheme'] === 'light') {
                $css_root_array[':root']['--color-background-dark']        = '#fafafa';
                $css_root_array[':root']['--color-text-main-dark']         = '#303030';
                $css_root_array[':root']['--color-text-secondary-dark']    = '#6c6f78';
                $css_root_array[':root']['--color-primary-dark']           = $color_primary_light;
                $css_root_array[':root']['--color-primary-dark-amplified'] = $color_primary_amplified_light;
                $css_root_array[':root']['--color-input-background-dark']  = '#f2f2f2';
                $css_root_array[':root']['--color-border-dark']            = '#ccc';
            } elseif ($_POST['global_color_scheme'] === 'dark') {
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
        if (isset($_POST['global_css_transition']) && $_POST['global_css_transition'] === 'on') {
            $css_root_array[':root']['--color-transition'] = 'all .2s ease-in-out';

            $css_media_motion_array[':root']['--color-transition'] = 'unset';
        }

        // Links underline
        if (isset($_POST['global_links_underline']) && $_POST['global_links_underline'] === 'on') {
            $css_root_array[':root']['--link-text-decoration']       = 'underline';
            $css_root_array[':root']['--link-text-decoration-style'] = 'dotted';
        }

        // Border radius
        if (isset($_POST['global_border_radius']) && $_POST['global_border_radius'] === 'on') {
            $css_root_array[':root']['--border-radius'] = '.168em';
        }

        // Header alignment
        $header_align_allowed = ['left', 'right'];

        if (isset($_POST['header_align']) && in_array($_POST['header_align'], $header_align_allowed, true)) {
            $css_root_array[':root']['--header-align'] = $_POST['header_align'];
        }

        // Header image
        if ((isset($_FILES['header_image']['name']) && $_FILES['header_image']['name'] !== '')
            || (isset($_POST['header_image-defined']) && $_POST['header_image-defined'] === 'true')
        ) {
            $css_main_array['#site-image']['width'] = '100%';

            $css_main_array['#site-image a']['display']       = 'block';
            $css_main_array['#site-image a']['outline-width'] = '.168em';

            $css_main_array['#site-image img']['display'] = 'inline-block';
        }

        // Post list type
        if (isset($_POST['content_postlist_type']) && $_POST['content_postlist_type'] === 'excerpt') {
            $css_main_array['.entry-list-excerpt .post']['margin']  = '1em -1rem';
            $css_main_array['.entry-list-excerpt .post']['padding'] = '1rem';

            $css_main_array['.entry-list-excerpt .post:first-child']['margin-top']   = '0';
            $css_main_array['.entry-list-excerpt .post:last-child']['margin-bottom'] = '0';

            $css_main_array['.entry-list-excerpt .entry-title']['font-size']    = '1.1rem';
            $css_main_array['.entry-list-excerpt .entry-title']['margin-block'] = '.5rem';

            $css_main_array['.entry-list-excerpt .post-excerpt']['margin-block'] = '.5rem';
        } elseif (isset($_POST['content_postlist_type']) && $_POST['content_postlist_type'] === 'content') {
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

        // Alternate post color
        if (isset($_POST['content_postlist_altcolor']) && $_POST['content_postlist_altcolor'] === 'on') {
            $css_main_array[':is(.entry-list, .entry-list-excerpt) .post:nth-child(even)']['background-color'] = 'var(--color-input-background, #f2f2f2)';
            $css_main_array[':is(.entry-list, .entry-list-excerpt) .post:nth-child(even)']['border-radius'] = 'var(--border-radius, unset)';
        }

        // Post thumbnail
        if (isset($_POST['content_postlist_thumbnail']) && $_POST['content_postlist_thumbnail'] === 'on') {
            if (isset($_POST['content_postlist_type']) && $_POST['content_postlist_type'] === 'excerpt') {
                $css_main_array['.post-list-excerpt']['display'] = 'block';

                $css_main_array['.entry-list-excerpt-img']['display']      = 'block';
                $css_main_array['.entry-list-excerpt-img']['margin-block'] = '1rem';

                if (isset($_POST['content_images_grayscale']) && $_POST['content_images_grayscale'] === 'on') {
                    $css_main_array['.entry-list-excerpt-img']['transition']                          = 'var(--color-transition, unset)';
                    $css_main_array['.entry-list-excerpt-img:not(:active, :focus, :hover)']['filter'] = 'grayscale(1)';
                }
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
            $css_root_array[':root']['--content-font-size'] = My::removeZero((int) $_POST['content_font_size'] / 100) . 'em';
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

        // Wide images
        if (isset($_POST['content_images_wide']) && $_POST['content_images_wide'] === 'on') {
            $css_main_array['body']['overflow-x'] = 'hidden';

            $css_main_array['.odyssey-img-wide']['display']     = 'block';
            $css_main_array['.odyssey-img-wide']['margin-left'] = '50%';
            $css_main_array['.odyssey-img-wide']['transform']   = 'translateX(-50%)';
            $css_main_array['.odyssey-img-wide']['max-width']   = '95vw';
        }

        // Grayscale images
        if (isset($_POST['content_images_grayscale']) && $_POST['content_images_grayscale'] === 'on') {
            $css_main_array['.content-text img']['transition']                          = 'var(--color-transition, unset)';
            $css_main_array['.content-text img:not(:active, :focus, :hover)']['filter'] = 'grayscale(1)';

            if (isset($_POST['content_postlist_thumbnail']) && $_POST['content_postlist_thumbnail'] === 'on') {
                if (isset($_POST['content_postlist_type']) && $_POST['content_postlist_type'] === 'one-line') {
                    $css_main_array['.entry-list-img']['transition']                          = 'var(--color-transition, unset)';
                    $css_main_array['.entry-list-img:not(:active, :focus, :hover)']['filter'] = 'grayscale(1)';
                }
            }
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

            $css_media_contrast_array['.reactions-button:is(:active, :focus, :hover) .reactions-button-icon.social-icon-si']['fill'] = 'var(--color-background)';
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
                'value' => $setting_value,
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

            if ($setting_value === null && $default_settings[$setting_id]['default'] !== false) {
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
            if (self::isHexColor($setting_value) === true) {
                return [
                    'value' => strtolower($setting_value),
                    'type'  => 'string'
                ];
            }
        }

        if ($setting_type === 'range') {
            $setting_value = (int) $setting_value;
            $range_min     = $default_settings[$setting_id]['range']['min'];
            $range_max     = $default_settings[$setting_id]['range']['max'];

            if ($setting_value >= $range_min && $setting_value <= $range_max) {
                return [
                    'value' => $setting_value,
                    'type'  => 'integer'
                ];
            } else {
                return [
                    'value' => $default_settings[$setting_id]['default'],
                    'type'  => 'integer'
                ];
            }
        }

        if ($setting_value != $default_settings[$setting_id]['default']) {
            return [
                'value' => filter_var($setting_value, FILTER_SANITIZE_SPECIAL_CHARS),
                'type'  => 'string'
            ];
        }

        return [];
    }

    public static function sanitizeHeaderImage(
        array  $image_file,
        string $setting_id,
        string $page_width_unit,
        string $page_width_value
    ): array
    {
        if (!empty($image_file)) {
            if (isset($image_file['error']) && $image_file['error'] === UPLOAD_ERR_OK) {
                $file_name = isset($image_file['name']) ? Files::tidyFileName($image_file['name']) : null;
                $file_path = $image_file['tmp_name'] ?? null;
                $file_type = $image_file['type']     ?? null;

                $mime_types_supported = Files::mimeTypes();

                if (file_exists($file_path)
                    && str_starts_with($file_type, 'image/')
                    && in_array($file_type, $mime_types_supported, true)
                ) {
                    if ($setting_id === 'header_image') {
                        // Gets the dimensions of the image.
                        list($header_image_width) = getimagesize($file_path);

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

                        $image_data = [
                            'name'     => $file_name,
                            'path_tmp' => $file_path,
                            'width'    => (int) $header_image_width
                        ];
                    } else {
                        $image_data = [
                            'name'     => $file_name,
                            'path_tmp' => $file_path
                        ];
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
        $units_allowed = ['em', 'px'];

        $unit  = in_array($unit, $units_allowed, true) ? $unit : 'em';
        $value = (int) $value ?: 30;

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
     * @param string $setting_id The social setting id.
     * @param string $value      The value of the social setting.
     *
     * @return array The value of the setting and its type.
     */
    public static function sanitizeSocialLink(string $setting_id, string $value): array
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
                        'value' => 'https://signal.me/#p/' . Html::escapeURL($value),
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
     * Checks if the input is an Hex color code.
     *
     * @param string $color The Hex color code.
     *
     * @return bool
     */
    public static function isHexColor(string $color): bool
    {
        if (preg_match('/#[A-Fa-f0-9]{6}/', $color)) {
            return true;
        }

        return false;
    }

    private static function _stylesCustomFile(string $styles_custom): void
    {
        $css_default_path_file = App::blog()->themesPath() . '/' . My::id() . '/' . 'style.min.css';
        $css_path_folder       = My::id() . '/css/';
        $css_custom_path_file  = $css_path_folder . 'style.min.css';

        if ($styles_custom) {
            $styles_default = '';

            // Gets default CSS content.
            if (file_exists($css_default_path_file)) {
                $styles_default = (string) file_get_contents($css_default_path_file) ?: '';
            }

            // Creates a custom CSS file in the public folder.
            if (ThemeConfig::canWriteCss(My::id(), true) && ThemeConfig::canWriteCss($css_path_folder, true)) {
                ThemeConfig::writeCss(
                    $css_path_folder,
                    'style.min',
                    $styles_custom . $styles_default
                );

                // Creates a entry in the database that contains the CSS URL.
                App::blog()->settings->odyssey->put(
                    'styles_url',
                    My::odysseyPublicFolder('url', '/css/style.min.css'),
                    'string',
                    __('setting-css-custom-url'),
                    true
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
}
