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
    '1.6',
    [
        'requires'    => [['core', '2.36']],
        'permissions' => 'My',
        'type'        => 'plugin',
        'support'     => 'https://github.com/JcDenis/' . $this->id . '/issues',
        'details'     => 'https://github.com/JcDenis/' . $this->id . '/',
        'repository'  => 'https://raw.githubusercontent.com/JcDenis/' . $this->id . '/master/dcstore.xml',
        'date'        => '2025-09-09T15:55:53+00:00',
    ]
);
