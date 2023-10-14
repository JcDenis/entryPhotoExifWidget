<?php

declare(strict_types=1);

namespace Dotclear\Plugin\entryPhotoExifWidget;

use Dotclear\App;
use Dotclear\Core\Process;

/**
 * @brief   entryPhotoExifWidget frontend class.
 * @ingroup entryPhotoExifWidget
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
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

        App::behavior()->addBehavior('initWidgets', Widgets::initWidgets(...));

        return true;
    }
}
