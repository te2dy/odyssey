<?php
/**
 * Odyssey, a Dotclear theme.
 *
 * This file contains functions for displaying the theme.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2023 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

namespace Dotclear\Theme\odyssey;

use Dotclear\App;
use Dotclear\Core\Process;

class Frontend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        // Behaviors.
        App::behavior()->addBehavior('publicHeadContent', FrontendBehaviors::odysseyHeadMeta(...));

        // Blocks
        App::frontend()->tpl->addBlock('odysseySidebar', FrontendBlocks::odysseySidebar(...));

        // Values.
        App::frontend()->tpl->addValue('odysseyURIRelative', FrontendValues::odysseyURIRelative(...));
        App::frontend()->tpl->addValue('origineEntryListImage', FrontendValues::origineEntryListImage(...));
        App::frontend()->tpl->addValue('odysseyPostTagsBefore', FrontendValues::odysseyPostTagsBefore(...));
        App::frontend()->tpl->addValue('odysseyFooterCredits', FrontendValues::odysseyFooterCredits(...));

        return true;
    }
}
