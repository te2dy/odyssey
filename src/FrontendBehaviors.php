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
use Dotclear\Core\Frontend\Url;

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
        $json_ld = [];

        switch (App::url()->type) {
            case 'default':
                $json_ld = [
                    '@context'  => 'http://schema.org',
                    '@type'     => 'WebPage',
                    'publisher' => App::blog()->name
                ];

                break;
            case 'post':
                $json_ld = [
                    '@context'  => 'http://schema.org',
                    '@type'     => 'BlogPosting',
                    'publisher' => App::blog()->name
                ];

                $json_ld['headline'] = App::frontend()->ctx->posts->post_title;

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

                $json_ld['datePublished'] = App::frontend()->ctx->posts->getDate('%Y-%m-%d');

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

                if (App::frontend()->ctx->posts->post_excerpt) {
                    $json_ld['description'] = App::frontend()->ctx->posts->post_excerpt;
                }

                /**
                 * @link https://www.contentpowered.com/blog/adding-schema-markup-blog/
                 *
                 * To add:
                 * - commentCourt
                 * - inLanguage
                 * - thumbnailUrl || image ?
                 * - isPartOf ?
                 * - mainEntityOfPage ?
                 * - articleBody ?
                 */
        }

        if (!empty($json_ld)) {
            echo '<script type="application/ld+json">', json_encode($json_ld), '</script>', "\n";
        }
    }
}
