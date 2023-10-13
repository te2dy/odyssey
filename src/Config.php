<?php
/**
 * Odyssey, a Dotclear theme.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2023 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

namespace Dotclear\Theme\odyssey;

use Dotclear\App;
use Dotclear\Core\Process;
//use Dotclear\Helper\Html\Form;
// use Dotclear\Helper\Html\Form\Select;
use form;

class Config extends Process
{
    public static function init(): bool
    {
        if (!self::status(My::checkContext(My::CONFIG))) {
            return false;
        }

        // My::l10n('admin');

        App::backend()->fonts = [
            __('settings-global-fontfamily-sansserif-default') => 'sans-serif',
            __('settings-global-fontfamily-serif')             => 'serif',
            __('settings-global-fontfamily-mono')              => 'monospace'
        ];

        return self::status();
    }

    /**
     * Processes the request(s).
     */
    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        return true;
    }

    /**
     * Renders the page.
     */
    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        echo form::combo('test', App::backend()->fonts, 'sans-serif');
    }
}
