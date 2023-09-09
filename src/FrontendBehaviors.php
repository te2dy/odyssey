<?php
/**
 * Odyssey, a Dotclear theme.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2023 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

namespace Dotclear\Theme\odyssey;

use Dotclear\App;
use Dotclear\Core\Process;
use Dotclear\Core\Frontend\Ctx;
use Dotclear\Core\Frontend\Url;
use Dotclear\Helper\Date;
use Dotclear\Helper\Html\Html;

require_once 'OdysseyUtils.php';
use OdysseyUtils as odUtils;

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
            odUtils::attrValueQuotes(App::blog()->settings->system->editor),
            '>' . "\n";
        }

        // Adds the content of the copyright notice.
        if (App::blog()->settings->system->copyright_notice) {
            echo '<meta name=copyright content=',
            odUtils::attrValueQuotes(App::blog()->settings->system->copyright_notice),
            '>' . "\n";
        }
    }

    /**
     * DEV
     */
    public static function odysseyJsonLd()
    {
        if (odUtils::configuratorSetting() === true) {
            $json_ld = [];

            switch (App::url()->type) {
                case 'default':
                    $json_ld = [
                        '@context' => 'http://schema.org',
                        '@type'    => 'WebPage',
                        'publisher' => [
                            '@type' => 'Organization',
                            'name'  => App::blog()->name
                        ],
                        'headline' => App::blog()->name,
                        'url'      => App::blog()->url
                    ];

                    break;
                case 'post':
                    $json_ld = [
                        '@context'  => 'http://schema.org',
                        '@type'     => 'BlogPosting',
                        'publisher' => [
                            '@type' => 'Organization',
                            'name'  => App::blog()->name
                        ]
                    ];

                    if (App::frontend()->ctx->posts->post_title) {
                        $json_ld['headline'] = App::frontend()->ctx->posts->post_title;
                    }
                    
                    if (App::frontend()->ctx->posts->post_excerpt) {
                        $post_excerpt = App::frontend()->ctx->posts->post_excerpt_xhtml;
                        $post_excerpt = Html::clean($post_excerpt);
                        $post_excerpt = Html::decodeEntities($post_excerpt);
                        $post_excerpt = preg_replace('/\s+/', ' ', $post_excerpt);
                        $post_excerpt = Html::escapeHTML($post_excerpt);
                        
                        $json_ld['description'] = $post_excerpt;
                    }
                    
                    if (Ctx::EntryFirstImageHelper("o", false, "", true)) {
                        $json_ld['image'] = Ctx::EntryFirstImageHelper("o", false, "", true);
                    }

                    if (App::frontend()->ctx->posts->user_displayname) {
                        $json_ld['author'] = [
                            '@type' => 'Person',
                            'name'  => App::frontend()->ctx->posts->user_displayname
                        ];
                    } elseif (App::frontend()->ctx->posts->user_name || App::frontend()->ctx->posts->user_firstname) {
                        $json_ld['author'] = [
                            '@type' => 'Person',
                            'name'  => trim(App::frontend()->ctx->posts->user_name . ' ' . App::frontend()->ctx->posts->user_firstname)
                        ];
                    }

                    if (App::frontend()->ctx->posts->cat_title) {
                        $json_ld['articleSection'] = App::frontend()->ctx->posts->cat_title;
                    }

                    if (App::frontend()->ctx->posts->post_meta) {
                        $post_meta = unserialize(App::frontend()->ctx->posts->post_meta);
                        $tags      = '';

                        if (is_array($post_meta) && isset($post_meta['tag'])) {
                            if (count($post_meta['tag']) > 1) {
                                $json_ld['keywords'] = $post_meta['tag'];
                            } else {
                                $json_ld['keywords'] = $post_meta['tag'][0];
                            }

                        }
                    }

                    if (App::frontend()->ctx->posts->post_url) {
                        $json_ld['url'] = odUtils::blogBaseURL() . '/' . App::frontend()->ctx->posts->post_url;
                    }
                    
                    if (App::frontend()->ctx->posts->post_dt && App::frontend()->ctx->posts->post_tz) {
                        $json_ld['datePublished'] = Date::iso8601(strtotime(App::frontend()->ctx->posts->post_dt), App::frontend()->ctx->posts->post_tz);
                    }
                
                    if (App::frontend()->ctx->posts->post_creadt && App::frontend()->ctx->posts->post_tz) {
                        $json_ld['dateCreated'] = Date::iso8601(strtotime(App::frontend()->ctx->posts->post_creadt), App::frontend()->ctx->posts->post_tz);
                    }
                    
                    if (App::frontend()->ctx->posts->post_upddt && App::frontend()->ctx->posts->post_tz) {
                        $json_ld['dateModified'] = Date::iso8601(strtotime(App::frontend()->ctx->posts->post_upddt), App::frontend()->ctx->posts->post_tz);
                    }

                    if (App::frontend()->ctx->posts->nb_comment) {
                        $json_ld['commentCount'] = App::frontend()->ctx->posts->nb_comment;
                    }

                    if (App::frontend()->ctx->posts->post_lang) {
                        $json_ld['inLanguage'] = App::frontend()->ctx->posts->post_lang;
                    }

                    // var_dump(App::frontend()->ctx->posts);

                    /**
                     * @link https://www.contentpowered.com/blog/adding-schema-markup-blog/
                     *
                     * To add:
                     * - thumbnailUrl || image ?
                     * - articleBody ?
                     */
            }

            if (!empty($json_ld)) {
                echo '<script type="application/ld+json">', json_encode($json_ld), '</script>', "\n";
            }
        }
    }
}
