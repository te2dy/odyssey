<?php
/**
 * Odyssey, a Dotclear theme.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2023 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

namespace Dotclear\Theme\odyssey;

use dcCore;
use Dotclear\Core\Process;

require_once 'CustomUtils.php';

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

        dcCore::app()->blog->settings->addNamespace('odyssey');

        // Hashes each JS files with the SHA-256 algorithm.
        $hashes = [
            'trackbackurl' => OdysseyUtils::hashJS('/odyssey/js/searchform.min.js'),
            'searchform'   => OdysseyUtils::hashJS('/odyssey/js/trackbackurl.min.js')
        ];

        // Saves the hashes in the database as an array.
        dcCore::app()->blog->settings->odyssey->put(
            'js_hash',
            $hashes,
            'array',
            __('prepend-hashes-save'),
            true
        );

        return true;
    }
}
