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

$core->addBehavior('initWidgets', ['entryPhotoExifWidget', 'setWidget']);

class entryPhotoExifWidget
{
    public static $supported_post_type = ['post', 'page', 'gal', 'galitem'];
    public static $widget_content      = '<ul>%s</ul>';
    public static $widget_text         = '<li class="epew-%s"><strong>%s</strong> %s</li>';
    public static $widget_thumb        = '<li><img class="img-thumbnail" alt="%s" src="%s" /></li>';

    public static function setWidget($w)
    {
        global $core;

        $categories_combo = ['-' => '', __('Uncategorized') => 'null'];
        $categories       = $core->blog->getCategories();
        while ($categories->fetch()) {
            $cat_title                    = html::escapeHTML($categories->cat_title);
            $categories_combo[$cat_title] = $categories->cat_id;
        }

        $thumbnail_combo = [
            __('None')      => '',
            __('square')    => 'sq',
            __('thumbnail') => 't',
            __('small')     => 's',
            __('medium')    => 'm'
        ];

        $w->create(
            'epew',
            __('Entry Photo Exif'),
            ['entryPhotoExifWidget', 'getWidget'],
            null,
            __('Show images exif of an entry')
        );

        $w->epew->setting(
            'title',
            __('Title:'),
            __('Photos EXIF'),
            'text'
        );
        $w->epew->setting(
            'showmeta_Title',
            sprintf(__('Show metadata: %s'), __('Title')),
            0,
            'check'
        );
        $w->epew->setting(
            'showmeta_Description',
            sprintf(__('Show metadata: %s'), __('Descritpion')),
            0,
            'check'
        );
        $w->epew->setting(
            'showmeta_Location',
            sprintf(__('Show metadata: %s'), __('Location')),
            0,
            'check'
        );
        $w->epew->setting(
            'showmeta_DateTimeOriginal',
            sprintf(__('Show metadata: %s'), __('Date')),
            0,
            'check'
        );
        $w->epew->setting(
            'showmeta_Make',
            sprintf(__('Show metadata: %s'), __('Manufacturer')),
            0,
            'check'
        );
        $w->epew->setting(
            'showmeta_Model',
            sprintf(__('Show metadata: %s'), __('Model')),
            1,
            'check'
        );
        $w->epew->setting(
            'showmeta_Lens',
            sprintf(__('Show metadata: %s'), __('Lens')),
            1,
            'check'
        );
        $w->epew->setting(
            'showmeta_ExposureProgram',
            sprintf(__('Show metadata: %s'), __('Exposure program')),
            0,
            'check'
        );
        $w->epew->setting(
            'showmeta_Exposure',
            sprintf(__('Show metadata: %s'), __('Exposure time')),
            1,
            'check'
        );
        $w->epew->setting(
            'showmeta_FNumber',
            sprintf(__('Show metadata: %s'), __('Aperture')),
            1,
            'check'
        );
        $w->epew->setting(
            'showmeta_ISOSpeedRatings',
            sprintf(__('Show metadata: %s'), __('Iso speed rating')),
            1,
            'check'
        );
        $w->epew->setting(
            'showmeta_FocalLength',
            sprintf(__('Show metadata: %s'), __('Focal lengh')),
            1,
            'check'
        );
        $w->epew->setting(
            'showmeta_ExposureBiasValue',
            sprintf(__('Show metadata: %s'), __('Exposure bias value')),
            0,
            'check'
        );
        $w->epew->setting(
            'showmeta_MeteringMode',
            sprintf(__('Show metadata: %s'), __('Metering mode')),
            0,
            'check'
        );
        $w->epew->setting(
            'showmeta',
            __('Show empty metadata'),
            0,
            'check'
        );
        $w->epew->setting(
            'category',
            __('Category limit:'),
            '',
            'combo',
            $categories_combo
        );
        $w->epew->setting(
            'thumbsize',
            __('Thumbnail size:'),
            't',
            'combo',
            $thumbnail_combo
        );
        $w->epew->setting(
            'content_only',
            __('Content only'),
            0,
            'check'
        );
        $w->epew->setting(
            'class',
            __('CSS class:'),
            ''
        );
        $w->epew->setting(
            'offline',
            __('Offline'),
            0,
            'check'
        );
    }

