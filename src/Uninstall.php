<?php
/**
 * Odyssey, a simple and customizable Dotclear theme.
 *
 * @author    Teddy <zozxebpyr@mozmail.com>
 * @copyright 2022-2025 Teddy
 * @license   GPL-3 (https://www.gnu.org/licenses/gpl-3.0.en.html)
 *
 * @link https://dotclear.watch/Billet/Le-plugin-Uninstaller
 */

namespace Dotclear\Theme\odyssey;

use Dotclear\App;
use Dotclear\Core\Process;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Plugin\Uninstaller\Uninstaller;

class Uninstall extends Process
{
    public static function init(): bool
    {
        My::l10n('admin');

        return self::status(My::checkContext(My::UNINSTALL));
    }

    public static function process(): bool
    {
        if (!self::status() || !App::themes()->moduleExists('odyssey')) {
            return false;
        }

        Uninstaller::instance()
            ->addUserAction(
                'settings',
                'delete_all',
                My::id(),
                true
            )
            ->addUserAction(
                'themes',
                'delete',
                My::id(),
                true
            )
        ;

        // Removes the odyssey public folder.
        if (isset($_POST['delete_odyssey_public_folder'])) {
            $dir_path = Path::real(App::blog()->publicPath() . '/' . My::id());

            My::deleteDirectory($dir_path);
        }

        return true;
    }

    /**
     * Adds a checkbox to the uninstaller form.
     *
     * @return void The checkbox.
     */
    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        echo (new Para())
            ->items([
                (new Checkbox('delete_odyssey_public_folder', true))
                    ->value(1),
                (new Label(__('uninstall-checkbox-label'), Label::OUTSIDE_LABEL_AFTER))
                    ->for('delete_odyssey_public_folder')
                    ->class('classic'),
            ])
            ->render();
    }
}
