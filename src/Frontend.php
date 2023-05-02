<?php
/**
 * Origine Mini, a minimal theme for Dotclear.
 *
 * This file contains functions for displaying the theme.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2023 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

namespace Dotclear\Theme\originemini;

use dcCore;
use dcNsProcess;
use context;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Text;
use Dotclear\Helper\L10n;

// Lets prepare to use custom functions.
require_once 'functions.php';
use OrigineMiniUtils as omUtils;

class Frontend extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_RC_PATH');

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        L10n::set(__DIR__ . '/../locales/' . dcCore::app()->lang . '/main');

        // Behaviors.
        dcCore::app()->addBehavior('publicHeadContent', [self::class, 'origineMiniHeadMeta']);
        dcCore::app()->addBehavior('publicHeadContent', [self::class, 'origineMiniSocialMarkups']);
        dcCore::app()->addBehavior('publicEntryBeforeContent', [self::class, 'origineMiniPostIntro']);
        dcCore::app()->addBehavior('publicFooterContent', [self::class, 'origineMiniSocialLinks']);
        dcCore::app()->addBehavior('publicFooterContent', [self::class, 'origineMiniScriptSearchForm']);
        dcCore::app()->addBehavior('publicFooterContent', [self::class, 'origineMiniScriptTrackbackURL']);
        dcCore::app()->addBehavior('publicFooterContent', [self::class, 'origineMiniScriptImagesWide']);

        // Values.
        dcCore::app()->tpl->addValue('origineMiniMetaDescriptionHome', [self::class, 'origineMiniMetaDescriptionHome']);
        dcCore::app()->tpl->addValue('origineMiniStylesInline', [self::class, 'origineMiniStylesInline']);
        dcCore::app()->tpl->addValue('origineMiniEntryLang', [self::class, 'origineMiniEntryLang']);
        dcCore::app()->tpl->addValue('origineMiniScreenReaderLinks', [self::class, 'origineMiniScreenReaderLinks']);
        dcCore::app()->tpl->addValue('origineMiniHeaderImage', [self::class, 'origineMiniHeaderImage']);
        dcCore::app()->tpl->addValue('origineMiniBlogDescription', [self::class, 'origineMiniBlogDescription']);
        dcCore::app()->tpl->addValue('origineMiniPostListType', [self::class, 'origineMiniPostListType']);
        dcCore::app()->tpl->addValue('origineMiniPostListReactionLink', [self::class, 'origineMiniPostListReactionLink']);
        dcCore::app()->tpl->addValue('origineMiniEntryTime', [self::class, 'origineMiniEntryTime']);
        dcCore::app()->tpl->addValue('origineMiniEntryExcerpt', [self::class, 'origineMiniEntryExcerpt']);
        dcCore::app()->tpl->addValue('origineMiniPostTagsBefore', [self::class, 'origineMiniPostTagsBefore']);
        dcCore::app()->tpl->addValue('origineMiniMarkdownSupportInfo', [self::class, 'origineMiniMarkdownSupportInfo']);
        dcCore::app()->tpl->addValue('origineMiniScriptTrackbackURLCopied', [self::class, 'origineMiniScriptTrackbackURLCopied']);
        dcCore::app()->tpl->addValue('origineMiniEmailAuthor', [self::class, 'origineMiniEmailAuthor']);
        dcCore::app()->tpl->addValue('origineMiniAttachmentTitle', [self::class, 'origineMiniAttachmentTitle']);
        dcCore::app()->tpl->addValue('origineMiniAttachmentSize', [self::class, 'origineMiniAttachmentSize']);
        dcCore::app()->tpl->addValue('origineMiniCategoryDescription', [self::class, 'origineMiniCategoryDescription']);
        dcCore::app()->tpl->addValue('origineMiniFooterCredits', [self::class, 'origineMiniFooterCredits']);
        dcCore::app()->tpl->addValue('origineMiniURIRelative', [self::class, 'origineMiniURIRelative']);

        // Blocks.
        dcCore::app()->tpl->addBlock('origineMiniPostFooter', [self::class, 'origineMiniPostFooter']);
        dcCore::app()->tpl->addBlock('origineMiniHeaderIdentity', [self::class, 'origineMiniHeaderIdentity']);
        dcCore::app()->tpl->addBlock('origineMiniCommentFormWrapper', [self::class, 'origineMiniCommentFormWrapper']);
        dcCore::app()->tpl->addBlock('origineMiniReactionFeedLink', [self::class, 'origineMiniReactionFeedLink']);
        dcCore::app()->tpl->addBlock('origineMiniTrackbackLink', [self::class, 'origineMiniTrackbackLink']);
        dcCore::app()->tpl->addBlock('origineMiniWidgetsNav', [self::class, 'origineMiniWidgetsNav']);
        dcCore::app()->tpl->addBlock('origineMiniWidgetSearchForm', [self::class, 'origineMiniWidgetSearchForm']);
        dcCore::app()->tpl->addBlock('origineMiniWidgetsExtra', [self::class, 'origineMiniWidgetsExtra']);
        dcCore::app()->tpl->addBlock('origineMiniFooter', [self::class, 'origineMiniFooter']);

        return true;
    }

    /**
     * Adds meta tags in the <head> section depending on the blog settings.
     *
     * @return void The head meta.
     */
    public static function origineMiniHeadMeta(): void
    {
        // Adds the name of the editor.
        if (dcCore::app()->blog->settings->system->editor) {
            $editor = dcCore::app()->blog->settings->system->editor;

            // Adds quotes if the value contains one or more spaces.
            $editor = strpos($editor, ' ') === false ? $editor : '"' . $editor . '"';

            echo '<meta name=author content=', $editor, '>', "\n";
        }

        // Adds the content of the copyright notice.
        if (dcCore::app()->blog->settings->system->copyright_notice) {
            $notice = dcCore::app()->blog->settings->system->copyright_notice;

            // Adds quotes if the value contains one or more spaces.
            $notice = strpos($notice, ' ') === false ? $notice : '"' . $notice . '"';

            echo '<meta name=copyright content=', $notice, '>', "\n";
        }

        // Adds the generator name of the blog.
        if (dcCore::app()->blog->settings->originemini->global_meta_generator === true) {
            echo '<meta name=generator content=Dotclear>', "\n";
        }
    }

    /**
     * Displays minimal social markups.
     *
     * @link https://meiert.com/en/blog/minimal-social-markup/
     *
     * @return void The social markups.
     */
    public static function origineMiniSocialMarkups(): void
    {
        if (dcCore::app()->blog->settings->originemini->global_meta_social === true) {
            $title = '';
            $desc  = '';
            $img   = '';

            // Posts and pages.
            if (dcCore::app()->url->type === 'post' || dcCore::app()->url->type === 'pages') {
                $title = dcCore::app()->ctx->posts->post_title;
                $desc  = dcCore::app()->ctx->posts->getExcerpt();

                if ($desc === '') {
                    $desc = dcCore::app()->ctx->posts->getContent();
                }

                $desc = Html::decodeEntities(Html::clean($desc));
                $desc = preg_replace('/\s+/', ' ', $desc);

                if (strlen($desc) > 180) {
                    $desc = Text::cutString($desc, 179) . '…';
                }

                if (context::EntryFirstImageHelper('o', true, '', true)) {
                    $img = omUtils::blogBaseURL() . context::EntryFirstImageHelper('o', true, '', true);
                }

            // Home.
            } elseif (dcCore::app()->url->type === 'default' || dcCore::app()->url->type === 'default-page') {
                $title = dcCore::app()->blog->name;

                if ((int) context::PaginationPosition() > 1 ) {
                    $desc = sprintf(
                        __('meta-social-page-with-number'),
                        context::PaginationPosition()
                    );
                }

                if (dcCore::app()->blog->settings->originemini->global_meta_home_description || dcCore::app()->blog->desc) {
                    if ($desc) {
                        $desc .= ' – ';
                    }

                    if (dcCore::app()->blog->settings->originemini->global_meta_home_description) {
                        $desc .= dcCore::app()->blog->settings->originemini->global_meta_home_description;
                    } elseif (dcCore::app()->blog->desc) {
                        $desc .= dcCore::app()->blog->desc;
                    }

                    $desc = Html::decodeEntities(Html::clean($desc));
                    $desc = preg_replace('/\s+/', ' ', $desc);

                    if (strlen($desc) > 180) {
                        $desc = Text::cutString($desc, 179) . '…';
                    }
                }

            // Categories.
            } elseif (dcCore::app()->url->type === 'category') {
                $title = dcCore::app()->ctx->categories->cat_title;

                if (dcCore::app()->ctx->categories->cat_desc) {
                    $desc = dcCore::app()->ctx->categories->cat_desc;
                    $desc = Html::decodeEntities(Html::clean($desc));
                    $desc = preg_replace('/\s+/', ' ', $desc);

                    if (strlen($desc) > 180) {
                        $desc = Text::cutString($desc, 179) . '…';
                    }
                }

            // Tags.
            } elseif (dcCore::app()->url->type === 'tag' && dcCore::app()->ctx->meta->meta_type === 'tag') {
                $title = dcCore::app()->ctx->meta->meta_id;
                $desc  = sprintf(__('meta-social-tags-post-related'), $title);
            }

            $title = Html::escapeHTML($title);

            if ($title) {
                $desc = Html::escapeHTML($desc);

                if (!$img && dcCore::app()->blog->settings->originemini->header_image['url']) {
                    $img = omUtils::blogBaseURL() . dcCore::app()->blog->settings->originemini->header_image['url'];
                }

                $img  = Html::escapeURL($img);

                if ($img) {
                    echo '<meta name=twitter:card content=summary_large_image>', "\n";
                }

                echo '<meta property=og:title content="', $title, '">', "\n";

                // Quotes seem required for the following meta properties.
                if ($desc) {
                    echo '<meta property="og:description" name="description" content="', $desc, '">', "\n";
                }

                if ($img) {
                    echo '<meta property="og:image" content="', $img, '">', "\n";
                }
            }
        }
    }

    /**
     * Displays the excerpt as an introduction before post content.
     *
     * @return void The excerpt in a div.
     */
    public static function origineMiniPostIntro(): void
    {
        if (dcCore::app()->blog->settings->originemini->content_post_intro === true && dcCore::app()->ctx->posts->post_excerpt) {
            echo '<div id=post-intro>', dcCore::app()->ctx->posts->getExcerpt(), '</div>';
        }
    }

    /**
     * Displays social links in the footer.
     *
     * @return void A list of social links displayed as icons.
     */
    public static function origineMiniSocialLinks(): void
    {
        $social_links = [];

        if (dcCore::app()->blog->settings->originemini->footer_social_links_diaspora) {
            $social_links['Diaspora'] = dcCore::app()->blog->settings->originemini->footer_social_links_diaspora;
        }

        if (dcCore::app()->blog->settings->originemini->footer_social_links_discord) {
            $social_links['Discord'] = dcCore::app()->blog->settings->originemini->footer_social_links_discord;
        }

        if (dcCore::app()->blog->settings->originemini->footer_social_links_facebook) {
            $social_links['Facebook'] = dcCore::app()->blog->settings->originemini->footer_social_links_facebook;
        }

        if (dcCore::app()->blog->settings->originemini->footer_social_links_github) {
            $social_links['GitHub'] = dcCore::app()->blog->settings->originemini->footer_social_links_github;
        }

        if (dcCore::app()->blog->settings->originemini->footer_social_links_mastodon) {
            $social_links['Mastodon'] = dcCore::app()->blog->settings->originemini->footer_social_links_mastodon;
        }

        if (dcCore::app()->blog->settings->originemini->footer_social_links_signal) {
            $social_links['Signal'] = dcCore::app()->blog->settings->originemini->footer_social_links_signal;
        }

        if (dcCore::app()->blog->settings->originemini->footer_social_links_tiktok) {
            $social_links['TikTok'] = dcCore::app()->blog->settings->originemini->footer_social_links_tiktok;
        }

        if (dcCore::app()->blog->settings->originemini->footer_social_links_twitter) {
            $social_links['Twitter'] = dcCore::app()->blog->settings->originemini->footer_social_links_twitter;
        }

        if (dcCore::app()->blog->settings->originemini->footer_social_links_whatsapp) {
            $social_links['WhatsApp'] = dcCore::app()->blog->settings->originemini->footer_social_links_whatsapp;
        }

        if (!empty($social_links)) {
            ?>

            <div class=footer-social-links>
                <ul>
                    <?php
                    foreach ($social_links as $site => $link) {
                        if ($site === 'Signal') {
                            if (substr($link, 0, 1) === '+') {
                                $link = 'https://signal.me/#p/' . $link;
                            }
                        } elseif ($site === 'WhatsApp') {
                            $link = 'https://wa.me/' . str_replace('+', '', $link);
                        } elseif ($site === 'Twitter') {
                            $link = 'https://twitter.com/' . str_replace('@', '', $link);
                        }
                        ?>

                        <li>
                            <a href="<?php echo Html::escapeURL($link); ?>" rel=me>
                                <span class=footer-social-links-icon-container>
                                    <svg class=footer-social-links-icon role=img viewBox="0 0 24 24" xmlns=http://www.w3.org/2000/svg>
                                        <title><?php echo Html::escapeHTML($site); ?></title>

                                        <?php echo strip_tags(omUtils::origineMiniSocialIcons($site), '<path>'); ?>
                                    </svg>
                                </span>
                            </a>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </div>

            <?php
        }
    }

    /**
     * Loads a script in the footer to improve the search form.
     *
     * Loaded only on the search page or on all pages if the search form
     * is in the widget nav area.
     *
     * The content of the variable $script must be exactly the same
     * as the content of the corresponding JS file.
     *
     * @see ./js/searchform.min.js
     *
     * @return void The script.
     */
    public static function origineMiniScriptSearchForm(): void
    {
        if (dcCore::app()->blog->settings->originemini->global_js === true && (dcCore::app()->blog->settings->originemini->widgets_search_form === true || (!dcCore::app()->blog->settings->originemini->widgets_search_form && dcCore::app()->url->type === 'search'))) {
            $script = 'window.onload=function(){var e;document.getElementsByClassName("search-form-submit")[0]&&(""!==(e=new URL(document.location).searchParams.get("q"))&&(document.getElementsByClassName("search-form-submit")[0].disabled=!0),document.getElementsByClassName("search-form")[0].oninput=function(){document.getElementsByClassName("search-form-field")[0].value&&document.getElementsByClassName("search-form-field")[0].value!==e?document.getElementsByClassName("search-form-submit")[0].disabled=!1:document.getElementsByClassName("search-form-submit")[0].disabled=!0})};' . "\n";

            echo '<script>' . $script . '</script>' . "\n";
        }
    }

    /**
     * Loads a script in the footer to copy the trackback URL in one click.
     *
     * Loaded only on posts or pages.
     *
     * The content of the variable $script must be exactly the same
     * as the content of the corresponding JS file.
     *
     * @see ./js/trackbackurl.min.js
     *
     * @return void The script.
     */
    public static function origineMiniScriptTrackbackURL(): void
    {
        if (dcCore::app()->blog->settings->originemini->global_js === true && (dcCore::app()->url->type === 'post' || dcCore::app()->url->type === 'pages')) {
            $script = 'window.onload=function(){document.getElementById("trackback-url")&&(document.getElementById("trackback-url").onclick=function(){window.location.protocol,window.location.host;var t,e=document.getElementById("trackback-url").innerHTML;try{t=new URL(e).href}catch(t){return!1}!1!==t.href&&navigator.clipboard.writeText(t).then(()=>{document.getElementById("trackback-url-copied").style.display="inline"},()=>{document.getElementById("trackback-url-copied").style.display="none"})})};' . "\n";

            echo '<script>' . $script . '</script>' . "\n";
        }
    }

    /**
     * Loads a script in the footer to enlarge big images in posts and pages.
     *
     * Loaded only on posts or pages.
     *
     * The content of the variable $script must be exactly the same
     * as the content of the corresponding JS file.
     *
     * @see ./js/imagewide.min.js
     *
     * @return void The script.
     */
    public static function origineMiniScriptImagesWide(): void
    {
        $om_settings = dcCore::app()->blog->settings->originemini;
        $page_type   = dcCore::app()->url->type;

        if ($om_settings->content_images_wide === true) {
            $context = 'entry';

            if ($om_settings->content_post_list_type === 'content') {
                $context .= '-list';
            }

            if ($context === 'entry-list' || ($context === 'entry' && ($page_type === 'post' || $page_type === 'pages'))) {
                $page_width = in_array(
                    $om_settings->global_page_width,
                    [30, 35, 40],
                    true
                ) ? $om_settings->global_page_width : 30;

                if ((int) $om_settings->content_images_wide_size > 1) {
                    $img_width = (int) $om_settings->content_images_wide_size;
                } else {
                    $img_width = '150';
                }

                $script = 'function getMeta(e,t){var i=new Image;i.src=e,i.addEventListener("load",function(){t(this.width,this.height)})}function imageWide(){var e=parseInt(document.getElementById("script-images-wide").getAttribute("data-pagewidth")),d=0,m=0,t=0,i=(-1===[30,35,40].indexOf(e)&&(e=30),document.createElement("div")),n=(i.style.width="1rem",i.style.display="none",document.body.append(i),window.getComputedStyle(i).getPropertyValue("width").match(/\d+/));i.remove(),0<(t=n&&1<=n.length?parseInt(n[0]):16)&&(m=(d=e*t)+parseInt(document.getElementById("script-images-wide").getAttribute("data-imgwidth")));for(var r=0,s=("entry"===document.getElementById("script-images-wide").getAttribute("data-context")?document.getElementsByTagName("article"):document.getElementsByClassName("entry-list-content"))[0].getElementsByTagName("img");r<s.length;){let a=s[r];getMeta(a.src,function(e,t){let i=e,n=t;i>d&&i>n&&(i>m&&(n=parseInt(m*n/i),i=m),a.setAttribute("style","display:block;margin-left:50%;transform:translateX(-50%);max-width:95vw;"),i&&a.setAttribute("width",i),n)&&a.setAttribute("height",n)}),r++}}document.getElementById("script-images-wide").getAttribute("data-pagewidth")&&document.getElementsByTagName("article")[0]&&(window.addEventListener("load",imageWide),window.addEventListener("resize",imageWide));' . "\n";

                echo '<script data-context=', $context,
                ' data-pagewidth=', $page_width,
                ' data-imgwidth=', $img_width,
                ' id=script-images-wide>',
                $script,
                '</script>', "\n";
            }
        }
    }

    /**
     * Displays the description of the blog homepage to add in a meta description tag.
     *
     * If a custom description is not set in the configurator, displays the blog description.
     *
     * @param array $attr Unused.
     *
     * @return string The description.
     */
    public static function origineMiniMetaDescriptionHome($attr): string
    {
        if (dcCore::app()->blog->settings->originemini->global_meta_home_description) {
            return '<?php echo ' . sprintf(dcCore::app()->tpl->getFilters($attr), 'dcCore::app()->blog->settings->originemini->global_meta_home_description') . '; ?>';
        } else {
            return '<?php echo ' . sprintf(dcCore::app()->tpl->getFilters($attr), 'dcCore::app()->blog->desc') . '; ?>';
        }
    }

    /**
     * Adds styles in the head.
     *
     * @return string The styles.
     */
    public static function origineMiniStylesInline()
    {
        $styles = '';

        if (dcCore::app()->blog->settings->originemini->styles) {
            $styles .= dcCore::app()->blog->settings->originemini->styles;
        }

        if (dcCore::app()->blog->settings->originemini->global_css_custom_mini) {
            $styles .= dcCore::app()->blog->settings->originemini->global_css_custom_mini;
        }

        if ($styles) {
            return '<style>' . $styles . '</style>';
        }
    }

    /**
     * Displays a lang attribute and its value when the language
     * of the current post is different from the language defined
     * for the whole blog.
     *
     * @return string The lang attribute.
     */
    public static function origineMiniEntryLang(): string
    {
        return '<?php
        if (dcCore::app()->ctx->posts->post_lang !== dcCore::app()->blog->settings->system->lang) {
            echo " lang=", dcCore::app()->ctx->posts->post_lang;
        }
        ?>';
    }

    /**
     * Displays navigation links for screen readers.
     *
     * @return string The navigation links.
     */
    public static function origineMiniScreenReaderLinks(): string
    {
        $links = '<a id=skip-content class=skip-links href=#site-content>' . __('skip-link-content') . '</a>';

        // If simpleMenu exists, is activated and a menu has been set, then adds a link to it.
        if (dcCore::app()->plugins->moduleExists('simpleMenu') && dcCore::app()->blog->settings->system->simpleMenu_active === true) {
            $links .= '<a id=skip-menu class=skip-links href=#main-menu>' . __('skip-link-menu') . '</a>';
        }

        // Adds a link to the footer except if it has been disabled in the configurator.
        if (dcCore::app()->blog->settings->originemini->footer_enabled !== false) {
            $links .= '<a id=skip-footer class=skip-links href=#site-footer>' . __('skip-link-footer') . '</a>';
        }

        return $links;
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
    public static function origineMiniHeaderImage($attr)
    {
        if (dcCore::app()->blog->settings->originemini->header_image && dcCore::app()->blog->settings->originemini->header_image['url']) {
            if (!empty($attr['position'])
                && (($attr['position'] === 'bottom' && dcCore::app()->blog->settings->originemini->header_image_position === 'bottom')
                || ($attr['position'] === 'top' && !dcCore::app()->blog->settings->originemini->header_image_position))
            ) {
                $image_url = Html::escapeURL(dcCore::app()->blog->settings->originemini->header_image['url']);
                $srcset    = '';

                if (dcCore::app()->blog->settings->originemini->header_image_description) {
                    $alt = ' alt="' . Html::escapeHTML(dcCore::app()->blog->settings->originemini->header_image_description) . '"';
                } else {
                    $alt = ' alt="' . __('header-image-alt') . '"';
                }

                if (dcCore::app()->blog->settings->originemini->header_image2x) {
                    $image2x_url = Html::escapeURL(dcCore::app()->blog->settings->originemini->header_image2x);

                    $srcset  = ' srcset="';
                    $srcset .= $image_url . ' 1x, ';
                    $srcset .= $image2x_url . ' 2x';
                    $srcset .= '"';
                }

                // Does not add a link to the home page on home page.
                if (dcCore::app()->url->type === 'default') {
                    return '<div id=site-image><img' . $alt . ' src="' . $image_url . '"' . $srcset . '></div>';
                } else {
                    return '<div id=site-image><a href="' . dcCore::app()->blog->url . '" rel=home><img' . $alt . ' src="' . $image_url . '"' . $srcset . '></a></div>';
                }
            }
        }
    }

    /**
     * Displays the blog description.
     *
     * @return string The blog description.
     */
    public static function origineMiniBlogDescription()
    {
        if (dcCore::app()->blog->desc && dcCore::app()->blog->settings->originemini->header_description === true) {
            $description = Html::decodeEntities(Html::clean(dcCore::app()->blog->desc));
            $description = preg_replace('/\s+/', ' ', $description);
            $description = Html::escapeHTML($description);

            if ($description) {
                return '<h2 class=text-secondary id=site-description>' . $description . '</h2>';
            }
        }
    }

    /**
     * Loads the right entry-list template based on theme settings.
     * Default: short
     *
     * @return string The entry-list template.
     */
    public static function origineMiniPostListType(): string
    {
        if (!dcCore::app()->blog->settings->originemini->content_post_list_type || dcCore::app()->blog->settings->originemini->content_post_list_type === 'short') {
            return dcCore::app()->tpl->includeFile(['src' => '_entry-list-short.html']);
        } else {
            $postlist_type_allowed = ['short', 'excerpt', 'content'];

            $postlist_type = dcCore::app()->blog->settings->originemini->content_post_list_type;
            $postlist_type = in_array($postlist_type, $postlist_type_allowed, true) ? $postlist_type : 'short';

            return dcCore::app()->tpl->includeFile(['src' => '_entry-list-' . $postlist_type . '.html']);
        }
    }

    /**
     * Displays a link to comments in the post list.
     *
     * Only if at least a comment has been published.
     *
     * @return string
     */
    public static function origineMiniPostListReactionLink()
    {
        if (dcCore::app()->blog->settings->originemini->content_post_list_reaction_link && dcCore::app()->blog->settings->originemini->content_post_list_reaction_link !== 'disabled') {
            $class = 'class=post-reaction-link';

            $small_open  = '<small>';
            $small_close = '</small>';

            if (dcCore::app()->blog->settings->originemini->content_post_list_type === 'content') {
                $class = 'class=\"post-reaction-link button\"';

                $small_open = $small_close = '';
            }

            return '<?php
            $nb_reactions = intval((int) dcCore::app()->ctx->posts->nb_comment + (int) dcCore::app()->ctx->posts->nb_trackback);

            if ($nb_reactions > 0 || dcCore::app()->blog->settings->originemini->content_post_list_reaction_link === "always") {
                echo "<a aria-label=\"";

                if ($nb_reactions > 1) {
                    printf(__("entry-list-multiple-reactions-link-aria-label"), $nb_reactions);
                } elseif ($nb_reactions === 1) {
                    echo __("entry-list-one-reaction-link-aria-label");
                } elseif ($nb_reactions === 0 && dcCore::app()->blog->settings->originemini->content_post_list_reaction_link === "always") {
                    echo __("entry-list-no-reaction-link-aria-label");
                }

                echo "\" ' . $class . ' href=\"", Html::escapeURL(dcCore::app()->ctx->posts->getURL()), "#", __("reactions-id"), "\">' . $small_open . '";

                if ($nb_reactions > 1) {
                    printf(__("entry-list-multiple-reactions"), $nb_reactions);
                } elseif ($nb_reactions === 1) {
                    echo __("entry-list-one-reaction");
                } elseif ($nb_reactions === 0 && dcCore::app()->blog->settings->originemini->content_post_list_reaction_link === "always") {
                    echo __("entry-list-no-reaction");
                }

                echo "' . $small_close . '</a>";
            }
            ?>';
        }
    }

    /**
     * Displays the published time of posts in the post list.
     *
     * @param array $attr Attributes to customize the value.
     *                    Attribute allowed: context
     *                    Values allowed:
     *                    - (string) entry-list
     *                    - (string) post
     *
     * @return string The published time of the post.
     */
    public static function origineMiniEntryTime($attr)
    {
        if (!empty($attr['context']) && (dcCore::app()->blog->settings->originemini->content_post_list_time === true && $attr['context'] === 'entry-list') || (dcCore::app()->blog->settings->originemini->content_post_time === true && $attr['context'] === 'post')) {
            if (dcCore::app()->blog->settings->originemini->content_separator) {
                $content_separator = ' ' . Html::escapeHTML(dcCore::app()->blog->settings->originemini->content_separator);
            } else {
                $content_separator = ' |';
            }

            return '<?php
                echo "' . $content_separator . '", " ", dcCore::app()->ctx->posts->getDate("' . dcCore::app()->blog->settings->system->time_format . '");
            ?>';
        }
    }

    /**
     * Returns an excerpt of the post for the entry-list-extended template.
     *
     * Gets the excerpt defined by the author or, if it does not exists,
     * an excerpt from the content.
     *
     * @param array $attr Modifying attributes.
     *
     * @return string The entry excerpt.
     */
    public static function origineMiniEntryExcerpt($attr): string
    {
        return '<?php
        $the_excerpt = "";

        if (' . sprintf(dcCore::app()->tpl->getFilters($attr), 'dcCore::app()->ctx->posts->getExcerpt()') . ') {
            $the_excerpt = ' . sprintf(dcCore::app()->tpl->getFilters($attr), 'dcCore::app()->ctx->posts->getExcerpt()') . ';
        } else {
            $the_excerpt = ' . sprintf(dcCore::app()->tpl->getFilters($attr), 'dcCore::app()->ctx->posts->getContent()') . ';

            if (strlen($the_excerpt) > 200) {
                $the_excerpt  = substr($the_excerpt, 0, 200);
                $the_excerpt  = preg_replace("/[^a-z0-9]+\Z/i", "", $the_excerpt);
                $the_excerpt .= "…";
            }
        }

        if ($the_excerpt) {
            if (dcCore::app()->ctx->posts->post_lang === dcCore::app()->blog->settings->system->lang) {
                $lang = "";
            } else {
                $lang = " lang=" . dcCore::app()->ctx->posts->post_lang;
            }

            echo "<p class=\"content-text post-excerpt text-secondary\"" . $lang . ">",
            $the_excerpt,
            " <a aria-label=\"", sprintf(__("entry-list-open-aria"), dcCore::app()->ctx->posts->post_title), "\" href=\"", dcCore::app()->ctx->posts->getURL(), "\">" . __("entry-list-open"), "</a>",
            "</p>";
        }
        ?>';
    }

    /**
     * Adds a text string before the tag list of posts.
     *
     * @return string The text string.
     */
    public static function origineMiniPostTagsBefore()
    {
        return '<?php
        if (dcCore::app()->ctx->posts->post_meta) {
            $post_meta = unserialize(dcCore::app()->ctx->posts->post_meta);

            if (is_array($post_meta) && isset($post_meta["tag"])) {
                if (count($post_meta["tag"]) > 1) {
                    echo __("post-tags-prefix-multiple");
                } elseif (count($post_meta["tag"]) === 1) {
                    echo __("post-tags-prefix-one");
                }
            }
        }
        ?>';
    }

    /**
     * Displays a notice informing about the support of the Markdown syntax.
     *
     * @return string The notice.
     */
    public static function origineMiniMarkdownSupportInfo()
    {
        if (dcCore::app()->blog->settings->system->markdown_comments === true) {
            $markdown_notice = sprintf(
                __('reactions-comment-markdown-support'),
                __('reactions-comment-markdown-support-link')
            );

            return '<br><small class=text-secondary><em>' . $markdown_notice . '</em></small>';
        }
    }

    /**
     * Displays "copied" after the trackback URL in posts.
     *
     * Should only be displayed when a visitor click on the URL.
     *
     * @return string Copied alert.
     */
    public static function origineMiniScriptTrackbackURLCopied()
    {
        if (dcCore::app()->blog->settings->originemini->global_js === true) {
            return ' <span id=trackback-url-copied>' . __('reactions-trackback-url-copied') . '</span>';
        }
    }

    /**
     * Displays a link to reply to the author of the post by email.
     *
     * @return string The private comment section.
     */
    public static function origineMiniEmailAuthor()
    {
        if (dcCore::app()->blog->settings->originemini->content_post_email_author !== 'disabled') {
            return '<?php
            if (isset(dcCore::app()->ctx->posts->user_email) && dcCore::app()->ctx->posts->user_email && (dcCore::app()->blog->settings->originemini->content_post_email_author === "always" || (dcCore::app()->blog->settings->originemini->content_post_email_author === "comments_open" && dcCore::app()->ctx->posts->post_open_comment === "1"))
            ) {
            ?>
                <div class=comment-private>
                    <h3 class=reaction-title>' . __('reactions-comment-private-title') . '</h3>

                    <?php $body = "' . __('reactions-comment-private-body-post-url') . ' " . dcCore::app()->ctx->posts->getURL(); ?>

                    <p>
                        <a class=button href="mailto:<?php echo urlencode(dcCore::app()->ctx->posts->user_email); ?>?subject=<?php echo htmlentities("' . __("reactions-comment-private-email-prefix") . ' " . dcCore::app()->ctx->posts->post_title . "&body=" . $body); ?>">' . __('reactions-comment-private-button-text') . '</a>
                    </p>
                </div>
            <?php }
            ?>';
        }
    }

    /**
     * Adds a title in the plural or singular at the top of post attachment list.
     *
     * @return string The attachment area title.
     */
    public static function origineMiniAttachmentTitle(): string
    {
        return '<?php
        if (count(dcCore::app()->ctx->attachments) === 1) {
            echo __("attachments-title-one");
        } else {
            echo __("attachments-title-multiple");
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
    public static function origineMiniAttachmentSize(): string
    {
        return '<?php
        $kb = 1024;
        $mb = 1024 * $kb;
        $gb = 1024 * $mb;
        $tb = 1024 * $gb;

        $size = $attach_f->size;

        // Setting ignored for some reason:
        // setlocale(LC_ALL, "fr_FR");

        if (dcCore::app()->blog->settings->system->lang === "fr") {
            $locale_decimal = ",";
        } else {
            $lang_conv      = localeconv();
            $locale_decimal = $lang_conv["decimal_point"];
        }

        if ($size > 0) {
            if ($size < $kb) {
                printf(__("attachment-size-b"), $size);
            } elseif ($size < $mb) {
                printf(__("attachment-size-kb"), number_format($size / $kb, 1, $locale_decimal));
            } elseif ($size < $gb) {
                printf(__("attachment-size-mb"), number_format($size / $mb, 1, $locale_decimal));
            } elseif ($size < $tb) {
                printf(__("attachment-size-gb"), number_format($size / $gb, 1, $locale_decimal));
            } else {
                printf(__("attachment-size-tb"), number_format($size / $tb, 1, $locale_decimal));
            }
        }
        ?>';
    }

    /**
     * Displays the category description in a block only if a description is set.
     *
     * @return string The category description.
     */
    public static function origineMiniCategoryDescription()
    {
        if (dcCore::app()->ctx->categories->cat_desc) {
            return '<div class=content-text>' . dcCore::app()->ctx->categories->cat_desc . '</div>';
        }
    }

    /**
     * Credits to display at the bottom of the site.
     *
     * Dotclear and theme versions are shown only on dev environments.
     *
     * @return string The footer credits.
     */
    public static function origineMiniFooterCredits()
    {
        if (dcCore::app()->blog->settings->originemini->footer_credits !== false) {
            if (!defined('DC_DEV') || (defined('DC_DEV') && DC_DEV === false)) {
                return '<div class=site-footer-block>' . __('footer-powered-by') . '</div>';
            } else {
                $dc_version       = dcCore::app()->getVersion('core');
                $dc_version_parts = explode('-', $dc_version);
                $dc_version_short = $dc_version_parts[0] ?? $dc_version;

                $theme_version = dcCore::app()->themes->moduleInfo('originemini', 'version');

                return '<div class=site-footer-block>' . sprintf(__('footer-powered-by-dev'), $dc_version, $dc_version_short, $theme_version) . '</div>';
            }
        }
    }

    /**
     * Returns the relative URI of the current page.
     *
     * @return string The relative URI.
     */
    public static function origineMiniURIRelative(): string
    {
        return '<?php echo filter_var($_SERVER["REQUEST_URI"], FILTER_SANITIZE_URL); ?>';
    }

    /**
     * Displays the footer of the post if it has content.
     *
     * @param array $attr    Unused.
     * @param void  $content The post footer.
     *
     * @return string The post footer.
     */
    public static function origineMiniPostFooter($attr, $content)
    {
        $has_attachment = false;
        $has_category   = false;
        $has_tag        = false;

        if (dcCore::app()->ctx->posts->countMedia('attachment') > 0) {
            $has_attachment = true;
        }

        if (dcCore::app()->ctx->posts->cat_id) {
            $has_category = true;
        }

        if (dcCore::app()->ctx->posts->post_meta) {
            $post_meta = unserialize(dcCore::app()->ctx->posts->post_meta);

            if (is_array($post_meta) && isset($post_meta['tag']) && count($post_meta['tag']) > 0) {
                $has_tag = true;
            }
        }

        if ($has_attachment === true || $has_category === true || $has_tag === true) {
            return $content;
        }
    }

    /**
     * Displays the header site title and description if the description is shown.
     *
     * @param array $attr    Unused.
     * @param void  $content The header.
     *
     * @return string The link.
     */
    public static function origineMiniHeaderIdentity($attr, $content): string
    {
        if (dcCore::app()->blog->settings->originemini->header_description !== true) {
            return $content;
        } else {
            return '<div id=site-identity>' . $content . '</div>';
        }
    }

    /**
     * Displays the comment form, possibly inside a wrapper to toggle it.
     *
     * @param array $attr    Unused.
     * @param void  $content The comment form.
     *
     * @return string The comment form.
     */
    public static function origineMiniCommentFormWrapper($attr, $content): string
    {
        if (!dcCore::app()->blog->settings->originemini->content_commentform_hide) {
            return '<h3 class=reaction-title>' . __('reactions-comment-form-title') . '</h3>' . $content;
        } else {
            if (dcCore::app()->ctx->comment_preview !== null && dcCore::app()->ctx->comment_preview["preview"]) {
                return '<div id=react-content><h3 class=reaction-title>' . __('reactions-comment-form-preview-title') . '</h3>' . $content . '</div>';
            } else {
                return '<details><summary class=button>' . __('reactions-react-link-title') . '</summary><div id=react-content><h3 class=reaction-title>' . __('reactions-comment-form-title') . '</h3>' . $content . '</div></details>';
            }
        }
    }

    /**
     * Displays a link to the comment feed.
     *
     * @param array $attr    Unused.
     * @param void  $content The link.
     *
     * @return string The link.
     */
    public static function origineMiniReactionFeedLink($attr, $content)
    {
        if (dcCore::app()->blog->settings->originemini->content_reaction_feed !== false) {
            return $content;
        }
    }

    /**
     * Displays a link to copy the trackback URL.
     *
     * @param array $attr    Unused.
     * @param void  $content The link.
     *
     * @return string The link.
     */
    public static function origineMiniTrackbackLink($attr, $content)
    {
        if (dcCore::app()->blog->settings->originemini->content_trackback_link !== false) {
            return $content;
        }
    }

    /**
     * Displays navigation widgets.
     *
     * @param array $attr    Unused.
     * @param void  $content The content of the widget area.
     *
     * @return string The navigation widget.
     */
    public static function origineMiniWidgetsNav($attr, $content)
    {
        if (dcCore::app()->blog->settings->originemini->widgets_nav_position !== 'disabled') {
            return $content;
        }
    }

    /**
     * Displays a search form before the navigation widget area.
     *
     * @param array $attr    Unused.
     * @param void  $content The content of the search form.
     *
     * @return string The search form.
     */
    public static function origineMiniWidgetSearchForm($attr, $content)
    {
        if (dcCore::app()->blog->settings->originemini->widgets_search_form === true && \dcCore::app()->url->type !== 'search') {
            return $content;
        }
    }

    /**
     * Displays extra widgets.
     *
     * @param array $attr    Unused.
     * @param void  $content The content of the widget area.
     *
     * @return string The navigation widget.
     */
    public static function origineMiniWidgetsExtra($attr, $content)
    {
        if (dcCore::app()->blog->settings->originemini->widgets_extra_enabled !== false) {
            return $content;
        }
    }

    /**
     * Displays the footer.
     *
     * @param array $attr    Unused.
     * @param void  $content The content of the footer.
     *
     * @return string The footer.
     */
    public static function origineMiniFooter($attr, $content)
    {
        if (dcCore::app()->blog->settings->originemini->footer_enabled !== false) {
            return $content;
        }
    }
}
