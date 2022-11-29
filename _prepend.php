<?php
/**
 * Origine Mini, a minimal theme for Dotclear.
 *
 * @copyright Teddy
 * @copyright GPL-3.0
 */

if (!defined('DC_CONTEXT_ADMIN')) {
    return;
}

$new_version = dcCore::app()->themes->moduleInfo('origine-mini', 'version');
$old_version = dcCore::app()->getVersion('origine-mini');

if (version_compare($old_version, $new_version, '<')) {
    dcCore::app()->blog->settings->addNamespace('originemini');

    $imagewide_hash    = 'sha256-' . base64_encode(hash_file('sha256', __DIR__ . '/js/imagewide.min.js', true));
    $searchform_hash   = 'sha256-' . base64_encode(hash_file('sha256', __DIR__ . '/js/searchform.min.js', true));
    $trackbackurl_hash = 'sha256-' . base64_encode(hash_file('sha256', __DIR__ . '/js/trackbackurl.min.js', true));

    $hashes = [
        'trackbackurl' => http::escapeHTML($trackbackurl_hash),
        'searchform'   => http::escapeHTML($searchform_hash),
        'imagewide'    => http::escapeHTML($imagewide_hash)
    ];

    dcCore::app()->blog->settings->originemini->put('js_hash', $hashes, 'array', __("Hash for the JavaScript file of the theme."), true);

    dcCore::app()->setVersion('origine-mini', $new_version);
}
