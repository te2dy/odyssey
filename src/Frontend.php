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
use Dotclear\Helper\L10n;
use Dotclear\Helper\Text;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Html;

// Lets prepare to use custom functions.
require_once 'functions.php';
use OrigineMiniUtils as omUtils;
use OrigineMiniSettings as omSettings;

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
        dcCore::app()->addBehavior('publicAfterContentFilterV2', [self::class, 'origineMiniImageWide']);

        // Values.
        dcCore::app()->tpl->addValue('origineMiniMetaDescriptionHome', [self::class, 'origineMiniMetaDescriptionHome']);
        dcCore::app()->tpl->addValue('origineMiniStylesInline', [self::class, 'origineMiniStylesInline']);
        dcCore::app()->tpl->addValue('origineMiniEntryLang', [self::class, 'origineMiniEntryLang']);
        dcCore::app()->tpl->addValue('origineMiniScreenReaderLinks', [self::class, 'origineMiniScreenReaderLinks']);
        dcCore::app()->tpl->addValue('origineMiniHeaderImage', [self::class, 'origineMiniHeaderImage']);
        dcCore::app()->tpl->addValue('origineMiniBlogDescription', [self::class, 'origineMiniBlogDescription']);
        dcCore::app()->tpl->addValue('origineMiniPostListType', [self::class, 'origineMiniPostListType']);
        dcCore::app()->tpl->addValue('origineMiniPostTemplate', [self::class, 'origineMiniPostTemplate']);
        dcCore::app()->tpl->addValue('origineMiniPageTemplate', [self::class, 'origineMiniPageTemplate']);
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

            echo '<meta name=author content=', omUtils::attrValue($editor), '>', "\n";
        }

        // Adds the content of the copyright notice.
        if (dcCore::app()->blog->settings->system->copyright_notice) {
            echo '<meta name=copyright content=',
            omUtils::attrValue(dcCore::app()->blog->settings->system->copyright_notice),
            '>', "\n";
        }

        // Adds the generator name of the blog.
        if (omSettings::value('global_meta_generator') === true) {
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
        if (omSettings::value('global_meta_social') === true) {
            $title = '';
            $desc  = '';
            $img   = '';

            switch (dcCore::app()->url->type) {
                case 'post':
                case 'pages':
                    $title = dcCore::app()->ctx->posts->post_title;

                    $desc = dcCore::app()->ctx->posts->getExcerpt() ?: dcCore::app()->ctx->posts->getContent();
                    $desc = Html::clean($desc);
                    $desc = Html::decodeEntities($desc);
                    $desc = preg_replace('/\s+/', ' ', $desc);

                    if (strlen($desc) > 180) {
                        $desc = Text::cutString($desc, 179) . '…';
                    }

                    if (context::EntryFirstImageHelper('o', true, '', true)) {
                        $img = omUtils::blogBaseURL() . context::EntryFirstImageHelper('o', true, '', true);
                    }

                    break;

                case 'default':
                case 'default-page':
                    $title = dcCore::app()->blog->name;

                    if ((int) context::PaginationPosition() > 1 ) {
                        $desc = sprintf(
                            __('meta-social-page-with-number'),
                            context::PaginationPosition()
                        );
                    }

                    if (omSettings::value('global_meta_home_description') || dcCore::app()->blog->desc) {
                        if ($desc) {
                            $desc .= ' – ';
                        }

                        if (omSettings::value('global_meta_home_description')) {
                            $desc .= omSettings::value('global_meta_home_description');
                        } elseif (dcCore::app()->blog->desc) {
                            $desc .= dcCore::app()->blog->desc;
                        }

                        $desc = Html::clean($desc);
                        $desc = Html::decodeEntities($desc);
                        $desc = preg_replace('/\s+/', ' ', $desc);

                        if (strlen($desc) > 180) {
                            $desc = Text::cutString($desc, 179) . '…';
                        }
                    }

                    break;

                case 'category':
                    $title = dcCore::app()->ctx->categories->cat_title;

                    if (dcCore::app()->ctx->categories->cat_desc) {
                        $desc = dcCore::app()->ctx->categories->cat_desc;
                        $desc = Html::clean($desc);
                        $desc = Html::decodeEntities($desc);
                        $desc = preg_replace('/\s+/', ' ', $desc);

                        if (strlen($desc) > 180) {
                            $desc = Text::cutString($desc, 179) . '…';
                        }
                    }

                    break;

                case 'tag':
                    if (dcCore::app()->ctx->meta->meta_type === 'tag') {
                        $title = dcCore::app()->ctx->meta->meta_id;
                        $desc  = sprintf(
                            __('meta-social-tags-post-related'),
                            $title
                        );
                    }
            }

            $title = Html::escapeHTML($title);

            if ($title) {
                $desc = Html::escapeHTML($desc);

                if (!$img && isset(omSettings::value('header_image')['url'])) {
                    $img = omUtils::blogBaseURL() . omSettings::value('header_image')['url'];
                }

                $img = Html::escapeURL($img);

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
        if (omSettings::value('content_post_intro') === true && dcCore::app()->ctx->posts->post_excerpt) {
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
        // A list of social sites supported by the theme.
        $social_sites = [
            'Diaspora',
            'Discord',
            'Facebook',
            'GitHub',
            'Mastodon',
            'Signal',
            'TikTok',
            'Twitter',
            'WhatsApp'
        ];

        // The array of social links to be displayed.
        $social_links = [];

        // Builds the array of social links to display in the footer.
        foreach ($social_sites as $site) {
            $setting_id = 'footer_social_links_' . strtolower($site);

            // If the setting has a value.
            if (omSettings::value($setting_id)) {
                $social_links[$site] = omSettings::value($setting_id);
            }
        }

        if (!empty($social_links)) :
            ?>

            <div class=footer-social-links>
                <ul>
                    <?php
                    foreach ($social_links as $site => $link) :
                        switch ($site) {
                            case 'Signal':
                                if (substr($link, 0, 1) === '+') {
                                    $link = 'https://signal.me/#p/' . $link;
                                }

                                break;
                            case 'WhatsApp':
                                $link = 'https://wa.me/' . str_replace('+', '', $link);

                                break;
                            case 'Twitter':
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
                    endforeach;
                    ?>
                </ul>
            </div>

            <?php
        endif;
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
        if (!omSettings::value('global_js')) {
            return;
        }

        if (!omSettings::value('widgets_search_form') && dcCore::app()->url->type !== 'search') {
            return;
        }

        if (!defined('DC_DEV') || (defined('DC_DEV') && DC_DEV === false)) {
            $script = 'window.onload=function(){var e;document.getElementsByClassName("search-form-submit")[0]&&(""!==(e=new URL(document.location).searchParams.get("q"))&&(document.getElementsByClassName("search-form-submit")[0].disabled=!0),document.getElementsByClassName("search-form")[0].oninput=function(){document.getElementsByClassName("search-form-field")[0].value&&document.getElementsByClassName("search-form-field")[0].value!==e?document.getElementsByClassName("search-form-submit")[0].disabled=!1:document.getElementsByClassName("search-form-submit")[0].disabled=!0})};' . "\n";

            echo '<script>', $script, '</script>', "\n";
        } else {
            echo '<script src=', dcCore::app()->blog->settings->system->themes_url, '/originemini/js/searchform.min.js></script>';
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
        if (!omSettings::value('global_js')) {
            return;
        }

        if (dcCore::app()->url->type !== 'post' && dcCore::app()->url->type !== 'pages') {
            return;
        }

        if (!defined('DC_DEV') || (defined('DC_DEV') && DC_DEV === false)) {
            $script = 'window.onload=function(){document.getElementById("trackback-url")&&(document.getElementById("trackback-url").onclick=function(){window.location.protocol,window.location.host;var t,e=document.getElementById("trackback-url").innerHTML;try{t=new URL(e).href}catch(t){return!1}!1!==t.href&&navigator.clipboard.writeText(t).then(()=>{document.getElementById("trackback-url-copied").style.display="inline"},()=>{document.getElementById("trackback-url-copied").style.display="none"})})};' . "\n";

            echo '<script>', $script, '</script>', "\n";
        } else {
            echo '<script src=', dcCore::app()->blog->settings->system->themes_url, '/originemini/js/trackbackurl.min.js></script>';
        }
    }

    /**
     * Displays wide image.
     *
     * @param array $tag  The tags.
     * @param array $args The args.
     *
     * @return void The image.
     */
    public static function origineMiniImageWide($tag, $args): void
    {
        // If only on Entry content.
        if (!in_array($tag, ['EntryContent'])) {
            return;
        }

        if (!omSettings::value('content_images_wide')) {
            return;
        }

        // Matches all images by regex.
        $args[0] = preg_replace_callback(
            '/<img[^>]*>/',
            function ($matches) {
                // The image HTML code.
                $img = $matches[0];

                // Gets the image src attribute.
                preg_match('/src="([^"]*)/', $img, $src_match);

                $src_attr  = isset($src_match[0]) ? $src_match[0] . '"' : '';
                $src_value = isset($src_match[1]) ? $src_match[1] : '';

                // Builds an array that will contain all image sizes.
                $img = [
                    'o' => [
                        'url'    => $src_value,
                        'width'  => null,
                        'height' => null
                    ]
                ];

                // If the original image size exists.
                if (file_exists(DC_ROOT . $src_value)) {
                    /**
                     * Sets the maximum width of the image to display.
                     *
                     * It can be superior to the content width
                     * if the option "content_images_wide" is enabled.
                     *
                     * @see Config.php
                     */
                    $option_image_wide = false;

                    switch (omSettings::value('content_images_wide')) {
                        case 'posts-pages' :
                            if (dcCore::app()->url->type === 'post' || dcCore::app()->url->type === 'pages') {
                                $option_image_wide = true;
                            }

                            break;
                        case 'always' :
                            $option_image_wide = true;
                    }

                    $img_width_max = omSettings::contentWidth('px');

                    if ($option_image_wide === true) {
                        if (omSettings::value('content_images_wide_size')) {
                            $img_width_max += (int) (omSettings::value('content_images_wide_size') * 2);
                        } else {
                            $img_width_max += 120 * 2;
                        }
                    }

                    // Gets original image dimensions.
                    list($width, $height) = getimagesize(DC_ROOT . $src_value);

                    $img['o']['width']  = $width;
                    $img['o']['height'] = $height;

                    $media_sizes = dcCore::app()->media->thumb_sizes;

                    // Adds eventual custom image sizes.
                    if (omSettings::value('content_image_custom_size')) {
                        $custom_image_sizes = explode(',', omSettings::value('content_image_custom_size'));

                        foreach ($custom_image_sizes as $size_id) {
                            $media_sizes[$size_id] = [
                                0, // Width.
                                'ratio'
                            ];
                        }
                    }

                    $info = Path::info($src_value);

                    // The image to set in the src attribute.
                    $src_image_size = 'o';

                    foreach ($media_sizes as $size_id => $size_data) {
                        if (isset($size_data[1])
                            && $size_data[1] === 'ratio'
                            && file_exists(DC_ROOT . $info['dirname'] . '/.' . $info['base'] . '_' . $size_id . '.' . strtolower($info['extension']))
                        ) {
                            $img[$size_id]['url']   = $info['dirname'] . '/.' . $info['base'] . '_' . $size_id . '.' . strtolower($info['extension']);
                            $img[$size_id]['width'] = isset($size_data[0]) ? $size_data[0] : '';

                            list($width, $height) = getimagesize(DC_ROOT . $img[$size_id]['url']);

                            if (!$img[$size_id]['width']) {
                                $img[$size_id]['width']   = $width;
                                $media_sizes[$size_id][0] = $width;
                            }

                            $img[$size_id]['height'] = $height;

                            if ($media_sizes[$size_id][0] >= $img_width_max
                                && $img[$src_image_size]['width'] > $img[$size_id]['width']
                            ) {
                                $src_image_size = $size_id;
                            }
                        }
                    }

                    // Sort $img by width.
                    uasort($img, function ($a, $b) {
                        return $a['width'] <=> $b['width'];
                    });

                    // Defines image attributes.
                    $attr  = 'src="' . $img[$src_image_size]['url'] . '" ';
                    $attr .= 'srcset="';

                    // Puts every image size in the srcset attribute.
                    foreach ($img as $img_id => $img_data) {
                        $attr .= $img_data['url'] . ' ' . $img_data['width'] . 'w';

                        if ($img_id !== array_key_last($img)) {
                            $attr .= ', ';
                        }
                    }

                    $attr .= '" ';

                    $attr .= 'sizes="100vw" ';

                    // If it's a landscape format image only, displays it wide.
                    if (omSettings::value('content_images_wide')
                        && $img[$src_image_size]['width'] > $img[$src_image_size]['height']
                        && $img[$src_image_size]['width'] >= $img_width_max
                    ) {
                        $attr .= 'style="display: block; margin-left: 50%; transform: translateX(-50%); max-width: 95vw;" ';
                        $attr .= 'width="' . $img_width_max . '" ';
                        $attr .= 'height="' . (int) ($img_width_max * $img[$src_image_size]['height'] / $img[$src_image_size]['width'] ). '"';
                    }

                    return str_replace($src_attr, trim($attr), $matches[0]);
                }

                return $matches[0];
            },
            $args[0]
        );
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
        if (omSettings::value('global_meta_home_description')) {
            return '<?php echo ' . sprintf(dcCore::app()->tpl->getFilters($attr), 'dcCore::app()->blog->settings->originemini->global_meta_home_description') . '; ?>';
        }

        return '<?php echo ' . sprintf(dcCore::app()->tpl->getFilters($attr), 'dcCore::app()->blog->desc') . '; ?>';
    }

    /**
     * Adds styles in the head.
     *
     * @return string The styles.
     */
    public static function origineMiniStylesInline()
    {
        $styles  = omSettings::value('styles') ?: '';
        $styles .= omSettings::value('global_css_custom_mini') ?: '';

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
        if (omSettings::value('footer_enabled') !== false) {
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
        if (omSettings::value('header_image') && isset(omSettings::value('header_image')['url'])) {
            if (!empty($attr['position'])
                && (($attr['position'] === 'bottom' && omSettings::value('header_image_position') === 'bottom')
                || ($attr['position'] === 'top' && !omSettings::value('header_image_position')))
            ) {
                $image_url = Html::escapeURL(omSettings::value('header_image')['url']);
                $srcset    = '';

                if (omSettings::value('header_image_description')) {
                    $alt = ' alt="' . Html::escapeHTML(omSettings::value('header_image_description')) . '"';
                } else {
                    $alt = ' alt="' . __('header-image-alt') . '"';
                }

                if (omSettings::value('header_image2x')) {
                    $image2x_url = Html::escapeURL(omSettings::value('header_image2x'));

                    $srcset  = ' srcset="';
                    $srcset .= $image_url . ' 1x, ';
                    $srcset .= $image2x_url . ' 2x';
                    $srcset .= '"';
                }

                // Does not add a link to the home page on home page.
                if (dcCore::app()->url->type === 'default') {
                    return '<div id=site-image><img' . $alt . ' src="' . $image_url . '"' . $srcset . '></div>';
                }

                return '<div id=site-image><a href="' . dcCore::app()->blog->url . '" rel=home><img' . $alt . ' src="' . $image_url . '"' . $srcset . '></a></div>';
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
        if (dcCore::app()->blog->desc && omSettings::value('header_description') === true) {
            $description = dcCore::app()->blog->desc;
            $description = Html::clean($description);
            $description = Html::decodeEntities($description);
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
        if (!omSettings::value('content_post_list_type')) {
            return dcCore::app()->tpl->includeFile(['src' => '_entry-list-short.html']);
        }

        $postlist_type_allowed = ['excerpt', 'content', 'custom'];

        $postlist_type = omSettings::value('content_post_list_type');
        $postlist_type = in_array($postlist_type, $postlist_type_allowed, true) ? $postlist_type : 'short';
        $postlist_tpl  = '_entry-list-' . $postlist_type . '.html';

        if ($postlist_type === 'custom') {
            if (omSettings::value('content_post_list_custom')) {
                $postlist_tpl = omSettings::value('content_post_list_custom');
            } else {
                $postlist_tpl = '_entry-list-short.html';
            }
        }

        return dcCore::app()->tpl->includeFile(['src' => $postlist_tpl]);
    }

    /**
     * Loads the post template.
     *
     * @return string The post template.
     */
    public static function origineMiniPostTemplate(): string
    {
        if (!omSettings::value('content_post_template')) {
            return dcCore::app()->tpl->includeFile(['src' => '_entry-post.html']);
        }

        return dcCore::app()->tpl->includeFile(['src' => omSettings::value('content_post_template')]);
    }

    /**
     * Loads the page template.
     *
     * @return string The page template.
     */
    public static function origineMiniPageTemplate(): string
    {
        if (!omSettings::value('content_page_template')) {
            return dcCore::app()->tpl->includeFile(['src' => '_entry-page.html']);
        }

        return dcCore::app()->tpl->includeFile(['src' => omSettings::value('content_page_template')]);
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
        if (!omSettings::value('content_post_list_reaction_link')) {
            return;
        }

        $class = 'class=post-reaction-link';

        $small_open  = '<small>';
        $small_close = '</small>';

        if (omSettings::value('content_post_list_type') === 'content') {
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
        if (!empty($attr['context']) && (omSettings::value('content_post_list_time') === true && $attr['context'] === 'entry-list') || (omSettings::value('content_post_time') === true && $attr['context'] === 'post')) {
            if (omSettings::value('content_separator')) {
                $content_separator = ' ' . Html::escapeHTML(omSettings::value('content_separator'));
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
        if (omSettings::value('global_js') === true) {
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
        if (omSettings::value('content_post_email_author') !== 'disabled') {
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
        if (omSettings::value('footer_credits') !== false) {
            if (!defined('DC_DEV') || (defined('DC_DEV') && DC_DEV === false)) {
                return '<div class=site-footer-block>' . __('footer-powered-by') . '</div>';
            }

            $dc_version       = dcCore::app()->getVersion('core');
            $dc_version_parts = explode('-', $dc_version);
            $dc_version_short = $dc_version_parts[0] ?? $dc_version;

            $theme_version = dcCore::app()->themes->moduleInfo('originemini', 'version');

            return '<div class=site-footer-block>' . sprintf(__('footer-powered-by-dev'), $dc_version, $dc_version_short, $theme_version) . '</div>';
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
        if (omSettings::value('header_description') !== true) {
            return $content;
        }

        return '<div id=site-identity>' . $content . '</div>';
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
        if (!omSettings::value('content_commentform_hide')) {
            return '<h3 class=reaction-title>' . __('reactions-comment-form-title') . '</h3>' . $content;
        } elseif (dcCore::app()->ctx->comment_preview && dcCore::app()->ctx->comment_preview["preview"]) {
            return '<div id=react-content><h3 class=reaction-title>' . __('reactions-comment-form-preview-title') . '</h3>' . $content . '</div>';
        }

        return '<details><summary class=button>' . __('reactions-react-link-title') . '</summary><div id=react-content><h3 class=reaction-title>' . __('reactions-comment-form-title') . '</h3>' . $content . '</div></details>';
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
        if (omSettings::value('content_reaction_feed') !== false) {
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
        if (omSettings::value('content_trackback_link') !== false) {
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
        if (omSettings::value('widgets_nav_position') !== 'disabled') {
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
        if (omSettings::value('widgets_search_form') === true && dcCore::app()->url->type !== 'search') {
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
        if (omSettings::value('widgets_extra_enabled') !== false) {
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
        if (omSettings::value('footer_enabled') !== false) {
            return $content;
        }
    }
}
