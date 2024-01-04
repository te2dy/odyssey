<?php
/**
 * Odyssey, a Dotclear theme.
 *
 * This file contains functions for displaying the theme.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2024 Teddy
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
        App::frontend()->template()->addBlock('odysseyHeaderMinimal', FrontendBlocks::odysseyHeaderMinimal(...));
        App::frontend()->template()->addBlock('odysseyCommentFormWrapper', FrontendBlocks::odysseyCommentFormWrapper(...));
        App::frontend()->template()->addBlock('odysseySidebar', FrontendBlocks::odysseySidebar(...));
        App::frontend()->template()->addBlock('odysseyFooter', FrontendBlocks::odysseyFooter(...));

        // Values.
        App::frontend()->template()->addValue('odysseyURIRelative', FrontendValues::odysseyURIRelative(...));
        App::frontend()->template()->addValue('odysseyStylesInline', FrontendValues::odysseyStylesInline(...));
        App::frontend()->template()->addValue('odysseyBlogDescription', FrontendValues::odysseyBlogDescription(...));
        App::frontend()->template()->addValue('odysseyEntryListImage', FrontendValues::odysseyEntryListImage(...));
        App::frontend()->template()->addValue('odysseyPostListReactions', FrontendValues::odysseyPostListReactions(...));
        App::frontend()->template()->addValue('odysseyAttachmentTitle', FrontendValues::odysseyAttachmentTitle(...));
        App::frontend()->template()->addValue('odysseyAttachmentSize', FrontendValues::odysseyAttachmentSize(...));
        App::frontend()->template()->addValue('odysseyPostTagsBefore', FrontendValues::odysseyPostTagsBefore(...));
        App::frontend()->template()->addValue('odysseyMarkdownSupportInfo', FrontendValues::odysseyMarkdownSupportInfo(...));
        App::frontend()->template()->addValue('odysseyFooterCredits', FrontendValues::odysseyFooterCredits(...));

        return true;
    }
}
