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
     * Displays the description of the blog homepage in a meta description tag.
     *
     * If a custom description has not been set in the configurator,
     * displays the blog description.
     *
     * @param array $attr Unused.
     *
     * @return string The description.
     */
    public static function odysseyMetaDescriptionHome($attr): string
    {
        if (My::settingValue('advanced_meta_description')) {
            return '<?php echo ' . sprintf(App::frontend()->template()->getFilters($attr), 'App::blog()->settings->odyssey->advanced_meta_description') . '; ?>';
        }

        return '<?php echo ' . sprintf(App::frontend()->template()->getFilters($attr), 'App::blog()->desc') . '; ?>';
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
                $image_url  = Html::escapeURL(My::settingValue('header_image')['url']);
                $image_size = (int) My::settingValue('header_image')['width'];

                $srcset = '';
                $sizes  = '';

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

                    $sizes = ' sizes=' . $image_size . 'vw';
                }

                // Does not add a link to the home page on home page.
                if (App::url()->type === 'default') {
                    return '<div id=site-image><img' . $alt . ' src="' . $image_url . '"' . $srcset . $sizes . '></div>';
                }

                return '<div id=site-image><a href="' . App::blog()->url . '" rel=home><img' . $alt . ' src="' . $image_url . '"' . $srcset . $sizes . '></a></div>';
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
     * Loads the right entry-list template based on theme settings.
     * Default: one-line
     *
     * @return void The entry-list template.
     */
    public static function odysseyPostListType()
    {
        if (My::settingValue('content_postlist_type') !== 'excerpt') {
            return App::frontend()->template()->includeFile(['src' => '_entry-list.html']);
        }

        return App::frontend()->template()->includeFile(['src' => '_entry-list-excerpt.html']);
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

    /**
     * Returns an excerpt of the post for the entry-list-extended template.
     *
     * Gets the excerpt defined by the author or, if it does not exists,
     * an excerpt from the content.
     *
     * @param array $attr Modifying attributes.
     *
     * @return The entry excerpt.
     */
    public static function odysseyEntryExcerpt($attr)
    {
        return '<?php
            $the_excerpt = "";

            if (' . sprintf(App::frontend()->template()->getFilters($attr), 'App::frontend()->context()->posts->getExcerpt()') . ') {
                $the_excerpt = ' . sprintf(App::frontend()->template()->getFilters($attr), 'App::frontend()->context()->posts->getExcerpt()') . ';

                $the_excerpt = Html::clean($the_excerpt);
                $the_excerpt = Html::decodeEntities($the_excerpt);
                $the_excerpt = preg_replace("/\s+/", " ", $the_excerpt);
            } else {
                $the_excerpt = ' . sprintf(App::frontend()->template()->getFilters($attr), 'App::frontend()->context()->posts->getContent()') . ';

                $the_excerpt = Html::clean($the_excerpt);
                $the_excerpt = Html::decodeEntities($the_excerpt);
                $the_excerpt = preg_replace("/\s+/", " ", $the_excerpt);

                if (strlen($the_excerpt) > 200) {
                    $the_excerpt  = substr($the_excerpt, 0, 200);
                    $the_excerpt  = preg_replace("/[^a-z0-9]+\Z/i", "", $the_excerpt);
                    $the_excerpt .= "…";
                }
            }

            if ($the_excerpt) {
                if (App::frontend()->context()->posts->post_lang === App::blog()->settings->system->lang) {
                    $lang = "";
                } else {
                    $lang = " lang=" . App::frontend()->context()->posts->post_lang;
                }

                echo "<p class=\"content-text post-excerpt text-secondary\"" . $lang . ">",
                $the_excerpt,
                // " <a aria-label=\"", sprintf(__("entry-list-open-aria"), App::frontend()->context()->posts->post_title), "\" href=\"", App::frontend()->context()->posts->getURL(), "\">" . __("entry-list-open"), "</a>",
                "</p>";
            }
        ?>';
    }

    /**
     * Displays a link to reactions in the post list.
     *
     * @return string The link.
     */
    public static function odysseyPostListReactions(): string
    {
        if (My::settingValue('content_postlist_reactions') === true) {
            $separator = '';
            $tag_open  = '<div class=post-list-reaction-link><small>';
            $tag_close = '</small></div>';

            if (My::settingValue('content_postlist_type') === 'excerpt') {
                $separator = '| ';
                $tag_open  = '';
                $tag_close = '';
            }

            return '<?php
            $nb_reactions = (int) App::frontend()->context()->posts->nb_comment + (int) App::frontend()->context()->posts->nb_trackback;

            if ($nb_reactions > 0) {
                if ($nb_reactions > 1) {
                    $reaction_text = (string) sprintf("' . __("reactions-reactions-title-count-multiple") . '", $nb_reactions);
                } else {
                    $reaction_text = "' . __("reactions-reactions-title-count-one") . '";
                }

                echo "' . $separator . $tag_open . '<a href=\"" .  App::frontend()->context()->posts->getURL() . "#' . __('reactions-id') . '\">" . $reaction_text . "</a>' . $tag_close . '";
            }
            ?>';
        }

        return '';
    }

    /**
     * Displays a link to reply to the author of the post by email.
     *
     * @return The private comment section.
     */
    public static function odysseyPrivateCommentLink()
    {
        if (My::settingValue('reactions_private_comment') !== 'disabled') {
            return '<?php
            if (isset(App::frontend()->context()->posts->user_email) && App::frontend()->context()->posts->user_email && (App::blog()->settings->odyssey->reactions_private_comment === "always" || (App::blog()->settings->odyssey->reactions_private_comment === "comments_open" && App::frontend()->context()->posts->post_open_comment === "1"))
            ) {

                $body = "' . __('reactions-comment-private-body-post-url') . ' " . App::frontend()->context()->posts->getURL();
            ?>
                <div class="comment-private form-entry">
                    <h3 class=reaction-title>' . __('reactions-comment-private-title') . '</h3>

                    <p>
                        <a class=button href="mailto:<?php echo urlencode(App::frontend()->context()->posts->user_email); ?>?subject=<?php echo htmlentities("' . __("reactions-comment-private-email-prefix") . ' " . App::frontend()->context()->posts->post_title . "&body=" . $body); ?>">' . __('reactions-comment-private-button-text') . '</a>
                    </p>
                </div>
            <?php }
            ?>';
        }
    }

    /**
     * Displays a title for attachments.
     * 
     * @return string The title.
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
     * Displays social links in the footer.
     *
     * @return Social links.
     */
    public static function odysseyFooterSocialLinks()
    {
        $social_sites = My::socialSites();
        $output       = '';
        $count        = 0;

        foreach ($social_sites as $id => $data) {
            if (My::settingValue('footer_social_' . $id) !== null) {
                $count++;

                if ($count === 1) {
                    $output .= '<div class=site-footer-block>';
                    $output .= '<ul class=footer-social-links>';
                }


                if (My::svgIcons($id)['author'] === 'simpleicons') {
                    $class = 'footer-social-links-icon-si';
                } elseif (My::svgIcons($id)['author'] === 'feathericons') {
                    $class = 'footer-social-links-icon-fi';
                }

                $output .= '<li>';
                $output .= '<a href=' . My::attrValue(My::settingValue('footer_social_' . $id)) . '>';
                $output .= '<span class=footer-social-links-icon-container>';
                $output .= '<svg class=' . $class . ' role=img viewBox="0 0 24 24" xmlns=http://www.w3.org/2000/svg>';
                $output .= '<title>' . $data['name'] . '</title>';
                $output .= My::svgIcons($id)['path'];
                $output .= '</svg>';
                $output .= '</span>';
                $output .= '</a>';
                $output .= '</li>';
            }
        }

        if ($count > 0) {
            $output .= '</ul>';
            $output .= '</div>';
        }

        return $output;
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
