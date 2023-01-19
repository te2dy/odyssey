<?php
/**
 * Origine Mini, a minimal theme for Dotclear.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2023 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

namespace themes\origine_mini;

if (!defined('DC_RC_PATH')) {
    return;
}

// Lets prepare to use custom functions.
require_once 'inc/functions.php';
use \OrigineMiniUtils as omUtils;

\l10n::set(__DIR__ . '/locales/' . \dcCore::app()->lang . '/main');

\dcCore::app()->addBehavior('publicHeadContent', [__NAMESPACE__ . '\OrigineMiniPublicBehaviors', 'origineMiniHeadMeta']);
\dcCore::app()->addBehavior('publicHeadContent', [__NAMESPACE__ . '\OrigineMiniPublicBehaviors', 'origineMiniSocialMarkups']);
\dcCore::app()->addBehavior('publicEntryBeforeContent', [__NAMESPACE__ . '\OrigineMiniPublicBehaviors', 'origineMiniPostIntro']);
\dcCore::app()->addBehavior('publicFooterContent', [__NAMESPACE__ . '\OrigineMiniPublicBehaviors', 'origineMiniSocialLinks']);
\dcCore::app()->addBehavior('publicFooterContent', [__NAMESPACE__ . '\OrigineMiniPublicBehaviors', 'origineMiniScriptSearchForm']);
\dcCore::app()->addBehavior('publicFooterContent', [__NAMESPACE__ . '\OrigineMiniPublicBehaviors', 'origineMiniScriptTrackbackURL']);
\dcCore::app()->addBehavior('publicFooterContent', [__NAMESPACE__ . '\OrigineMiniPublicBehaviors', 'origineMiniScriptImagesWide']);

/**
 * Adds behaviors to the theme's template files.
 */
