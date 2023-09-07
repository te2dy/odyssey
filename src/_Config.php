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

require_once 'OdysseySettings.php';
use OdysseySettings as odSettings;

require_once 'OdysseyUtils.php';
use OdysseyUtils as odUtils;

class Config extends Process
{
    public static function init(): bool
    {
        // Limits to backend permissions.
        if (!self::status(My::checkContext(My::CONFIG))) {
            return false;
        }

        // Loads locales.
        // My::l10n('admin');

        App::backend()->odyssey_settings = OdysseySettings::default();;

        return self::status();
    }

    /**
     * Processes the request(s).
     */
    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

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
                            'footer_social_links_x',
                            'footer_social_links_diaspora',
                            'footer_social_links_mastodon',
                            'footer_social_links_peertube'
                        ];

                        if (!in_array($setting_id, $specific_settings, true)) {
                            // The current setting is not a specific one.
                            if (isset($_POST[$setting_id])) {
                                // The current setting has a set value.
                                if ($_POST[$setting_id] != $default_settings[$setting_id]['default']) {
                                    $setting_data = self::sanitizeSetting($setting_type, $setting_id, $_POST[$setting_id]);
                                } else {
                                    /**
                                     * If the value is equal to the default value,
                                     * removes the parameter.
                                     */
                                    dcCore::app()->blog->settings->odyssey->drop($setting_id);
                                }
                            } elseif (!isset($_POST[$setting_id]) && $default_settings[$setting_id]['type'] === 'checkbox') {
                                /**
                                 * No value is set for the current checkbox setting,
                                 * means that the checkbox is empty.
                                 */
                                $setting_data = self::sanitizeSetting('checkbox', $setting_id, 0);
                            } else {
                                // Removes every other settings.
                                dcCore::app()->blog->settings->odyssey->drop($setting_id);
                            }
                        } else {
                            // The current setting is specific one.
                            switch ($setting_id) {
                                case 'header_image':
                                case 'header_image2x':
                                    $setting_data = self::sanitizeHeaderImage(
                                        $setting_id,
                                        $_POST['header_image'],
                                        $_POST['global_page_width_unit'],
                                        $_POST['global_page_width_value']
                                    );
                                    break;

                                case 'global_css_custom':
                                case 'global_css_custom_mini':
                                    $setting_data = self::sanitizeCustomCSS($setting_id, $_POST['global_css_custom']);
                                    break;

                                case 'global_page_width_unit':
                                case 'global_page_width_value':
                                    $setting_data = self::sanitizePageWidth(
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
                                    $setting_data = self::sanitizeSocialLink(
                                        $setting_id,
                                        $_POST[$setting_id]
                                    );
                                    break;

                                case 'footer_social_links_x':
                                    $setting_data = self::sanitizeXUsername($_POST['footer_social_links_x']);
                                    break;

                                case 'footer_social_links_diaspora':
                                case 'footer_social_links_mastodon':
                                case 'footer_social_links_peertube':
                                    $setting_data = self::sanitizeLink($_POST[$setting_id]);
                                    break;

                                case 'footer_social_links_signal':
                                case 'footer_social_links_telegram':
                                case 'footer_social_links_whatsapp':
                                    $setting_data = self::sanitizeMessagingAppsLink($setting_id, $_POST[$setting_id]);
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
     * Displays the theme configuration page.
     *
     * @return void
     */
    public static function render(): void
    {
        if (!static::$init) {
            return;
        }

        var_dump(App::backend()->odyssey_settings);
    }
}
