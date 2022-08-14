<?php
/**
 * Origine Mini, a minimalist them for Dotclear.
 *
 * @copyright Teddy
 * @copyright GPL-3.0
 */

namespace themes\origine_mini;

use dcCore;

if (!defined('DC_RC_PATH')) {
    return;
}

\l10n::set(dirname(__FILE__) . '/locales/' . \dcCore::app()->lang . '/main');

\dcCore::app()->addBehavior('publicHeadContent', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniHeadMeta']);

\dcCore::app()->tpl->addValue('origineConfigActive', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineConfigActive']);
\dcCore::app()->tpl->addValue('origineMiniStylesInline', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniStylesInline']);
\dcCore::app()->tpl->addValue('origineMiniEntryLang', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniEntryLang']);
\dcCore::app()->tpl->addValue('origineMiniScreenReaderLinks', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniScreenReaderLinks']);
\dcCore::app()->tpl->addValue('origineMiniFooterCredits', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniFooterCredits']);
\dcCore::app()->tpl->addValue('origineMiniURIRelative', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniURIRelative']);

\dcCore::app()->tpl->addBlock('origineMiniWidgetsNav', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniWidgetsNav']);
\dcCore::app()->tpl->addBlock('origineMiniWidgetSearchForm', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniWidgetSearchForm']);
\dcCore::app()->tpl->addBlock('origineMiniWidgetsExtra', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniWidgetsExtra']);
\dcCore::app()->tpl->addBlock('origineMiniFooter', [__NAMESPACE__ . '\tplOrigineMiniTheme', 'origineMiniFooter']);

class tplOrigineMiniTheme
{
    /**
     * Adds meta tags in the <head> section depending on the blog settings.
     *
     * @return void
     */
    public static function origineMiniHeadMeta()
    {
        // Adds the name of the editor.
        if (\dcCore::app()->blog->settings->system->editor) {
            $editor = \dcCore::app()->blog->settings->system->editor;
            $editor = strpos($editor, ' ') === false ? $editor : '"' . $editor . '"';

            echo '<meta name=author content=', $editor, '>', "\n";
        }

        // Adds the content of the copyright notice.
        if (\dcCore::app()->blog->settings->system->copyright_notice) {
            $notice = \dcCore::app()->blog->settings->system->copyright_notice;
            $notice = strpos($notice, ' ') === false ? $notice : '"' . $notice . '"';

            echo '<meta name=copyright content=', $notice, '>', "\n";
        }
    }

    /**
     * Checks if the plugin origineConfig is installed and activated.
     *
     * @return bool Returns true if the plugin is installed and activated.
     */
    public static function origineConfigActive()
    {
        if (\dcCore::app()->plugins->moduleExists('origineConfig') === true && \dcCore::app()->blog->settings->origineConfig->active === true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Adds styles in the head.
     *
     * If origineConfig is activated, it returns custom styles generated by it;
     * otherwise, default styles are returned.
     *
     * @return string The styles.
     */
    public static function origineMiniStylesInline()
    {
        $plugin_activated = self::origineConfigActive();

        $styles  = ':root{';
        $styles .= '--page-width:30em;';
        $styles .= '--order-content:2;';
        $styles .= '--order-widgets-nav:3;';
        $styles .= '--order-widgets-extra:4;';
        $styles .= '--order-footer:5;';
        $styles .= '--font-family:-apple-system,BlinkMacSystemFont,"Avenir Next",Avenir,"Segoe UI","Helvetica Neue",Helvetica,Ubuntu,Roboto,Noto,Arial,sans-serif;';
        $styles .= '--font-size:1em;';
        $styles .= '--color-h:220;';
        $styles .= '--color-s:100%;';
        $styles .= '--color-l:45%;';
        $styles .= '--text-align:left;';
        $styles .= '}';

        if ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->css_origine_mini) {
            $styles = \dcCore::app()->blog->settings->origineConfig->css_origine_mini;
        }

        return '<style>' . $styles . '</style>';
    }

    /**
     * Displays a lang attribute and its value when the language of the current post is different
     * from the language defined for the whole blog.
     *
     * @return void
     */
    public static function origineMiniEntryLang()
    {
        return '<?php if (\dcCore::app()->ctx->posts->post_lang !== \dcCore::app()->blog->settings->system->lang) { echo " lang=" . dcCore::app()->ctx->posts->post_lang; } ?>';
    }

    /**
     * Displays navigation links for screen readers.
     *
     * @return void
     */
    public static function origineMiniScreenReaderLinks()
    {
        $links  = '<a id="skip-content" class="skip-links" href="#site-content">';
        $links .= __('Skip to content');
        $links .= '</a>';

        // If simpleMenu exists, is activated and a menu has been set.
        if (\dcCore::app()->plugins->moduleExists('simpleMenu') && \dcCore::app()->blog->settings->system->simpleMenu_active === true) {
          $links .= '<a id="skip-menu" class="skip-links" href="#main-menu">';
          $links .= __('Skip to menu');
          $links .= '</a>';
        }

        return $links;
    }

    /**
     * Credits to display at the bottom of the site.
     *
     * They can be remove with the plugin origineConfig.
     *
     * @return void
     */
    public static function origineMiniFooterCredits()
    {
        $plugin_activated = self::origineConfigActive();

        if ($plugin_activated === false || ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->footer_credits === true)) {
            return '<div class=site-footer-block>' . sprintf(__('Powered by <a href=%s>Dotclear</a> et <a href=%s>Origine Mini</a>'), __('https://dotclear.org/'), 'https://github.com/te2dy/origine-mini') . '</div>';
        }
    }

    /**
     * Returns the relative URI of the current page.
     *
     * @return void
     */
    public static function origineMiniURIRelative()
    {
        return $_SERVER['REQUEST_URI'];
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
     * Displays a search form at before the navigation widget area.
     *
     * @param array $attr    Unused.
     * @param void  $content The content of the search form.
     *
     * @return void The search form.
     */
    public static function origineMiniWidgetSearchForm($attr, $content)
    {
        $plugin_activated = self::origineConfigActive();

        if ($plugin_activated === true && \dcCore::app()->blog->settings->origineConfig->widgets_search_form === true) {
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