class OrigineMiniPublicBehaviors
{
    /**
     * Adds meta tags in the <head> section depending on the blog settings.
     *
     * @return void The head meta.
     */
    public static function origineMiniHeadMeta()
    {
        // Adds the name of the editor.
        if (\dcCore::app()->blog->settings->system->editor) {
            $editor = \dcCore::app()->blog->settings->system->editor;

            // Adds quotes if the value contains one or more spaces.
            $editor = strpos($editor, ' ') === false ? $editor : '"' . $editor . '"';

            echo '<meta name=author content=', $editor, '>', "\n";
        }

        // Adds the content of the copyright notice.
        if (\dcCore::app()->blog->settings->system->copyright_notice) {
            $notice = \dcCore::app()->blog->settings->system->copyright_notice;

            // Adds quotes if the value contains one or more spaces.
            $notice = strpos($notice, ' ') === false ? $notice : '"' . $notice . '"';

            echo '<meta name=copyright content=', $notice, '>', "\n";
        }

        // Adds the generator of the blog.
        if (\dcCore::app()->blog->settings->originemini->global_meta_generator === true) {
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
    public static function origineMiniSocialMarkups()
    {
        if (\dcCore::app()->blog->settings->originemini->global_meta_social === true) {
            $title = '';
            $desc  = '';
            $img   = '';

            // Posts and pages.
            if (\dcCore::app()->url->type === 'post' || \dcCore::app()->url->type === 'pages') {
                $title = \dcCore::app()->ctx->posts->post_title;
                $desc  = \dcCore::app()->ctx->posts->getExcerpt();

                if ($desc === '') {
                    $desc = \dcCore::app()->ctx->posts->getContent();
                }

                $desc = \html::decodeEntities(\html::clean($desc));
                $desc = preg_replace('/\s+/', ' ', $desc);

                if (strlen($desc) > 180) {
                    $desc = \text::cutString($desc, 179) . '…';
                }

                if (\context::EntryFirstImageHelper('o', true, '', true)) {
                    $img = \context::EntryFirstImageHelper('o', true, '', true);
                }

            // Home.
            } elseif (\dcCore::app()->url->type === 'default' || \dcCore::app()->url->type === 'default-page') {
                $title = \dcCore::app()->blog->name;

                if ((int) \context::PaginationPosition() > 1 ) {
                    $desc = sprintf(
                        __('meta-social-page-with-number'),
                        \context::PaginationPosition()
                    );
                }

                if (\dcCore::app()->blog->settings->originemini->global_meta_home_description || \dcCore::app()->blog->desc) {
                    if ($desc) {
                        $desc .= ' – ';
                    }

                    if (\dcCore::app()->blog->settings->originemini->global_meta_home_description) {
                        $desc .= \dcCore::app()->blog->settings->originemini->global_meta_home_description;
                    } elseif (\dcCore::app()->blog->desc) {
                        $desc .= \dcCore::app()->blog->desc;
                    }

                    $desc  = \html::decodeEntities(\html::clean($desc));
                    $desc  = preg_replace('/\s+/', ' ', $desc);

                    if (strlen($desc) > 180) {
                        $desc = \text::cutString($desc, 179) . '…';
                    }
                }

            // Categories.
            } elseif (\dcCore::app()->url->type === 'category') {
                $title = \dcCore::app()->ctx->categories->cat_title;

                if (\dcCore::app()->ctx->categories->cat_desc) {
                    $desc = \dcCore::app()->ctx->categories->cat_desc;
                    $desc = \html::decodeEntities(\html::clean($desc));
                    $desc = preg_replace('/\s+/', ' ', $desc);

                    if (strlen($desc) > 180) {
                        $desc = \text::cutString($desc, 179) . '…';
                    }
                }

            // Tags.
            } elseif (\dcCore::app()->url->type === 'tag' && \dcCore::app()->ctx->meta->meta_type === 'tag') {
                $title = \dcCore::app()->ctx->meta->meta_id;
                $desc  = sprintf(__('meta-social-tags-post-related'), $title);
            }

            $title = \html::escapeHTML($title);

            if ($title) {
                $desc = \html::escapeHTML($desc);
                $img  = \html::escapeURL($img);

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
    public static function origineMiniPostIntro()
    {
        if (\dcCore::app()->blog->settings->originemini->content_post_intro === true && \dcCore::app()->ctx->posts->post_excerpt) {
            echo '<div id=post-single-excerpt>', \dcCore::app()->ctx->posts->getExcerpt(), '</div>';
        }
    }

    /**
     * Displays social links in the footer.
     *
     * @return void A list of social links displayed as icons.
     */
    public static function origineMiniSocialLinks()
    {
        $social_links = [];

        if (\dcCore::app()->blog->settings->originemini->footer_social_links_diaspora) {
            $social_links['Diaspora'] = \dcCore::app()->blog->settings->originemini->footer_social_links_diaspora;
        }

        if (\dcCore::app()->blog->settings->originemini->footer_social_links_discord) {
            $social_links['Discord'] = \dcCore::app()->blog->settings->originemini->footer_social_links_discord;
        }

        if (\dcCore::app()->blog->settings->originemini->footer_social_links_facebook) {
            $social_links['Facebook'] = \dcCore::app()->blog->settings->originemini->footer_social_links_facebook;
        }

        if (\dcCore::app()->blog->settings->originemini->footer_social_links_github) {
            $social_links['GitHub'] = \dcCore::app()->blog->settings->originemini->footer_social_links_github;
        }

        if (\dcCore::app()->blog->settings->originemini->footer_social_links_mastodon) {
            $social_links['Mastodon'] = \dcCore::app()->blog->settings->originemini->footer_social_links_mastodon;
        }

        if (\dcCore::app()->blog->settings->originemini->footer_social_links_signal) {
            $social_links['Signal'] = \dcCore::app()->blog->settings->originemini->footer_social_links_signal;
        }

        if (\dcCore::app()->blog->settings->originemini->footer_social_links_tiktok) {
            $social_links['TikTok'] = \dcCore::app()->blog->settings->originemini->footer_social_links_tiktok;
        }

        if (\dcCore::app()->blog->settings->originemini->footer_social_links_twitter) {
            $social_links['Twitter'] = \dcCore::app()->blog->settings->originemini->footer_social_links_twitter;
        }

        if (\dcCore::app()->blog->settings->originemini->footer_social_links_whatsapp) {
            $social_links['WhatsApp'] = \dcCore::app()->blog->settings->originemini->footer_social_links_whatsapp;
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
                            <a href="<?php echo \html::escapeURL($link); ?>" rel=me>
                                <span class=footer-social-links-icon-container>
                                    <svg class=footer-social-links-icon role=img viewBox="0 0 24 24" xmlns=http://www.w3.org/2000/svg>
                                        <title><?php echo \html::escapeHTML($site); ?></title>

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
    public static function origineMiniScriptSearchForm()
    {
        if (\dcCore::app()->blog->settings->originemini->global_js === true && (\dcCore::app()->blog->settings->originemini->widgets_search_form === true || (!\dcCore::app()->blog->settings->originemini->widgets_search_form && \dcCore::app()->url->type === 'search'))) {
            $script = 'window.onload=function(){if(document.getElementsByClassName("search-form-submit")[0]){var e=new URL(document.location).searchParams.get("q");""!==e&&(document.getElementsByClassName("search-form-submit")[0].disabled=!0),document.getElementsByClassName("search-form")[0].oninput=function(){document.getElementsByClassName("search-form-field")[0].value&&document.getElementsByClassName("search-form-field")[0].value!==e?document.getElementsByClassName("search-form-submit")[0].disabled=!1:document.getElementsByClassName("search-form-submit")[0].disabled=!0}}};' . "\n";

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
    public static function origineMiniScriptTrackbackURL()
    {
        if (\dcCore::app()->blog->settings->originemini->global_js === true && (\dcCore::app()->url->type === 'post' || \dcCore::app()->url->type === 'pages')) {
            $script = 'window.onload=function(){document.getElementById("trackback-url")&&(document.getElementById("trackback-url").onclick=function(){window.location.protocol,window.location.host;var e,t=document.getElementById("trackback-url").innerHTML;try{e=new URL(t).href}catch(c){return!1}!1!==e.href&&navigator.clipboard.writeText(e).then(()=>{document.getElementById("trackback-url-copied").style.display="inline"},()=>{document.getElementById("trackback-url-copied").style.display="none"})})};' . "\n";

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
    public static function origineMiniScriptImagesWide()
    {
        if (\dcCore::app()->blog->settings->originemini->content_images_wide === true) {
            if (\dcCore::app()->url->type === 'post' || \dcCore::app()->url->type === 'pages') {
                $page_width_allowed = [30, 35, 40];

                if (in_array(\dcCore::app()->blog->settings->originemini->global_page_width, $page_width_allowed, true)) {
                    $page_width = \dcCore::app()->blog->settings->originemini->global_page_width;
                } else {
                    $page_width = 30;
                }

                $script = 'function getMeta(e,t){var i=new Image;i.src=e,i.addEventListener("load",function(){t(this.width,this.height)})}function imageWide(){var e=parseInt(document.getElementById("script-images-wide").getAttribute("data-pagewidth")),t=e+10,i=0,a=0,d=0;-1===[30,35,40].indexOf(e)&&(e=30);let n=document.createElement("div");n.style.width="1rem",n.style.display="none",document.body.append(n);var r=window.getComputedStyle(n).getPropertyValue("width").match(/\d+/);n.remove(),(d=r&&r.length>=1?parseInt(r[0]):16)>0&&(i=e*d,a=t*d);for(var g=document.getElementsByTagName("article")[0].getElementsByTagName("img"),l=0;l<g.length;){let s=g[l];getMeta(s.src,function(e,t){let d=e,n=t,r="";d>i&&d>n&&(d>a&&(n=parseInt(a*n/d),d=a),r="display:block;margin-left:50%;transform:translateX(-50%);max-width:95vw;",s.setAttribute("style",r),d&&s.setAttribute("width",d),n&&s.setAttribute("height",n))}),l++}}document.getElementById("script-images-wide").getAttribute("data-pagewidth")&&document.getElementsByTagName("article")[0]&&(window.addEventListener("load",imageWide),window.addEventListener("resize",imageWide));' . "\n";

                echo '<script data-pagewidth=' . $page_width . ' id=script-images-wide>' . $script . '</script>' . "\n";
            }
        }
    }
}

\dcCore::app()->tpl->addValue('origineMiniMetaDescriptionHome', [__NAMESPACE__ . '\OrigineMiniPublicValues', 'origineMiniMetaDescriptionHome']);
\dcCore::app()->tpl->addValue('origineMiniStylesInline', [__NAMESPACE__ . '\OrigineMiniPublicValues', 'origineMiniStylesInline']);
\dcCore::app()->tpl->addValue('origineMiniEntryLang', [__NAMESPACE__ . '\OrigineMiniPublicValues', 'origineMiniEntryLang']);
\dcCore::app()->tpl->addValue('origineMiniScreenReaderLinks', [__NAMESPACE__ . '\OrigineMiniPublicValues', 'origineMiniScreenReaderLinks']);
\dcCore::app()->tpl->addValue('origineMiniHeaderImage', [__NAMESPACE__ . '\OrigineMiniPublicValues', 'origineMiniHeaderImage']);
\dcCore::app()->tpl->addValue('origineMiniBlogDescription', [__NAMESPACE__ . '\OrigineMiniPublicValues', 'origineMiniBlogDescription']);
\dcCore::app()->tpl->addValue('origineMiniPostListType', [__NAMESPACE__ . '\OrigineMiniPublicValues', 'origineMiniPostListType']);
\dcCore::app()->tpl->addValue('origineMiniPostListReactionLink', [__NAMESPACE__ . '\OrigineMiniPublicValues', 'origineMiniPostListReactionLink']);
\dcCore::app()->tpl->addValue('origineMiniEntryTime', [__NAMESPACE__ . '\OrigineMiniPublicValues', 'origineMiniEntryTime']);
\dcCore::app()->tpl->addValue('origineMiniEntryExcerpt', [__NAMESPACE__ . '\OrigineMiniPublicValues', 'origineMiniEntryExcerpt']);
\dcCore::app()->tpl->addValue('origineMiniPostTagsBefore', [__NAMESPACE__ . '\OrigineMiniPublicValues', 'origineMiniPostTagsBefore']);
\dcCore::app()->tpl->addValue('origineMiniScriptTrackbackURLCopied', [__NAMESPACE__ . '\OrigineMiniPublicValues', 'origineMiniScriptTrackbackURLCopied']);
\dcCore::app()->tpl->addValue('origineMiniEmailAuthor', [__NAMESPACE__ . '\OrigineMiniPublicValues', 'origineMiniEmailAuthor']);
\dcCore::app()->tpl->addValue('origineMiniAttachmentTitle', [__NAMESPACE__ . '\OrigineMiniPublicValues', 'origineMiniAttachmentTitle']);
\dcCore::app()->tpl->addValue('origineMiniAttachmentSize', [__NAMESPACE__ . '\OrigineMiniPublicValues', 'origineMiniAttachmentSize']);
\dcCore::app()->tpl->addValue('origineMiniCategoryDescription', [__NAMESPACE__ . '\OrigineMiniPublicValues', 'origineMiniCategoryDescription']);
\dcCore::app()->tpl->addValue('origineMiniFooterCredits', [__NAMESPACE__ . '\OrigineMiniPublicValues', 'origineMiniFooterCredits']);
\dcCore::app()->tpl->addValue('origineMiniURIRelative', [__NAMESPACE__ . '\OrigineMiniPublicValues', 'origineMiniURIRelative']);

/**
 * Adds custom values to the theme's template files.
 */
class OrigineMiniPublicValues
{
    /**
     * Displays the description of the blog homepage to add in a meta description tag.
     *
     * If a custom description is not set in the configurator, displays the blog description.
     *
     * @param array $attr Unused.
     *
     * @return void The description.
     */
    public static function origineMiniMetaDescriptionHome($attr)
    {
        if (\dcCore::app()->blog->settings->originemini->global_meta_home_description) {
            return '<?php echo ' . sprintf(\dcCore::app()->tpl->getFilters($attr), 'dcCore::app()->blog->settings->originemini->global_meta_home_description') . '; ?>';
        } else {
            return '<?php echo ' . sprintf(\dcCore::app()->tpl->getFilters($attr), 'dcCore::app()->blog->desc') . '; ?>';
        }
    }

    /**
     * Adds styles in the head.
     *
     * @return string The styles.
     */
    public static function origineMiniStylesInline()
    {
        if (\dcCore::app()->blog->settings->originemini->styles) {
            return '<style>' . \dcCore::app()->blog->settings->originemini->styles . '</style>';
        }
    }

    /**
     * Displays a lang attribute and its value when the language
     * of the current post is different from the language defined
     * for the whole blog.
     *
     * @return void The lang attribute.
     */
    public static function origineMiniEntryLang()
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
     * @return void The navigation links.
     */
    public static function origineMiniScreenReaderLinks()
    {
        $links = '<a id=skip-content class=skip-links href=#site-content>' . __('skip-link-content') . '</a>';

        // If simpleMenu exists, is activated and a menu has been set, then adds a link to it.
        if (\dcCore::app()->plugins->moduleExists('simpleMenu') && \dcCore::app()->blog->settings->system->simpleMenu_active === true) {
            $links .= '<a id=skip-menu class=skip-links href=#main-menu>' . __('skip-link-menu') . '</a>';
        }

        // Adds a link to the footer except if it has been disabled in the configurator.
        if (\dcCore::app()->blog->settings->originemini->footer_enabled !== false) {
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
     * @return void
     */
    public static function origineMiniHeaderImage($attr)
    {
        if (\dcCore::app()->blog->settings->originemini->header_image && \dcCore::app()->blog->settings->originemini->header_image['url']) {
            if (!empty($attr['position'])
                && (($attr['position'] === 'bottom' && \dcCore::app()->blog->settings->originemini->header_image_position === 'bottom')
                || ($attr['position'] === 'top' && !\dcCore::app()->blog->settings->originemini->header_image_position))
            ) {
                $image_url = \html::escapeURL(\dcCore::app()->blog->settings->originemini->header_image['url']);
                $srcset    = '';

                if (\dcCore::app()->blog->settings->originemini->header_image2x) {
                    $image2x_url = \html::escapeURL(\dcCore::app()->blog->settings->originemini->header_image2x);

                    $srcset  = ' srcset="';
                    $srcset .= $image_url . ' 1x, ';
                    $srcset .= $image2x_url . ' 2x';
                    $srcset .= '"';
                }

                if (\dcCore::app()->url->type === 'default') {
                    return '<div id=site-image><img alt="' . __('Header Image') . '" src="' . $image_url . '"' . $srcset . '></div>';
                } else {
                    return '<div id=site-image><a alt="' . __('Header Image') . '" href="' . \dcCore::app()->blog->url . '" rel=home><img src="' . $image_url . '"' . $srcset . '></a></div>';
                }
            }
        }
    }

    /**
     * Displays the blog description.
     *
     * @return void The blog description.
     */
    public static function origineMiniBlogDescription()
    {
        if (\dcCore::app()->blog->desc && \dcCore::app()->blog->settings->originemini->header_description === true) {
            $description = \html::decodeEntities(\html::clean(\dcCore::app()->blog->desc));
            $description = preg_replace('/\s+/', ' ', $description);
            $description = \html::escapeHTML($description);

            if ($description) {
                return '<h2 class=text-secondary id=site-description>' . $description . '</h2>';
            }
        }
    }

    /**
     * Displays the category description in a block only if a description is set.
     *
     * @return void The category description.
     */
    public static function origineMiniCategoryDescription()
    {
        if (\dcCore::app()->ctx->categories->cat_desc) {
            return '<div class=content-text>' . \dcCore::app()->ctx->categories->cat_desc . '</div>';
        }
    }

    /**
     * Credits to display at the bottom of the site.
     *
     * Dotclear and theme versions are shown only on dev environments.
     *
     * @return void The footer credits.
     */
    public static function origineMiniFooterCredits()
    {
        if (\dcCore::app()->blog->settings->originemini->footer_credits !== false) {
            if (!defined('DC_DEV') || (defined('DC_DEV') && DC_DEV === false)) {
                return '<div class=site-footer-block>' . __('footer-powered-by') . '</div>';
            } else {
                $dc_version       = \dcCore::app()->getVersion('core');
                $dc_version_parts = explode('-', $dc_version);
                $dc_version_short = $dc_version_parts[0] ?? $dc_version;

                $theme_version = \dcCore::app()->themes->moduleInfo('origine-mini', 'version');

                return '<div class=site-footer-block>' . sprintf(__('footer-powered-by-dev'), $dc_version, $dc_version_short, $theme_version) . '</div>';
            }
        }
    }

    /**
     * Loads the right entry-list template based on theme settings.
     * Default: short
     *
     * @return void The entry-list template.
     */
    public static function origineMiniPostListType()
    {
        if (!\dcCore::app()->blog->settings->originemini->content_post_list_type || \dcCore::app()->blog->settings->originemini->content_post_list_type === 'short') {
            return \dcCore::app()->tpl->includeFile(['src' => '_entry-list-short.html']);
        } else {
            return \dcCore::app()->tpl->includeFile(['src' => '_entry-list-extended.html']);
        }
    }

    /**
     * Displays a link to comments in the post list.
     *
     * Only if at least a comment has been published.
     *
     * @return void
     */
    public static function origineMiniPostListReactionLink()
    {
        if (\dcCore::app()->blog->settings->originemini->content_post_list_reaction_link === true) {
            return '<?php
                if ((int) dcCore::app()->ctx->posts->nb_comment > 0) {
                    echo "<a aria-label=\"";

                    if ((int) dcCore::app()->ctx->posts->nb_comment > 1) {
                        printf(__("post-list-multiple-reactions-link-aria-label"), dcCore::app()->ctx->posts->nb_comment);
                    } else {
                        echo __("post-list-one-reaction-link-aria-label");
                    }

                    echo "\" class=post-reaction-link href=\"", html::escapeURL(dcCore::app()->ctx->posts->getURL()), "#", __("reactions-id"), "\"><small>";

                    if ((int) dcCore::app()->ctx->posts->nb_comment > 1) {
                        printf(__("post-list-multiple-reactions"), dcCore::app()->ctx->posts->nb_comment);
                    } else {
                        echo __("post-list-one-reaction");
                    }

                    echo "</small></a>";
                };
            ?>';
        }
    }

    /**
     * Displays the published time of posts in the post list.
     *
     * @param array $attr Attributes to customize the value.
     *                    Attribute allowed: context
     *                    Values allowed:
     *                    - (string) post-list
     *                    - (string) post
     *
     * @return void The published time of the post.
     */
    public static function origineMiniEntryTime($attr)
    {
        if (!empty($attr['context']) && (\dcCore::app()->blog->settings->originemini->content_post_list_time === true && $attr['context'] === 'post-list') || (\dcCore::app()->blog->settings->originemini->content_post_time === true && $attr['context'] === 'post')) {
            if (\dcCore::app()->blog->settings->originemini->content_separator) {
                $content_separator = ' ' . \html::escapeHTML(\dcCore::app()->blog->settings->originemini->content_separator);
            } else {
                $content_separator = ' |';
            }

            return '<?php
                echo "' . $content_separator . '", " ", dcCore::app()->ctx->posts->getDate("' . \dcCore::app()->blog->settings->system->time_format . '");
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
     * @return void The entry excerpt.
     */
    public static function origineMiniEntryExcerpt($attr)
    {
        return '<?php
            $the_excerpt = "";

            if (' . sprintf(\dcCore::app()->tpl->getFilters($attr), 'dcCore::app()->ctx->posts->getExcerpt()') . ') {
                $the_excerpt = ' . sprintf(\dcCore::app()->tpl->getFilters($attr), 'dcCore::app()->ctx->posts->getExcerpt()') . ';
            } else {
                $the_excerpt = ' . sprintf(\dcCore::app()->tpl->getFilters($attr), 'dcCore::app()->ctx->posts->getContent()') . ';

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
                " <a aria-label=\"", sprintf(__("post-list-open-aria"), dcCore::app()->ctx->posts->post_title), "\" href=\"", dcCore::app()->ctx->posts->getURL(), "\">" . __("post-list-open"), "</a>",
                "</p>";
            }
        ?>';
    }

    /**
     * Adds a text string before the tag list of posts.
     *
     * @return void The text string.
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
     * Displays "copied" after the trackback URL in posts.
     *
     * Should only be displayed when a visitor click on the URL.
     *
     * @return void The private comment section.
     */
    public static function origineMiniScriptTrackbackURLCopied()
    {
        if (\dcCore::app()->blog->settings->originemini->global_js === true) {
            return ' <span id=trackback-url-copied>' . __('reactions-trackback-url-copied') . '</span>';
        }
    }

    /**
     * Displays a link to reply to the author of the post by email.
     *
     * @return void The private comment section.
     */
    public static function origineMiniEmailAuthor()
    {
        if (\dcCore::app()->blog->settings->originemini->content_post_email_author !== 'disabled') {
            return '<?php
                if (isset(dcCore::app()->ctx->posts->user_email) && dcCore::app()->ctx->posts->user_email && (dcCore::app()->blog->settings->originemini->content_post_email_author === "always" || (dcCore::app()->blog->settings->originemini->content_post_email_author === "comments_open" && dcCore::app()->ctx->posts->post_open_comment === "1"))
                ) {
                ?>
                    <div class=comment-private>
                        <h3 class=reaction-title>' . __('private-comment-title') . '</h3>

                        <?php $body = "' . __('private-comment-body-post-url') . ' " . dcCore::app()->ctx->posts->getURL(); ?>

                        <p>
                            <a class=button href="mailto:<?php echo urlencode(dcCore::app()->ctx->posts->user_email); ?>?subject=<?php echo htmlentities("' . __("private-comment-email-prefix") . ' " . dcCore::app()->ctx->posts->post_title . "&body=" . $body); ?>">' . __('private-comment-button-text') . '</a>
                        </p>
                    </div>
                <?php }
            ?>';
        }
    }

    /**
     * Adds a title in the plural or singular at the top of post attachment list.
     *
     * @return void The attachment area title.
     */
    public static function origineMiniAttachmentTitle()
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
     * @return void The attachment size.
     */
    public static function origineMiniAttachmentSize()
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
     * Returns the relative URI of the current page.
     *
     * @return void The relative URI.
     */
    public static function origineMiniURIRelative()
    {
        return '<?php echo filter_var($_SERVER["REQUEST_URI"], FILTER_SANITIZE_URL); ?>';
    }
}

\dcCore::app()->tpl->addBlock('origineMiniPostFooter', [__NAMESPACE__ . '\OrigineMiniPublicBlocks', 'origineMiniPostFooter']);
\dcCore::app()->tpl->addBlock('origineMiniHeaderIdentity', [__NAMESPACE__ . '\OrigineMiniPublicBlocks', 'origineMiniHeaderIdentity']);
\dcCore::app()->tpl->addBlock('origineMiniCommentFormWrapper', [__NAMESPACE__ . '\OrigineMiniPublicBlocks', 'origineMiniCommentFormWrapper']);
\dcCore::app()->tpl->addBlock('origineMiniReactionFeedLink', [__NAMESPACE__ . '\OrigineMiniPublicBlocks', 'origineMiniReactionFeedLink']);
\dcCore::app()->tpl->addBlock('origineMiniTrackbackLink', [__NAMESPACE__ . '\OrigineMiniPublicBlocks', 'origineMiniTrackbackLink']);
\dcCore::app()->tpl->addBlock('origineMiniWidgetsNav', [__NAMESPACE__ . '\OrigineMiniPublicBlocks', 'origineMiniWidgetsNav']);
\dcCore::app()->tpl->addBlock('origineMiniWidgetSearchForm', [__NAMESPACE__ . '\OrigineMiniPublicBlocks', 'origineMiniWidgetSearchForm']);
\dcCore::app()->tpl->addBlock('origineMiniWidgetsExtra', [__NAMESPACE__ . '\OrigineMiniPublicBlocks', 'origineMiniWidgetsExtra']);
\dcCore::app()->tpl->addBlock('origineMiniFooter', [__NAMESPACE__ . '\OrigineMiniPublicBlocks', 'origineMiniFooter']);

/**
 * Adds blocks to the theme's template files.
 */
class OrigineMiniPublicBlocks
{
    /**
     * Displays the footer of the post if it has content.
     *
     * @param array $attr    Unused.
     * @param void  $content The post footer.
     *
     * @return void The post footer.
     */
    public static function origineMiniPostFooter($attr, $content)
    {
        $has_attachment = false;
        $has_category   = false;
        $has_tag        = false;

        if (\dcCore::app()->ctx->posts->countMedia('attachment') > 0) {
            $has_attachment = true;
        }

        if (\dcCore::app()->ctx->posts->cat_id) {
            $has_category = true;
        }

        if (\dcCore::app()->ctx->posts->post_meta) {
            $post_meta = unserialize(\dcCore::app()->ctx->posts->post_meta);

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
     * @return void The link.
     */
    public static function origineMiniHeaderIdentity($attr, $content)
    {
        if (\dcCore::app()->blog->settings->originemini->header_description !== true) {
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
     * @return void The comment form.
     */
    public static function origineMiniCommentFormWrapper($attr, $content)
    {
        if (!\dcCore::app()->blog->settings->originemini->content_commentform_hide) {
            return '<h3 class=reaction-title>' . __('reactions-comment-form-title') . '</h3>' . $content;
        } else {
            return '<details id=react-wrapper><summary><small>' . __('reactions-react-link-title') . '</small></summary><h3 class=reaction-title>' . __('reactions-comment-form-title') . '</h3>' . $content . '</details>';
        }
    }

    /**
     * Displays a link to the comment feed.
     *
     * @param array $attr    Unused.
     * @param void  $content The link.
     *
     * @return void The link.
     */
    public static function origineMiniReactionFeedLink($attr, $content)
    {
        if (\dcCore::app()->blog->settings->originemini->content_reaction_feed !== false) {
            return $content;
        }
    }

    /**
     * Displays a link to copy the trackback URL.
     *
     * @param array $attr    Unused.
     * @param void  $content The link.
     *
     * @return void The link.
     */
    public static function origineMiniTrackbackLink($attr, $content)
    {
        if (\dcCore::app()->blog->settings->originemini->content_trackback_link !== false) {
            return $content;
        }
    }

    /**
     * Displays navigation widgets.
     *
     * @param array $attr    Unused.
     * @param void  $content The content of the widget area.
     *
     * @return void The navigation widget.
     */
    public static function origineMiniWidgetsNav($attr, $content)
    {
        if (\dcCore::app()->blog->settings->originemini->widgets_nav_position !== 'disabled') {
            return $content;
        }
    }

    /**
     * Displays a search form before the navigation widget area.
     *
     * @param array $attr    Unused.
     * @param void  $content The content of the search form.
     *
     * @return void The search form.
     */
    public static function origineMiniWidgetSearchForm($attr, $content)
    {
        if (\dcCore::app()->blog->settings->originemini->widgets_search_form === true && \dcCore::app()->url->type !== 'search') {
            return $content;
        }
    }

    /**
     * Displays extra widgets.
     *
     * @param array $attr    Unused.
     * @param void  $content The content of the widget area.
     *
     * @return void The navigation widget.
     */
    public static function origineMiniWidgetsExtra($attr, $content)
    {
        if (\dcCore::app()->blog->settings->originemini->widgets_extra_enabled !== false) {
            return $content;
        }
    }

    /**
     * Displays the footer.
     *
     * @param array $attr    Unused.
     * @param void  $content The content of the footer.
     *
     * @return void The footer.
     */
    public static function origineMiniFooter($attr, $content)
    {
        if (\dcCore::app()->blog->settings->originemini->footer_enabled !== false) {
            return $content;
        }
    }
}
