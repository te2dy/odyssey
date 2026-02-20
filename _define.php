<?php
/**
 * Odyssey, a simple and customizable Dotclear theme.
 *
 * @author    Teddy
 * @copyright 2022-2026 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

$this->registerModule(
    'Odyssey',
    'A simple and customizable theme to write your own odyssey',
    'Teddy',
    '2.30',
    [
        'requires' => [
            ['core', '2.36']
        ],

        'type' => 'theme',

        'support'    => 'https://dotclear.org/post/2026/01/02/Thème-Odyssée-%3A-présentation-et-aide',
        'details'    => 'https://dotclear.org/theme/detail/odyssey',
        'repository' => 'https://raw.githubusercontent.com/te2dy/odyssey/main/dcstore.xml',

        // Allows a full control for the theme configurator.
        'standalone_config' => true,

        // Replaces default heading levels.
        'widgettitleformat'    => '<h3>%s</h3>',
        'widgetsubtitleformat' => '<h4>%s</h4>'
    ]
);
