<?php
/**
 * Odyssey, a simple and customizable Dotclear theme.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2025 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

namespace Dotclear\Theme\odyssey;

use ArrayObject;
use Dotclear\App;
use Dotclear\Core\Frontend\Ctx;
use Dotclear\Core\Frontend\Url;
use Dotclear\Helper\Date;
use Dotclear\Helper\Text;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Html;

class FrontendBehaviors
{
    /**
     * Adds meta tags in the head of the document.
     *
     * @return void
     */
    public static function odysseyHead(): void
    {
        // Adds the name of the editor.
        if (App::blog()->settings->system->editor) {
            echo '<meta name=author content=',
            My::displayAttr(App::blog()->settings->system->editor),
            '>', "\n";
        }

        // Adds the content of the copyright notice.
        if (App::blog()->settings->system->copyright_notice) {
            echo '<meta name=copyright content=',
            My::displayAttr(App::blog()->settings->system->copyright_notice),
            '>', "\n";
        }

        if (My::settings()->advanced_meta_social) {
            self::_odysseySocialMarkups();
        }

        if (My::settings()->advanced_json) {
            self::_odysseyJsonLd();
        }
    }

    /**
     * Displays minimal social markups.
     *
     * @return void The social markups.
     *
     * @link https://meiert.com/en/blog/minimal-social-markup/
     */
    private static function _odysseySocialMarkups(): void
    {
        $title = '';
        $desc  = '';
        $img   = '';

        switch (App::url()->type) {
            case 'post':
            case 'pages':
                $title = App::frontend()->context()->posts->post_title;

                $desc = App::frontend()->context()->posts->getExcerpt() ?: App::frontend()->context()->posts->getContent();
                $desc = My::cleanStr($desc);

                if (strlen($desc) > 180) {
                    $desc = Text::cutString($desc, 179) . '…';
                }

                $img_url_rel = Ctx::EntryFirstImageHelper('o', true, '', true);

                if ($img_url_rel) {
                    $img = My::blogBaseURL() . $img_url_rel;
                }

                break;
            case 'default':
            case 'default-page':
            case 'static':
                $title = App::blog()->name;

                // Specific title for the post list page when a static home page has been set.
                if (App::blog()->settings()->system->static_home && App::url()->type === 'default') {
                    $title = sprintf(
                        __('meta-title-static-postlist'),
                        $title
                    );
                }

                $page = (int) Ctx::PaginationPosition();

                if ($page > 1) {
                    $desc = sprintf(__('meta-social-page-with-number'), $page);
                }

                if (My::settings()->advanced_meta_description || App::blog()->desc) {
                    if ($desc) {
                        $desc .= ' – ';
                    }

                    $desc .= My::settings()->advanced_meta_description ?: App::blog()->desc ?: '';

                    $desc = My::cleanStr($desc);

                    if (strlen($desc) > 180) {
                        $desc = Text::cutString($desc, 179) . '…';
                    }
                }

                break;
            case 'category':
                $title = App::frontend()->context()->categories->cat_title;

                if (App::frontend()->context()->categories->cat_desc) {
                    $desc = App::frontend()->context()->categories->cat_desc;
                    $desc = My::cleanStr($desc);

                    if (strlen($desc) > 180) {
                        $desc = Text::cutString($desc, 179) . '…';
                    }
                }

                break;
            case 'tag':
                if (App::frontend()->context()->meta->meta_type === 'tag') {
                    $title = App::frontend()->context()->meta->meta_id;
                    $desc  = sprintf(__('meta-social-tags-post-related'), $title);
                }
        }

        $title = Html::escapeHTML($title);
        $img   = My::escapeURL($img);

        if ($title) {
            if (!$img && isset(My::settings()->header_image['url'])) {
                $img = My::escapeURL(App::blog()->url() . My::settings()->header_image['url']);
            }

            if ($img) {
                echo '<meta name="twitter:card" content="summary_large_image">', "\n";

                if (My::settings()->social_x) {
                    echo '<meta property="twitter:creator" content="@',
                    str_replace('https://x.com/', '', Html::escapeHTML(My::settings()->social_x)),
                    '">', "\n";
                }
            }

            // Quotes seem required for the following meta properties.
            echo '<meta property="og:title" content="', $title, '">', "\n";

            $desc = trim(Html::escapeHTML($desc));

            if ($desc) {
                echo '<meta property="og:description" content="', $desc, '">', "\n";
            }

            if ($img) {
                echo '<meta property="og:image" content="', $img, '">', "\n";
            }
        }
    }

    /**
     * Displays structured data as JSON-LD.
     *
     * @return void The structured data.
     */
    private static function _odysseyJsonLd(): void
    {
        $json_ld = [];

        switch (App::url()->type) {
            case 'default':
            case 'static':
                $blog_name = App::blog()->name;

                // Specific title for the post list page when a static home page has been set.
                if (App::blog()->settings()->system->static_home && App::url()->type === 'default') {
                    $blog_name = sprintf(
                        __('meta-title-static-postlist'),
                        $blog_name
                    );
                }

                $json_ld = [
                    '@context'    => 'http://schema.org',
                    '@type'       => 'WebPage',
                    'publisher' => [
                        '@type' => 'Organization',
                        'name'  => $blog_name
                    ],
                    'name'        => $blog_name,
                    'description' => My::settings()->advanced_meta_description ?: App::blog()->desc,
                    'url'         => App::blog()->url
                ];

                // Logo
                if (isset(My::settings()->header_image['url'])) {
                    // Retrieves the image path.
                    $image_path = App::config()->dotclearRoot() . My::settings()->header_image['url'];

                    if (file_exists($image_path)) {
                        list($width, $height) = getimagesize($image_path);

                        $json_ld['publisher']['logo'] = [
                            '@type'  => 'ImageObject',
                            'url'    => My::blogBaseURL() . My::settings()->header_image['url'],
                            'width'  => (int) $width,
                            'height' => (int) $height
                        ];
                    }
                }

                // Social links
                $social_sites   = My::socialSites();
                $social_links   = [];
                $social_exclude = ['phone', 'signal', 'sms', 'whatsapp'];

                foreach ($social_sites as $id => $data) {
                    $footer_social_id = 'footer_social_' . $id;

                    if (My::settings()->$footer_social_id !== null && !in_array($id, $social_exclude, true)) {
                        $json_ld['sameAs'][] = My::settings()->$footer_social_id;
                    }
                }

                $json_ld['copyrightHolder'] = App::blog()->settings->system->editor;
                $json_ld['copyrightNotice'] = App::blog()->settings->system->copyright_notice;

                $json_ld['inLanguage'] = App::blog()->settings()->system->lang;

                break;
            case 'post':
                $json_ld = [
                    '@context' => 'http://schema.org',
                    '@type'    => 'BlogPosting'
                ];

                $json_ld['headline'] = App::frontend()->context()->posts->post_title;

                $json_ld['description'] = My::cleanStr(App::frontend()->context()->posts->post_excerpt_xhtml);

                $json_ld['articleBody'] = App::frontend()->context()->posts->post_content_xhtml;

                // First image
                if (Ctx::EntryFirstImageHelper('o', false, '', true)) {
                    $image_path = App::config()->dotclearRoot() . Ctx::EntryFirstImageHelper('o', false, '', true);

                    if (file_exists($image_path)) {
                        list($width, $height) = getimagesize($image_path);

                        $json_ld['image'] = [
                            '@type'  => 'ImageObject',
                            'url'    => My::blogBaseURL() . Ctx::EntryFirstImageHelper('o', false, '', true),
                            'width'  => (int) $width,
                            'height' => (int) $height
                        ];
                    }
                }

                // Author
                if (App::frontend()->context()->posts->user_displayname) {
                    $json_ld['author'] = [
                        '@type' => 'Person',
                        'name'  => App::frontend()->context()->posts->user_displayname,
                        'url'   => App::frontend()->context()->posts->user_url
                    ];
                } elseif (App::frontend()->context()->posts->user_name || App::frontend()->context()->posts->user_firstname) {
                    $json_ld['author'] = [
                        '@type' => 'Person',
                        'name'  => trim(App::frontend()->context()->posts->user_name . ' ' . App::frontend()->context()->posts->user_firstname),
                        'url'   => App::frontend()->context()->posts->user_url
                    ];
                }

                $json_ld['publisher'] = [
                    '@type' => 'Organization',
                    'name'  => App::blog()->name,
                    'url'   => App::blog()->url
                ];

                if (isset(My::settings()->header_image['url'])) {
                    // Retrieves the image path.
                    $image_path = App::config()->dotclearRoot() . My::settings()->header_image['url'];

                    if (file_exists($image_path)) {
                        list($width, $height) = getimagesize($image_path);

                        $json_ld['publisher']['logo'] = [
                            '@type'  => 'ImageObject',
                            'url'    => My::blogBaseURL() . My::settings()->header_image['url'],
                            'width'  => (int) $width,
                            'height' => (int) $height
                        ];
                    }
                }

                $json_ld['copyrightHolder'] = App::blog()->settings->system->editor;
                $json_ld['copyrightNotice'] = App::blog()->settings->system->copyright_notice;

                $json_ld['articleSection'] = App::frontend()->context()->posts->cat_title;

                if (App::frontend()->context()->posts->post_meta) {
                    $post_meta = unserialize(App::frontend()->context()->posts->post_meta);
                    $tags      = '';

                    if (is_array($post_meta) && isset($post_meta['tag'])) {
                        if (count($post_meta['tag']) > 1) {
                            $json_ld['keywords'] = $post_meta['tag'];
                        } else {
                            $json_ld['keywords'] = $post_meta['tag'][0];
                        }

                    }
                }

                $json_ld['url'] = App::frontend()->context()->posts->getURL();

                $json_ld['datePublished'] = Date::iso8601(strtotime(App::frontend()->context()->posts->post_dt), App::frontend()->context()->posts->post_tz);

                $json_ld['dateCreated'] = Date::iso8601(strtotime(App::frontend()->context()->posts->post_creadt), App::frontend()->context()->posts->post_tz);

                $json_ld['dateModified'] = Date::iso8601(strtotime(App::frontend()->context()->posts->post_upddt), App::frontend()->context()->posts->post_tz);

                $json_ld['commentCount'] = App::frontend()->context()->posts->nb_comment;

                $json_ld['inLanguage'] = App::frontend()->context()->posts->post_lang;

                break;
            case 'pages':
                $json_ld = [
                    '@context' => 'http://schema.org',
                    '@type'    => 'WebPage'
                ];

                $json_ld['mainEntity'] = [
                    '@type'       => 'WebPageElement',
                    'name'        => App::frontend()->context()->posts->post_title,
                    'description' => App::frontend()->context()->posts->post_excerpt_xhtml,
                    'text'        => App::frontend()->context()->posts->post_content_xhtml
                ];

                // First image
                if (Ctx::EntryFirstImageHelper('o', false, '', true)) {
                    $image_path = App::config()->dotclearRoot() . Ctx::EntryFirstImageHelper('o', false, '', true);

                    if (file_exists($image_path)) {
                        list($width, $height) = getimagesize($image_path);

                        $json_ld['image'] = [
                            '@type'  => 'ImageObject',
                            'url'    => My::blogBaseURL() . Ctx::EntryFirstImageHelper('o', false, '', true),
                            'width'  => (int) $width,
                            'height' => (int) $height
                        ];
                    }
                }

                // Author
                if (App::frontend()->context()->posts->user_displayname) {
                    $json_ld['author'] = [
                        '@type' => 'Person',
                        'name'  => App::frontend()->context()->posts->user_displayname,
                        'url'   => App::frontend()->context()->posts->user_url
                    ];
                } elseif (App::frontend()->context()->posts->user_name || App::frontend()->context()->posts->user_firstname) {
                    $json_ld['author'] = [
                        '@type' => 'Person',
                        'name'  => trim(App::frontend()->context()->posts->user_name . ' ' . App::frontend()->context()->posts->user_firstname),
                        'url'   => App::frontend()->context()->posts->user_url
                    ];
                }

                $json_ld['publisher'] = [
                    '@type' => 'Organization',
                    'name'  => App::blog()->name,
                    'url'   => App::blog()->url
                ];

                if (isset(My::settings()->header_image['url'])) {
                    // Retrieves the image path.
                    $image_path = App::config()->dotclearRoot() . My::settings()->header_image['url'];

                    if (file_exists($image_path)) {
                        list($width, $height) = getimagesize($image_path);

                        $json_ld['publisher']['logo'] = [
                            '@type'  => 'ImageObject',
                            'url'    => My::blogBaseURL() . My::settings()->header_image['url'],
                            'width'  => (int) $width,
                            'height' => (int) $height
                        ];
                    }
                }

                $json_ld['copyrightHolder'] = App::blog()->settings->system->editor;
                $json_ld['copyrightNotice'] = App::blog()->settings->system->copyright_notice;

                $json_ld['url'] = App::frontend()->context()->posts->getURL();

                $json_ld['datePublished'] = Date::iso8601(strtotime(App::frontend()->context()->posts->post_dt), App::frontend()->context()->posts->post_tz);

                $json_ld['dateCreated'] = Date::iso8601(strtotime(App::frontend()->context()->posts->post_creadt), App::frontend()->context()->posts->post_tz);

                $json_ld['dateModified'] = Date::iso8601(strtotime(App::frontend()->context()->posts->post_upddt), App::frontend()->context()->posts->post_tz);

                $json_ld['commentCount'] = App::frontend()->context()->posts->nb_comment;

                $json_ld['inLanguage'] = App::frontend()->context()->posts->post_lang;
        }

        // Removes empty values.
        $json_ld = array_filter($json_ld);

        if (!empty($json_ld)) {
            $json_ld = json_encode($json_ld, JSON_HEX_QUOT|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS);

            /**
             * To be replaced by json_validate() function
             * available on PHP 8.3.
             */
            if (My::jsonValidate($json_ld)) {
                echo '<script type=application/ld+json>', $json_ld, '</script>', "\n";
            }
        }
    }

    /**
     * Modifies the entry content depending on theme settings.
     *
     * @param string $tag  The tag.
     * @param array  $args The args.
     *
     * @return string The entry content.
     */
    public static function odysseyAfterContent(string $tag, array $args): string
    {
        // Displays wide images.
        if ($tag === 'EntryContent' && My::settings()->content_images_wide) {
            $args[0] = self::odysseyImageWide($args[0]);
        }

        // Adds quotes to a template value only if is_attr argument is set.
        if (isset($args['is_attr']) && $args['is_attr'] === '1') {
            $args[0] = My::displayAttr($args[0]);
        }

        return $args[0];
    }

    /**
     * Displays wide images.
     *
     * Function used by self::odysseyAfterContent().
     *
     * @param array $tag  The tags.
     * @param array $args The args.
     *
     * @return void The image.
     */
    public static function odysseyImageWide(string $entry_content): string
    {
        // Matches all images by regex.
        $entry_content = preg_replace_callback(
            '/<img[^>]*>/',
            function ($matches) {
                // The image HTML code.
                $img = $matches[0];

                // Gets the image src attribute.
                preg_match('/src="([^"]*)/', $img, $src_match);

                $src_attr  = $src_match[0] ?? '';
                $src_value = $src_match[1] ?? '';

                // Transforms absolute URLs in relative ones.
                if (str_starts_with($src_value, My::blogBaseURL())) {
                    $src_value = str_replace(My::blogBaseURL(), '', $src_value);
                }

                // Builds an array that will contain all image sizes.
                $img = [
                    'o' => [
                        'url'    => $src_value,
                        'width'  => null,
                        'height' => null
                    ]
                ];

                // If the original image size exists.
                if ($src_value && file_exists(App::config()->dotclearRoot() . $src_value)) {

                    // Gets original image dimensions.
                    list($width, $height) = getimagesize(App::config()->dotclearRoot() . $src_value);

                    $img['o']['width']    = (int) $width;
                    $img['o']['height']   = (int) $height;

                    // Gets image orientation.
                    $portrait = false;

                    if ($width < $height) {
                        $portrait = true;
                    }

                    // Sets wide image width in px.
                    $content_width = My::getContentWidth('px')['value'];
                    $margin_max    = '120';

                    $img_width_max = $content_width;

                    if (!$portrait) {
                        if (App::url()->type === 'post'
                            || App::url()->type === 'pages'
                            || My::settings()->content_postlist_type === 'content'
                        ) {
                            $img_width_max += $margin_max * 2;
                        }
                    }

                    // If the image width is lower than the content + margin width.
                    $margin_diff = abs(($img_width_max - $width) / 2);

                    if ($margin_diff < $margin_max) {
                        $img_width_max = $width;
                    }

                    $info = Path::info($src_value);

                    foreach (App::media()->getThumbSizes() as $size_id => $size_data) {
                        $img_width = $size_data[0] ?? null;
                        $img_crop  = $size_data[1] ?? null;

                        if ($img_width && $img_crop === 'ratio') {
                            $dc_img_pattern = App::media()->getThumbnailFilePattern($info['extension']);
                            $img_pattern    = sprintf($dc_img_pattern, $info['dirname'], $info['base'], '%s');
                            $img_path_rel   = sprintf($img_pattern, $size_id);
                            $img_path       = App::config()->dotclearRoot() . $img_path_rel;

                            if (file_exists($img_path)) {
                                $img[$size_id]['url']    = $img_path_rel;
                                $img[$size_id]['width']  = (int) $img_width;
                                $img[$size_id]['height'] = isset(getimagesize($img_path)[1]) ? (int) getimagesize($img_path)[1] : null;
                            }
                        }
                    }

                    // Sort $img by width.
                    uasort(
                        $img,
                        function ($a, $b) {
                            return $a['width'] <=> $b['width'];
                        }
                    );

                    // Defines image attributes.
                    $attr = 'src=' . My::displayAttr($img['o']['url'], 'url') . ' ';

                    // If multiple image sizes exist, displays them.
                    if (count($img) > 1) {
                        $attr .= 'srcset="';

                        // Puts every image size in the srcset attribute.
                        foreach ($img as $img_id => $img_data) {
                            $attr .= My::escapeURL($img_data['url']) . ' ' . (int) $img_data['width'] . 'w';

                            if ($img_id !== array_key_last($img)) {
                                $attr .= ', ';
                            }
                        }

                        $attr .= '" ';

                        // Displays the image wide if its format is landscape or square.
                        if ($img['o']['width'] >= $img['o']['height'] && $img['o']['width'] >= $content_width) {
                            $attr .= 'class=odyssey-img-wide ';
                        }

                        $attr .= 'sizes=100vw ';
                        $attr .= 'width=' . (int) $img_width_max . ' ';
                        $attr .= 'height=' . (int) ($img_width_max * $img['o']['height'] / $img['o']['width']);
                    }

                    return str_replace($src_attr . '"', trim($attr), $matches[0]);
                }

                return $matches[0];
            },
            $entry_content
        );

        return $entry_content;
    }

    /**
     * Adds two new conditions to tpl:EntryIf:
     * - "has_tag" if a post has tags.
     * - "has_reaction" if the post has comments or trackbacks.
     *
     * @param string                     $tag     The EntryIf tag
     * @param ArrayObject<string, mixed> $attr    The attributes
     * @param string                     $content The content
     * @param ArrayObject<string, mixed> $if      The if conditions
     *
     * @return void
     */
    public static function odysseyTplConditions(string $tag, ArrayObject $attr, string $content, ArrayObject $if): void
    {
        if ($tag === 'EntryIf' && isset($attr['has_reaction']) && My::settings()->content_postlist_reactions) {
            $sign = (bool) $attr['has_reaction'] ? '' : '!';

            $if->append($sign . '(App::frontend()->context()->posts->hasComments() || App::frontend()->context()->posts->hasTrackbacks())');
        }
    }
}
