<?php
/**
 * Odyssey, a simple and customizable Dotclear theme.
 *
 * @author    Teddy <contact.teddy@laposte.net>
 * @copyright 2022-2025 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

$this->registerModule(
    'Odyssey',
    'A simple and customizable theme to write your own odyssey',
    'Teddy',
    '2.21',
    [
        'requires' => [
            ['core', '2.36'],
            ['php', '8.1']
        ],

        'type' => 'theme',

        // 'support'    => 'https://forum.dotclear.org/viewtopic.php?id=51635', // The forum is offline.
        'details'    => 'https://themes.dotaddict.org/galerie-dc2/details/odyssey',
        'repository' => 'https://raw.githubusercontent.com/te2dy/odyssey/main/dcstore.xml',

        // Allows a full control for the theme configurator.
        'standalone_config' => true,

        // Replaces default heading levels.
        'widgettitleformat'    => '<h3>%s</h3>',
        'widgetsubtitleformat' => '<h4>%s</h4>'
    ]
);
