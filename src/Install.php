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
use Dotclear\Core\Backend\ThemeConfig;
use Dotclear\Core\Process;

class Install extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::INSTALL));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        self::_odysseyUpdateStyles();

        return true;
    }

    /**
     * If custom styles have been set on a previous version of the theme,
     * saves them again with the updated default stylesheet.
     *
     * This prevents new styles from not being applied after a theme update.
     *
     * @return void
     */
    private static function _odysseyUpdateStyles(): void
    {
        $styles_custom = My::settingValue('styles');

        if ($styles_custom) {
            $styles_default = '';

            $css_default_path_file = My::getInThemeFolder('style.min.css', 'path');

            if (file_exists($css_default_path_file) && (string) file_get_contents($css_default_path_file) !== '') {
                $styles_default = (string) file_get_contents($css_default_path_file);
            }

            $css_custom_path_folder = App::blog()->settings()->system->theme . '/css/';
            $css_custom_path_file   = $css_custom_path_folder . 'style.min.css';

            if (ThemeConfig::canWriteCss(App::blog()->settings()->system->theme, true)
                && ThemeConfig::canWriteCss($css_custom_path_folder, true) === true
                && $styles_default
            ) {
                ThemeConfig::writeCss(
                    $css_custom_path_folder,
                    'style.min',
                    $styles_custom . $styles_default
                );
            }
        }
    }
}
