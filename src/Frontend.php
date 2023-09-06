<?php
/**
 * Odyssey, a Dotclear theme.
 *
 * This file contains functions for displaying the theme.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2023 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 */

namespace Dotclear\Theme\odyssey;

use Dotclear\App;
use Dotclear\Core\Process;
use Dotclear\Core\Frontend\Ctx;

require_once 'CustomUtils.php';
use OdysseyUtils as odUtils;

/* à trier */
use dcCore;
use Dotclear\Helper\L10n;
use Dotclear\Helper\Text;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Html;

class Frontend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        // Behaviors.
        App::behavior()->addBehavior('publicHeadContent', self::odysseyHeadMeta(...));

        // Values.
        App::frontend()->tpl->addValue('odysseyPostContainsImage', self::odysseyPostContainsImage(...));
        App::frontend()->tpl->addValue('odysseyFooterCredits', self::odysseyFooterCredits(...));

        return true;
    }

    /**
     * Adds meta tags in the <head> section depending on the blog settings.
     *
     * @return void The head meta.
     */
    public static function odysseyHeadMeta()
    {
        // Adds the name of the editor.
        if (App::blog()->settings->system->editor) {
            echo '<meta name=author content=' . odUtils::attrValueQuotes(App::blog()->settings->system->editor) . '>' . "\n";
        }

        // Adds the content of the copyright notice.
        if (App::blog()->settings->system->copyright_notice) {
            echo '<meta name=copyright content=' . odUtils::attrValueQuotes(App::blog()->settings->system->copyright_notice) . '>' . "\n";
        }
    }

    /**
     * Displays a camera emoji if the post contains at least one image.
     *
     * @return string Possibly a camera emoji.
     */
    public static function odysseyPostContainsImage(): string
    {
        return '<?php
            if (' . Ctx::class . '::EntryFirstImageHelper("t", true, "", true)) {
                echo "📷 ";
            }
        ?>';
    }

    /**
     * Displays Dotclear and Odyssey as credits in the footer.
     *
     * @return string The credits.
     */
    public static function odysseyFooterCredits(): string
    {
        if (!defined('DC_DEV') || (defined('DC_DEV') && DC_DEV === false)) {
            return '<div class=site-footer-block>' . __(
                'footer-powered-by',
                My::name()
            ) . '</div>';
        }

        $dc_version       = App::version()->getVersion('core');
        $dc_version_short = explode('-', $dc_version)[0] ?? $dc_version;

        $theme_version = App::version()->getVersion('odyssey');

        return '<div class=site-footer-block>' . sprintf(
            __('footer-powered-by-dev'),
            $dc_version,
            $dc_version_short,
            My::name(),
            $theme_version
        ) . '</div>';
    }
}
