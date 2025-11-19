<?php
/**
 * Odyssey, a simple and customizable Dotclear theme.
 *
 * @author    Teddy <contact.teddy@laposte.net>
 * @copyright 2022-2025 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

namespace Dotclear\Theme\odyssey;

use Dotclear\App;

class FrontendBlocks
{
    public static function odysseyJsUtil($attr, $content): string
    {
        if (My::settings()->advanced_js_util === false || !App::blog()->settings->system->jquery_needed) {
            return '';
        }

        return $content;
    }

    /**
     * Displays the right blog header on posts.
     *
     * @param array $attr    Unused.
     * @param void  $content The full header.
     *
     * @return string The header.
     */
    public static function odysseyHeaderMinimal($attr, $content): string
    {
        if (My::settings()->header_post_full !== true) {
            return $content;
        }

        return App::frontend()->template()->includeFile(['src' => '_post-header-full.html']);
    }

    /**
     * Hides the blog title block if the header image has been set as blog title.
     *
     * @param array $attr    Unused.
     * @param void  $content The blog title block.
     *
     * @return string The blog title block.
     */
    public static function odysseySiteTitle($attr, $content): string
    {
        if (My::settings()->header_image_as_title !== true
            || (My::settings()->header_image_as_title && !My::settings()->header_image)
        ) {
            return $content;
        }

        return '';
    }

    /**
     * Displays/hides the post pagination.
     *
     * @param array $attr    Unused.
     * @param void  $content The post pagination.
     *
     * @return string The post pagination.
     */
    public static function odysseyPostPagination($attr, $content): string
    {
        if (My::settings()->content_post_pagination !== false) {
            return $content;
        }

        return '';
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
        if (My::settings()->reactions_button === null
            && (isset(App::frontend()->context()->comment_preview['content']) && App::frontend()->context()->comment_preview['content'] === '')
        ) {
            return '<details class=reactions-details>
                <summary class=reactions-button>
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
     * @return string The sidebar.
     */
    public static function odysseySidebar($attr, $content): string
    {
        if (My::settings()->widgets_display !== false) {
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
     * @return string The footer.
     */
    public static function odysseyFooter($attr, $content): string
    {
        if (My::settings()->footer_enabled !== false) {
            return $content;
        }

        if (My::settings()->footer_enabled === false && App::blog()->settings->system->jquery_needed === true && My::settings()->advanced_js_util === null) {
            if (App::url()->type === 'post' || App::url()->type === 'pages') {
                if (App::frontend()->context()->posts->commentsActive()) {
                    return '<footer id=site-footer>' . My::scriptRememberMe() . '</footer>';
                }
            }
        }

        return '';
    }
}
