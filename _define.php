<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of entryPhotoExifWidget, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2016 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcdenis.net
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {
    return null;
}

$this->registerModule(
    'Entry Photo Exif Widget',              // Name
    'Show images exif of an entry',         // Description
    'Jean-Christian Denis and contibutors', // Author
    '1.0.1',                                // Version
    [
        'permissions' => 'admin',
        'type'        => 'plugin',
        'dc_min' => '2.10',
        'support' => 'http://forum.dotclear.org/',
        'details' => 'https://plugins.dotaddict.org/dc2/details/entryPhotoExifWidget'
    ]
);