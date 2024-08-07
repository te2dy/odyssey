<?php
/**
 * Odyssey, a customizable Dotclear theme.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2024 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

$this->registerModule(
    'Odyssey',
    'Write your own with this theme',
    'Teddy',
    '0.3',
    [
        'requires' => [
            ['core', '2.30'],
            ['php', '8']
        ],

        'type' => 'theme',

        // Allows a full control for the theme configurator.
        'standalone_config' => true,

        // Replaces default heading levels.
        'widgettitleformat'    => '<h3>%s</h3>',
        'widgetsubtitleformat' => '<h4>%s</h4>'
    ]
);
