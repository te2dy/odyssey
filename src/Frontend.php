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

    /**
     * Performs action and/or prepares render.
     *
     * @return bool
     */
    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        // Behaviors.
        App::behavior()->addBehavior('publicHeadContent', FrontendBehaviors::odysseyHeadMeta(...));
        App::behavior()->addBehavior('publicHeadContent', FrontendBehaviors::odysseySocialMarkups(...));
        App::behavior()->addBehavior('publicHeadContent', FrontendBehaviors::odysseyJsonLd(...));
        App::behavior()->addBehavior('publicAfterContentFilterV2', FrontendBehaviors::odysseyImageWide(...));

        // Blocks
        App::frontend()->tpl->addBlock('odysseyCommentFormWrapper', FrontendBlocks::odysseyCommentFormWrapper(...));
        App::frontend()->tpl->addBlock('odysseySidebar', FrontendBlocks::odysseySidebar(...));

        // Values.
        App::frontend()->tpl->addValue('odysseyURIRelative', FrontendValues::odysseyURIRelative(...));
        App::frontend()->tpl->addValue('odysseyBlogDescription', FrontendValues::odysseyBlogDescription(...));
        App::frontend()->tpl->addValue('origineEntryListImage', FrontendValues::origineEntryListImage(...));
        App::frontend()->tpl->addValue('odysseyAttachmentTitle', FrontendValues::odysseyAttachmentTitle(...));
        App::frontend()->tpl->addValue('odysseyAttachmentSize', FrontendValues::odysseyAttachmentSize(...));        
        App::frontend()->tpl->addValue('odysseyPostTagsBefore', FrontendValues::odysseyPostTagsBefore(...));
        App::frontend()->tpl->addValue('odysseyMarkdownSupportInfo', FrontendValues::odysseyMarkdownSupportInfo(...));
        App::frontend()->tpl->addValue('odysseyFooterCredits', FrontendValues::odysseyFooterCredits(...));

        return true;
    }
}
