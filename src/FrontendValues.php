<?php
/**
 * Odyssey, a simple and customizable Dotclear theme.
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
    public static function odysseyStyles(): string
    {
        if (!My::settingValue('styles_url')) {
            return '<link rel=stylesheet href=' . My::attrValue(App::blog()->settings()->system->themes_url . '/' . App::blog()->settings()->system->theme . '/style.min.css') . '>';
        }

        return '<link rel=stylesheet href=' . My::attrValue(My::settingValue('styles_url')) . '>';
    }

    /**
     * Displays an image in the header that has been defined
     * in the theme configuration page.
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
                $image_url  = My::settingValue('header_image')['url'];
                $image_size = (int) My::settingValue('header_image')['width'];

                $srcset = '';
                $sizes  = '';

                if (My::settingValue('header_image_description')) {
                    $alt = ' alt=' . My::attrValue(My::settingValue('header_image_description'));
                } else {
                    $alt = ' alt=' . My::attrValue(__('header-image-alt'));
                }

                if (My::settingValue('header_image2x')) {
                    $image2x_url = My::settingValue('header_image2x');

                    $srcset  = ' srcset="';
                    $srcset .= $image_url . ' 1x, ';
                    $srcset .= $image2x_url . ' 2x';
                    $srcset .= '"';

                    $sizes = ' sizes=' . $image_size . 'vw';
                }

                // Does not add a link to the home page on home page.
                if (App::url()->type === 'default') {
                    return '<div id=site-image><img' . $alt . ' src=' . My::attrValue($image_url) . '' . $srcset . $sizes . '></div>';
                }

                return '<div id=site-image><a href=' . My::attrValue(App::blog()->url) . ' rel=home><img' . $alt . ' src=' . My::attrValue($image_url) . $srcset . $sizes . '></a></div>';
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

            return '<div id=site-desc>' . My::cleanAttr($desc) . '</div>';
        }

        return '';
    }

    /**
     * Loads the right entry-list template based on theme settings.
     * Default: one-line
     *
     * @return The entry-list template.
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
     * This function replaces the tag {{tpl:EntryFirstImage}}
     * that should have been put in the template in order to support
     * responsive images with the srcset attribute.
     *
     * @param array $attr Attributes to customize the value.
     *
     * @return string The image.
     */
    public static function odysseyEntryListImage($attr): string
    {
        if (My::settingValue('content_postlist_thumbnail') === false) {
            return '';
        }

        return '<?php
        $context = "' . $attr['context'] . '";

        switch ($context) {
            case "entry-list" :
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

                break;
            case "entry-list-excerpt" :
                $img = ' . Ctx::class . '::EntryFirstImageHelper("o", false, "entry-list-excerpt-img");

                if ($img) {
                    $content_width = "' . My::getContentWidth('px')['value'] . '";

                    $img_o         = ' . Ctx::class . '::EntryFirstImageHelper("o", false, "", true) ?: null;
                    $img_o_path    = App::blog()->public_path . str_replace(
                        App::blog()->settings->system->public_url . "/",
                        "/",
                        $img_o
                    );
                    list($width_o) = getimagesize($img_o_path);

                    $img_m   = ' . Ctx::class . '::EntryFirstImageHelper("m", false, "", true) ?: null;
                    $width_m = ' . App::media()->thumb_sizes['m'][0] . ';

                    $img_s   = ' . Ctx::class . '::EntryFirstImageHelper("s", false, "", true) ?: null;
                    $width_s = ' . App::media()->thumb_sizes['s'][0] . ';

                    if ($img_o && $width_o >= $content_width) {
                        $img_src = "src=\"" . $img_o . "\"";

                        if ($img_m || $img_s) {
                            $img_src_srcset = "";

                            if ($img_s) {
                                $img_src_srcset .= $img_s . " " . $width_s . "w, ";
                            }

                            if ($img_m) {
                                $img_src_srcset .= $img_m . " " . $width_m . "w, ";
                            }

                            $img_src_srcset .= $img_o . " " . $width_o . "w";
                        }

                        $img_src_srcset = $img_src . " srcset=\"" . $img_src_srcset . "\" size=100vw";

                        $img = str_replace($img_src, $img_src_srcset, $img);
                    }

                    echo $img;
                }
        } ?>';
    }

    /**
     * Returns an excerpt of the post for the entry-list-excerpt template.
     *
     * Gets the excerpt defined by the author or, if it does not exists,
     * an excerpt from the content.
     *
     * @param array $attr Modifying attributes.
     *
     * @return string The entry excerpt.
     */
    public static function odysseyEntryExcerpt($attr): string
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
            "</p>";
        } ?>';
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
     * Displays the trackback link of the current post.
     *
     * @return string The trackback link.
     */
    public static function odysseyTrackbackLink(): string
    {
        if (My::settingValue('reactions_other_trackbacks') !== false) {
            return '<?php if (App::frontend()->context()->posts->trackbacksActive() === true) : ?>
              <details class=reactions-details>
                <summary class=reactions-button>
                  <svg class="reactions-button-icon social-icon-fi" role=img viewBox="0 0 24 24" xmlns=http://www.w3.org/2000/svg>' . My::svgIcons('trackback')['path'] . '</svg>
                  <span class=reactions-button-text>' . __('reactions-trackbacks-add-title') . '</span>
                </summary>

                <div class=reactions-details-content><code><?php echo App::frontend()->context()->posts->getTrackbackLink(); ?></code></div>
              </details>
            <?php endif; ?>';
        }

        return '';
    }

    /**
     * Displays the RSS/Atom feed link of the current post.
     *
     * @return string The feed link.
     */
    public static function odysseyFeedLink(): string
    {
        if (My::settingValue('reactions_feed_link') === null) {
            return '';
        }

        $feed_type = My::settingValue('reactions_feed_link');

        return '<?php
            if (App::frontend()->context()->posts->commentsActive() === true
                || App::frontend()->context()->posts->trackbacksActive() === true
                || App::frontend()->context()->posts->hasComments() === true
                || App::frontend()->context()->posts->hasTrackbacks() === true
            ) :

            $feed_link = App::blog()->url() . App::url()->getURLFor("feed", "' . $feed_type . '") . "/comments/" . App::frontend()->context()->posts->post_id;
            ?>

            <p>
                <a class=reactions-button href="<?php echo $feed_link; ?>" rel=nofollow>
                    <svg class="reactions-button-icon social-icon-fi" role=img viewBox="0 0 24 24" xmlns=http://www.w3.org/2000/svg>' . My::svgIcons('feed')['path'] . '</svg>

                    <span class=reactions-button-text>' . __('reactions-subscribe-link-reactions') . '</span>
                </a>
            </p>
        <?php endif; ?>';
    }

    /**
     * Displays a link to reply to the author of the post by email.
     *
     * @return string The private comment section.
     */
    public static function odysseyPrivateCommentLink(): string
    {
        if (!My::settingValue('reactions_other')) {
            return '';
        }

        if (My::settingValue('reactions_other_email') !== true
            && My::settingValue('reactions_other_facebook') !== true
            && My::settingValue('reactions_other_mastodon') !== true
            && My::settingValue('reactions_other_sms') !== true
            && My::settingValue('reactions_other_signal') !== true
            && My::settingValue('reactions_other_whatsapp') !== true
            && My::settingValue('reactions_other_x') !== true
        ) {
            return '';
        }

        $output = '<?php $reactions_other = ""; ?>';

        if (My::settingValue('reactions_other_email') === true) {
            $output .= '<?php
            if (isset(App::frontend()->context()->posts->user_email) && App::frontend()->context()->posts->user_email && (App::blog()->settings->odyssey->reactions_other === "always" || (App::blog()->settings->odyssey->reactions_other === "comments_open" && App::frontend()->context()->posts->post_open_comment === "1"))
            ) {
                $mailto  = App::frontend()->context()->posts->user_email;
                $subject = "' . __("reactions-other-email-prefix") . '" . App::frontend()->context()->posts->post_title;
                $body    = "' . __('reactions-other-email-body-post-url') . ' " . App::frontend()->context()->posts->getURL();
                $href    = "mailto:" . urlencode($mailto) . "?subject=" . rawurlencode($subject) . "&body=" . rawurlencode($body);

                $reactions_other .= "<p><a class=reactions-button href=\"" . $href . "\"><svg class=\"reactions-button-icon social-icon-fi\" role=img viewBox=\"0 0 24 24\" xmlns=http://www.w3.org/2000/svg>' . str_replace('"', '\"', My::svgIcons('email')['path']) . '</svg> <span class=reactions-button-text>' . __('reactions-other-email-button-text') . '</span></a></p>";

            }
            ?>';
        }

        if (My::settingValue('social_facebook') && My::settingValue('reactions_other_facebook') === true) {
            $output .= '<?php
            $facebook_url = "";

            if (App::frontend()->context()->posts->getURL()) {
                $facebook_url = "https://www.facebook.com/sharer/sharer.php?u=" . urlencode(App::frontend()->context()->posts->getURL());

                if (App::frontend()->context()->posts->post_title) {
                    $facebook_url .= "&t=" . App::frontend()->context()->posts->post_title;
                }
            }

            if ($facebook_url !== "") {
                $reactions_other .= "<p><a class=reactions-button href=\"" . $facebook_url . "\"><svg class=\"reactions-button-icon social-icon-si\" role=img viewBox=\"0 0 24 24\" xmlns=http://www.w3.org/2000/svg>' . str_replace('"', '\"', My::svgIcons('facebook')['path']) . '</svg> <span class=reactions-button-text>' . sprintf(__('reactions-other-facebook-button'), My::socialSites('facebook')['name']) . '</span></a></p>";
            }
            ?>';
        }

        if (My::settingValue('social_mastodon') && My::settingValue('reactions_other_mastodon') === true) {
            $output .= '<?php
            $mastodon_url = "";

            if (App::frontend()->context()->posts->getURL()) {
                $mastodon_url = "https://mastodonshare.com/?url=" . urlencode(App::frontend()->context()->posts->getURL());

                if (App::frontend()->context()->posts->post_title) {
                    $mastodon_url .= "&text=" . App::frontend()->context()->posts->post_title;
                }
            }

            if ($mastodon_url !== "") {
                $reactions_other .= "<p><a class=reactions-button href=\"" . $mastodon_url . "\"><svg class=\"reactions-button-icon social-icon-si\" role=img viewBox=\"0 0 24 24\" xmlns=http://www.w3.org/2000/svg>' . str_replace('"', '\"', My::svgIcons('mastodon')['path']) . '</svg> <span class=reactions-button-text>' . sprintf(__('reactions-other-mastodon-button'), My::socialSites('mastodon')['name']) . '</span></a></p>";
            }
            ?>';
        }

        if (My::settingValue('social_sms') && My::settingValue('reactions_other_sms') === true) {
            $output .= '<?php
            $phone_number = "' . My::settingValue('social_sms') . '";

            if ($phone_number !== "") {
                $sms_href = "sms:' . My::settingValue('social_sms') . '";

                if (App::frontend()->context()->posts->post_title) {
                    $sms_href .= "?body=' . __('reactions-other-email-prefix') . ' " . App::frontend()->context()->posts->post_title;
                }

                $reactions_other .= "<p><a class=reactions-button href=\"" . $sms_href . "\"><svg class=\"reactions-button-icon social-icon-fi\" role=img viewBox=\"0 0 24 24\" xmlns=http://www.w3.org/2000/svg>' . str_replace('"', '\"', My::svgIcons('sms')['path']) . '</svg> <span class=reactions-button-text>' . sprintf(__('reactions-other-sms-button'), My::socialSites('sms')['name']) . '</span></a></p>";
            }
            ?>';
        }

        if (My::settingValue('social_signal') && My::settingValue('reactions_other_signal') === true) {
            $output .= '<?php
            $signal_url = "' . My::settingValue('social_signal') . '";

            if ($signal_url !== "") {
                $reactions_other .= "<p><a class=reactions-button href=\"' . My::settingValue('social_signal') . '\"><svg class=\"reactions-button-icon social-icon-si\" role=img viewBox=\"0 0 24 24\" xmlns=http://www.w3.org/2000/svg>' . str_replace('"', '\"', My::svgIcons('signal')['path']) . '</svg> <span class=reactions-button-text>' . sprintf(__('reactions-other-signal-button'), My::socialSites('signal')['name']) . '</span></a></p>";
            }
            ?>';
        }

        if (My::settingValue('social_whatsapp') && My::settingValue('reactions_other_whatsapp') === true) {
            $output .= '<?php
            $whatsapp_text = App::frontend()->context()->posts->post_title
            ? "?text=' . __('reactions-other-email-prefix') . ' " . App::frontend()->context()->posts->post_title
            : "";
            $whatsapp_url  = "' . My::settingValue('social_whatsapp') . '" . $whatsapp_text;

            if ($whatsapp_url !== "") {
                $reactions_other .= "<p><a class=reactions-button href=\"" . $whatsapp_url . "\"><svg class=\"reactions-button-icon social-icon-si\" role=img viewBox=\"0 0 24 24\" xmlns=http://www.w3.org/2000/svg>' . str_replace('"', '\"', My::svgIcons('whatsapp')['path']) . '</svg> <span class=reactions-button-text>' . sprintf(__('reactions-other-whatsapp-button'), My::socialSites('whatsapp')['name']) . '</span></a></p>";
            }
            ?>';
        }

        if (My::settingValue('social_x') && My::settingValue('reactions_other_x') === true) {
            $output .= '<?php
            $x_url = "' . My::settingValue('social_x') . '";

            if ($x_url !== "") {
                $x_url_parameters = [];

                $x_url_parameters["url"] = urlencode(App::frontend()->context()->posts->getURL());

                if (str_starts_with($x_url, "https://x.com/")) {
                    $x_url_parameters["via"] = str_replace("https://x.com/", "", $x_url);
                } elseif (str_starts_with($x_url, "https://twitter.com/")) {
                    $x_url_parameters["via"] = str_replace("https://twitter.com/", "@", $x_url);
                } else {
                    $x_url_parameters["via"] = "";
                }

                $x_url_share = "https://x.com/intent/tweet?";

                foreach ($x_url_parameters as $param => $value) {
                    $x_url_share .= $param . "=" . $value . "&";
                }

                $x_url_share = substr($x_url_share, 0, -1);

                $reactions_other .= "<p><a class=reactions-button href=\"" . $x_url_share . "\"><svg class=\"reactions-button-icon social-icon-si\" role=img viewBox=\"0 0 24 24\" xmlns=http://www.w3.org/2000/svg>' . str_replace('"', '\"', My::svgIcons('x')['path']) . '</svg> <span class=reactions-button-text>' . sprintf(__('reactions-other-x-button'), My::socialSites('x')['name']) . '</span></a></p>";
            }
            ?>';
        }

        $output .= '<?php
        if ($reactions_other !== "") {
            echo $reactions_other;
        }
        ?>';

        return $output;
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
    public static function odysseyPostTagsBefore(): string
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
     * Displays a notice informing about the support of the Markdown syntax
     * in comments.
     *
     * @return string The notice.
     */
    public static function odysseyMarkdownSupportInfo(): string
    {
        if (My::settingValue('reactions_markdown_notice') === true
            && App::plugins()->moduleExists('legacyMarkdown')
            && App::blog()->settings()->system->markdown_comments === true
        ) {
            $markdown_notice = sprintf(
                __('reactions-comment-markdown-support'),
                __('reactions-comment-markdown-support-link')
            );

            return '<br><small class=text-secondary><em>' . $markdown_notice . '</em></small>';
        }

        return '';
    }

    /**
     * Displays social links in the footer.
     *
     * @return Social links.
     */
    public static function odysseyFooterSocialLinks(): string
    {
        $output = '';
        $count  = 0;

        foreach (My::socialSites() as $id => $data) {
            if (My::settingValue('social_' . $id) !== null && My::settingValue('footer_social_' . $id) !== false) {
                $count++;

                if ($count === 1) {
                    $output .= '<div class=site-footer-block>';
                    $output .= '<ul class=footer-social-links>';
                }

                if (My::svgIcons($id)['author'] === 'simpleicons') {
                    $class = 'social-icon-si footer-social-links-icon-si';
                } elseif (My::svgIcons($id)['author'] === 'feathericons') {
                    $class = 'social-icon-fi footer-social-links-icon-fi';
                }

                $url = My::settingValue('social_' . $id);

                if ($id === 'phone') {
                    $url = 'tel:' . $url;
                } elseif ($id === 'sms') {
                    $url = 'sms:' . $url;
                }

                $output .= '<li>';
                $output .= '<a href=' . My::attrValue($url) . '>';
                $output .= '<span class=footer-social-links-icon-container>';
                $output .= '<svg class="' . $class . '" role=img viewBox="0 0 24 24" xmlns=http://www.w3.org/2000/svg>';
                $output .= '<title>' . $data['name'] . '</title>';
                $output .= My::svgIcons($id)['path'];
                $output .= '</svg>';
                $output .= '</span>';
                $output .= '</a>';
                $output .= '</li>';
            }
        }

        if (My::settingValue('footer_feed') !== null) {
            if ($count === 0) {
                $count++;

                $output .= '<div class=site-footer-block>';
                $output .= '<ul class=footer-social-links>';
            }

            $feed_link = App::blog()->url() . App::url()->getURLFor("feed", My::settingValue('footer_feed'));

            $output .= '<li>';
            $output .= '<a href=' . My::attrValue($feed_link) . '>';
            $output .= '<span class=footer-social-links-icon-container>';
            $output .= '<svg class="social-icon-fi footer-social-links-icon-fi" role=img viewBox="0 0 24 24" xmlns=http://www.w3.org/2000/svg>';
            $output .= '<title>' . __('footer-social-links-feed-title') . '</title>';
            $output .= My::svgIcons('feed')['path'];
            $output .= '</svg>';
            $output .= '</span>';
            $output .= '</a>';
            $output .= '</li>';
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
