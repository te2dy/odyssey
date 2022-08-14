<?php
/**
 * Origine Mini, a minimalistic Dotclear theme
 *
 * @copyright Teddy
 * @copyright GPL-3.0
 */

if (!defined('DC_RC_PATH')) {
    return;
}

$this->registerModule(
    'Origine Mini',                  // Name
    'A minimalistic Dotclear theme', // Description
    'Teddy',                         // Author
    '0.1.0.9',                       // Version
    [
        'type'                 => 'theme',
        'widgettitleformat'    => '<h3>%s</h3>', // h3 instead of h2 by default. No class needed.
        'widgetsubtitleformat' => '<h4>%s</h4>'
    ]
);
