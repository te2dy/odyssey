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
            return '<details class="reactions-button reactions-details">
                <summary>
                    <svg class="reactions-button-icon social-icon-fi" role=img viewBox="0 0 24 24" xmlns=http://www.w3.org/2000/svg>' . My::svgIcons('comment')['path'] . '</svg>

                    <span class=reactions-button-text>' . __('reactions-comment-form-title') . '</span>
                </summary>

                <div class=reactions-details-content>' . $content . '</div>
            </details>';
        }

        return '<h3>' . __('reactions-comment-form-title') . '</h3>' . $content;
    }

    /**
     * Displays/hides the sidebar.
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
     * Displays/hides the footer.
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
