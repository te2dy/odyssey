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
use Dotclear\Core\Process;
use Dotclear\Core\Frontend\Ctx;
use Dotclear\Core\Frontend\Url;
use Dotclear\Helper\Date;
use Dotclear\Helper\Text;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Html;

use context;

class FrontendBehaviors
{
    /**
     * Adds meta tags in the <head> section depending on the blog settings.
     *
     * @return void The head meta.
     */
    public static function odysseyHeadMeta()
    {
        // Adds the name of the editor.
        if (App::blog()->settings->system->editor) {
            echo '<meta name=author content=',
            My::attrValue(App::blog()->settings->system->editor),
            '>' . "\n";
        }

        // Adds the content of the copyright notice.
        if (App::blog()->settings->system->copyright_notice) {
            echo '<meta name=copyright content=',
            My::attrValue(App::blog()->settings->system->copyright_notice),
            '>' . "\n";
        }
    }

    /**
     * Displays minimal social markups.
     *
     * @return void The social markups.
     *
     * @link https://meiert.com/en/blog/minimal-social-markup/
     */
    public static function odysseySocialMarkups(): void
    {
        if (My::settingValue('advanced_meta_social') === true) {
            $title = '';
            $desc  = '';
            $img   = '';

            switch (App::url()->type) {
                case 'post':
                case 'pages':
                    $title = App::frontend()->context()->posts->post_title;

                    $desc = App::frontend()->context()->posts->getExcerpt() ?: App::frontend()->context()->posts->getContent();
                    $desc = My::cleanAttr($desc);

                    if (strlen($desc) > 180) {
                        $desc = Text::cutString($desc, 179) . '…';
                    }

                    if (context::EntryFirstImageHelper('o', true, '', true)) {
                        $img = My::blogBaseURL() . context::EntryFirstImageHelper('o', true, '', true);
                    }

                    break;
                case 'default':
                case 'default-page':
                    $title = App::blog()->name;

                    if ((int) Ctx::PaginationPosition() > 1) {
                        $desc = sprintf(
                            __('meta-social-page-with-number'),
                            Ctx::PaginationPosition()
                        );
                    }

                    if (My::settingValue('advanced_meta_description') || App::blog()->desc) {
                        if ($desc) {
                            $desc .= ' – ';
                        }

                        if (My::settingValue('advanced_meta_description')) {
                            $desc .= My::settingValue('advanced_meta_description');
                        } elseif (App::blog()->desc) {
                            $desc .= App::blog()->desc;
                        }

                        $desc = My::cleanAttr($desc);

                        if (strlen($desc) > 180) {
                            $desc = Text::cutString($desc, 179) . '…';
                        }
                    }

                    break;
                case 'category':
                    $title = App::frontend()->context()->categories->cat_title;

                    if (App::frontend()->context()->categories->cat_desc) {
                        $desc = App::frontend()->context()->categories->cat_desc;
                        $desc = My::cleanAttr($desc);

                        if (strlen($desc) > 180) {
                            $desc = Text::cutString($desc, 179) . '…';
                        }
                    }

                    break;
                case 'tag':
                    if (App::frontend()->context()->meta->meta_type === 'tag') {
                        $title = App::frontend()->context()->meta->meta_id;
                        $desc  = sprintf(
                            __('meta-social-tags-post-related'),
                            $title
                        );
                    }
            }

            $title = Html::escapeHTML($title);

            if ($title) {
                $desc = Html::escapeHTML($desc);

                if (!$img && isset(My::settingValue('header_image')['url'])) {
                    $img = My::blogBaseURL() . My::settingValue('header_image')['url'];
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
     * Displays structured data as JSON-LD.
     *
     * @return string The structured data.
     */
    public static function odysseyJsonLd()
    {
        if (My::settingValue('advanced_json') === true) {
            $json_ld = [];

            switch (App::url()->type) {
                case 'default':
                    $json_ld = [
                        '@context'    => 'http://schema.org',
                        '@type'       => 'WebPage',
                        'publisher' => [
                            '@type' => 'Organization',
                            'name'  => App::blog()->name
                        ],
                        'name'  => App::blog()->name,
                        'description' => My::settingValue('advanced_meta_description') ?: App::blog()->desc,
                        'url'         => App::blog()->url
                    ];

                    // Logo
                    if (isset(My::settingValue('header_image')['url'])) {
                        // Retrieves the image path.
                        $image_path = App::blog()->public_path . str_replace(
                            App::blog()->settings->system->public_url . '/',
                            '/',
                            My::settingValue('header_image')['url']
                        );

                        list($width, $height) = getimagesize($image_path);

                        $json_ld['publisher']['logo'] = [
                            '@type'  => 'ImageObject',
                            'url'    => My::blogBaseURL() . My::settingValue('header_image')['url'],
                            'width'  => (int) $width,
                            'height' => (int) $height
                        ];
                    }

                    // Social links
                    $social_sites   = My::socialSites();
                    $social_links   = [];
                    $social_exclude = ['signal', 'whatsapp'];

                    foreach ($social_sites as $id => $data) {
                        if (My::settingValue('footer_social_' . $id) !== null && !in_array($id, $social_exclude, true)) {
                            $json_ld['sameAs'][] = Html::escapeURL(My::settingValue('footer_social_' . $id));
                        }
                    }

                    $json_ld['copyrightHolder'] = App::blog()->settings->system->editor;
                    $json_ld['copyrightNotice'] = App::blog()->settings->system->copyright_notice;

                    $json_ld['inLanguage'] = App::blog()->settings()->system->lang;

                    break;
                case 'post':
                    $json_ld = [
                        '@context'  => 'http://schema.org',
                        '@type'     => 'BlogPosting'
                    ];

                    $json_ld['headline'] = App::frontend()->context()->posts->post_title;

                    $json_ld['description'] = My::cleanAttr(App::frontend()->context()->posts->post_excerpt_xhtml);

                    $json_ld['articleBody'] = App::frontend()->context()->posts->post_content_xhtml;

                    // First image
                    if (Ctx::EntryFirstImageHelper('o', false, '', true)) {
                        $image_path = App::blog()->public_path . str_replace(
                            App::blog()->settings->system->public_url . '/',
                            '/',
                            Ctx::EntryFirstImageHelper('o', false, '', true)
                        );

                        list($width, $height) = getimagesize($image_path);

                        $json_ld['image'] = [
                            '@type'  => 'ImageObject',
                            'url'    => Ctx::EntryFirstImageHelper("o", false, "", true),
                            'width'  => (int) $width,
                            'height' => (int) $height
                        ];
                    }

                    // Author
                    if (App::frontend()->context()->posts->user_displayname) {
                        $json_ld['author'] = [
                            '@type' => 'Person',
                            'name'  => App::frontend()->context()->posts->user_displayname,
                            'url'  => App::frontend()->context()->posts->user_url
                        ];
                    } elseif (App::frontend()->context()->posts->user_name || App::frontend()->context()->posts->user_firstname) {
                        $json_ld['author'] = [
                            '@type' => 'Person',
                            'name'  => trim(App::frontend()->context()->posts->user_name . ' ' . App::frontend()->context()->posts->user_firstname),
                            'url'  => App::frontend()->context()->posts->user_url
                        ];
                    }

                    $json_ld['publisher'] = [
                        '@type' => 'Organization',
                        'name'  => App::blog()->name,
                        'url'   => App::blog()->url
                    ];

                    if (isset(My::settingValue('header_image')['url'])) {
                        // Retrieves the image path.
                        $image_path = App::blog()->public_path . str_replace(
                            App::blog()->settings->system->public_url . '/',
                            '/',
                            My::settingValue('header_image')['url']
                        );

                        list($width, $height) = getimagesize($image_path);

                        $json_ld['publisher']['logo'] = [
                            '@type'  => 'ImageObject',
                            'url'    => My::blogBaseURL() . My::settingValue('header_image')['url'],
                            'width'  => (int) $width,
                            'height' => (int) $height
                        ];
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

                    $json_ld['url'] = My::blogBaseURL() . '/' . App::frontend()->context()->posts->post_url;

                    $json_ld['datePublished'] = Date::iso8601(strtotime(App::frontend()->context()->posts->post_dt), App::frontend()->context()->posts->post_tz);

                    $json_ld['dateCreated'] = Date::iso8601(strtotime(App::frontend()->context()->posts->post_creadt), App::frontend()->context()->posts->post_tz);

                    $json_ld['dateModified'] = Date::iso8601(strtotime(App::frontend()->context()->posts->post_upddt), App::frontend()->context()->posts->post_tz);

                    $json_ld['commentCount'] = App::frontend()->context()->posts->nb_comment;

                    $json_ld['inLanguage'] = App::frontend()->context()->posts->post_lang;
            }

            // Removes empty values.
            $json_ld = array_filter($json_ld);

            if (!empty($json_ld)) {
                $json_ld = json_encode($json_ld);

                /**
                 * To be replaced by json_validate() function
                 * available on PHP 8.3.
                 */
                if (My::odysseyJsonValidate($json_ld)) {
                    echo '<script type="application/ld+json">', $json_ld, '</script>', "\n";
                }
            }
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
    public static function odysseyImageWide($tag, $args): void
    {

        // If only on Entry content.
        if (!in_array($tag, ['EntryContent'])) {
            return;
        }

        if (!My::settingValue('content_images_wide')) {
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
                if (file_exists(App::config()->dotclearRoot() . $src_value) && str_starts_with($src_value, '/')) {
                    if (App::url()->type === 'post' || App::url()->type === 'pages') {
                        $option_image_wide = true;
                    } else {
                        $option_image_wide = false;
                    }

                    $img_width_max = My::getContentWidth('px')['value'];

                    if ($option_image_wide === true) {
                        $img_width_max += 120 * 2;
                    }

                    // Gets original image dimensions.
                    list($width, $height) = getimagesize(App::config()->dotclearRoot() . $src_value);

                    $img['o']['width']  = $width;
                    $img['o']['height'] = $height;

                    $media_sizes = App::media()->thumb_sizes;

                    $info = Path::info($src_value);

                    // The image to set in the src attribute.
                    $src_image_size = 'o';

                    foreach ($media_sizes as $size_id => $size_data) {
                        if (isset($size_data[1])
                            && $size_data[1] === 'ratio'
                            && file_exists(App::config()->dotclearRoot() . $info['dirname'] . '/.' . $info['base'] . '_' . $size_id . '.' . strtolower($info['extension']))
                        ) {
                            $img[$size_id]['url']   = $info['dirname'] . '/.' . $info['base'] . '_' . $size_id . '.' . strtolower($info['extension']);
                            $img[$size_id]['width'] = isset($size_data[0]) ? $size_data[0] : '';

                            list($width, $height) = getimagesize(App::config()->dotclearRoot() . $img[$size_id]['url']);

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
                    if (//My::settingValue('content_images_wide') &&
                        $img[$src_image_size]['width'] > $img[$src_image_size]['height']
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
}
