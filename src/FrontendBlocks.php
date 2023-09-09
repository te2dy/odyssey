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

require_once 'OdysseyUtils.php';
use OdysseyUtils as odUtils;

class FrontendBlocks
{
    /**
     * Hides the sidebar.
     */
    public static function odysseySidebar($attr, $content): mixed
    {
        if (odUtils::configuratorSetting() === true) {
            return '';
        }

        return $content;
    }
}
