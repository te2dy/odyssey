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

require_once 'OdysseyUtils.php';
use OdysseyUtils as odUtils;

class FrontendBehaviors
{
    /**
     * Adds meta tags in the <head> section depending on the blog settings.
     *
     * @return void The head meta.
     */
    public static function odysseyHeadMeta()
    {
        // Adds the name of the editor.
        if (App::blog()->settings->system->editor) {
            echo '<meta name=author content=',
            odUtils::attrValueQuotes(App::blog()->settings->system->editor),
            '>' . "\n";
        }

        // Adds the content of the copyright notice.
        if (App::blog()->settings->system->copyright_notice) {
            echo '<meta name=copyright content=',
            odUtils::attrValueQuotes(App::blog()->settings->system->copyright_notice),
            '>' . "\n";
        }
    }
}
