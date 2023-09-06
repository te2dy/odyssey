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

class FrontendValues
{
    /**
     * Adds a text string before the tag list of posts.
     *
     * @return string The text string.
     */
    public static function odysseyPostTagsBefore()
    {
        return '<?php
        if (App::frontend()->ctx->posts->post_meta) {
            $post_meta = unserialize(App::frontend()->ctx->posts->post_meta);

            if (is_array($post_meta) && isset($post_meta["tag"])) {
                if (count($post_meta["tag"]) > 1) {
                    echo "' . __('post-tags-prefix-multiple') . '";
                } elseif (count($post_meta["tag"]) === 1) {
                    echo "' . __('post-tags-prefix-one') . '";
                }
            }
        }
        ?>';
    }

    /**
     * Displays Dotclear and Odyssey as credits in the footer.
     *
     * @return string The credits.
     */
    public static function odysseyFooterCredits(): string
    {
        if (!defined('DC_DEV') || (defined('DC_DEV') && DC_DEV === false)) {
            return '<div class=site-footer-block>' . __(
                'footer-powered-by',
                My::name()
            ) . '</div>';
        }

        $dc_version       = App::version()->getVersion('core');
        $dc_version_short = explode('-', $dc_version)[0] ?? $dc_version;
        $theme_name       = My::name();
        $theme_version    = App::version()->getVersion(My::id());

        return '<div class=site-footer-block>' . sprintf(
            __('footer-powered-by-dev'),
            $dc_version,
            $dc_version_short,
            $theme_name,
            $theme_version
        ) . '</div>';
    }
}
