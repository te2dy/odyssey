<?php
/**
 * Odyssey, a customizable Dotclear theme.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2024 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

namespace Dotclear\Theme\odyssey;

use Dotclear\App;
use Dotclear\Core\Frontend\Ctx;

class FrontendBlocks
{
    /**
     * Displays the right blog header on posts.
     *
     * @param array $attr    Unused.
     * @param void  $content The full header.
     *
     * @return mixed The header.
     */
    public static function odysseyHeaderMinimal($attr, $content): mixed
    {
        if (My::settingValue('header_post_full') !== true) {
            return $content;
        }

        return App::frontend()->template()->includeFile(['src' => '_post-header-full.html']);
    }

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
        if (My::settingValue('reactions_button') !== false && (isset(App::frontend()->context()->comment_preview['content']) && App::frontend()->context()->comment_preview['content'] === '')) {
            return '<details id=reactions-react-button><summary class=button>' . __('reactions-react-link-title') . '</summary><div id=react-content>' . $content . '</div></details>';
        }

        return $content;
    }

    /**
     * Display/hides the sidebar.
     *
     * @param array $attr    Unused.
     * @param void  $content The sidebar.
     *
     * @return mixed The sidebar.
     */
    public static function odysseySidebar($attr, $content): mixed
    {
        if (My::settingValue('widgets_display') !== false) {
            return $content;
        }

        return '';
    }

    /**
     * Displays the footer.
     *
     * @param array $attr    Unused.
     * @param void  $content The content of the footer.
     *
     * @return mixed The footer.
     */
    public static function odysseyFooter($attr, $content): mixed
    {
        if (My::settingValue('footer_enabled') !== false) {
            return $content;
        }

        return '';
    }
}
