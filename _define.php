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

if (!defined('DC_RC_PATH'))
{
	return null;
}

$this->registerModule(
	/* Name */			"Entry Photo Exif Widget",
	/* Description*/		"Show images exif of an entry",
	/* Author */			"Jean-Christian Denis and contibutors",
	/* Version */			'1.0',
	/* Properties */
	array(
		'permissions' => 'admin',
		'type' => 'plugin',
		'dc_min' => '2.10',
		'support' => 'http://forum.dotclear.org/',
		'details' => 'http://plugins.dotaddict.org/dc2/details/photoExifWidget'
	)
);