<?php
/**
 * Odyssey, a Dotclear theme.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2024 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

namespace Dotclear\Theme\odyssey;

use Dotclear\App;
use Dotclear\Core\Frontend\Ctx;
use Dotclear\Helper\Html\Html;

class FrontendValues
{
    /**
     * Returns the relative URI of the current page.
     *
     * @return string The relative URI.
     */
    public static function odysseyURIRelative(): string
    {
        return '<?php echo Html::escapeURL($_SERVER["REQUEST_URI"]); ?>';
    }

    /**
     * Adds styles in the head.
     *
     * @return string The styles.
     */
    public static function odysseyStylesInline()//: string
    {
        if (!My::settingValue('styles')) {
            return '';
        }

        return '<style>' . My::settingValue('styles') . '</style>';
    }

    /**
     * Displays in the header an image defined in the theme configuration page.
     *
     * @param array $attr Attributes to customize the value.
     *                    Attribute allowed: position, to define the place
     *                    of the image in the header.
     *                    Values allowed:
     *                    - (string) top
     *                    - (string) bottom
     *
     * @return string
     */
    public static function odysseyHeaderImage($attr)
    {
        if (My::settingValue('header_image') && isset(My::settingValue('header_image')['url'])) {
            if (!empty($attr['position'])
                && (($attr['position'] === 'bottom' && My::settingValue('header_image_position') === 'bottom')
                || ($attr['position'] === 'top' && !My::settingValue('header_image_position')))
            ) {
                $image_url = Html::escapeURL(My::settingValue('header_image')['url']);
                $srcset    = '';

                if (My::settingValue('header_image_description')) {
                    $alt = ' alt="' . Html::escapeHTML(My::settingValue('header_image_description')) . '"';
                } else {
                    $alt = ' alt="' . __('header-image-alt') . '"';
                }

                if (My::settingValue('header_image2x')) {
                    $image2x_url = Html::escapeURL(My::settingValue('header_image2x'));

                    $srcset  = ' srcset="';
                    $srcset .= $image_url . ' 1x, ';
                    $srcset .= $image2x_url . ' 2x';
                    $srcset .= '"';
                }

                // Does not add a link to the home page on home page.
                if (App::url()->type === 'default') {
                    return '<div id=site-image><img' . $alt . ' src="' . $image_url . '"' . $srcset . '></div>';
                }

                return '<div id=site-image><a href="' . App::blog()->url . '" rel=home><img' . $alt . ' src="' . $image_url . '"' . $srcset . '></a></div>';
            }
        }
    }

    /**
     * Displays the blog description.
     * 
     * @return string The blog description.
     */
    public static function odysseyBlogDescription(): string
    {
        if (My::settingValue('header_description') === false) {
            return '';
        }

        if (App::blog()->desc) {
            $desc = strip_tags(App::blog()->desc, ['<em>', '<strong>']);
            $desc = Html::decodeEntities($desc);
            $desc = preg_replace('/\s+/', ' ', $desc);

            return '<div id=site-desc>' . $desc . '</div>';
        }

        return '';
    }

    /**
     * Displays a thumbnail in the post list of the first image found in each post.
     *
     * This function replaces the tag {{tpl:EntryFirstImage}} that should have been put
     * in the template in order to support responsive images with the srcset attribute.
     *
     * @return string The image.
     */
    public static function odysseyEntryListImage(): string
    {
        if (My::settingValue('content_postlist_thumbnail') === false) {
            return '';
        }

        return '<?php
            $img = ' . Ctx::class . '::EntryFirstImageHelper("t", false, "entry-list-img");

            if ($img) {
                $img_t   = ' . Ctx::class . '::EntryFirstImageHelper("t", false, "", true);
                $width_t = ' . App::media()->thumb_sizes['t'][0] . ';

                $img_s   = ' . Ctx::class . '::EntryFirstImageHelper("s", false, "", true);
                $width_s = ' . App::media()->thumb_sizes['s'][0] . ';

                if ($img_s && $img_s !== $img_t) {
                    $img_src = "src=\"" . $img_t . "\"";

                    $img_src_srcset = $img_src . " srcset=\"" . $img_s . " " . $width_s . "w, " . $img_t . " " . $width_t . "w\" size=100vw";

                    $img = str_replace($img_src, $img_src_srcset, $img);
                }

                echo $img;
            }
        ?>';
    }

    public static function odysseyPostListReactions(): string
    {
        if (My::settingValue('content_postlist_reactions') === true) {
            return '<?php
            $nb_reactions = (int) App::frontend()->context()->posts->nb_comment + (int) App::frontend()->context()->posts->nb_trackback;

            if ($nb_reactions > 0) {
                $reaction_text  = (string) $nb_reactions . " reaction";
                $reaction_text .= $nb_reactions > 1 ? "s" : "";

                echo "<div class=post-list-reaction-link><small><a href=\"" .  App::frontend()->context()->posts->getURL() . "#' . __('reactions-id') . '\">" . $reaction_text . "</a></small></div>";
            }
            ?>';
        }

        return '';
    }
    
    /**
     * Displays a title for attachments.
     * 
     * @return void The title.
     */
    public static function odysseyAttachmentTitle(): string
    {
        return '<?php
            if (count(App::frontend()->context()->attachments) === 1) {
                echo "' . __('attachments-title-one') . '";
            } else {
                echo "' . __('attachments-title-multiple') . '";
            }
        ?>';
    }
    
    /**
     * Displays the attachment size.
     *
     * Based on Clearbricks package, Common subpackage and files class.
     *
     * @return string The attachment size.
     */
    public static function odysseyAttachmentSize(): string
    {
        return '<?php
            $kb = 1024;
            $mb = 1024 * $kb;
            $gb = 1024 * $mb;
            $tb = 1024 * $gb;

            $size = $attach_f->size;

            // Setting ignored for some reason:
            // setlocale(LC_ALL, "fr_FR");

            if (App::lang()->getLang() === "fr") {
                $locale_decimal = ",";
            } else {
                $lang_conv      = localeconv();
                $locale_decimal = $lang_conv["decimal_point"];
            }

            if ($size > 0) {
                if ($size < $kb) {
                    printf("' . __('attachment-size-b') . '", $size);
                } elseif ($size < $mb) {
                    printf("' . __('attachment-size-kb') . '", number_format($size / $kb, 1, $locale_decimal));
                } elseif ($size < $gb) {
                    printf("' . __('attachment-size-mb') . '", number_format($size / $mb, 1, $locale_decimal));
                } elseif ($size < $tb) {
                    printf("' . __('attachment-size-gb') . '", number_format($size / $gb, 1, $locale_decimal));
                } else {
                    printf("' . __('attachment-size-tb') . '", number_format($size / $tb, 1, $locale_decimal));
                }
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
        return '<?php
            if (App::frontend()->context()->posts->post_meta) {
                $post_meta = unserialize(App::frontend()->context()->posts->post_meta);
    
                if (is_array($post_meta) && isset($post_meta["tag"])) {
                    if (count($post_meta["tag"]) > 1) {
                        echo "' . __('post-tags-prefix-multiple') . '";
                    } else {
                        echo "' . __('post-tags-prefix-one') . '";
                    }
                }
            }
        ?>';
    }

    /**
     * Displays a notice informing about the support of the Markdown syntax in comments.
     *
     * @return string The notice.
     */
    public static function odysseyMarkdownSupportInfo(): string
    {
        if (My::settingValue('reactions_markdown_notice') !== true) {
            return '';
        }

        $markdown_notice = sprintf(
            __('reactions-comment-markdown-support'),
            __('reactions-comment-markdown-support-link')
        );

        return '<br><small class=text-secondary><em>' . $markdown_notice . '</em></small>';
    }

    /**
     * Displays Dotclear and Odyssey as credits in the footer.
     *
     * @return string The credits.
     */
    public static function odysseyFooterCredits(): string
    {
        if (My::settingValue('footer_credits') !== false) {
            // If we are not in a development environment.
            if (!defined('DC_DEV') || (defined('DC_DEV') && DC_DEV === false)) {
                return '<div class=site-footer-block>' . sprintf(
                    __('footer-powered-by'),
                    My::name()
                ) . '</div>';
            }

            // Otherwise, displays a more detailed information.
            $dc_version       = App::config()->dotclearVersion();
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

        return '';
    }
}
