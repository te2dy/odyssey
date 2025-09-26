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
    '2.11',
    [
        'requires' => [
            ['core', '2.30'],
            ['php', '8']
        ],

        'type' => 'theme',

        // The forum is offline:
        // 'support'    => 'https://forum.dotclear.org/viewtopic.php?id=51635',
        'details'    => 'https://themes.dotaddict.org/galerie-dc2/details/odyssey',
        'repository' => 'https://raw.githubusercontent.com/te2dy/odyssey/main/dcstore.xml',

        // Allows theme files edit.
        'overload' => true,

        // Allows a full control for the theme configurator.
        'standalone_config' => true,

        // Replaces default heading levels.
        'widgettitleformat'    => '<h3>%s</h3>',
        'widgetsubtitleformat' => '<h4>%s</h4>'
    ]
);
