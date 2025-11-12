<?php
/**
 * Odyssey, a simple and customizable Dotclear theme.
 *
 * This file contains functions for displaying the theme.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2025 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

namespace Dotclear\Theme\odyssey;

use Dotclear\App;
use Dotclear\Helper\Process\TraitProcess;

class Frontend
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    /**
     * Performs actions and/or prepares render.
     *
     * @return bool
     */
    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        // Behaviors.
        App::behavior()->addBehaviors([
            'publicHeadContent'          => FrontendBehaviors::odysseyHead(...),
            'publicAfterContentFilterV2' => FrontendBehaviors::odysseyAfterContent(...),
            'tplIfConditions'            => FrontendBehaviors::odysseyTplConditions(...)
        ]);

        // Blocks.
        App::frontend()->template()->addBlock('odysseyJsUtil', FrontendBlocks::odysseyJsUtil(...));
        App::frontend()->template()->addBlock('odysseyHeaderMinimal', FrontendBlocks::odysseyHeaderMinimal(...));
        App::frontend()->template()->addBlock('odysseySiteTitle', FrontendBlocks::odysseySiteTitle(...));
        App::frontend()->template()->addBlock('odysseyPostPagination', FrontendBlocks::odysseyPostPagination(...));
        App::frontend()->template()->addBlock('odysseyCommentFormWrapper', FrontendBlocks::odysseyCommentFormWrapper(...));
        App::frontend()->template()->addBlock('odysseySidebar', FrontendBlocks::odysseySidebar(...));
        App::frontend()->template()->addBlock('odysseyFooter', FrontendBlocks::odysseyFooter(...));

        // Values.
        App::frontend()->template()->addValue('odysseyLang', FrontendValues::odysseyLang(...));
        App::frontend()->template()->addValue('odysseyMetaDescriptionHome', FrontendValues::odysseyMetaDescriptionHome(...));
        App::frontend()->template()->addValue('odysseyMetaRobots', FrontendValues::odysseyMetaRobots(...));
        App::frontend()->template()->addValue('odysseyMetaCanonical', FrontendValues::odysseyMetaCanonical(...));
        App::frontend()->template()->addValue('odysseyStylesheetURL', FrontendValues::odysseyStylesheetURL(...));
        App::frontend()->template()->addValue('odysseyJqueryURL', FrontendValues::odysseyJqueryURL(...));
        App::frontend()->template()->addValue('odysseyScreenReaderLinks', FrontendValues::odysseyScreenReaderLinks(...));
        App::frontend()->template()->addValue('odysseyHeaderImage', FrontendValues::odysseyHeaderImage(...));
        App::frontend()->template()->addValue('odysseyBlogNameLink', FrontendValues::odysseyBlogNameLink(...));
        App::frontend()->template()->addValue('odysseyBlogDescription', FrontendValues::odysseyBlogDescription(...));
        App::frontend()->template()->addValue('odysseyPostListType', FrontendValues::odysseyPostListType(...));
        App::frontend()->template()->addValue('odysseyEntryListImage', FrontendValues::odysseyEntryListImage(...));
        App::frontend()->template()->addValue('odysseyEntryExcerpt', FrontendValues::odysseyEntryExcerpt(...));
        App::frontend()->template()->addValue('odysseyPostListReactions', FrontendValues::odysseyPostListReactions(...));
        App::frontend()->template()->addValue('odysseyTrackbackLink', FrontendValues::odysseyTrackbackLink(...));
        App::frontend()->template()->addValue('odysseyFeedLink', FrontendValues::odysseyFeedLink(...));
        App::frontend()->template()->addValue('odysseyPrivateCommentLink', FrontendValues::odysseyPrivateCommentLink(...));
        App::frontend()->template()->addValue('odysseyAttachmentTitle', FrontendValues::odysseyAttachmentTitle(...));
        App::frontend()->template()->addValue('odysseyAttachmentSize', FrontendValues::odysseyAttachmentSize(...));
        App::frontend()->template()->addValue('odysseyPostTagsBefore', FrontendValues::odysseyPostTagsBefore(...));
        App::frontend()->template()->addValue('odysseyMarkdownSupportInfo', FrontendValues::odysseyMarkdownSupportInfo(...));
        App::frontend()->template()->addValue('odysseyFooterSocialLinks', FrontendValues::odysseyFooterSocialLinks(...));
        App::frontend()->template()->addValue('odysseyFooterCredits', FrontendValues::odysseyFooterCredits(...));

        return true;
    }
}
