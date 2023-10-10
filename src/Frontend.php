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

use dcCore;
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
        dcCore::app()->addBehavior('publicHeadContent', [FrontendBehaviors::class, 'odysseyHeadMeta']);
        dcCore::app()->addBehavior('publicHeadContent', [FrontendBehaviors::class, 'odysseySocialMarkups']);
        dcCore::app()->addBehavior('publicHeadContent', [FrontendBehaviors::class, 'odysseyJsonLd']);
        dcCore::app()->addBehavior('publicAfterContentFilterV2', [FrontendBehaviors::class, 'odysseyImageWide']);

        // Blocks
        dcCore::app()->tpl->addBlock('odysseyCommentFormWrapper', [FrontendBlocks::class, 'odysseyCommentFormWrapper']);
        dcCore::app()->tpl->addBlock('odysseySidebar', [FrontendBlocks::class, 'odysseySidebar']);

        // Values.
        dcCore::app()->tpl->addValue('odysseyURIRelative', [FrontendValues::class, 'odysseyURIRelative']);
        dcCore::app()->tpl->addValue('odysseyBlogDescription', [FrontendValues::class, 'odysseyBlogDescription']);
        dcCore::app()->tpl->addValue('origineEntryListImage', [FrontendValues::class, 'origineEntryListImage']);
        dcCore::app()->tpl->addValue('odysseyAttachmentTitle', [FrontendValues::class, 'odysseyAttachmentTitle']);
        dcCore::app()->tpl->addValue('odysseyAttachmentSize', [FrontendValues::class, 'odysseyAttachmentSize']);
        dcCore::app()->tpl->addValue('odysseyPostTagsBefore', [FrontendValues::class, 'odysseyPostTagsBefore']);
        dcCore::app()->tpl->addValue('odysseyMarkdownSupportInfo', [FrontendValues::class, 'odysseyMarkdownSupportInfo']);
        dcCore::app()->tpl->addValue('odysseyFooterCredits', [FrontendValues::class, 'odysseyFooterCredits']);

        return true;
    }
}
