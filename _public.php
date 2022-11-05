<?php
/**
 * Origine Mini, a minimal theme for Dotclear.
 *
 * @copyright Teddy
 * @copyright GPL-3.0
 */

namespace themes\origine_mini;

use dcCore;

if (!defined('DC_RC_PATH')) {
    return;
}

\l10n::set(__DIR__ . '/locales/' . \dcCore::app()->lang . '/main');

\dcCore::app()->addBehavior('publicHeadContent', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniHeadMeta']);
\dcCore::app()->addBehavior('publicHeadContent', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniMediaQueriesInline']);

\dcCore::app()->tpl->addValue('origineConfigActive', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineConfigActive']);
\dcCore::app()->tpl->addValue('origineMiniStylesInline', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniStylesInline']);
\dcCore::app()->tpl->addValue('origineMiniEntryLang', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniEntryLang']);
\dcCore::app()->tpl->addValue('origineMiniScreenReaderLinks', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniScreenReaderLinks']);
\dcCore::app()->tpl->addValue('origineMiniPostListType', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniPostListType']);
\dcCore::app()->tpl->addValue('origineMiniEntryExcerpt', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniEntryExcerpt']);
\dcCore::app()->tpl->addValue('origineMiniPostDate', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniPostDate']);
\dcCore::app()->tpl->addValue('origineMiniAttachmentTitle', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniAttachmentTitle']);
\dcCore::app()->tpl->addValue('origineMiniAttachmentSize', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniAttachmentSize']);
\dcCore::app()->tpl->addValue('origineMiniFooterCredits', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniFooterCredits']);
\dcCore::app()->tpl->addValue('origineMiniURIRelative', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniURIRelative']);

\dcCore::app()->tpl->addBlock('origineMiniCommentFeedLink', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniCommentFeedLink']);
\dcCore::app()->tpl->addBlock('origineMiniWidgetsNav', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniWidgetsNav']);
\dcCore::app()->tpl->addBlock('origineMiniWidgetSearchForm', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniWidgetSearchForm']);
\dcCore::app()->tpl->addBlock('origineMiniWidgetsExtra', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniWidgetsExtra']);
\dcCore::app()->tpl->addBlock('origineMiniFooter', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniFooter']);

class tplOrigineMiniTheme
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
    }

    /**
     * Adds media queries in the head.
     *
     * If origineConfig is activated, it will return custom styles generated by the plugin.
     *
     * @return string The styles.
     */
    public static function origineMiniMediaQueriesInline()
    {
        $plugin_activated = self::origineConfigActive();

        if ($plugin_activated === false) {
            $max_width = 30;
        } elseif (\dcCore::app()->blog->settings->origineConfig->global_page_width) {
            $max_width = (int) \dcCore::app()->blog->settings->origineConfig->global_page_width;
        } else {
            $max_width = 30;
        }

        $media = '
            @media (max-width: ' . $max_width + 4 . 'em) {
                body{
                    margin-top: 2rem;
                    margin-bottom: 2rem;
                }
                #site {
                    margin-right: 2rem;
                    margin-left: 2rem;
                    max-width: 100%;
                    row-gap: 2rem;
                }
                #site-header {
                    align-items: normal;
                    flex-direction: column;
                    justify-content: unset;
                    row-gap: .5rem;
                }
                #main-menu {
                    text-align: left;
                }
                #main-menu li,
                #main-menu li:first-child {
                    margin-left: 0;
                    margin-right: .5rem;
                }
                #main-menu li:last-child {
                    margin-right: 0;
                }
                .post-list .post {
                    flex-direction: column;
                    row-gap: .25rem;
                }
                .post-comment-link {
                    order: 3;
                }
                .post-list .post-title {
                    margin-right: 0;
                    order: 2;
                }
                .post-list .post-date {
                    margin-left: 0;
                    order: 1;
                    text-align: initial;
                }
                #nav-archive {
                    column-gap: 1rem;
                    display: block;
                    flex-direction: column;
                    justify-content: unset;
                }
                #nav-archive a {
                    display: block;
                    margin: .25rem 0;
                }
                .pagination-links {
                    display: block;
                    justify-content: unset;
                    text-align: center;
                }
                #search-form {
                    align-items: unset;
                    column-gap: unset;
                    display: block;
                    flex-direction: unset;
                }
                #search-form input {
                    margin-bottom: .5rem;
                }
            }
        ';

        $style_search  = [' {', ': ', ', ', '; ', "\n", "\r", '  ', '@media ('];
        $style_replace = ['{',  ':',  ',',  ';',  '',   '',   '',   '@media('];

        $media = str_replace($style_search, $style_replace, $media);

        echo '<style>', $media, '</style>';
    }

    /**
     * Checks if the plugin origineConfig is installed and activated.
     *
     * To support the user's settings, the version of the plugin must be superior or equal to 2.1.
     *
     * @return bool Returns true if the plugin is installed and activated.
     */
    public static function origineConfigActive()
    {
        if (\dcCore::app()->plugins->moduleExists('origineConfig') === true && version_compare('2.1', \dcCore::app()->plugins->moduleInfo('origineConfig', 'version'), '<=') && \dcCore::app()->blog->settings->origineConfig->active === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Adds styles in the head.
     *
     * If origineConfig is activated, it will return custom styles generated by the plugin.
     *
     * @return string The styles.
     */
    public static function origineMiniStylesInline()
    {
        $plugin_activated = self::origineConfigActive();

        if ($plugin_activated === false) {
            $styles = '
                :root {
                    --page-width: 30em;
                    --order-content: 2;
                    --order-widgets-nav: 3;
                    --order-widgets-extra: 4;
                    --order-footer: 5;
                    --font-family: -apple-system, BlinkMacSystemFont, "Avenir Next", Avenir, "Segoe UI", "Helvetica Neue", Helvetica, Ubuntu, Roboto, Noto, Arial, sans-serif;
                    --font-size: 1em;
                    --color-primary: #1742cf;
                    --color-background: #fcfcfd;
                    --color-text-main: #2e3038;
                    --color-text-secondary: #797c86;
                    --color-border: #c2c7d6;
                    --color-input-background: #f1f2f4;
                    --text-align: left;
                }

                @media (prefers-color-scheme:dark) {
                    :root{
                        --color-primary:#94c9ff;
                    }
                }
            ';

            $style_search  = [' {', ': ', ', ', '; ', "\n", "\r", '  ', '@media ('];
            $style_replace = ['{',  ':',  ',',  ';',  '',   '',   '',   '@media('];

            $styles = str_replace($style_search, $style_replace, $styles);
        } elseif ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->css_origine_mini) {
            $styles = \dcCore::app()->blog->settings->origineConfig->css_origine_mini;
        }

        return '<style>' . $styles . '</style>';
    }

    /**
     * Displays a lang attribute and its value when the language of the current post is different
     * from the language defined for the whole blog.
     *
     * @return void The lang attribute.
     */
    public static function origineMiniEntryLang()
    {
        return '<?php
            if (\dcCore::app()->ctx->posts->post_lang !== \dcCore::app()->blog->settings->system->lang) {
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
        $links  = '<a id=skip-content class=skip-links href=#site-content>' . __('skip-link-content') . '</a>';

        // If simpleMenu exists, is activated and a menu has been set, then adds a link to it.
        if (\dcCore::app()->plugins->moduleExists('simpleMenu') && \dcCore::app()->blog->settings->system->simpleMenu_active === true) {
          $links .= '<a id=skip-menu class=skip-links href=#main-menu>' . __('skip-link-menu') . '</a>';
        }

        return $links;
    }

    /**
     * Credits to display at the bottom of the site.
     *
     * They can be removed with the plugin origineConfig.
     * Dotclear and theme versions are shown only on dev environments.
     *
     * @return void The footer credits.
     */
    public static function origineMiniFooterCredits()
    {
        $plugin_activated = self::origineConfigActive();

        if ($plugin_activated === false || ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->footer_credits === true)) {
            if (!defined('DC_DEV') || (defined('DC_DEV') && DC_DEV === 'false')) {
                return '<div class=site-footer-block>' . __('footer-powered-by') . '</div>';
            } else {
                $dc_version       = \dcCore::app()->getVersion('core');
                $dc_version_parts = explode('-', $dc_version);
                $dc_version_short = $dc_version_parts[0] ? $dc_version_parts[0] : $dc_version;

                $theme_version = \dcCore::app()->themes->moduleInfo('origine-mini', 'version');

                return '<div class=site-footer-block>' . sprintf(__('footer-powered-by-dev'), $dc_version, $dc_version_short, $theme_version) . '</div>';
            }
        }
    }

    /**
     * Loads the right entry-list template based on origineConfig settings.
     * Default: short
     *
     * @return void The entry-list template.
     */
    public static function origineMiniPostListType()
    {
        $plugin_activated = self::origineConfigActive();

        $post_list_types = ['short', 'extended'];

        if ($plugin_activated !== true || ($plugin_activated === true && !in_array(\dcCore::app()->blog->settings->origineConfig->content_post_list_type, $post_list_types))) {
            return \dcCore::app()->tpl->includeFile(['src' => '_entry-list-short.html']);
        } else {
            return \dcCore::app()->tpl->includeFile(['src' => '_entry-list-' . \dcCore::app()->blog->settings->origineConfig->content_post_list_type . '.html']);
        }
    }

    /**
     * Returns an excerpt of the post for the entry-list-extended template.
     *
     * Gets the excerpt defined by the author or, if it does not exists, an excerpt from the content.
     *
     * @param array $attr Modifying attributes.
     *
     * @return void The entry excerpt.
     */
    public static function origineMiniEntryExcerpt($attr)
    {
        return '<?php
            $the_excerpt = "";

            if (' . sprintf(\dcCore::app()->tpl->getFilters($attr), 'dcCore::app()->ctx->posts->getExcerpt()') . ' !== "") {
                $the_excerpt = ' . sprintf(\dcCore::app()->tpl->getFilters($attr), '\dcCore::app()->ctx->posts->getExcerpt()') . ';
            } else {
                $the_excerpt = ' . sprintf(\dcCore::app()->tpl->getFilters($attr), '\dcCore::app()->ctx->posts->getContent()') . ';

                if (strlen($the_excerpt) > 200) {
                    $the_excerpt  = substr($the_excerpt, 0, 200);
                    $the_excerpt  = preg_replace("/[^a-z0-9]+\Z/i", "", $the_excerpt);
                    $the_excerpt .= "…";
                }
            }

            if ($the_excerpt !== "") {
                echo "<p class=\"post-excerpt text-secondary\">",
                     $the_excerpt,
                     " <a aria-label=\"", sprintf(__("post-list-open-aria"), \dcCore::app()->ctx->posts->post_title), "\" href=\"", \dcCore::app()->ctx->posts->getURL(), "\">" . __("post-list-open"), "</a>",
                     "</p>";
            }
        ?>';
    }

    /**
     * Displays the date of publication of posts.
     *
     * The time can be added via origineConfig.
     *
     * @return void The post date (and time).
     */
    public static function origineMiniPostDate()
    {
        $format_date = \dcCore::app()->blog->settings->system->date_format;

        $post_time        = '';
        $plugin_activated = self::origineConfigActive();

        if ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->content_post_time === true) {
            $format_time = \dcCore::app()->blog->settings->system->time_format;

            $post_time = ' . " " . \dcCore::app()->blog->settings->origineConfig->content_separator . " " . \dcCore::app()->ctx->posts->getDate("' . $format_time . '", "creadt")';
        }

        return '<?php
            echo "<time aria-label=\"{{tpl:lang post-date-aria-label}}\" class=\"post-date text-secondary\" datetime=\"",
                 \dcCore::app()->ctx->posts->getDate("%Y-%m-%dT%H:%m", "creadt"), "\">",
                 \dcCore::app()->ctx->posts->getDate("' . $format_date . '", "creadt")' . $post_time . ',
                 "</time>";
        ?>';
    }

    /**
     * Adds a title in the plural or singular at the top of post attachment list.
     *
     * @return void The attachment area title.
     */
    public static function origineMiniAttachmentTitle()
    {
        return '<?php
            if (count(\dcCore::app()->ctx->attachments) === 1) {
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

            if (\dcCore::app()->blog->settings->system->lang === "fr") {
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

    /**
     * Displays a link to the comment feed.
     *
     * @param array $attr    Unused.
     * @param void  $content The link.
     *
     * @return void The link.
     */
    public static function origineMiniCommentFeedLink($attr, $content)
    {
        $plugin_activated = self::origineConfigActive();

        if ($plugin_activated === false || ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->content_comment_links === true)) {
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
        $plugin_activated = self::origineConfigActive();

        if ($plugin_activated === false || ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->widgets_nav_position !== 'disabled')) {
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
        $plugin_activated = self::origineConfigActive();

        if ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->widgets_search_form === true && \dcCore::app()->url->type !== 'search') {
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
        $plugin_activated = self::origineConfigActive();

        if ($plugin_activated === false || ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->widgets_extra_enabled === true)) {
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
        $plugin_activated = self::origineConfigActive();

        if ($plugin_activated === false || ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->footer_enabled === true)) {
            return $content;
        }
    }
}
