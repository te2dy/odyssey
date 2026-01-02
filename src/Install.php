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
use Dotclear\Helper\File\Files;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Process\TraitProcess;

class Install
{
    use TraitProcess;

    /**
     * Inits the process.
     *
     * @return bool
     */
    public static function init(): bool
    {
        return self::status(My::checkContext(My::INSTALL));
    }

    /**
     * Processes the multiple requests.
     *
     * @return bool
     */
    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        self::_updateStyles();

        // Removes the old unminified CSS if it has been set previously.
        App::backend()->themeConfig()->dropCss(My::id() . '/css', 'style');

        // Removes a previously used folder for backups if it still exists.
        Files::deltree(My::publicFolder('path') . '/backups');

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
        $css_theme_file_path = My::themeFolder('path', '/style.min.css');

        if (Path::real($css_theme_file_path)) {
            // If the theme stylesheet is readable.
            $styles        = My::settings()->styles        ?: '';
            $styles_custom = My::settings()->styles_custom ?: '';

            if ($styles || $styles_custom) {
                // If custom styles have been set.
                $css  = $styles;
                $css .= file_get_contents($css_theme_file_path) ?: '';
                $css  = trim($css);
                $css .= My::cssMinify($styles_custom) . PHP_EOL;

                $css_folder    = My::id() . '/css';
                $css_file_name = 'style.min';

                // Deletes the previous CSS folder instead of the CSS file only.
                Files::deltree(My::publicFolder('path') . '/css');

                // Creates the new CSS folder and file.
                if ($css && App::backend()->themeConfig()->canWriteCss($css_folder, true)) {
                    App::backend()->themeConfig()->writeCss($css_folder, $css_file_name, $css);
                }
            }
        }
    }
}
