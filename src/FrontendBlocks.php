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
use Dotclear\Core\Frontend\Ctx;

require_once 'OdysseyUtils.php';
use OdysseyUtils as odUtils;

class FrontendBlocks
{
    /**
     * Displays the comment form wrapper to toggle it.
     *
     * @param array $attr    Unused.
     * @param void  $content The comment form.
     *
     * @return string The comment form.
     */
    public static function odysseyCommentFormWrapper($attr, $content): string
    {
        if (odUtils::configuratorSetting() === true) {
            return '<details><summary class=button>' . __('reactions-react-link-title') . '</summary><div id=react-content><h3 class=reaction-title>' . __('reactions-comment-form-title') . '</h3>' . $content . '</div></details>';
        }

        // Part of the previous code:
        if (!odysseySettings::value('content_commentform_hide')) {
            return '<h3 class=reaction-title>' . __('reactions-comment-form-title') . '</h3>' . $content;
        } elseif (App::frontend()->ctx->comment_preview && App::frontend()->ctx->comment_preview["preview"]) {
            return '<div id=react-content><h3 class=reaction-title>' . __('reactions-comment-form-preview-title') . '</h3>' . $content . '</div>';
        }

        return '';
    }

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
