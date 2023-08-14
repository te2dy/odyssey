<?php
/**
 * Odyssey, a Dotclear theme.
 *
 * This file sets up the theme configuration page and settings.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2023 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

namespace Dotclear\Theme\odyssey;

use dcCore;
use dcNsProcess;
use dcPage;
use dcThemeConfig;
use Dotclear\Helper\File\Files;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\L10n;
use Dotclear\Helper\Network\Http;
use Exception;
use form;
use Dotclear\Core\Backend\Page;
use Dotclear\Core\Backend\Notices;

// Prepares to use custom functions.
require_once 'CustomSettings.php';
use odysseySettings as oSettings;

require_once 'CustomUtils.php';
use odysseyUtils as oUtils;

class Config extends dcNsProcess
{
    public static function init(): bool
    {
        if (!defined('DC_CONTEXT_ADMIN')) {
            return false;
        }

        static::$init = true;

        L10n::set(__DIR__ . '/../locales/' . dcCore::app()->lang . '/admin');

        return true;
    }

    /**
     * Processes the requests of the configurator
     * to save the settings in the database.
     *
     * @return bool
     */
    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        // Behavior.
        dcCore::app()->addBehavior('adminPageHTMLHead', [self::class, 'loadStylesScripts']);

        // On form submit.
        if (!empty($_POST)) {
            $default_settings = odysseySettings::default();
            $saved_settings   = odysseySettings::saved();

            try {
                dcCore::app()->blog->settings->addNamespace('odyssey');

                if (isset($_POST['save'])) {
                    // Save button has been clicked.
                    foreach ($default_settings as $setting_id => $setting_value) {
                        $setting_data = [];

                        $specific_settings = [
                            'styles',
                            'header_image',
                            'header_image2x',
                            'global_css_custom',
                            'global_css_custom_mini',
                            'global_page_width_unit',
                            'global_page_width_value',
                            'footer_social_links_500px',
                            'footer_social_links_dailymotion',
                            'footer_social_links_discord',
                            'footer_social_links_facebook',
                            'footer_social_links_github',
                            'footer_social_links_signal',
                            'footer_social_links_telegram',
                            'footer_social_links_tiktok',
                            'footer_social_links_twitch',
                            'footer_social_links_vimeo',
                            'footer_social_links_whatsapp',
                            'footer_social_links_youtube',
                            'footer_social_links_x'
                        ];

                        if (!in_array($setting_id, $specific_settings, true)) {
                            // The current setting is not a specific one.
                            if (isset($_POST[$setting_id])) {
                                // The current setting has a set value.
                                if ($_POST[$setting_id] != $default_settings[$setting_id]['default']) {
                                    /**
                                     * The parameter has a new value that is different
                                     * from the default (and is not an unchecked checkbox).
                                     */
                                    switch ($default_settings[$setting_id]['type']) {
                                        case 'select' :
                                            $setting_data = self::saveSelectSetting($setting_id, $_POST[$setting_id]);
                                            break;

                                        case 'select_int' :
                                            $setting_data = self::saveSelectIntSetting($setting_id, $_POST[$setting_id]);
                                            break;

                                        case 'checkbox' :
                                            $setting_data = self::saveCheckboxSetting($setting_id, $_POST[$setting_id]);
                                            break;

                                        case 'integer' :
                                            $setting_data = self::saveIntegerSetting($setting_id, $_POST[$setting_id]);
                                            break;

                                        default :
                                            $setting_data = self::saveDefaultSetting($setting_id, $_POST[$setting_id]);
                                    }
                                } else {
                                    /**
                                     * The value is equal to the default value,
                                     * removes the parameter.
                                     */
                                    dcCore::app()->blog->settings->odyssey->drop($setting_id);
                                }
                            } elseif (!isset($_POST[$setting_id]) && $default_settings[$setting_id]['type'] === 'checkbox') {
                                /**
                                 * No value is set for the current checkbox setting,
                                 * means that the checkbox is empty.
                                 */
                                $setting_data = self::saveCheckboxSetting($setting_id);
                            } else {
                                // Removes every other settings.
                                dcCore::app()->blog->settings->odyssey->drop($setting_id);
                            }
                        } else {
                            // The current setting is specific one.
                            switch ($setting_id) {
                                case 'header_image':
                                case 'header_image2x':
                                    $setting_data = self::saveHeaderImage(
                                        $setting_id,
                                        $_POST['header_image'],
                                        $_POST['global_page_width_unit'],
                                        $_POST['global_page_width_value']
                                    );
                                    break;

                                case 'global_css_custom':
                                case 'global_css_custom_mini':
                                    $setting_data = self::saveCustomCSS($setting_id, $_POST['global_css_custom']);
                                    break;

                                case 'global_page_width_unit':
                                case 'global_page_width_value':
                                    $setting_data = self::savePageWidth(
                                        $setting_id,
                                        $_POST['global_page_width_unit'],
                                        $_POST['global_page_width_value']
                                    );
                                    break;

                                case 'footer_social_links_500px':
                                case 'footer_social_links_dailymotion':
                                case 'footer_social_links_discord':
                                case 'footer_social_links_facebook':
                                case 'footer_social_links_github':
                                case 'footer_social_links_tiktok':
                                case 'footer_social_links_twitch':
                                case 'footer_social_links_vimeo':
                                case 'footer_social_links_youtube':
                                    $setting_data = self::saveSocialLink(
                                        $setting_id,
                                        $_POST[$setting_id]
                                    );
                                    break;

                                case 'footer_social_links_x':
                                    $setting_data = self::saveXUsername($_POST['footer_social_links_x']);
                                    break;

                                case 'footer_social_links_signal':
                                case 'footer_social_links_telegram':
                                case 'footer_social_links_whatsapp':
                                    $setting_data = self::saveMessagingAppsLink($setting_id, $_POST[$setting_id]);
                                    break;

                                case 'styles':
                                    $setting_data = self::saveStyles();
                            }
                        }

                        if (isset($setting_data['value']) && isset($setting_data['type'])) {
                            $setting_label = $default_settings[$setting_id]['title'];

                            if ($setting_id === 'footer_social_links_x') {
                                $setting_label = str_replace(
                                    '𝕏',
                                    'X',
                                    $default_settings[$setting_id]['title']
                                );
                            }

                            $setting_label = Html::clean($setting_label);

                            dcCore::app()->blog->settings->odyssey->put(
                                $setting_id,
                                $setting_data['value'],
                                $setting_data['type'],
                                $setting_label,
                                true
                            );
                        } else {
                            dcCore::app()->blog->settings->odyssey->drop($setting_id);
                        }
                    }

                    dcPage::addSuccessNotice(__('settings-config-updated'));
                } elseif (isset($_POST['reset'])) {
                    /**
                     * Reset button has been clicked.
                     * Drops all settings.
                     */
                    foreach ($default_settings as $setting_id => $setting_value) {
                        dcCore::app()->blog->settings->odyssey->drop($setting_id);
                    }

                    dcPage::addSuccessNotice(__('settings-config-reset'));
                }

                // Refreshes the blog.
                dcCore::app()->blog->triggerBlog();

                // Resets template cache.
                dcCore::app()->emptyTemplatesCache();

                /**
                 * Redirects to refresh form values.
                 *
                 * With the parameters ['module' => 'odyssey', 'conf' => '1'],
                 * the & is interpreted as &amp; causing a wrong redirect.
                 */
                Http::redirect(
                    dcCore::app()->adminurl->get(
                        'admin.blog.theme',
                        ['module' => 'odyssey']
                    ) . '&conf=1'
                );
            } catch (Exception $e) {
                dcCore::app()->error->add($e->getMessage());
            }
        }

        return true;
    }

    /**
     * Saves a type "select" setting.
     *
     * @param string $setting_id The id of the setting.
     *
     * @return void Saves the setting value.
     */
    public static function saveSelectSetting($setting_id, $setting_value)
    {
        $default_settings = odysseySettings::default();

        if (in_array($setting_value, $default_settings[$setting_id]['choices'])) {
            return [
                'value' => Html::escapeHTML($setting_value),
                'type'  => 'string'
            ];
        }
    }

    /**
     * Saves a type "select_int" setting.
     *
     * @param string $setting_id The id of the setting.
     *
     * @return void Saves the setting value.
     */
    public static function saveSelectIntSetting($setting_id, $setting_value)
    {
        $default_settings = odysseySettings::default();

        if (in_array((int) $setting_value, $default_settings[$setting_id]['choices'], true)) {
            return [
                'value' => (int) $setting_value,
                'type'  => 'integer'
            ];
        }
    }

    /**
     * Saves a type "checkbox" setting.
     *
     * @param string $setting_id The id of the setting.
     *
     * @return void Saves the setting value.
     */
    public static function saveCheckboxSetting($setting_id, $setting_value = '0')
    {
        $default_settings = odysseySettings::default();

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

    /**
     * Saves a type "integer" setting.
     *
     * @param string $setting_id The id of the setting.
     *
     * @return void Saves the setting value.
     */
    public static function saveIntegerSetting($setting_id, $setting_value)
    {
        $default_settings = odysseySettings::default();

        if (is_numeric($setting_value) && $setting_value != $default_settings[$setting_id]['default']) {
            return [
                'value' => (int) $setting_value,
                'type'  => 'integer'
            ];
        }
    }

    /**
     * Saves a type "default" setting.
     *
     * @param string $setting_id The id of the setting.
     *
     * @return void Saves the setting value.
     */
    public static function saveDefaultSetting($setting_id, $setting_value)
    {
        $default_settings = odysseySettings::default();

        if ($setting_value != $default_settings[$setting_id]['default']) {
            return [
                'value' => Html::escapeHTML($setting_value),
                'type'  => 'string'
            ];
        }
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
    public static function saveHeaderImage($setting_id, $image_url, $page_width_unit, $page_width_value)
    {
        $default_settings = odysseySettings::default();
        $image_url        = $image_url ?: '';
        $page_width_unit  = $page_width_unit ?: '';
        $page_width_value = $page_width_value ?: '';

        if ($image_url) {
            // Gets relative url and path of the public folder.
            $public_url  = dcCore::app()->blog->settings->system->public_url;
            $public_path = dcCore::app()->blog->public_path;

            // Converts the absolute URL in a relative one if necessary.
            $image_url = Html::stripHostURL($image_url);

            // Retrieves the image path.
            $image_path = $public_path . str_replace($public_url . '/', '/', $image_url);

            if (oUtils::imageExists($image_path)) {

                // Gets the dimensions of the image.
                list($header_image_width) = getimagesize($image_path);

                /**
                 * Limits the maximum width value of the image if its superior to the page width,
                 * and sets its height proportionally.
                 */
                $page_width_data = oSettings::getContentWidth($page_width_unit, $page_width_value, true);

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
    }

    public static function saveCustomCSS($setting_id, $css_value)
    {
        $default_settings = odysseySettings::default();

        $css_value = $css_value ?: '';

        if ($css_value) {
            if ($setting_id === 'global_css_custom') {
                return [
                    'value' => Html::escapeHTML($css_value),
                    'type'  => 'string'
                ];
            } elseif ($setting_id === 'global_css_custom_mini') {
                $css_value_mini = str_replace("\n", "", $css_value);
                $css_value_mini = str_replace("\r", "", $css_value_mini);
                $css_value_mini = str_replace("  ", " ", $css_value_mini);
                $css_value_mini = str_replace("  ", " ", $css_value_mini);
                $css_value_mini = str_replace(" {", "{", $css_value_mini);
                $css_value_mini = str_replace("{ ", "{", $css_value_mini);
                $css_value_mini = str_replace(" }", "}", $css_value_mini);
                $css_value_mini = str_replace("} ", "}", $css_value_mini);
                $css_value_mini = str_replace(", ", ",", $css_value_mini);
                $css_value_mini = str_replace("; ", ";", $css_value_mini);
                $css_value_mini = str_replace(": ", ":", $css_value_mini);

                return [
                    'value' => Html::escapeHTML($css_value_mini),
                    'type'  => 'string'
                ];
            }
        }
    }

    public static function savePageWidth($setting_id, $page_width_unit, $page_width_value)
    {
        $default_settings = odysseySettings::default();

        $page_width_unit  = $page_width_unit ?: 'px';
        $page_width_value = $page_width_value ? (int) $page_width_value : 30;

        $page_width_data = oSettings::getContentWidth(
            $page_width_unit,
            $page_width_value
        );

        if ($setting_id === 'global_page_width_unit' && isset($page_width_data['unit'])) {
            return [
                'value' => Html::escapeHTML($page_width_data['unit']),
                'type'  => 'string'
            ];
        }

        if ($setting_id === 'global_page_width_value' && isset($page_width_data['value'])) {
            return [
                'value' => Html::escapeHTML($page_width_data['value']),
                'type'  => 'integer'
            ];
        }
    }

    public static function saveSocialLink($setting_id, $url): array
    {
        $url = $url ? Html::escapeURL($url) : '';

        $output = [];

        $social_base_url = [
            'footer_social_links_500px'       => 'https://500px.com/',
            'footer_social_links_dailymotion' => 'https://www.dailymotion.com/',
            'footer_social_links_discord'     => 'https://discord.com/',
            'footer_social_links_github'      => 'https://github.com/',
            'footer_social_links_tiktok'      => 'https://tiktok.com/',
            'footer_social_links_twitch'      => 'https://www.twitch.tv/',
            'footer_social_links_vimeo'       => 'https://vimeo.com/',
            'footer_social_links_youtube'     => 'https://www.youtube.com/',
        ];

        if ($url) {
            if (array_key_exists($setting_id, $social_base_url)) {
                if (str_starts_with($url, $social_base_url[$setting_id])) {
                    $output['value'] = Html::escapeHTML($url);
                    $output['type']  = 'string';
                }
            } elseif ($setting_id === 'footer_social_links_facebook'
                && str_contains(parse_url($url, PHP_URL_HOST), '.facebook.com')
            ) {
                $output['value'] = Html::escapeHTML($url);
                $output['type']  = 'string';
            }
        }

        return $output;
    }

    /**
     * Validates an X username and returns its URL.
     *
     * @param string $username The given username to save.
     *
     * @return array The URL of the X account.
     */
    public static function saveXUsername($username): array
    {
        $output = [];

        if (preg_match('/^@[A-Za-z0-9_]{4,15}/', $username)) {
            $output['value'] = Html::escapeHTML('https://twitter.com/' . substr($username, 1));
            $output['type']  = 'string';
        }

        return $output;
    }

    public static function saveMessagingAppsLink($setting_id, $input): array
    {
        $output = [];

        if ($setting_id === 'footer_social_links_signal') {
            if (preg_match('/^\\+[1-9][0-9]{7,14}$/', $input)
                || str_starts_with($input, 'sgnl://signal.me/')
                || str_starts_with($input, 'https://signal.me/')
            ) {
                $output['value'] = Html::escapeHTML($input);
                $output['type']  = 'string';
            }
        } elseif ($setting_id === 'footer_social_links_telegram') {
            if (str_starts_with($input, 'https://t.me/')
                || str_starts_with($input, 'https://telegram.me/')
                || str_starts_with($input, 'tg://')
            ) {
                $output['value'] = Html::escapeHTML($input);
                $output['type']  = 'string';
            }
        } elseif ($setting_id === 'footer_social_links_whatsapp') {
            if (str_starts_with($input, 'https://wa.me/')
                || str_starts_with($input, 'whatsapp://')
            ) {
                $output['value'] = Html::escapeHTML($input);
                $output['type']  = 'string';
            }
        }

        return $output;
    }

    /**
     * Loads styles and scripts of the theme configurator.
     *
     * @return void
     */
    public static function loadStylesScripts()
    {
        echo dcPage::cssLoad(dcCore::app()->blog->settings->system->themes_url . '/odyssey/css/admin.min.css'),
        dcPage::jsLoad(dcCore::app()->blog->settings->system->themes_url . '/odyssey/js/admin.min.js');
    }

    /**
     * Adds custom styles to the theme to apply the settings.
     *
     * @param int $header_image_width The width if the header image.
     *
     * @return void
     */
    public static function saveStyles($header_image_width = '')
    {

        $css = '';

        $css_root_array                    = [];
        $css_root_media_array              = [];
        $css_main_array                    = [];
        $css_supports_initial_letter_array = [];
        $css_media_array                   = [];
        $css_media_contrast_array          = [];
        $css_media_motion_array            = [];
        $css_media_print_array             = [];

        $default_settings = odysseySettings::default();

        // Page width.
        if (isset($_POST['global_page_width_unit']) && isset($_POST['global_page_width_value'])) {
            $page_width_unit  = $_POST['global_page_width_unit'];
            $page_width_value = $_POST['global_page_width_value'];

            if ($page_width_unit === 'px' && $page_width_value === '') {
                $page_width_value = '480';
            }

            $page_width_data = oSettings::getContentWidth($page_width_unit, $page_width_value);

            if (!empty($page_width_data)) {
                $css_root_array[':root']['--page-width'] = $page_width_data['value'] . $page_width_data['unit'];
            }
        }

        // Font size.
        $font_size_allowed = [80, 90, 110, 120];

        if (isset($_POST['global_font_size']) && in_array((int) $_POST['global_font_size'], $font_size_allowed, true)) {
            $css_root_array[':root']['--font-size'] = oUtils::removeZero($_POST['global_font_size'] / 100) . 'em';
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
                $themes_url = dcCore::app()->blog->settings->system->themes_url;

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

        // Font antialiasing.
        if (isset($_POST['global_font_antialiasing']) && $_POST['global_font_antialiasing'] === '1') {
            $css_root_array['body']['-moz-osx-font-smoothing'] = 'grayscale';
            $css_root_array['body']['-webkit-font-smoothing']  = 'antialiased';
            $css_root_array['body']['font-smooth']             = 'always';

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
                'gray'  => [
                    'h' => '0',
                    's' => '0%',
                    'l' => '10%'
                ],
                'green' => [
                    'h' => '120',
                    's' => '75%',
                    'l' => '30%'
                ],
                'red'   => [
                    'h' => '0',
                    's' => '90%',
                    'l' => '45%'
                ]
            ],
            'light-amplified' => [
                'gray'  => [
                    'l' => '28%'
                ],
                'green' => [
                    's' => '60%',
                    'l' => '40%'
                ],
                'red'   => [
                    's' => '100%',
                    'l' => '55%'
                ]
            ],
            'dark' => [
                'gray'  => [
                    'h' => '0%',
                    'l' => '99%'
                ],
                'green' => [
                    's' => '60%',
                    'l' => '80%'
                ],
                'red'   => [
                    's' => '70%',
                    'l' => '85%'
                ]
            ],
            'dark-amplified' => [
                'gray'  => [
                    'l' => '80%'
                ],
                'green' => [
                    's' => '50%',
                    'l' => '60%'
                ],
                'red'   => [
                    'l' => '70%'
                ]
            ]
        ];

        if (isset($_POST['global_color_primary']) && in_array($_POST['global_color_primary'], $primary_colors_allowed, true)) {

            // Light.
            $css_root_array[':root']['--color-primary-h-custom'] = $primary_colors['light'][$_POST['global_color_primary']]['h'];
            $css_root_array[':root']['--color-primary-s-custom'] = $primary_colors['light'][$_POST['global_color_primary']]['s'];
            $css_root_array[':root']['--color-primary-l-custom'] = $primary_colors['light'][$_POST['global_color_primary']]['l'];

            // Light & amplified.
            if (isset($primary_colors['light-amplified'][$_POST['global_color_primary']]['s'])) {
                $css_root_array[':root']['--color-primary-amplified-s-custom'] = $primary_colors['light-amplified'][$_POST['global_color_primary']]['s'];
            }

            if (isset($primary_colors['light-amplified'][$_POST['global_color_primary']]['l'])) {
                $css_root_array[':root']['--color-primary-amplified-l-custom'] = $primary_colors['light-amplified'][$_POST['global_color_primary']]['l'];
            }

            // Dark.
            if (isset($primary_colors['dark'][$_POST['global_color_primary']]['h'])) {
                $css_root_array[':root']['--color-primary-dark-h-custom'] = $primary_colors['dark'][$_POST['global_color_primary']]['h'];
            }

            if (isset($primary_colors['dark'][$_POST['global_color_primary']]['s'])) {
                $css_root_array[':root']['--color-primary-dark-s-custom'] = $primary_colors['dark'][$_POST['global_color_primary']]['s'];
            }

            if (isset($primary_colors['dark'][$_POST['global_color_primary']]['l'])) {
                $css_root_array[':root']['--color-primary-dark-l-custom'] = $primary_colors['dark'][$_POST['global_color_primary']]['l'];
            }

            // Dark & amplified.
            if (isset($primary_colors['dark-amplified'][$_POST['global_color_primary']]['s'])) {
                $css_root_array[':root']['--color-primary-dark-amplified-s-custom'] = $primary_colors['dark-amplified'][$_POST['global_color_primary']]['s'];
            }

            if (isset($primary_colors['dark-amplified'][$_POST['global_color_primary']]['l'])) {
                $css_root_array[':root']['--color-primary-dark-amplified-l-custom'] = $primary_colors['dark-amplified'][$_POST['global_color_primary']]['l'];
            }
        }

        // Background color.
        $background_colors_allowed = ['beige', 'blue', 'gray', 'green', 'red'];

        $background_colors = [
            'beige' => [
                'h' => '45',
                's' => '65%',
                'l' => '96%'
            ],
            'blue'  => [
                'h' => '226',
                's' => '100%',
                'l' => '98%'
            ],
            'gray'  => [
                'h' => '0',
                's' => '0%',
                'l' => '97%'
            ],
            'green' => [
                'h' => '105',
                's' => '90%',
                'l' => '98%'
            ],
            'red'   => [
                'h' => '0',
                's' => '90%',
                'l' => '98%'
            ]
        ];

        if (isset($_POST['global_color_background']) && in_array($_POST['global_color_background'], $background_colors_allowed, true)) {

            // Main background.
            if (isset($background_colors[$_POST['global_color_background']]['h'])) {
                $css_root_array[':root']['--color-background-h-custom'] = $background_colors[$_POST['global_color_background']]['h'];
            }

            if (isset($background_colors[$_POST['global_color_background']]['s'])) {
                $css_root_array[':root']['--color-background-s-custom'] = $background_colors[$_POST['global_color_background']]['s'];
            }

            if (isset($background_colors[$_POST['global_color_background']]['l'])) {
                $css_root_array[':root']['--color-background-l-custom'] = $background_colors[$_POST['global_color_background']]['l'];
            }

            $css_root_array[':root']['--color-input-background'] = '#fff';
        }

        // Transitions.
        if (isset($_POST['global_css_transition']) && $_POST['global_css_transition'] === '1') {
            $css_main_array['a']['transition'] = 'all .2s ease-in-out';

            $css_main_array['a:active, a:hover']['transition'] = 'all .2s ease-in-out';

            $css_main_array['input[type="submit"], .form-submit, .button']['transition'] = 'all .2s ease-in-out';

            $css_main_array['input[type="submit"]:hover, .button:hover, .form-submit:hover']['transition'] = 'all .2s ease-in-out';

            $css_media_motion_array['a']['transition'] = 'unset';

            $css_media_motion_array['a:active, a:hover']['transition'] = 'unset';

            $css_media_motion_array['input[type="submit"], .form-submit, .button']['transition'] = 'unset';

            $css_media_motion_array['input[type="submit"]:hover, .button:hover, .form-submit:hover']['transition'] = 'unset';
        }

        // Links underline.
        if (isset($_POST['global_css_links_underline']) && $_POST['global_css_links_underline'] === '1') {
            $css_root_array[':root']['--link-text-decoration']       = 'underline';
            $css_root_array[':root']['--link-text-decoration-style'] = 'dotted';

            $css_root_array['.button']['text-decoration']       = 'none';
            $css_root_array['.button']['text-decoration-style'] = 'none';
        }

        // Border radius.
        if (isset($_POST['global_css_border_radius']) && $_POST['global_css_border_radius'] === '1') {
            $css_root_array[':root']['--border-radius'] = '.168rem';
        }

        // JS.
        if (isset($_POST['global_js']) && $_POST['global_js'] === '1') {
            if (isset($_POST['content_trackback_link']) && $_POST['content_trackback_link'] === '1') {
                $css_main_array['#trackback-url']['color']                 = 'var(--color-primary)';
                $css_main_array['#trackback-url']['text-decoration']       = 'var(--link-text-decoration, none)';
                $css_main_array['#trackback-url']['text-decoration-style'] = 'var(--link-text-decoration-style, unset)';

                $css_main_array['#trackback-url:is(:active, :focus, :hover)']['cursor']                = 'pointer';
                $css_main_array['#trackback-url:is(:active, :focus, :hover)']['filter']                = 'brightness(1.25)';
                $css_main_array['#trackback-url:is(:active, :focus, :hover)']['text-decoration']       = 'underline';
                $css_main_array['#trackback-url:is(:active, :focus, :hover)']['text-decoration-style'] = 'solid';

                $css_main_array['#trackback-url-copied']['display'] = 'none';

                $css_media_contrast_array['#trackback-url:is(:active, :focus, :hover)']['background-color'] = 'var(--color-text-main)';
                $css_media_contrast_array['#trackback-url:is(:active, :focus, :hover)']['color']            = 'var(--color-background)';
                $css_media_contrast_array['#trackback-url:is(:active, :focus, :hover)']['outline']          = '.168rem solid var(--color-text-main)';
                $css_media_contrast_array['#trackback-url:is(:active, :focus, :hover)']['text-decoration']  = 'none';
            }
        }

        // Header banner
        if (isset($_POST['header_image']) && $_POST['header_image'] !== '') {
            $css_main_array['#site-image']['width'] = '100%';

            $css_media_contrast_array['#site-image a']['outline'] = 'inherit';

            if (isset($_POST['global_css_border_radius']) && $_POST['global_css_border_radius'] === '1') {
                $css_main_array['#site-image img']['border-radius'] = 'var(--border-radius)';
            }

            if (isset($header_image_width) && $header_image_width >= 100) {
                $css_main_array['#site-image img']['width'] = '100%';
            }
        }

        // Blog description.
        if (isset($_POST['header_description']) && $_POST['header_description'] === '1') {
            $css_main_array['#site-identity']['align-items'] = 'center';
            $css_main_array['#site-identity']['column-gap']  = '.5rem';
            $css_main_array['#site-identity']['display']     = 'flex';
            $css_main_array['#site-identity']['flex-wrap']   = 'wrap';
            $css_main_array['#site-identity']['row-gap']     = '.5rem';

            $css_main_array['#site-description']['font-size']   = '.8em';
            $css_main_array['#site-description']['font-style']  = 'italic';
            $css_main_array['#site-description']['font-weight'] = 'normal';
            $css_main_array['#site-description']['margin']      = '0';
        }

        // Content font family.
        if (isset($_POST['content_text_font']) && $_POST['content_text_font'] !== 'same' && $_POST['global_font_family'] !== $_POST['content_text_font']) {
            if ($_POST['content_text_font'] === 'sans-serif') {
                $css_root_array[':root']['--font-family-content'] = '-apple-system, BlinkMacSystemFont, "Avenir Next", Avenir, "Segoe UI", "Helvetica Neue", Helvetica, Ubuntu, Roboto, Noto, Arial, sans-serif';
            } elseif ($_POST['content_text_font'] === 'serif') {
                $css_main_array[':root']['--font-family-content'] = '"Iowan Old Style", "Apple Garamond", Baskerville, "Times New Roman", "Droid Serif", Times, "Source Serif Pro", serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol"';
            } elseif ($_POST['content_text_font'] === 'monospace') {
                $css_main_array[':root']['--font-family-content'] = 'Menlo, Consolas, Monaco, "Liberation Mono", "Lucida Console", monospace';
            } elseif ($_POST['content_text_font'] === 'sans-serif-browser') {
                $css_main_array[':root']['--font-family-content'] = 'sans-serif';
            } elseif ($_POST['content_text_font'] === 'serif-browser') {
                $css_main_array[':root']['--font-family-content'] = 'serif';
            } elseif ($_POST['content_text_font'] === 'monospace-browser') {
                $css_main_array[':root']['--font-family-content'] = 'monospace';
            } elseif ($_POST['content_text_font'] === 'atkinson') {
                $themes_url = dcCore::app()->blog->settings->system->themes_url;

                $css_main_array[4]['@font-face']['font-family'] = '"Atkinson Hyperlegible"';
                $css_main_array[4]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/Atkinson-Hyperlegible-Regular-102a.woff2") format("woff2")';
                $css_main_array[4]['@font-face']['font-style']  = 'normal';
                $css_main_array[4]['@font-face']['font-weight'] = '400';

                $css_main_array[5]['@font-face']['font-family'] = '"Atkinson Hyperlegible"';
                $css_main_array[5]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/Atkinson-Hyperlegible-Italic-102a.woff2") format("woff2")';
                $css_main_array[5]['@font-face']['font-style']  = 'italic';
                $css_main_array[5]['@font-face']['font-weight'] = '400';

                $css_main_array[6]['@font-face']['font-family'] = '"Atkinson Hyperlegible"';
                $css_main_array[6]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/Atkinson-Hyperlegible-Bold-102a.woff2") format("woff2")';
                $css_main_array[6]['@font-face']['font-style']  = 'normal';
                $css_main_array[6]['@font-face']['font-weight'] = '700';

                $css_main_array[7]['@font-face']['font-family'] = '"Atkinson Hyperlegible"';
                $css_main_array[7]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/Atkinson-Hyperlegible-BoldItalic-102a.woff2") format("woff2")';
                $css_main_array[7]['@font-face']['font-style']  = 'italic';
                $css_main_array[7]['@font-face']['font-weight'] = '700';

                $css_root_array[':root']['--font-family-content'] = '"Atkinson Hyperlegible", sans-serif';
            } elseif ($_POST['content_text_font'] === 'eb-garamond') {
                $themes_url = dcCore::app()->blog->settings->system->themes_url;

                $css_main_array[4]['@font-face']['font-family'] = '"EB Garamond"';
                $css_main_array[4]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/EBGaramond-Regular.ttf") format("truetype")';
                $css_main_array[4]['@font-face']['font-style']  = 'normal';
                $css_main_array[4]['@font-face']['font-weight'] = '400';

                $css_main_array[5]['@font-face']['font-family'] = '"EB Garamond"';
                $css_main_array[5]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/EBGaramond-Italic.ttf") format("truetype")';
                $css_main_array[5]['@font-face']['font-style']  = 'italic';
                $css_main_array[5]['@font-face']['font-weight'] = '400';

                $css_main_array[6]['@font-face']['font-family'] = '"EB Garamond"';
                $css_main_array[6]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/EBGaramond-Bold.ttf") format("truetype")';
                $css_main_array[6]['@font-face']['font-style']  = 'normal';
                $css_main_array[6]['@font-face']['font-weight'] = '700';

                $css_main_array[7]['@font-face']['font-family'] = '"EB Garamond"';
                $css_main_array[7]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/EBGaramond-BoldItalic.ttf") format("truetype")';
                $css_main_array[7]['@font-face']['font-style']  = 'italic';
                $css_main_array[7]['@font-face']['font-weight'] = '700';

                $css_root_array[':root']['--font-family-content'] = '"EB Garamond", serif';
            } elseif ($_POST['content_text_font'] === 'luciole') {
                $themes_url = dcCore::app()->blog->settings->system->themes_url;

                $css_main_array[4]['@font-face']['font-family'] = '"Luciole"';
                $css_main_array[4]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/Luciole-Regular.ttf") format("truetype")';
                $css_main_array[4]['@font-face']['font-style']  = 'normal';
                $css_main_array[4]['@font-face']['font-weight'] = '400';

                $css_main_array[5]['@font-face']['font-family'] = '"Luciole"';
                $css_main_array[5]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/Luciole-Regular-Italic.ttf") format("truetype")';
                $css_main_array[5]['@font-face']['font-style']  = 'italic';
                $css_main_array[5]['@font-face']['font-weight'] = '400';

                $css_main_array[6]['@font-face']['font-family'] = '"Luciole"';
                $css_main_array[6]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/Luciole-Bold.ttf") format("truetype")';
                $css_main_array[6]['@font-face']['font-style']  = 'normal';
                $css_main_array[6]['@font-face']['font-weight'] = '700';

                $css_main_array[7]['@font-face']['font-family'] = '"Luciole"';
                $css_main_array[7]['@font-face']['src']         = 'url("' . $themes_url . '/odyssey/fonts/Luciole-Bold-Italic.ttf") format("truetype")';
                $css_main_array[7]['@font-face']['font-style']  = 'italic';
                $css_main_array[7]['@font-face']['font-weight'] = '700';

                $css_root_array[':root']['--font-family-content'] = 'Luciole, sans-serif';
            }
        }

        // Line Height
        $line_height_allowed = [125, 175];

        if (isset($_POST['content_line_height']) && in_array((int) $_POST['content_line_height'], $line_height_allowed, true)) {
            $css_root_array[':root']['--text-line-height'] = (int) $_POST['content_line_height'] / 100;
        }

        // Text align
        if (isset($_POST['content_text_align']) && ($_POST['content_text_align'] === 'justify' || $_POST['content_text_align'] === 'justify_not_mobile')) {
            $css_root_array[':root']['--text-align'] = 'justify';

            $css_media_contrast_array[':root']['--text-align'] = 'left';

            if ($_POST['content_text_align'] === 'justify_not_mobile') {
                $css_media_array[':root']['--text-align'] = 'left';
            }
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

            if ($_POST['content_hyphens'] === 'enabled_not_mobile') {
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

        // Initial letter.
        if (isset($_POST['content_initial_letter']) && $_POST['content_initial_letter'] === '1') {
            $css_supports_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['-moz-initial-letter']    = '2';
            $css_supports_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['-webkit-initial-letter'] = '2';
            $css_supports_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['initial-letter']         = '2';
            $css_supports_initial_letter_array[':is(.post, .page) .content-text > p:first-of-type::first-letter']['margin-right']           = '.25rem';
        }

        // Post introduction.
        if (isset($_POST['content_post_intro']) && $_POST['content_post_intro'] === '1') {
            $css_main_array['#post-intro']['border-block']  = '.063rem solid var(--color-border, #ccc)';
            $css_main_array['#post-intro']['font-weight']   = '700';
            $css_main_array['#post-intro']['margin-bottom'] = '2rem';

            $css_main_array['#post-intro strong']['font-weight'] = '900';
        }

        // Post list appearence.
        if (isset($_POST['content_post_list_type'])) {
            if ($_POST['content_post_list_type'] === 'excerpt') {
                $css_main_array['.entry-list-extended']['list-style'] = 'none';
                $css_main_array['.entry-list-extended']['margin']     = '0';
                $css_main_array['.entry-list-extended']['padding']    = '0';

                $css_main_array['.entry-list-extended .post']['margin-bottom'] = '3rem';

                $css_main_array['.entry-list-extended .post-selected']['padding'] = '1rem';

                $css_main_array['.entry-list-extended .entry-title']['margin-block'] = '.5rem';

                $css_main_array['.entry-list-extended .post-excerpt']['margin'] = '.5rem 0 0';
            } elseif ($_POST['content_post_list_type'] === 'content') {
                $css_main_array['.entry-list-content .post']['margin-bottom']  = '4rem';
                $css_main_array['.entry-list-content .post']['border-bottom']  = '.063rem solid var(--color-border, #ccc)';
                $css_main_array['.entry-list-content .post']['padding-bottom'] = '4rem';

                $css_main_array['.entry-list-content .post:last-child']['margin-bottom'] = '0';

                $css_main_array['.entry-list-content .entry-title']['font-size'] = '1.4em';
            }
        }

        // Content links.
        if (!isset($_POST['content_links_underline'])) {
            $css_root_array[':root']['--content-link-text-decoration-line']      = 'none';
            $css_root_array[':root']['--content-link-text-decoration-style']     = 'unset';
            $css_root_array[':root']['--content-link-text-decoration-thickness'] = '.063rem';
        }

        // Link to reactions in the post list.
        if (isset($_POST['content_post_list_reaction_link']) && $_POST['content_post_list_reaction_link'] !== 'disabled') {
            $css_main_array['.entry-list .post']['flex-wrap'] = 'wrap';

            $css_main_array['.post-reaction-link']['color'] = 'var(--color-text-secondary, #6c6f78)';

            if (!isset($_POST['content_post_list_type']) || (isset($_POST['content_post_list_type']) && $_POST['content_post_list_type'] !== 'excerpt')) {
                $css_main_array['.post-reaction-link-container']['flex-basis'] = '100%';

                $css_media_array['.post-reaction-link-container']['order'] = '3';
            } else {
                $css_main_array['.post-reaction-link-container']['display']    = 'inline-block';
                $css_main_array['.post-reaction-link-container']['flex-basis'] = '100%';
                $css_main_array['.post-reaction-link-container']['margin-top'] = '.5rem';
            }
        }

        // Hide comment form.
        if (isset($_POST['content_commentform_hide']) && $_POST['content_commentform_hide'] === '1') {
            $css_main_array['#react-content']['margin-top'] = '1rem';
        }

        // Private comments.
        if (isset($_POST['content_post_email_author']) && $_POST['content_post_email_author'] !== 'disabled') {
            $css_main_array['.comment-private']['margin-bottom'] = '2rem';
        }

        // Sets the order of the blog elements.
        $structure_order = [2 => '',];

        if (isset($_POST['widgets_nav_position']) && $_POST['widgets_nav_position'] === 'header_content') {
            $structure_order[2] = '--order-widgets-nav';
        }

        if ($structure_order[2] === '') {
            $structure_order[2] = '--order-content';
        } else {
            $structure_order[] = '--order-content';
        }

        if (isset($_POST['widgets_nav_position']) && $_POST['widgets_nav_position'] === 'content_footer') {
            $structure_order[] = '--order-widgets-nav';
        }

        if (isset($_POST['widgets_extra_enabled']) && $_POST['widgets_extra_enabled'] === '1') {
            $structure_order[] = '--order-widgets-extra';
        }

        if (isset($_POST['footer_enabled']) && $_POST['footer_enabled'] === '1') {
            $structure_order[] = '--order-footer';
        }

        if (array_search('--order-content', $structure_order) !== 2) {
            $css_root_array[':root']['--order-content'] = array_search('--order-content', $structure_order);
        }

        if (in_array('--order-widgets-nav', $structure_order, true) && array_search('--order-widgets-nav', $structure_order) !== 3) {
            $css_root_array[':root']['--order-widgets-nav'] = array_search('--order-widgets-nav', $structure_order);
        }

        if (in_array('--order-widgets-extra', $structure_order, true) && array_search('--order-widgets-extra', $structure_order) !== 4) {
            $css_root_array[':root']['--order-widgets-extra'] = array_search('--order-widgets-extra', $structure_order);
        }

        if (in_array('--order-footer', $structure_order, true) && array_search('--order-footer', $structure_order) !== 5) {
            $css_root_array[':root']['--order-footer'] = array_search('--order-footer', $structure_order);
        }

        // Social links.
        $social_sites = oSettings::socialSites();

        $display_social_links = false;

        foreach ($social_sites as $site_id) {
            if (isset($_POST['footer_social_links_' . $site_id]) && $_POST['footer_social_links_' . $site_id] !== '') {
                $display_social_links = true;

                break;
            }
        }

        if (isset($_POST['footer_enabled']) && $_POST['footer_enabled'] === '1' && $display_social_links === true) {
            $css_main_array['.footer-social-links']['margin-bottom'] = '1rem';

            $css_main_array['.footer-social-links ul']['list-style']                 = 'none';
            $css_main_array['.footer-social-links ul']['margin']                     = '0';
            $css_main_array['.footer-social-links ul']['padding-left']               = '0';
            $css_main_array['.footer-social-links ul li']['display']                 = 'inline-block';
            $css_main_array['.footer-social-links ul li']['margin']                  = '.25em';
            $css_main_array['.footer-social-links ul li:first-child']['margin-left'] = '0';
            $css_main_array['.footer-social-links ul li:last-child']['margin-right'] = '0';

            $css_main_array['.footer-social-links a']['display'] = 'inline-block';

            $css_main_array['.footer-social-links-icon-container']['align-items']      = 'center';
            $css_main_array['.footer-social-links-icon-container']['background-color'] = 'var(--color-primary)';
            $css_main_array['.footer-social-links-icon-container']['border-radius']    = 'var(--border-radius, unset)';
            $css_main_array['.footer-social-links-icon-container']['display']          = 'flex';
            $css_main_array['.footer-social-links-icon-container']['height']           = '1.5rem';
            $css_main_array['.footer-social-links-icon-container']['justify-content']  = 'center';
            $css_main_array['.footer-social-links-icon-container']['width']            = '1.5rem';

            $css_main_array['.footer-social-links a:is(:active, :focus, :hover) .footer-social-links-icon-container']['background-color'] = 'var(--color-primary-amplified)';

            $css_main_array['.footer-social-links-icon']['fill']            = 'var(--color-background, #fcfcfd)';
            $css_main_array['.footer-social-links-icon']['stroke']          = 'none';
            $css_main_array['.footer-social-links-icon']['width']           = '1rem';

            if (isset($_POST['global_css_transition']) && $_POST['global_css_transition'] === '1') {
                $css_main_array['.footer-social-links-icon-container']['transition'] = 'all .2s ease-in-out';

                $css_main_array['.footer-social-links a:is(:active, :focus, :hover) .footer-social-links-icon-container']['transition'] = 'all .2s ease-in-out';
            }

            $css_media_contrast_array['.footer-social-links a:is(:active, :focus, :hover) .footer-social-links-icon-container']['background-color'] = 'var(--color-background)';
            $css_media_contrast_array['.footer-social-links a:is(:active, :focus, :hover) .footer-social-links-icon-container']['color']            = 'var(--color-text-main)';
            $css_media_contrast_array['.footer-social-links a:is(:active, :focus, :hover) .footer-social-links-icon-container']['outline']          = '.168rem solid var(--color-text-main)';

            $css_media_contrast_array['.footer-social-links a:is(:active, :focus, :hover) .footer-social-links-icon']['fill'] = 'var(--color-text-main)';
        }

        $css .= !empty($css_root_array) ? oUtils::stylesArrayToString($css_root_array) : '';
        $css .= !empty($css_root_media_array) ? '@media (prefers-color-scheme:dark){' . oUtils::stylesArrayToString($css_root_media_array) . '}' : '';
        $css .= !empty($css_main_array) ? oUtils::stylesArrayToString($css_main_array) : '';
        $css .= !empty($css_supports_initial_letter_array) ? '@supports (initial-letter: 2) or (-webkit-initial-letter: 2) or (-moz-initial-letter: 2){' . oUtils::stylesArrayToString($css_supports_initial_letter_array) . '}' : '';
        $css .= !empty($css_media_array) ? '@media (max-width:34em){' . oUtils::stylesArrayToString($css_media_array) . '}' : '';
        $css .= !empty($css_media_contrast_array) ? '@media (prefers-contrast:more),(-ms-high-contrast:active),(-ms-high-contrast:black-on-white){' . oUtils::stylesArrayToString($css_media_contrast_array) . '}' : '';
        $css .= !empty($css_media_motion_array) ? '@media (prefers-reduced-motion:reduce){' . oUtils::stylesArrayToString($css_media_motion_array) . '}' : '';
        $css .= !empty($css_media_print_array) ? '@media print{' . oUtils::stylesArrayToString($css_media_print_array) . '}' : '';

        if (!empty($css)) {
            return [
                'value' => str_replace('&gt;', ">", htmlspecialchars($css, ENT_NOQUOTES)),
                'type'  => 'string'
            ];
        }

        return [];
    }

    /**
     * Displays each parameter according to its type.
     *
     * @param strong $setting_id The id of the setting to display.
     *
     * @return void The parameter.
     */
    public static function settingRendering($setting_id = '')
    {
        $default_settings = odysseySettings::default();
        $saved_settings   = odysseySettings::saved();

        if ($setting_id && array_key_exists($setting_id, $default_settings)) {
            echo '<p id=', $setting_id, '-input>';

            // Displays the default value of the parameter if it is not defined.
            if (isset($saved_settings[$setting_id])) {
                $setting_value = $saved_settings[$setting_id];
            } else {
                $setting_value = isset($default_settings[$setting_id]['default'])
                ? $default_settings[$setting_id]['default']
                : '';
            }

            // Particular values to render.
            if ($setting_id === 'footer_social_links_x' && $setting_value) {
                $setting_value = str_replace('https://twitter.com/', '@', $setting_value);
            }

            switch ($default_settings[$setting_id]['type']) {
                case 'checkbox' :
                    echo Form::checkbox(
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
                    Form::combo(
                        $setting_id,
                        $default_settings[$setting_id]['choices'],
                        strval($setting_value)
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

                default :
                    $placeholder = isset($default_settings[$setting_id]['placeholder'])
                    ? 'placeholder="' . $default_settings[$setting_id]['placeholder'] . '"'
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

                    break;
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
    }

    /**
     * Displays the theme configuration page.
     *
     * @return void
     */
    public static function render(): void
    {
        if (!static::$init) {
            return;
        }

        /*
        Page::openModule(
            My::name(),
            Page::jsPageTabs(dcCore::app()->admin->part)
        );

        echo Notices::getNotices() .
        '<div id=section-global class="multi-part" title="Global Settings">' .
        '<div id=section-header class="multi-part" title="Header Settings">' .
        '<h3 class="out-of-screen-if-js">' . sprintf(__('Settings for %s'), Html::escapeHTML(dcCore::app()->blog->name)) . '</h3>';

        Page::closeModule();
        */

        /**
         * Creates a table that contains all the parameters and their titles according to the following pattern:
         *
         * $sections_with_settings_id = [
         *     'section_1_id' => [
         *         'sub_section_1_id' => ['setting_1_id', 'option_2_id'],
         *         'sub_section_2_id' => …
         *     ]
         * ];
         */
        $sections_with_settings_id = [];

        $sections = odysseySettings::sections();
        $settings = odysseySettings::default();

        // Puts titles in the setting array.
        foreach ($sections as $section_id => $section_data) {
            $sections_with_settings_id[$section_id] = [];
        }

        // Puts all settings in their section.
        foreach ($settings as $setting_id => $setting_data) {
            $ignored_settings = ['header_image2x', 'global_css_custom_mini', 'styles'];

            if (!in_array($setting_id, $ignored_settings, true)) {
                // If a sub-section is set.
                if (isset($setting_data['section'][1])) {
                    $sections_with_settings_id[$setting_data['section'][0]][$setting_data['section'][1]][] = $setting_id;
                } else {
                    $sections_with_settings_id[$setting_data['section'][0]][] = $setting_id;
                }
            }
        }

        // Removes the titles if they are not associated with any parameter.
        $sections_with_settings_id = array_filter($sections_with_settings_id);
        ?>

        <form action="<?php echo dcCore::app()->adminurl->get('admin.blog.theme', ['module' => 'odyssey', 'conf' => '1']); ?>" enctype=multipart/form-data id=theme-config-form method=post>
            <?php
            // Displays the title of each section and places the corresponding parameters under each one.
            foreach ($sections_with_settings_id as $section_id => $section_data) {
                echo '<h3 id=section-', $section_id, '>',
                $sections[$section_id]['name'],
                '</h3>',
                '<div class=fieldset>';

                foreach ($section_data as $sub_section_id => $setting_id) {
                    // Displays the name of the sub-section unless its ID is "no-title".
                    if ($sub_section_id !== 'no-title') {
                        echo '<h4 id=section-', $section_id, '-', $sub_section_id, '>',
                        $sections[$section_id]['sub_sections'][$sub_section_id],
                        '</h4>';
                    }

                    // Displays the parameter.
                    foreach ($setting_id as $setting_id_value) {
                        self::settingRendering($setting_id_value);
                    }
                }

                echo '</div>';
            }

            echo Form::hidden('page_width_em_default', '30');
            echo Form::hidden('page_width_px_default', '480');
            ?>

            <p>
                <details id=odyssey-message-js>
                    <summary><?php echo __('settings-scripts-title'); ?></summary>

                    <div class=warning-msg>
                        <p><?php echo __('settings-scripts-message-intro'); ?></p>

                        <p>
                            <?php
                            printf(
                                __('settings-scripts-message-csp'),
                                __('settings-scripts-message-csp-href'),
                                __('settings-scripts-message-csp-title')
                            );
                            ?>
                        </p>

                        <p><?php echo __('settings-scripts-message-hash-intro'); ?></p>

                        <?php
                        /**
                         * Displays the list of script hashes if they are loaded.
                         *
                         * @see /_prepend.php
                         */
                        if (dcCore::app()->blog->settings->odyssey->js_hash) {
                            $hashes = dcCore::app()->blog->settings->odyssey->js_hash;

                            if (!empty($hashes)) {
                                echo '<ul>';

                                foreach ($hashes as $script_id => $hash) {
                                    $hash = '<code>' . $hash . '</code>';

                                    echo '<li id=hash-', $script_id, '>';

                                    switch ($script_id) {
                                        case 'searchform':
                                            echo __('settings-scripts-message-hash-searchform'),
                                            '<br>',
                                            $hash;

                                            break;
                                        case 'trackbackurl':
                                            echo __('settings-scripts-message-hash-trackbackurl'),
                                            '<br>',
                                            $hash;
                                    }

                                    echo '</li>';
                                }

                                echo '</ul>';
                            }
                        }
                        ?>

                        <p>
                            <?php
                            printf(
                                __('settings-scripts-message-example'),
                                'https://open-time.net/post/2022/08/15/CSP-mon-amour-en-public',
                                'fr',
                                'CSP mon amour en public'
                            );
                            ?>
                        </p>

                        <p><?php echo __('settings-scripts-message-note'); ?></p>
                    </div>
                </details>
            </p>

            <p>
                <?php echo dcCore::app()->formNonce(); ?>

                <input name=save type=submit value="<?php echo __('settings-save-button-text'); ?>">

                <input class=delete name=reset value="<?php echo __('settings-reset-button-text'); ?>" type=submit>
            </p>
        </form>

        <h3><?php echo __('config-about-title'); ?></h3>

        <p><?php echo __('config-about-text'); ?></p>

        <ul id=theme-config-links>
            <li>
                <a href="#" title="<?php echo __('config-link-github-title'); ?>">
                    <svg class=theme-config-icon role=img viewBox="0 0 24 24" xmlns=http://www.w3.org/2000/svg>
                        <?php echo strip_tags(oUtils::odysseySocialIcons('github'), '<path>'); ?>
                    </svg>

                    <?php echo __('config-link-github'); ?>
                </a>
            </li>

            <li>
                <a href="#" title="<?php echo __('config-link-dotclear-forum-title'); ?>">
                    <svg class=theme-config-icon role=img viewBox="0 0 64 49" xmlns=http://www.w3.org/2000/svg>
                        <path d="m41.106 51.828s-13.196 13.726-13.309 26.99c1.778 13.675 7.537 10.929 13.309 39.539 6.299-2.431 19.033-19.077 16.465-42.569-3.337-16.775-16.465-23.96-16.465-23.96" fill="#88c200" transform="matrix(.605548 .795809 .795809 -.605548 -55.6488 38.9583)"/><path d="m6.936 105.565s14.608-11.719 30.499-19.789c25.131-12.765 31.66-27.311 31.66-27.311s-7.264 13.608-32.395 26.372c-15.891 8.07-30.499 19.789-30.499 19.789z" fill="#676e78" transform="translate(-6.201 -56.844)">
                    </svg>

                    <?php echo __('config-link-dotclear-forum'); ?>
                </a>
            </li>

            <li>
                <a href="#" title="<?php echo __('config-link-github-issues-title'); ?>">
                    <svg class=theme-config-icon role=img viewBox="0 0 24 24" xmlns=http://www.w3.org/2000/svg>
                        <?php echo strip_tags(oUtils::odysseySocialIcons('github'), '<path>'); ?>
                    </svg>

                    <?php echo __('config-link-github-issues'); ?>
                </a>
            </li>
        </ul>

        <?php
    }
}

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
        $social_sites = oSettings::socialSites();

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
}
