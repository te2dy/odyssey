<?php
/**
 * Origine Mini, a minimal theme for Dotclear.
 *
 * The purpose of this file is to generate, at each version change of the theme,
 * a digital fingerprint of the JS files and save them in the database.
 *
 * @author Teddy
 * @copyright GPL-3.0
 */

if (!defined('DC_CONTEXT_ADMIN')) {
    return;
}

// Gets the new version number of the theme and the old one.
$old_version = (string) dcCore::app()->getVersion(basename(__DIR__));
$new_version = (string) dcCore::app()->themes->moduleInfo('origine-mini', 'version');

if (version_compare($old_version, $new_version, '<')) {
    dcCore::app()->blog->settings->addNamespace('originemini');

    // Hashes each JS files with the SHA-256 algorithm.
    $imagewide_hash    = 'sha256-' . base64_encode(hash_file('sha256', __DIR__ . '/js/imagewide.min.js', true));
    $searchform_hash   = 'sha256-' . base64_encode(hash_file('sha256', __DIR__ . '/js/searchform.min.js', true));
    $trackbackurl_hash = 'sha256-' . base64_encode(hash_file('sha256', __DIR__ . '/js/trackbackurl.min.js', true));

    $hashes = [
        'trackbackurl' => htmlspecialchars($trackbackurl_hash, ENT_COMPAT, 'UTF-8'),
        'searchform'   => htmlspecialchars($searchform_hash, ENT_COMPAT, 'UTF-8'),
        'imagewide'    => htmlspecialchars($imagewide_hash, ENT_COMPAT, 'UTF-8')
    ];

    /**
     * Saves the hashes in the database as an array.
     *
     * @see adminConfigOrigineMini::page_rendering() (/_config.php)
     */
    dcCore::app()->blog->settings->originemini->put('js_hash', $hashes, 'array', __('prepend-hashes-save'), true);

    // Pushes the new version of the theme in the database.
    dcCore::app()->setVersion('origine-mini', $new_version);
}

