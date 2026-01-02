<?php
/**
 * Odyssey, a simple and customizable Dotclear theme.
 *
 * @author    Teddy <contact.teddy@laposte.net>
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
                $css  = $styles;
                $css .= file_get_contents($css_theme_file_path) ?: '';
                $css  = trim($css);
                $css .= My::cssMinify($styles_custom) . PHP_EOL;

                $css_folder    = My::id() . '/css';
                $css_file_name = 'style.min';

                // Deletes the previous CSS file if it exists.
                App::backend()->themeConfig()->dropCss($css_folder, $css_file_name);

                // Creates the CSS file.
                if ($css && App::backend()->themeConfig()->canWriteCss($css_folder, true)) {
                    // Creates an Odyssey public folder if it does not exist.
                    Files::makeDir(My::publicFolder('path'));

                    App::backend()->themeConfig()->writeCss($css_folder, $css_file_name, $css);
                } else {
                    App::backend()->themeConfig()->dropCss($css_folder, $css_file_name);

                    // Removes the CSS folder if it exists and is empty.
                    if (Files::isDeletable(App::backend()->themeConfig()->cssPath($css_folder))
                        && empty(Files::getDirList(App::backend()->themeConfig()->cssPath($css_folder))['files'])
                    ) {
                        Files::deltree(App::backend()->themeConfig()->cssPath($css_folder));
                    }
                }
            }
        }
    }
}
