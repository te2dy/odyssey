<?php
/**
 * Origine Mini, a minimal theme for Dotclear.
 *
 * @copyright Teddy
 * @copyright GPL-3.0
 */

if (!defined('DC_RC_PATH')) {
    return;
}

$this->registerModule(
    'Origine Mini',             // Name
    'A minimal Dotclear theme', // Description
    'Teddy',                    // Author
    '1.0.4',                    // Version
    [
        'requires'             => [['core', '2.24']],
        'standalone_config'    => true, // Allows a full control for the theme configurator.
        'type'                 => 'theme',
        'widgettitleformat'    => '<h3>%s</h3>', // h3 instead of h2 by default. No class needed.
        'widgetsubtitleformat' => '<h4>%s</h4>'
    ]
);