    public static function getWidget($w)
    {
        global $core, $_ctx;

        # Widget is offline
        if ($w->offline) {
            return null;
        }

        # Not in post context
        if (!$_ctx->exists('posts') || !$_ctx->posts->post_id) {
            return null;
        }

        # Not supported post type
        if (!in_array($_ctx->posts->post_type, self::$supported_post_type)) {
            return null;
        }

        # Category limit
        if ($w->category == 'null' && $_ctx->posts->cat_id !== null
         || $w->category != 'null' && $w->category != '' && $w->category != $_ctx->posts->cat_id) {
            return null;
        }

        # Content lookup
        $text = $_ctx->posts->post_excerpt_xhtml . $_ctx->posts->post_content_xhtml;

        # Find source images
        $images = self::getImageSource($core, $text, $w->thumbsize);

        # No images
        if (empty($images)) {
            return null;
        }

        $contents = '';

        # Loop through images
        foreach ($images as $img) {
            # List metas
            $metas = self::getImageMeta($core, $img['source']);

            $content = '';
            foreach ($metas as $k => $v) {
                # Don't show unwanted metadata or empty metadata
                if (!$w->__get('showmeta_' . $k) || !$w->showmeta && empty($v[1])) {
                    continue;
                }
                $content .= sprintf(self::$widget_text, $k, $v[0], $v[1]);
            }

            # No meta
            if (empty($content)) {
                return null;
            }

            # Thumbnail
            if ($img['thumb']) {
                $content = sprintf(self::$widget_thumb, $img['title'], $img['thumb']) .
                $content;
            }
            $contents .= $content;
        }

        # Nothing found
        if (empty($contents)) {
            return null;
        }

        # Paste widget
        return $w->renderDiv(
            $w->content_only,
            'photoExifWidget ' . $w->class,
            '',
            ($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '') .
            sprintf(self::$widget_content, $contents)
        );
    }

    public static function getImageSource($core, $subject, $size = '')
    {
        # Path and url
        $p_url  = $core->blog->settings->system->public_url;
        $p_site = preg_replace('#^(.+?//.+?)/(.*)$#', '$1', $core->blog->url);
        $p_root = $core->blog->public_path;

        # Image pattern
        $pattern = '(?:' . preg_quote($p_site, '/') . ')?' . preg_quote($p_url, '/');
        $pattern = sprintf('/<img.+?src="%s(.*?\.(?:jpg|jpeg|png|gif))"[^>]+/msu', $pattern);

        # No image
        if (!preg_match_all($pattern, $subject, $m)) {
            return;
        }

        $res         = $duplicate         = [];
        $allowed_ext = ['.jpg', '.JPG', '.jpeg', '.JPEG', '.png', '.PNG', '.gif', '.GIF'];

        # Loop through images
        foreach ($m[1] as $i => $img) {
            $src  = $thb  = $alt  = false;
            $info = path::info($img);
            $base = $info['base'];
            $ext  = $info['extension'];

            # Not original
            if (preg_match('/^\.(.+)_(sq|t|s|m)$/', $base, $mbase)) {
                $base = $mbase[1];
            }

            # Full path
            $f = $p_root . '/' . $info['dirname'] . '/' . $base;

            # Find extension
            foreach ($allowed_ext as $end) {
                if (file_exists($f . $end)) {
                    $src = $f . $end;

                    break;
                }
            }

            # No file
            if (!$src || in_array($src, $duplicate)) {
                continue;
            }

            # Prevent double images
            $duplicate[] = $src;

            # Find thumbnail
            if (!empty($size)) {
                $t = $p_root . '/' . $info['dirname'] . '/.' . $base . '_' . $size . '.jpg';
                if (file_exists($t)) {
                    $thb = $p_url . (dirname($img) != '/' ? dirname($img) : '') . '/.' . $base . '_' . $size . '.jpg';
                }
            }

            # Find image description
            if (preg_match('/alt="([^"]+)"/', $m[0][$i], $malt)) {
                $alt = $malt[1];
            }

            $res[] = [
                'source' => $src,
                'thumb'  => $thb,
                'title'  => $alt
            ];
        }

        return $res;
    }

