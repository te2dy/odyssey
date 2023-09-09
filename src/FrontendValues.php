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
use Dotclear\Helper\Html\Html;

require_once 'OdysseyUtils.php';
use OdysseyUtils as odUtils;

class FrontendValues
{
    /**
     * Returns the relative URI of the current page.
     *
     * @return string The relative URI.
     */
    public static function odysseyURIRelative()
    {
        return Html::escapeURL($_SERVER['REQUEST_URI']);
    }

    /**
     * Displays a thumbnail in the post list of the first image found in each post.
     *
     * This function replaces the tag {{tpl:EntryFirstImage}} that should have been put
     * in the template in order to support responsive images with the srcset attribute.
     *
     * @return string The image.
     */
    public static function origineEntryListImage(): string
    {
        if (odUtils::configuratorSetting() === false) {
            return '';
        }

        return '<?php
            $img = ' . Ctx::class . '::EntryFirstImageHelper("t", false, "entry-list-img");

            if ($img) {
                $img_t   = ' . Ctx::class . '::EntryFirstImageHelper("t", false, "", true);
                $width_t = getimagesize(DC_ROOT . $img_t)[0];

                $img_s   = ' . Ctx::class . '::EntryFirstImageHelper("s", false, "", true);
                $width_s = getimagesize(DC_ROOT . $img_s)[0];

                $img_src = "src=\"" . $img_t . "\"";

                $img_src_srcset = $img_src . " srcset=\"" . $img_s . " " . $width_s . "w, " . $img_t . " " . $width_t . "w\" size=100vw";

                $img = str_replace($img_src, $img_src_srcset, $img);

                echo $img;
            }
        ?>';
    }

    /**
     * Adds a text string before the tag list of posts that respects pluralization.
     *
     * @return string The text string.
     */
    public static function odysseyPostTagsBefore()
    {
        if (App::frontend()->ctx->posts->post_meta) {
            $post_meta = unserialize(App::frontend()->ctx->posts->post_meta);

            if (is_array($post_meta) && isset($post_meta['tag'])) {
                if (count($post_meta['tag']) > 1) {
                    return __('post-tags-prefix-multiple');
                }

                return __('post-tags-prefix-one');
            }
        }
    }

    /**
     * Displays Dotclear and Odyssey as credits in the footer.
     *
     * @return string The credits.
     */
    public static function odysseyFooterCredits(): string
    {
        // If we are not in a development environment.
        if (!defined('DC_DEV') || (defined('DC_DEV') && DC_DEV === false)) {
            return '<div class=site-footer-block>' . __(
                'footer-powered-by',
                My::name()
            ) . '</div>';
        }

        // Otherwise, displays a more detailed information.
        $dc_version       = App::version()->getVersion('core');
        $dc_version_short = explode('-', $dc_version)[0] ?? $dc_version;
        $theme_name       = My::name();
        $theme_version    = App::themes()->moduleInfo(My::id(), 'version');

        return '<div class=site-footer-block>' . sprintf(
            __('footer-powered-by-dev'),
            $dc_version,
            $dc_version_short,
            $theme_name,
            $theme_version
        ) . '</div>';
    }
}
