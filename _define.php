<?php
/**
 * Odyssey, a simple and customizable Dotclear theme.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2025 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

$this->registerModule(
    'Odyssey',
    'A simple and customizable theme to write your own odyssey',
    'Teddy',
    '1.12',
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
