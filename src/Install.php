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
use Dotclear\Helper\File\Files;

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

        self::_updateStyles();

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
    private static function _updateStyles(): void
    {
        $styles        = My::settings()->styles        ?: '';
        $styles_custom = My::settings()->styles_custom ?: '';

        if ($styles || $styles_custom) {
            $css_theme_file_path = My::themeFolder('path', '/style.min.css');

            $css = $styles;

            if (file_exists($css_theme_file_path)) {
                $css .= (string) file_get_contents($css_theme_file_path);
            }

            $css .= $styles_custom

            $css_public_dir_path = My::publicFolder('path', '/css');

            if (!is_dir($css_public_dir_path) && is_writable(App::blog()->publicPath())) {
                Files::makeDir($css_public_dir_path, true);
            }

            if (is_dir($css_public_dir_path) && is_writable($css_public_dir_path)) {
                Files::putContent($css_public_dir_path, $css . '/style.css');
            }
        }
    }
}
