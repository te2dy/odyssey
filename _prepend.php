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

    $hash = 'sha256-' . base64_encode(hash_file('sha256', __DIR__ . '/js/public.min.js', true));

    dcCore::app()->blog->settings->originemini->put('public_js_sha256', html::escapeHTML($hash), 'string', 'Hash for the JavaScript file of the theme.', true);

    dcCore::app()->setVersion('origine-mini', $new_version);
}