    public static function getImageMeta($core, $src)
    {
        $metas = [
            'Title'             => [__('Title:'), ''],
            'Description'       => [__('Description:'), ''],
            'Location'          => [__('Location:'), ''],
            'DateTimeOriginal'  => [__('Date:'), ''],
            'Make'              => [__('Manufacturer:'), ''],
            'Model'             => [__('Model:'), ''],
            'Lens'              => [__('Lens:'), ''],
            'ExposureProgram'   => [__('Program:'), ''],
            'Exposure'          => [__('Speed:'), ''],
            'FNumber'           => [__('Aperture:'), ''],
            'ISOSpeedRatings'   => [__('ISO:'), ''],
            'FocalLength'       => [__('Focal:'), ''],
            'ExposureBiasValue' => [__('Exposure Bias:'), ''],
            'MeteringMode'      => [__('Metering mode:'), '']
        ];

        $exp_prog = [
            0 => __('Not defined'),
            1 => __('Manual'),
            2 => __('Normal program'),
            3 => __('Aperture priority'),
            4 => __('Shutter priority'),
            5 => __('Creative program'),
            6 => __('Action program'),
            7 => __('Portait mode'),
            8 => __('Landscape mode')
        ];

        $met_mod = [
            0 => __('Unknow'),
            1 => __('Average'),
            2 => __('Center-weighted average'),
            3 => __('Spot'),
            4 => __('Multi spot'),
            5 => __('Pattern'),
            6 => __('Partial'),
            7 => __('Other')
        ];

        if (!$src || !file_exists($src)) {
            return $metas;
        }

        $m = imageMeta::readMeta($src);

        # Title
        if (!empty($m['Title'])) {
            $metas['Title'][1] = html::escapeHTML($m['Title']);
        }

        # Description
        if (!empty($m['Description'])) {
            if (!empty($m['Title']) && $m['Title'] != $m['Description']) {
                $metas['Description'][1] = html::escpeHTML($m['Description']);
            }
        }

        # Location
        if (!empty($m['City'])) {
            $metas['Location'][1] .= html::escapeHTML($m['City']);
        }
        if (!empty($m['City']) && !empty($m['country'])) {
            $metas['Location'][1] .= ', ';
        }
        if (!empty($m['country'])) {
            $metas['Location'][1] .= html::escapeHTML($m['Country']);
        }

        # DateTimeOriginal
        if (!empty($m['DateTimeOriginal'])) {
            $dt_ft                        = $core->blog->settings->system->date_format . ', ' . $core->blog->settings->system->time_format;
            $dt_tz                        = $core->blog->settings->system->blog_timezone;
            $metas['DateTimeOriginal'][1] = dt::dt2str($dt_ft, $m['DateTimeOriginal'], $dt_tz);
        }

        # Make
        if (isset($m['Make'])) {
            $metas['Make'][1] = html::escapeHTML($m['Make']);
        }

        # Model
        if (isset($m['Model'])) {
            $metas['Model'][1] = html::escapeHTML($m['Model']);
        }

        # Lens
        if (isset($m['Lens'])) {
            $metas['Lens'][1] = html::escapeHTML($m['Lens']);
        }

        # ExposureProgram
        if (isset($m['ExposureProgram'])) {
            $metas['ExposureProgram'][1] = $exp_prog[$m['ExposureProgram']] ?? $m['ExposureProgram'];
        }

        # Exposure
        if (!empty($m['Exposure'])) {
            $metas['Exposure'][1] = $m['Exposure'] . 's';
        }

        # FNumber
        if (!empty($m['FNumber'])) {
            $ap                  = sscanf($m['FNumber'], '%d/%d');
            $metas['FNumber'][1] = $ap ? 'f/' . ($ap[0] / $ap[1]) : $m['FNumber'];
        }

        # ISOSpeedRatings
        if (!empty($m['ISOSpeedRatings'])) {
            $metas['ISOSpeedRatings'][1] = $m['ISOSpeedRatings'];
        }

        # FocalLength
        if (!empty($m['FocalLength'])) {
            $fl                      = sscanf($m['FocalLength'], '%d/%d');
            $metas['FocalLength'][1] = $fl ? $fl[0] / $fl[1] . 'mm' : $m['FocalLength'];
        }

        # ExposureBiasValue
        if (isset($m['ExposureBiasValue'])) {
            $metas['ExposureBiasValue'][1] = $m['ExposureBiasValue'];
        }

        # MeteringMode
        if (isset($m['MeteringMode'])) {
            $metas['MeteringMode'][1] = isset($met_mod[$m['MeteringMode']]) ?
            $exp_prog[$m['MeteringMode']] : $m['MeteringMode'];
        }

        return $metas;
    }
}
