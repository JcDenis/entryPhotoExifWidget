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
    'Entry Photo Exif Widget',              // Name
    'Show images exif of an entry',         // Description
    'Jean-Christian Denis and contibutors', // Author
    '1.0.2',                                // Version
    [
        'permissions' => 'admin',
        'type'=> 'plugin',
        'dc_min' => '2.18',
        'support' => 'https://github.com/JcDenis/entryPhotoExifWidget',
        'details' => 'https://plugins.dotaddict.org/dc2/details/entryPhotoExifWidget'
    ]
);