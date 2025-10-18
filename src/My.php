<?php
/**
 * Odyssey, a simple and customizable Dotclear theme.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2025 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

namespace Dotclear\Theme\odyssey;

use Dotclear\App;
use Dotclear\Module\MyTheme;
use Dotclear\Helper\File\Files;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Html;

class My extends MyTheme
{
    /**
     * Declares the sections of the theme configuration page.
     *
     * @param string $section_id The id of the section (optional).
     *
     * @return array All the sections or one section data
     *               if a section id is set.
     */
    public static function settingsSections(string $section_id = ''): array
    {
        $sections = [
            'global' => [
                'name'         => __('section-global'),
                'sub_sections' => [
                    'layout'       => __('section-global-layout'),
                    'fonts'        => __('section-global-fonts'),
                    'colors'       => __('section-global-colors'),
                    'colors-light' => __('section-global-colorslight'),
                    'colors-dark'  => __('section-global-colorsdark'),
                    'other'        => __('section-global-other')
                ]
            ],
            'header' => [
                'name'         => __('section-header'),
                'sub_sections' => [
                    'image'    => __('section-header-image')
                ]
            ],
            'content' => [
                'name'         => __('section-content'),
                'sub_sections' => [
                    'postlist'        => __('section-content-postlist'),
                    'post'            => __('section-content-post'),
                    'text-formatting' => __('section-content-textformatting'),
                    'images'          => __('section-content-images'),
                    'pagination'      => __('section-content-pagination')
                ]
            ],
            'reactions' => [
                'name'         => __('section-reactions'),
                'sub_sections' => [
                    'form'       => __('section-reactions-form'),
                    'trackbacks' => __('section-reactions-trackbacks'),
                    'feed'       => __('section-reactions-feed'),
                    'other'      => __('section-reactions-other')
                ]
            ],
            'widgets' => [
                'name'         => __('section-widgets'),
                'sub_sections' => []
            ],
            'footer' => [
                'name'         => __('section-footer'),
                'sub_sections' => [
                    'social' => __('section-footer-social')
                ]
            ],
            'social' => [
                'name' => __('section-social')
            ],
            'advanced' => [
                'name'         => __('section-advanced'),
                'sub_sections' => [
                    'appearance' => __('section-advanced-appearance'),
                    'seo'        => __('section-advanced-seo'),
                    'js'         => __('section-advanced-js')
                ]
            ]
        ];

        if ($section_id) {
            return $sections[$section_id] ?? '';
        }

        return $sections;
    }

    /**
     * Declares all the settings of the theme configurator.
     *
     * @param string $setting_id The id of the setting (optional).
     *
     * @return array All the sections or only on setting data
     *               if a setting id is set.
     */
    public static function settingsDefault(string $setting_id = ''): array
    {
        $default_settings['global_unit'] = [
            'title'       => __('settings-global-unit-title'),
            'description' => __('settings-global-unit-description'),
            'label'       => 'CSS length unit',
            'type'        => 'select',
            'choices'     => [
                __('settings-global-unit-relative-default') => 'em',
                __('settings-global-unit-static')           => 'px'
            ],
            'default'     => 'em',
            'section'     => ['global', 'layout']
        ];

        $default_settings['global_page_width_value'] = [
            'title'       => __('settings-global-pagewidthvalue-title'),
            'description' => __('settings-global-pagewidthvalue-description'),
            'label'       => 'Page width',
            'type'        => 'range',
            'sanitizer'   => 'sanitizePageWidth',
            'range'       => [
                'min'  => 30,
                'max'  => 80,
                'step' => 1,
                'unit' => 'em'
            ],
            'default'     => 30,
            'section'     => ['global', 'layout']
        ];

        $default_settings['global_font_family'] = [
            'title'       => __('settings-global-fontfamily-title'),
            'description' => __('settings-global-fontfamily-description'),
            'label'       => 'Global font',
            'type'        => 'select',
            'choices'     => [
                __('settings-global-fontfamily-system-default')     => 'system',
                __('settings-global-fontfamily-sansserif')          => 'sans-serif',
                __('settings-global-fontfamily-serif')              => 'serif',
                __('settings-global-fontfamily-transitional')       => 'transitional',
                __('settings-global-fontfamily-oldstyle')           => 'old-style',
                __('settings-global-fontfamily-garamond')           => 'garamond',
                __('settings-global-fontfamily-humanist')           => 'humanist',
                __('settings-global-fontfamily-geometrichumanist')  => 'geometric-humanist',
                __('settings-global-fontfamily-classicalhumanist')  => 'classical-humanist',
                __('settings-global-fontfamily-neogrotesque')       => 'neo-grotesque',
                __('settings-global-fontfamily-monospaceslabserif') => 'monospace-slab-serif',
                __('settings-global-fontfamily-monospacecode')      => 'monospace-code',
                __('settings-global-fontfamily-industrial')         => 'industrial',
                __('settings-global-fontfamily-roundedsans')        => 'rounded-sans',
                __('settings-global-fontfamily-slabserif')          => 'slab-serif',
                __('settings-global-fontfamily-antique')            => 'antique',
                __('settings-global-fontfamily-didone')             => 'didone',
                __('settings-global-fontfamily-handwritten')        => 'handwritten'
            ],
            'default'     => 'system',
            'section'     => ['global', 'fonts']
        ];

        $default_settings['global_font_size'] = [
            'title'       => __('settings-global-fontsize-title'),
            'description' => __('settings-global-fontsize-description'),
            'label'       => 'Font size',
            'type'        => 'range',
            'range'       => [
                'min'  => 80,
                'max'  => 120,
                'step' => 10,
                'unit' => '%'
            ],
            'default'     => 100,
            'section'     => ['global', 'fonts']
        ];

        $default_settings['global_font_antialiasing'] = [
            'title'       => __('settings-global-fontantialiasing-title'),
            'description' => __('settings-global-fontantialiasing-description'),
            'label'       => 'Font smoothing',
            'type'        => 'checkbox',
            'default'     => false,
            'section'     => ['global', 'fonts']
        ];

        $default_settings['global_color_scheme'] = [
            'title'       => __('settings-global-colorscheme-title'),
            'description' => __('settings-global-colorscheme-description'),
            'label'       => 'Color scheme',
            'type'        => 'select',
            'choices'     => [
                __('settings-global-colorscheme-auto-default') => 'auto',
                __('settings-global-colorscheme-light')        => 'light',
                __('settings-global-colorscheme-dark')         => 'dark'
            ],
            'default'     => 'auto',
            'section'     => ['global', 'colors']
        ];

        $global_color_primary_choices = [
            __('settings-global-primarycolor-blue-default') => 'blue',
            __('settings-global-primarycolor-gray')         => 'gray',
            __('settings-global-primarycolor-green')        => 'green',
            __('settings-global-primarycolor-red')          => 'red'
        ];

        ksort($global_color_primary_choices);

        // Adds a custom option at the end of the list.
        $global_color_primary_choices[__('settings-global-primarycolor-custom')] = 'custom';

        $default_settings['global_color_primary'] = [
            'title'       => __('settings-global-primarycolor-title'),
            'description' => __('settings-global-primarycolor-description'),
            'label'       => 'Main color',
            'type'        => 'select',
            'choices'     => $global_color_primary_choices,
            'default'     => 'blue',
            'section'     => ['global', 'colors']
        ];

        $default_settings['global_color_text_custom'] = [
            'title'       => __('settings-global-maintextcolorcustom-title'),
            'description' => '',
            'label'       => 'Custom text color',
            'type'        => 'color',
            'default'     => '#303030',
            'placeholder' => '#303030',
            'section'     => ['global', 'colors-light']
        ];

        $default_settings['global_color_text_secondary_custom'] = [
            'title'       => __('settings-global-secondarytextcolorcustom-title'),
            'description' => '',
            'label'       => 'Custom secondary text color',
            'type'        => 'color',
            'default'     => '#6c6f78',
            'placeholder' => '#6c6f78',
            'section'     => ['global', 'colors-light']
        ];

        $default_settings['global_color_primary_custom'] = [
            'title'       => __('settings-global-primarycolorcustom-title'),
            'description' => '',
            'label'       => 'Custom main color',
            'type'        => 'color',
            'default'     => '#1742cf',
            'placeholder' => '#1742cf',
            'section'     => ['global', 'colors-light']
        ];

        $default_settings['global_color_primary_amplified_custom'] = [
            'title'       => __('settings-global-primarycoloramplifiedcustom-title'),
            'description' => '',
            'label'       => 'Custom main color on hover',
            'type'        => 'color',
            'default'     => '#063ff9',
            'placeholder' => '#063ff9',
            'section'     => ['global', 'colors-light']
        ];

        $default_settings['global_color_input_custom'] = [
            'title'       => __('settings-global-inputcustom-title'),
            'description' => '',
            'label'       => 'Custom form input color',
            'type'        => 'color',
            'default'     => '#f2f2f2',
            'placeholder' => '#f2f2f2',
            'section'     => ['global', 'colors-light']
        ];

        $default_settings['global_color_border_custom'] = [
            'title'       => __('settings-global-bordercustom-title'),
            'description' => '',
            'label'       => 'Custom border color',
            'type'        => 'color',
            'default'     => '#cccccc',
            'placeholder' => '#cccccc',
            'section'     => ['global', 'colors-light']
        ];

        $default_settings['global_color_background_custom'] = [
            'title'       => __('settings-global-primarycolorbackgroundcustom-title'),
            'description' => '',
            'label'       => 'Custom background color',
            'type'        => 'color',
            'default'     => '#fafafa',
            'placeholder' => '#fafafa',
            'section'     => ['global', 'colors-light']
        ];

        $default_settings['global_color_text_dark_custom'] = [
            'title'       => __('settings-global-maintextcolorcustom-title'),
            'description' => '',
            'label'       => 'Custom text color for dark color scheme',
            'type'        => 'color',
            'default'     => '#cccccc',
            'placeholder' => '#cccccc',
            'section'     => ['global', 'colors-dark']
        ];

        $default_settings['global_color_text_secondary_dark_custom'] = [
            'title'       => __('settings-global-secondarytextcolorcustom-title'),
            'description' => '',
            'label'       => 'Custom secondary text color for dark color scheme',
            'type'        => 'color',
            'default'     => '#969696',
            'placeholder' => '#969696',
            'section'     => ['global', 'colors-dark']
        ];

        $default_settings['global_color_primary_dark_custom'] = [
            'title'       => __('settings-global-primarycolorcustom-title'),
            'description' => '',
            'label'       => 'Custom main color for dark color scheme',
            'type'        => 'color',
            'default'     => '#7592f0',
            'placeholder' => '#7592f0',
            'section'     => ['global', 'colors-dark']
        ];

        $default_settings['global_color_primary_dark_amplified_custom'] = [
            'title'       => __('settings-global-primarycoloramplifiedcustom-title'),
            'description' => '',
            'label'       => 'Custom main color on hover for dark color scheme',
            'type'        => 'color',
            'default'     => '#9cb2fc',
            'placeholder' => '#9cb2fc',
            'section'     => ['global', 'colors-dark']
        ];

        $default_settings['global_color_input_dark_custom'] = [
            'title'       => __('settings-global-inputcustom-title'),
            'description' => '',
            'label'       => 'Custom form input color for dark color scheme',
            'type'        => 'color',
            'default'     => '#2b2a33',
            'placeholder' => '#2b2a33',
            'section'     => ['global', 'colors-dark']
        ];

        $default_settings['global_color_border_dark_custom'] = [
            'title'       => __('settings-global-bordercustom-title'),
            'description' => '',
            'label'       => 'Custom border color for dark color scheme',
            'type'        => 'color',
            'default'     => '#cccccc',
            'placeholder' => '#cccccc',
            'section'     => ['global', 'colors-dark']
        ];

        $default_settings['global_color_background_dark_custom'] = [
            'title'       => __('settings-global-primarycolorbackgroundcustom-title'),
            'description' => '',
            'label'       => 'Custom background color for dark color scheme',
            'type'        => 'color',
            'default'     => '#16161d',
            'placeholder' => '#16161d',
            'section'     => ['global', 'colors-dark']
        ];

        $default_settings['global_css_transition'] = [
            'title'       => __('settings-global-colortransition-title'),
            'description' => __('settings-global-colortransition-description'),
            'label'       => 'Color transitions',
            'type'        => 'checkbox',
            'default'     => false,
            'section'     => ['global', 'colors-dark']
        ];

        $default_settings['global_links_underline'] = [
            'title'       => __('settings-global-linksunderline-title'),
            'description' => __('settings-global-linksunderline-description'),
            'label'       => 'Underline links',
            'type'        => 'checkbox',
            'default'     => false,
            'section'     => ['global', 'other']
        ];

        $default_settings['global_border_radius'] = [
            'title'       => __('settings-global-borderradius-title'),
            'description' => __('settings-global-borderradius-description'),
            'label'       => 'Round border corners',
            'type'        => 'checkbox',
            'default'     => false,
            'section'     => ['global', 'other']
        ];

        $default_settings['header_align'] = [
            'title'       => __('settings-header-align-title'),
            'description' => '',
            'label'       => 'Header alignment',
            'type'        => 'select',
            'choices'     => [
                __('settings-header-align-left')           => 'left',
                __('settings-header-align-center-default') => 'center',
                __('settings-header-align-right')          => 'right'
            ],
            'default'     => 'center',
            'section'     => ['header']
        ];

        $default_settings['header_description'] = [
            'title'       => __('settings-header-description-title'),
            'description' => sprintf(
                __('settings-header-description-description'),
                App::backend()->url()->get('admin.blog.pref')
            ),
            'label'       => 'Blog description in header',
            'type'        => 'checkbox',
            'default'     => true,
            'section'     => ['header']
        ];

        $default_settings['header_post_full'] = [
            'title'       => __('settings-header-postfull-title'),
            'description' => __('settings-header-postfull-description'),
            'label'       => 'Full header on all blog posts',
            'type'        => 'checkbox',
            'default'     => false,
            'section'     => ['header']
        ];

        $default_settings['header_image'] = [
            'title'       => __('settings-header-image-title'),
            'description' => sprintf(
                __('settings-header-image-description'),
                My::id()
            ),
            'label'       => 'Header image URL',
            'type'        => 'image',
            'default'     => '',
            'section'     => ['header', 'image'],
            'sanitizer'   => 'sanitizeHeaderImage'
        ];

        $default_settings['header_image2x'] = [
            'title'       => __('settings-header-image2x-title'),
            'description' => __('settings-header-image2x-description'),
            'label'       => 'Header image URL for dual-pixel-density screens',
            'type'        => 'image',
            'default'     => '',
            'section'     => ['header', 'image'],
            'sanitizer'   => 'sanitizeHeaderImage'
        ];

        $default_settings['header_image_as_title'] = [
            'title'       => __('settings-header-imageastitle-title'),
            'description' => __('settings-header-imageastitle-description'),
            'label'       => 'Use header image as blog title',
            'type'        => 'checkbox',
            'default'     => false,
            'section'     => ['header', 'image']
        ];

        $default_settings['header_image_position'] = [
            'title'       => __('settings-header-imageposition-title'),
            'description' => '',
            'label'       => 'Header image position',
            'type'        => 'select',
            'choices'     => [
                __('settings-header-imageposition-top-default') => 'top',
                __('settings-header-imageposition-bottom')      => 'bottom'
            ],
            'default'     => 'top',
            'section'     => ['header', 'image']
        ];

        $default_settings['header_image_description'] = [
            'title'       => __('settings-header-imagedescription-title'),
            'description' => __('settings-header-imagedescription-description'),
            'label'       => 'Header image description',
            'type'        => 'text',
            'default'     => '',
            'section'     => ['header', 'image']
        ];

        $default_settings['content_postlist_type'] = [
            'title'       => __('settings-content-postlisttype-title'),
            'description' => '',
            'label'       => 'Post list appearance',
            'type'        => 'select',
            'choices'     => [
                __('settings-content-postlisttype-oneline-default') => 'one-line',
                __('settings-content-postlisttype-excerpt')         => 'excerpt',
                __('settings-content-postlisttype-content')         => 'content'
            ],
            'default'     => 'one-line',
            'section'     => ['content', 'postlist']
        ];

        $default_settings['content_postlist_altcolor'] = [
            'title'       => __('settings-content-postlistaltcolor-title'),
            'description' => __('settings-content-postlistaltcolor-description'),
            'label'       => 'Alternate post color on post list',
            'type'        => 'checkbox',
            'default'     => false,
            'section'     => ['content', 'postlist']
        ];

        $default_settings['content_postlist_thumbnail'] = [
            'title'       => __('settings-content-postlistthumbnail-title'),
            'description' => '',
            'label'       => 'Thumbnail on post list',
            'type'        => 'checkbox',
            'default'     => true,
            'section'     => ['content', 'postlist']
        ];

        $default_settings['content_postlist_reactions'] = [
            'title'       => __('settings-content-postlistreactions-title'),
            'description' => '',
            'label'       => 'Link to reactions',
            'type'        => 'checkbox',
            'default'     => false,
            'section'     => ['content', 'postlist']
        ];

        $default_settings['content_text_font'] = [
            'title'       => __('settings-content-fontfamily-title'),
            'description' => '',
            'label'       => 'Content font',
            'type'        => 'select',
            'choices'     => [
                __('settings-content-fontfamily-same-default')      => 'same',
                __('settings-global-fontfamily-system')             => 'system',
                __('settings-global-fontfamily-sansserif')          => 'sans-serif',
                __('settings-global-fontfamily-serif')              => 'serif',
                __('settings-global-fontfamily-transitional')       => 'transitional',
                __('settings-global-fontfamily-oldstyle')           => 'old-style',
                __('settings-global-fontfamily-garamond')           => 'garamond',
                __('settings-global-fontfamily-humanist')           => 'humanist',
                __('settings-global-fontfamily-geometrichumanist')  => 'geometric-humanist',
                __('settings-global-fontfamily-classicalhumanist')  => 'classical-humanist',
                __('settings-global-fontfamily-neogrotesque')       => 'neo-grotesque',
                __('settings-global-fontfamily-monospaceslabserif') => 'monospace-slab-serif',
                __('settings-global-fontfamily-monospacecode')      => 'monospace-code',
                __('settings-global-fontfamily-industrial')         => 'industrial',
                __('settings-global-fontfamily-roundedsans')        => 'rounded-sans',
                __('settings-global-fontfamily-slabserif')          => 'slab-serif',
                __('settings-global-fontfamily-antique')            => 'antique',
                __('settings-global-fontfamily-didone')             => 'didone',
                __('settings-global-fontfamily-handwritten')        => 'handwritten'
            ],
            'default'     => 'same',
            'section'     => ['content', 'text-formatting']
        ];

        $default_settings['content_font_size'] = [
            'title'       => __('settings-content-fontsize-title'),
            'description' => __('settings-content-fontsize-description'),
            'label'       => 'Content font size',
            'type'        => 'range',
            'range'       => [
                'min'  => 80,
                'max'  => 120,
                'step' => 10,
                'unit' => '%'
            ],
            'default'     => 100,
            'section'     => ['content', 'text-formatting']
        ];

        $default_settings['content_line_height'] = [
            'title'       => __('settings-content-lineheight-title'),
            'description' => '',
            'label'       => 'Text line spacing',
            'type'        => 'select_int',
            'choices'     => [
                __('settings-content-lineheight-small')            => 125,
                __('settings-content-lineheight-standard-default') => 150,
                __('settings-content-lineheight-big')              => 175
            ],
            'default'     => 150,
            'section'     => ['content', 'text-formatting']
        ];

        $default_settings['content_text_align'] = [
            'title'       => __('settings-content-textalign-title'),
            'description' => __('settings-content-textalign-description'),
            'label'       => 'Text content alignment',
            'type'        => 'select',
            'choices'     => [
                __('settings-content-textalign-left-default')       => 'left',
                __('settings-content-textalign-justify')            => 'justify',
                __('settings-content-textalign-justify-not-mobile') => 'justify-not-mobile'
            ],
            'default'     => 'left',
            'section'     => ['content', 'text-formatting']
        ];

        $default_settings['content_hyphens'] = [
            'title'       => __('settings-content-hyphens-title'),
            'description' => '',
            'label'       => 'Content hyphenation',
            'type'        => 'select',
            'choices'     => [
                __('settings-content-hyphens-disabled-default') => 'disabled',
                __('settings-content-hyphens-enabled')          => 'enabled',
                __('settings-content-hyphens-enablednotmobile') => 'enabled-not-mobile'
            ],
            'default'     => 'disabled',
            'section'     => ['content', 'text-formatting']
        ];

        $default_settings['content_initial_letter'] = [
            'title'       => __('settings-content-initialletter-title'),
            'description' => __('settings-content-initialletter-description'),
            'label'       => 'Drop caps',
            'type'        => 'checkbox',
            'default'     => false,
            'section'     => ['content', 'text-formatting']
        ];

        if (App::plugins()->moduleExists('userThumbSizes')) {
            $plugin_userthumbsizes_url = App::backend()->url()->get('admin.plugin.userThumbSizes');
        } else {
            $plugin_userthumbsizes_url = App::backend()->url()->get('admin.plugins', ['m_search' => 'userThumbSizes']) . '#new';
        }

        $default_settings['content_images_wide'] = [
            'title'       => __('settings-content-imageswide-title'),
            'description' => sprintf(
                __('settings-content-imageswide-description'),
                $plugin_userthumbsizes_url
            ),
            'label'       => 'Enlarge wide enough images',
            'type'        => 'checkbox',
            'default'     => false,
            'section'     => ['content', 'images']
        ];

        $default_settings['content_images_grayscale'] = [
            'title'       => __('settings-content-imagesgrayscale-title'),
            'description' => __('settings-content-imagesgrayscale-description'),
            'label'       => 'Black and white image mode',
            'type'        => 'checkbox',
            'default'     => false,
            'section'     => ['content', 'images']
        ];

        $default_settings['content_post_pagination'] = [
            'title'       => __('settings-content-postpagination-title'),
            'description' => __('settings-content-postpagination-description'),
            'label'       => 'Pagination below posts',
            'type'        => 'checkbox',
            'default'     => true,
            'section'     => ['content', 'pagination']
        ];

        $default_settings['reactions_button'] = [
            'title'       => __('settings-reactions-button-title'),
            'description' => __('settings-reactions-button-description'),
            'label'       => 'Comment form by clicking',
            'type'        => 'checkbox',
            'default'     => true,
            'section'     => ['reactions', 'form']
        ];

        if (App::plugins()->moduleExists('legacyMarkdown')) {
            $default_settings['reactions_markdown_notice'] = [
                'title'       => __('settings-reactions-markdownnotice-title'),
                'description' => '',
                'label'       => 'Message about Markdown support in comments',
                'type'        => 'checkbox',
                'default'     => false,
                'section'     => ['reactions', 'form']
            ];
        }

        $default_settings['reactions_other_trackbacks'] = [
            'title'       => __('settings-reactions-other-trackbacks-title'),
            'description' => '',
            'label'       => 'Link below posts to add trackbacks',
            'type'        => 'checkbox',
            'default'     => false,
            'section'     => ['reactions', 'trackbacks']
        ];

        $default_settings['reactions_feed_link'] = [
            'title'       => __('settings-reactions-feedlink-title'),
            'description' => '',
            'label'       => 'Link to RSS/Atom of reactions',
            'type'        => 'select',
            'choices'     => [
                __('settings-reactions-feedlink-disabled-default') => 'disabled',
                __('settings-reactions-feedlink-atom')             => 'atom',
                __('settings-reactions-feedlink-rss2')             => 'rss2'
            ],
            'default'     => 'disabled',
            'section'     => ['reactions', 'feed']
        ];

        $default_settings['reactions_other'] = [
            'title'       => __('settings-reactions-other-title'),
            'description' => __('settings-reactions-other-description'),
            'label'       => 'Other reaction methods',
            'type'        => 'select',
            'choices'     => [
                __('settings-reactions-other-disabled-default') => 'disabled',
                __('settings-reactions-other-commentsopen')     => 'comments_open',
                __('settings-reactions-other-always')           => 'always'
            ],
            'default'     => 'disabled',
            'section'     => ['reactions', 'other']
        ];

        if (App::plugins()->moduleExists('signal')) {
            $plugin_signal_url = App::backend()->url()->get('admin.blog.pref') . '#params.signal';
        } else {
            $plugin_signal_url = App::backend()->url()->get('admin.plugins', ['m_search' => 'signal']) . '#new';
        }

        $default_settings['reactions_other_email'] = [
            'title'       => __('settings-reactions-otheremail-title'),
            'description' => sprintf(__('settings-reactions-otheremail-description'), $plugin_signal_url),
            'label'       => 'Replies to posts by e-mail',
            'type'        => 'checkbox',
            'default'     => false,
            'section'     => ['reactions', 'other']
        ];

        foreach (self::socialSites() as $site => $base) {
            if (isset($base['reactions']) && $base['reactions'] === true) {
                $default_settings['reactions_other_' . $site] = [
                    'title'       => sprintf(
                        __('settings-reactions-other-' . $site . '-title'),
                        $base['name']
                    ),
                    'description' => __('settings-reactions-other-' . $site . '-description'),
                    'label'       => 'Post sharing (' . $site . ')',
                    'type'        => 'checkbox',
                    'default'     => false,
                    'section'     => ['reactions', 'other']
                ];
            }
        }

        if (App::plugins()->moduleExists('widgets')) {
            $default_settings['widgets_display'] = [
                'title'       => __('settings-widgets-display-title'),
                'description' => sprintf(
                    __('settings-widgets-display-description'),
                    App::backend()->url()->get('admin.plugin.widgets')
                ),
                'label'       => 'Widgets display',
                'type'        => 'checkbox',
                'default'     => true,
                'section'     => ['widgets']
            ];
        }

        $default_settings['footer_enabled'] = [
            'title'       => __('settings-footer-activation-title'),
            'description' => __('settings-footer-activation-description'),
            'label'       => 'Footer display',
            'type'        => 'checkbox',
            'default'     => true,
            'section'     => ['footer']
        ];

        $default_settings['footer_align'] = [
            'title'       => __('settings-footer-align-title'),
            'description' => '',
            'label'       => 'Footer alignment',
            'type'        => 'select',
            'choices'     => [
                __('settings-footer-align-left-default') => 'left',
                __('settings-footer-align-center')       => 'center',
                __('settings-footer-align-right')        => 'right'
            ],
            'default'     => 'left',
            'section'     => ['footer']
        ];

        $default_settings['footer_credits'] = [
            'title'       => __('settings-footer-credits-title'),
            'description' => __('settings-footer-credits-description'),
            'label'       => 'Mention to Dotclear in the footer',
            'type'        => 'checkbox',
            'default'     => true,
            'section'     => ['footer']
        ];

        foreach (self::socialSites() as $site => $base) {
            // Provides a description for some sites only.
            $add_description = ['phone', 'signal', 'sms', 'whatsapp'];

            $social_description = '';

            if (in_array($site, $add_description, true)) {
                $social_description =  sprintf(
                    __('settings-footer-social-' . $site . '-description'),
                    $base['name']
                );
            }

            $default_settings['footer_social_' . $site] = [
                'title'       => sprintf(__('settings-footer-social-' . $site . '-title'), $base['name']),
                'description' => $social_description,
                'label'       => 'Social link in footer parameter (' . $site . ')',
                'type'        => 'checkbox',
                'default'     => true,
                'section'     => ['footer', 'social']
            ];
        }

        foreach (self::socialSites() as $site => $base) {
            $default_settings['social_' . $site] = [
                'title'       => __('social-site-' . $site),
                'description' => sprintf(__('settings-social-' . $site . '-description'), $base['name']),
                'label'       => 'Social link parameter (' . $site . ')',
                'type'        => 'text',
                'default'     => '',
                'section'     => ['social'],
                'sanitizer'   => 'sanitizeSocial'
            ];
        }

        $default_settings['footer_feed'] = [
            'title'       => __('settings-footer-feed-title'),
            'description' => '',
            'label'       => 'Link in the footer to the RSS/Atom feed',
            'type'        => 'select',
            'choices'     => [
                __('settings-footer-feed-disabled-default') => 'disabled',
                __('settings-footer-feed-atom')             => 'atom',
                __('settings-footer-feed-rss2')             => 'rss2'
            ],
            'default'     => 'disabled',
            'section'     => ['footer', 'social']
        ];

        $default_settings['styles_custom'] = [
            'title'       => __('settings-styles-custom-title'),
            'description' => __('settings-styles-custom-description'),
            'label'       => 'Custom styles',
            'type'        => 'textarea',
            'default'     => '',
            'placeholder' => '.' . __('settings-styles-custom-placeholder-selector') . ' {' . "\n" . '  ' . __('settings-styles-custom-placeholder-property') . ': ' . __('settings-styles-custom-placeholder-value') . ';' . "\n" . '}',
            'sanitizer'   => 'sanitizeCSS',
            'section'     => ['advanced', 'appearance']
        ];

        $default_settings['advanced_meta_description'] = [
            'title'       => __('settings-advanced-metadescription-title'),
            'description' => __('settings-advanced-metadescription-description'),
            'label'       => 'Description tag of the home page',
            'type'        => 'textarea',
            'default'     => '',
            'section'     => ['advanced', 'seo']
        ];

        $default_settings['advanced_meta_social'] = [
            'title'       => __('settings-advanced-metasocial-title'),
            'description' => __('settings-advanced-metasocial-description'),
            'label'       => 'Minimalist social tags',
            'type'        => 'checkbox',
            'default'     => false,
            'section'     => ['advanced', 'seo']
        ];

        $default_settings['advanced_canonical'] = [
            'title'       => __('settings-advanced-canonical-title'),
            'description' => __('settings-advanced-canonical-description'),
            'label'       => 'Canonical URLs',
            'type'        => 'checkbox',
            'default'     => false,
            'section'     => ['advanced', 'seo']
        ];

        $default_settings['advanced_json'] = [
            'title'       => __('settings-advanced-json-title'),
            'description' => __('settings-advanced-json-description'),
            'label'       => 'Metadata in JSON format',
            'type'        => 'checkbox',
            'default'     => false,
            'section'     => ['advanced', 'seo']
        ];

        $default_settings['advanced_js_util'] = [
            'title'       => __('settings-advanced-jsutil-title'),
            'description' => '',
            'label'       => 'Load util.js',
            'type'        => 'checkbox',
            'default'     => true,
            'section'     => ['advanced', 'js']
        ];

        $default_settings['styles'] = [
            'title'     => __('settings-odysseystyles-title'),
            'label'     => 'Styles based on configurator options',
            'sanitizer' => 'sanitizeStyles'
        ];

        if ($setting_id) {
            return $default_settings[$setting_id] ?? [];
        }

        return $default_settings;
    }

    /**
     * Wraps a string in quotes if it contains a least one space,
     * and escapes HTML inside it.
     *
     * Avoids unnecessarily quotation marks wrapping attributes.
     *
     * @param string $value      The value.
     * @param string $input_type The input type ('html' or 'url').
     *
     * @return string The string.
     */
    public static function displayAttr(string $value, ?string $input_type = null): string
    {
        if ($input_type === 'html') {
            $value = Html::escapeHTML($value);
        } elseif ($input_type === 'url') {
            $value = self::escapeURL($value);
        }

        if (!str_contains($value, ' ')
            && !str_contains($value, '=')
            && !str_contains($value, '"')
            && !str_contains($value, '&quot;')
        ) {
            return $value;
        }

        return '"' . $value . '"';
    }

    /**
     * Escapes URLs.
     *
     * @param string $url  The URL to escape.
     * @param bool   $attr true if the escaped URL should be displayed
     *                     in an HTML attribute.
     *
     * @return string The escaped URL.
     */
    public static function escapeURL(string $url, bool $attr = false): string
    {
        $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

        return $attr ? self::displayAttr($url, 'url') : $url;
    }

    /**
     * Cleans a string to be displayed.
     *
     * @param string $string       The string.
     * @param array  $tags_allowed A list of allowed tags (optional).
     *
     * @return string The cleaned string.
     */
    public static function cleanStr(string $string, array $tags_allowed = []): string
    {
        $string = empty($tags_allowed) ? strip_tags($string) : strip_tags($string, $tags_allowed);
        $string = Html::decodeEntities($string);

        return preg_replace('/\s+/mu', ' ', $string);
    }

    /**
     * Returns the content width of the blog in an array.
     *
     * @param string $unit Must be 'em' or 'px'.
     *
     * @return array The unit and the value of the width.
     */
    public static function getContentWidth($unit = 'em'): array
    {
        $page_width_unit  = My::settings()->global_unit ?: 'em' ;
        $page_width_value = (int) My::settings()->global_page_width_value ?: 30;

        if ($page_width_unit !== $unit) {
            if ($unit === 'px') {
                $page_width_unit  = 'px';
                $page_width_value = $page_width_value * 16;
            } else {
                $page_width_unit  = 'em';
                $page_width_value = $page_width_value / 16;
            }
        }

        return [
            'unit'  => $page_width_unit,
            'value' => $page_width_value
        ];
    }

    /**
     * Returns the odyssey public folder absolute path or relative URL.
     *
     * @param string $type        "path" or "url".
     * @param string $concatenate A string to be added at the end of the path or URL.
     *
     * @return string The path or URL.
     */
    public static function publicFolder(string $type, string $to_concatenate = ''): string
    {
        switch ($type) {
            case 'url':
                return App::blog()->settings()->system->public_url . '/' . self::id() . $to_concatenate;
            case 'path':
                return App::blog()->publicPath() . '/' . self::id() . $to_concatenate;
        }

        return '';
    }

    /**
     * Returns the var folder absolute path or relative URL.
     *
     * @param string $type        "path" or "vf".
     * @param string $concatenate A string to be added at the end of the path or URL.
     *
     * @return string The path or URL.
     */
    public static function varFolder(string $type, string $to_concatenate = ''): string
    {
        switch ($type) {
            case 'vf':
                return self::id() . $to_concatenate;
            case 'path':
                return App::config()->varRoot() . '/' . self::id() . $to_concatenate;
        }

        return '';
    }

    /**
     * Returns the relative path or URI to the theme
     * or to a file located inside the theme folder.
     *
     * @param string $type           The type of pathway (url or path).
     * @param string $to_concatenate A string to be added at the end of the path or URL.
     *
     * @return string The path.
     */
    public static function themeFolder(string $type, string $to_concatenate = ''): string
    {
        switch ($type) {
            case 'url':
                return App::blog()->settings()->system->themes_url . '/' . My::id() . $to_concatenate;
            case 'path':
                return App::blog()->themesPath() . '/' . My::id() . $to_concatenate;
        }

        return '';
    }

    /**
     * Converts styles to a string from an array.
     *
     * @param array $rules An array of styles.
     *
     * @return string $css The minified styles.
     */
    public static function stylesArrToStr(array $rules): string
    {
        $css = '';

        foreach ($rules as $selector => $properties) {
            $selector = $selector ?: null;

            if ($selector && is_array($properties) && !empty($properties)) {
                $css .= str_replace(', ', ',', $selector);
                $css .= '{';

                foreach ($properties as $property => $rule) {
                    if ($rule !== '') {
                        $css .= $property . ':';
                        $css .= str_replace(', ', ',', $rule) . ';';
                    }
                }

                $css .= '}';
            }
        }

        return $css;
    }

    /**
     * Removes 0 before decimal separator of numbers inferior to 1.
     *
     * @param $number The number.
     *
     * @return string The cleaned number.
     */
    public static function removeZero($number): string
    {
        $number = strval($number);

        if (str_starts_with($number, '0.')) {
            return substr($number, 1);
        }

        return $number;
    }

    /**
     * Checks if a file path points to a valid image.
     *
     * @param string $path The path to the image.
     *
     * @return bool true if the image exists.
     */
    public static function imageExists(string $path): bool
    {
        $mime_types_supported = Files::mimeTypes();
        $mime_type            = Files::getMimeType($path);
        $file_extension       = strtolower(Files::getExtension($path));

        // Returns true if the file exists and is an allowed type of image.
        if (file_exists($path)
            && str_starts_with($mime_type, 'image')
            && array_key_exists($file_extension, $mime_types_supported)
            && in_array($mime_type, $mime_types_supported, true)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the input is an Hex color code.
     *
     * @param string $color The Hex color code.
     *
     * @return bool
     */
    public static function isHexColor(string $color): bool
    {
        if (preg_match('/#[A-Fa-f0-9]{6}/', $color)) {
            return true;
        }

        return false;
    }

    /**
     * Returns the fonts to be used as styles in CSS
     *
     * @param string $fontname The name of the font to return the CSS rule (optional).
     *
     * @return string The font rule.
     */
    public static function fontStack(string $fontname = ''): string
    {
        if ($fontname === '') {
            return '';
        }

        $emoji = ', "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"';

        switch ($fontname) {
            case 'system' :
                return 'system-ui, ui-sans-serif, sans-serif' . $emoji;
            case 'sans-serif' :
                return 'ui-sans-serif, sans-serif' . $emoji;
            case 'serif' :
                return 'ui-serif, serif' . $emoji;
            case 'transitional' :
                return 'Charter, "Bitstream Charter", "Sitka Text", Cambria, serif' . $emoji;
            case 'old-style' :
                return '"Iowan Old Style", "Palatino Linotype", "URW Palladio L", P052, serif' . $emoji;
            case 'garamond' :
                return 'Garamond, Baskerville, "Baskerville Old Face", "Hoefler Text", "Times New Roman", serif' . $emoji;
            case 'humanist' :
                return 'Seravek, "Gill Sans Nova", Ubuntu, Calibri, "DejaVu Sans", source-sans-pro, sans-serif' . $emoji;
            case 'geometric-humanist' :
                return 'Avenir, Montserrat, Corbel, "URW Gothic", source-sans-pro, sans-serif' . $emoji;
            case 'classical-humanist' :
                return 'Optima, Candara, "Noto Sans", source-sans-pro, sans-serif' . $emoji;
            case 'neo-grotesque' :
                return 'Inter, Roboto, "Helvetica Neue", "Arial Nova", "Nimbus Sans", Arial, sans-serif' . $emoji;
            case 'monospace-slab-serif' :
                return '"Nimbus Mono PS", "Courier New", monospace' . $emoji;
            case 'monospace-code' :
                return 'ui-monospace, "Cascadia Code", "Source Code Pro", Menlo, Consolas, "DejaVu Sans Mono", monospace' . $emoji;
            case 'industrial' :
                return 'Bahnschrift, "DIN Alternate", "Franklin Gothic Medium", "Nimbus Sans Narrow", sans-serif-condensed, sans-serif' . $emoji;
            case 'rounded-sans' :
                return 'ui-rounded, "Hiragino Maru Gothic ProN", Quicksand, Comfortaa, Manjari, "Arial Rounded MT", "Arial Rounded MT Bold", Calibri, source-sans-pro, sans-serif' . $emoji;
            case 'slab-serif' :
                return 'Rockwell, "Rockwell Nova", "Roboto Slab", "DejaVu Serif", "Sitka Small", serif' . $emoji;
            case 'antique' :
                return 'Superclarendon, "Bookman Old Style", "URW Bookman", "URW Bookman L", "Georgia Pro", Georgia, serif' . $emoji;
            case 'didone' :
                return 'Didot, "Bodoni MT", "Noto Serif Display", "URW Palladio L", P052, Sylfaen, serif' . $emoji;
            case 'handwritten' :
                return '"Segoe Print", "Bradley Hand", Chilanka, TSCu_Comic, casual, cursive' . $emoji;
            default :
                return 'system-ui, ui-sans-serif, sans-serif' . $emoji;
        }
    }

    /**
     * Defines the social sites supported by the theme.
     *
     * $social_sites['site_id'] = [
     *      'name'      => string 'The name of the site',
     *      'base'      => string 'If type is "url", the base of the URL
     *                            of the site',
     *      'type'      => string 'The type of value (URL, phone number…)',
     *      'reactions' => bool   '"true" if the site can be used
     *                            as another reaction method for posts'
     * ];
     *
     * @param string $site_id The ID of a social site to retrieve the data.
     *
     * @return mixed A social site information or an array of all the social sites.
     */
    public static function socialSites(string $site_id = ''): mixed
    {
        $social_sites = [];

        $social_sites['bluesky'] = [
            'name'      => __('social-site-bluesky'),
            'base'      => 'https://bsky.app/',
            'type'      => 'url',
            'reactions' => true
        ];

        $social_sites['diaspora'] = [
            'name' => __('social-site-diaspora'),
            'type' => 'url'
        ];

        $social_sites['email'] = [
            'name' => __('social-site-email'),
            'type' => 'email'
        ];

        $social_sites['facebook'] = [
            'name'      => __('social-site-facebook'),
            'base'      => 'https://www.facebook.com/',
            'type'      => 'url',
            'reactions' => true
        ];

        $social_sites['instagram'] = [
            'name' => __('social-site-instagram'),
            'base' => 'https://www.instagram.com/',
            'type' => 'url'
        ];

        $social_sites['mastodon'] = [
            'name'      => __('social-site-mastodon'),
            'type'      => 'url',
            'reactions' => true
        ];

        $social_sites['matrix'] = [
            'name'      => __('social-site-matrix'),
            'type'      => 'matrix',
        ];

        $social_sites['phone'] = [
            'name' => __('social-site-phone'),
            'type' => 'phone-number'
        ];

        $social_sites['signal'] = [
            'name'      => __('social-site-signal'),
            'type'      => 'signal',
            'reactions' => true
        ];

        $social_sites['sms'] = [
            'name'      => __('social-site-sms'),
            'type'      => 'phone-number',
            'reactions' => true
        ];

        $social_sites['youtube'] = [
            'name' => __('social-site-youtube'),
            'base' => 'https://www.youtube.com/',
            'type' => 'url'
        ];

        $social_sites['whatsapp'] = [
            'name'      => __('social-site-whatsapp'),
            'type'      => 'whatsapp',
            'reactions' => true
        ];

        $social_sites['x'] = [
            'name'      => __('social-site-x'),
            'type'      => 'x',
            'reactions' => true
        ];

        $social_sites['other'] = [
            'name' => __('social-site-other'),
            'type' => 'url'
        ];

        if ($site_id && array_key_exists($site_id, $social_sites)) {
            return $social_sites[$site_id];
        }

        return $social_sites;
    }

    /**
     * Gets SVG info of a social site to display its icon.
     *
     * @param string $id The social site id.
     *
     * @return array The SVG info.
     *
     * @link https://simpleicons.org/
     * @link https://feathericons.com/
     */
    public static function svgIcons(string $id = ''): array
    {
        $icons = [];

        $icons['comment'] = [
            'path'    => '<polyline points="9 14 4 9 9 4" /><path d="M20 20v-7a4 4 0 0 0-4-4H4" />',
            'creator' => 'feathericons'
        ];

        $icons['bluesky'] = [
            'path'    => '<path d="M12 10.8c-1.087-2.114-4.046-6.053-6.798-7.995C2.566.944 1.561 1.266.902 1.565.139 1.908 0 3.08 0 3.768c0 .69.378 5.65.624 6.479.815 2.736 3.713 3.66 6.383 3.364.136-.02.275-.039.415-.056-.138.022-.276.04-.415.056-3.912.58-7.387 2.005-2.83 7.078 5.013 5.19 6.87-1.113 7.823-4.308.953 3.195 2.05 9.271 7.733 4.308 4.267-4.308 1.172-6.498-2.74-7.078a8.741 8.741 0 0 1-.415-.056c.14.017.279.036.415.056 2.67.297 5.568-.628 6.383-3.364.246-.828.624-5.79.624-6.478 0-.69-.139-1.861-.902-2.206-.659-.298-1.664-.62-4.3 1.24C16.046 4.748 13.087 8.687 12 10.8Z"/>',
            'creator' => 'simpleicons'
        ];

        $icons['diaspora'] = [
            'path'    => '<path d="M15.257 21.928l-2.33-3.255c-.622-.87-1.128-1.549-1.155-1.55-.027 0-1.007 1.317-2.317 3.115-1.248 1.713-2.28 3.115-2.292 3.115-.035 0-4.5-3.145-4.51-3.178-.006-.016 1.003-1.497 2.242-3.292 1.239-1.794 2.252-3.29 2.252-3.325 0-.056-.401-.197-3.55-1.247a1604.93 1604.93 0 0 1-3.593-1.2c-.033-.013.153-.635.79-2.648.46-1.446.845-2.642.857-2.656.013-.015 1.71.528 3.772 1.207 2.062.678 3.766 1.233 3.787 1.233.021 0 .045-.032.053-.07.008-.039.026-1.794.04-3.902.013-2.107.036-3.848.05-3.87.02-.03.599-.038 2.725-.038 1.485 0 2.716.01 2.735.023.023.016.064 1.175.132 3.776.112 4.273.115 4.33.183 4.33.026 0 1.66-.547 3.631-1.216 1.97-.668 3.593-1.204 3.605-1.191.04.045 1.656 5.307 1.636 5.327-.011.01-1.656.574-3.655 1.252-2.75.932-3.638 1.244-3.645 1.284-.006.029.94 1.442 2.143 3.202 1.184 1.733 2.148 3.164 2.143 3.18-.012.036-4.442 3.299-4.48 3.299-.015 0-.577-.767-1.249-1.705z" />',
            'creator' => 'simpleicons'
        ];

        $icons['email'] = [
            'path'    => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" /><polyline points="22,6 12,13 2,6" />',
            'creator' => 'feathericons'
        ];

        $icons['facebook'] = [
            'path'    => '<path d="M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 0 1 1.141.195v3.325a8.623 8.623 0 0 0-.653-.036 26.805 26.805 0 0 0-.733-.009c-.707 0-1.259.096-1.675.309a1.686 1.686 0 0 0-.679.622c-.258.42-.374.995-.374 1.752v1.297h3.919l-.386 2.103-.287 1.564h-3.246v8.245C19.396 23.238 24 18.179 24 12.044c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.628 3.874 10.35 9.101 11.647Z" />',
            'creator' => 'simpleicons'
        ];

        $icons['feed'] = [
            'path'    => '<path d="M4 11a9 9 0 0 1 9 9" /><path d="M4 4a16 16 0 0 1 16 16" /><circle cx="5" cy="19" r="1" />',
            'creator' => 'feathericons'
        ];

        $icons['instagram'] = [
            'path'    => '<path d="M7.0301.084c-1.2768.0602-2.1487.264-2.911.5634-.7888.3075-1.4575.72-2.1228 1.3877-.6652.6677-1.075 1.3368-1.3802 2.127-.2954.7638-.4956 1.6365-.552 2.914-.0564 1.2775-.0689 1.6882-.0626 4.947.0062 3.2586.0206 3.6671.0825 4.9473.061 1.2765.264 2.1482.5635 2.9107.308.7889.72 1.4573 1.388 2.1228.6679.6655 1.3365 1.0743 2.1285 1.38.7632.295 1.6361.4961 2.9134.552 1.2773.056 1.6884.069 4.9462.0627 3.2578-.0062 3.668-.0207 4.9478-.0814 1.28-.0607 2.147-.2652 2.9098-.5633.7889-.3086 1.4578-.72 2.1228-1.3881.665-.6682 1.0745-1.3378 1.3795-2.1284.2957-.7632.4966-1.636.552-2.9124.056-1.2809.0692-1.6898.063-4.948-.0063-3.2583-.021-3.6668-.0817-4.9465-.0607-1.2797-.264-2.1487-.5633-2.9117-.3084-.7889-.72-1.4568-1.3876-2.1228C21.2982 1.33 20.628.9208 19.8378.6165 19.074.321 18.2017.1197 16.9244.0645 15.6471.0093 15.236-.005 11.977.0014 8.718.0076 8.31.0215 7.0301.0839m.1402 21.6932c-1.17-.0509-1.8053-.2453-2.2287-.408-.5606-.216-.96-.4771-1.3819-.895-.422-.4178-.6811-.8186-.9-1.378-.1644-.4234-.3624-1.058-.4171-2.228-.0595-1.2645-.072-1.6442-.079-4.848-.007-3.2037.0053-3.583.0607-4.848.05-1.169.2456-1.805.408-2.2282.216-.5613.4762-.96.895-1.3816.4188-.4217.8184-.6814 1.3783-.9003.423-.1651 1.0575-.3614 2.227-.4171 1.2655-.06 1.6447-.072 4.848-.079 3.2033-.007 3.5835.005 4.8495.0608 1.169.0508 1.8053.2445 2.228.408.5608.216.96.4754 1.3816.895.4217.4194.6816.8176.9005 1.3787.1653.4217.3617 1.056.4169 2.2263.0602 1.2655.0739 1.645.0796 4.848.0058 3.203-.0055 3.5834-.061 4.848-.051 1.17-.245 1.8055-.408 2.2294-.216.5604-.4763.96-.8954 1.3814-.419.4215-.8181.6811-1.3783.9-.4224.1649-1.0577.3617-2.2262.4174-1.2656.0595-1.6448.072-4.8493.079-3.2045.007-3.5825-.006-4.848-.0608M16.953 5.5864A1.44 1.44 0 1 0 18.39 4.144a1.44 1.44 0 0 0-1.437 1.4424M5.8385 12.012c.0067 3.4032 2.7706 6.1557 6.173 6.1493 3.4026-.0065 6.157-2.7701 6.1506-6.1733-.0065-3.4032-2.771-6.1565-6.174-6.1498-3.403.0067-6.156 2.771-6.1496 6.1738M8 12.0077a4 4 0 1 1 4.008 3.9921A3.9996 3.9996 0 0 1 8 12.0077" />',
            'creator' => 'simpleicons'
        ];

        $icons['mastodon'] = [
            'path'    => '<path d="M23.268 5.313c-.35-2.578-2.617-4.61-5.304-5.004C17.51.242 15.792 0 11.813 0h-.03c-3.98 0-4.835.242-5.288.309C3.882.692 1.496 2.518.917 5.127.64 6.412.61 7.837.661 9.143c.074 1.874.088 3.745.26 5.611.118 1.24.325 2.47.62 3.68.55 2.237 2.777 4.098 4.96 4.857 2.336.792 4.849.923 7.256.38.265-.061.527-.132.786-.213.585-.184 1.27-.39 1.774-.753a.057.057 0 0 0 .023-.043v-1.809a.052.052 0 0 0-.02-.041.053.053 0 0 0-.046-.01 20.282 20.282 0 0 1-4.709.545c-2.73 0-3.463-1.284-3.674-1.818a5.593 5.593 0 0 1-.319-1.433.053.053 0 0 1 .066-.054c1.517.363 3.072.546 4.632.546.376 0 .75 0 1.125-.01 1.57-.044 3.224-.124 4.768-.422.038-.008.077-.015.11-.024 2.435-.464 4.753-1.92 4.989-5.604.008-.145.03-1.52.03-1.67.002-.512.167-3.63-.024-5.545zm-3.748 9.195h-2.561V8.29c0-1.309-.55-1.976-1.67-1.976-1.23 0-1.846.79-1.846 2.35v3.403h-2.546V8.663c0-1.56-.617-2.35-1.848-2.35-1.112 0-1.668.668-1.67 1.977v6.218H4.822V8.102c0-1.31.337-2.35 1.011-3.12.696-.77 1.608-1.164 2.74-1.164 1.311 0 2.302.5 2.962 1.498l.638 1.06.638-1.06c.66-.999 1.65-1.498 2.96-1.498 1.13 0 2.043.395 2.74 1.164.675.77 1.012 1.81 1.012 3.12z" />',
            'creator' => 'simpleicons'
        ];

        $icons['matrix'] = [
            'path'    => '<path d="M.632.55v22.9H2.28V24H0V0h2.28v.55zm7.043 7.26v1.157h.033c.309-.443.683-.784 1.117-1.024.433-.245.936-.365 1.5-.365.54 0 1.033.107 1.481.314.448.208.785.582 1.02 1.108.254-.374.6-.706 1.034-.992.434-.287.95-.43 1.546-.43.453 0 .872.056 1.26.167.388.11.716.286.993.53.276.245.489.559.646.951.152.392.23.863.23 1.417v5.728h-2.349V11.52c0-.286-.01-.559-.032-.812a1.755 1.755 0 0 0-.18-.66 1.106 1.106 0 0 0-.438-.448c-.194-.11-.457-.166-.785-.166-.332 0-.6.064-.803.189a1.38 1.38 0 0 0-.48.499 1.946 1.946 0 0 0-.231.696 5.56 5.56 0 0 0-.06.785v4.768h-2.35v-4.8c0-.254-.004-.503-.018-.752a2.074 2.074 0 0 0-.143-.688 1.052 1.052 0 0 0-.415-.503c-.194-.125-.476-.19-.854-.19-.111 0-.259.024-.439.074-.18.051-.36.143-.53.282-.171.138-.319.337-.439.595-.12.259-.18.6-.18 1.02v4.966H5.46V7.81zm15.693 15.64V.55H21.72V0H24v24h-2.28v-.55z" />',
            'creator' => 'simpleicons'
        ];

        $icons['phone'] = [
            'path'    => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />',
            'creator' => 'feathericons'
        ];

        $icons['signal'] = [
            'path'    => '<path d="m9.12.35.27 1.09a10.845 10.845 0 0 0-3.015 1.248l-.578-.964A11.955 11.955 0 0 1 9.12.35zm5.76 0-.27 1.09a10.845 10.845 0 0 1 3.015 1.248l.581-.964A11.955 11.955 0 0 0 14.88.35zM1.725 5.797A11.955 11.955 0 0 0 .351 9.119l1.09.27A10.845 10.845 0 0 1 2.69 6.374zm-.6 6.202a10.856 10.856 0 0 1 .122-1.63l-1.112-.168a12.043 12.043 0 0 0 0 3.596l1.112-.169A10.856 10.856 0 0 1 1.125 12zm17.078 10.275-.578-.964a10.845 10.845 0 0 1-3.011 1.247l.27 1.091a11.955 11.955 0 0 0 3.319-1.374zM22.875 12a10.856 10.856 0 0 1-.122 1.63l1.112.168a12.043 12.043 0 0 0 0-3.596l-1.112.169a10.856 10.856 0 0 1 .122 1.63zm.774 2.88-1.09-.27a10.845 10.845 0 0 1-1.248 3.015l.964.581a11.955 11.955 0 0 0 1.374-3.326zm-10.02 7.875a10.952 10.952 0 0 1-3.258 0l-.17 1.112a12.043 12.043 0 0 0 3.597 0zm7.125-4.303a10.914 10.914 0 0 1-2.304 2.302l.668.906a12.019 12.019 0 0 0 2.542-2.535zM18.45 3.245a10.914 10.914 0 0 1 2.304 2.304l.906-.675a12.019 12.019 0 0 0-2.535-2.535zM3.246 5.549A10.914 10.914 0 0 1 5.55 3.245l-.675-.906A12.019 12.019 0 0 0 2.34 4.874zm19.029.248-.964.577a10.845 10.845 0 0 1 1.247 3.011l1.091-.27a11.955 11.955 0 0 0-1.374-3.318zM10.371 1.246a10.952 10.952 0 0 1 3.258 0L13.8.134a12.043 12.043 0 0 0-3.597 0zM3.823 21.957 1.5 22.5l.542-2.323-1.095-.257-.542 2.323a1.125 1.125 0 0 0 1.352 1.352l2.321-.532zm-2.642-3.041 1.095.255.375-1.61a10.828 10.828 0 0 1-1.21-2.952l-1.09.27a11.91 11.91 0 0 0 1.106 2.852zm5.25 2.437-1.61.375.255 1.095 1.185-.275a11.91 11.91 0 0 0 2.851 1.106l.27-1.091a10.828 10.828 0 0 1-2.943-1.217zM12 2.25a9.75 9.75 0 0 0-8.25 14.938l-.938 4 4-.938A9.75 9.75 0 1 0 12 2.25z" />',
            'creator' => 'simpleicons'
        ];

        $icons['sms'] = [
            'path'    => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />',
            'creator' => 'feathericons'
        ];

        $icons['trackback'] = [
            'path'    => '<polyline points="17 1 21 5 17 9" /><path d="M3 11V9a4 4 0 0 1 4-4h14" /><polyline points="7 23 3 19 7 15" /><path d="M21 13v2a4 4 0 0 1-4 4H3" />',
            'creator' => 'feathericons'
        ];

        $icons['youtube'] = [
            'path'    => '<path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />',
            'creator' => 'simpleicons'
        ];

        $icons['whatsapp'] = [
            'path'    => '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />',
            'creator' => 'simpleicons'
        ];

        $icons['x'] = [
            'path'    => '<path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z" />',
            'creator' => 'simpleicons'
        ];

        $icons['other'] = [
            'path'    => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />',
            'creator' => 'feathericons'
        ];

        if ($id && isset($icons[$id])) {
            return $icons[$id];
        }

        return [];
    }

    /**
     * Validates JSON.
     *
     * @param string $string The JSON input.
     *
     * @return bool
     */
    public static function jsonValidate(string $string): bool
    {
        if (version_compare(App::config()->release('php_min'), '8.3', '>=')) {
            // If the required PHP version for Dotclear is at least 8.3.
            return json_validate($string);
        }

        json_decode($string);

        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Displays Dotclear version number.
     *
     * @param bool $short true to display a short version number
     *                    without the date of release (for testing versions).
     *
     * @return string The version number.
     */
    public static function dotclearVersion(bool $short = false): string
    {
        $dc_version = App::config()->dotclearVersion();

        if ($short === true) {
            $dc_version = explode('-', $dc_version)[0] ?? $dc_version;
        }

        return $dc_version;
    }

    /**
     * Checks if Dotclear version is superior or equal to a specific version number.
     *
     * @param string $version The minimum version.
     *
     * @return bool true if Doclear version is superior or equal to the version number passed.
     */
    public static function dotclearVersionMimimum(string $version): bool
    {
        if (version_compare(self::dotclearVersion(true), $version, '>=')) {
            return true;
        }

        return false;
    }
}
