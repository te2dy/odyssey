<?php
/**
 * Origine Mini, a minimal theme for Dotclear.
 *
 * The purpose of this file is to generate, at each version change of the theme,
 * a digital fingerprint of the JS files and save them in the database.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2023 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

namespace Dotclear\Theme\originemini;

use dcCore;
use dcNsProcess;

class Prepend extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_CONTEXT_ADMIN');

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        // Gets the new version number of the theme and the old one.
        $old_version = (string) dcCore::app()->getVersion(basename(__DIR__));
        $new_version = (string) dcCore::app()->themes->moduleInfo('originemini', 'version');

        if (version_compare($old_version, $new_version, '<')) {
            dcCore::app()->blog->settings->addNamespace('originemini');

            // Hashes each JS files with the SHA-256 algorithm.
            $hashes = [
                'trackbackurl' => self::hashJS('/originemini/js/searchform.min.js'),
                'searchform'   => self::hashJS('/originemini/js/trackbackurl.min.js')
            ];

            /**
             * Saves the hashes in the database as an array.
             *
             * @see Config::render() (/src/Config.php)
             */
            dcCore::app()->blog->settings->originemini->put(
                'js_hash',
                $hashes,
                'array',
                __('prepend-hashes-save'),
                true
            );

            // Pushes the new version of the theme in the database.
            dcCore::app()->setVersion('originemini', $new_version);
        }

        return true;
    }

    /**
     * Hashes JS file in ordered to be stored in the database.
     *
     * @param string $path_rel The relative JS file path.
     *
     * @return string The hash.
     */
    public static function hashJS($path_rel): string
    {
        $hash = '';

        if ($path_rel) {
            $hash = hash_file(
                'sha256',
                dcCore::app()->blog->themes_path . $path_rel,
                true
            );

            $hash = base64_encode($hash);
            $hash = 'sha256-' . $hash;
            $hash = htmlspecialchars($hash, ENT_COMPAT, 'UTF-8');
        }

        return $hash;
    }
}
