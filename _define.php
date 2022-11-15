<?php
/**
 * @brief entryPhotoExifWidget, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugin
 *
 * @author Jean-Christian Denis and contibutors
 *
 * @copyright Jean-Christian Denis
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('DC_RC_PATH')) {
    return null;
}

$this->registerModule(
    'Entry Photo Exif Widget',
    'Show images exif of an entry',
    'Jean-Christian Denis and contibutors',
    '1.1',
    [
        'requires'    => [['core', '2.24']],
        'permissions' => dcAuth::PERMISSION_ADMIN,
        'type'        => 'plugin',
        'support'     => 'https://github.com/JcDenis/entryPhotoExifWidget',
        'details'     => 'https://plugins.dotaddict.org/dc2/details/entryPhotoExifWidget',
        'repository'  => 'https://raw.githubusercontent.com/JcDenis/entryPhotoExifWidget/master/dcstore.xml',
    ]
);
