<?php
/**
 * @file
 * @brief       The plugin entryPhotoExifWidget definition
 * @ingroup     entryPhotoExifWidget
 *
 * @defgroup    entryPhotoExifWidget Plugin entryPhotoExifWidget.
 *
 * Show images EXIF of an entry.
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

$this->registerModule(
    'Entry Photo EXIF Widget',
    'Show images EXIF of an entry',
    'Jean-Christian Denis and contibutors',
    '1.5',
    [
        'requires'    => [['core', '2.28']],
        'permissions' => 'My',
        'type'       => 'plugin',
        'support'    => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/issues',
        'details'    => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/src/branch/master/README.md',
        'repository' => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/raw/branch/master/dcstore.xml',
    ]
);
