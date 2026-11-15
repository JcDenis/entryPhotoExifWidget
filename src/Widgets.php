<?php

declare(strict_types=1);

namespace Dotclear\Plugin\entryPhotoExifWidget;

use Dotclear\App;
use Dotclear\Database\MetaRecord;
use Dotclear\Helper\Date;
use Dotclear\Helper\File\Image\ImageMeta;
use Dotclear\Helper\File\Path;
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\widgets\WidgetsElement;
use Dotclear\Plugin\widgets\WidgetsStack;

/**
 * @brief   entryPhotoExifWidget widgets class.
 * @ingroup entryPhotoExifWidget
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Widgets
{
    /**
     * @var     string[]    $supported_post_type
     */
    public static array $supported_post_type = ['post', 'page', 'gal', 'galitem'];
    public static string $widget_content     = '<ul>%s</ul>';
    public static string $widget_text        = '<li class="epew-%s"><strong>%s</strong> %s</li>';
    public static string $widget_thumb       = '<li><img class="img-thumbnail" alt="%s" src="%s" /></li>';

    public static function initWidgets(WidgetsStack $w): void
    {
        if (!App::blog()->isDefined()) {
            return;
        }

        $categories_combo = ['-' => '', __('Uncategorized') => 'null'];
        $categories       = App::blog()->getCategories();
        while ($categories->fetch()) {
            $categories_combo[Html::escapeHTML($categories->strField('cat_title'))] = (string) $categories->intField('cat_id');
        }

        $thumbnail_combo = [
            __('None')      => '',
            __('square')    => 'sq',
            __('thumbnail') => 't',
            __('small')     => 's',
            __('medium')    => 'm',
        ];

        $w->create(
            'epew',
            __('Entry Photo EXIF'),
            self::renderWidget(...),
            null,
            __('Show images exif of an entry')
        )
        ->addTitle(__('Photos EXIF'))
        ->setting(
            'showmeta_Title',
            sprintf(__('Show metadata: %s'), __('Title')),
            0,
            'check'
        )
        ->setting(
            'showmeta_Description',
            sprintf(__('Show metadata: %s'), __('Description')),
            0,
            'check'
        )
        ->setting(
            'showmeta_Location',
            sprintf(__('Show metadata: %s'), __('Location')),
            0,
            'check'
        )
        ->setting(
            'showmeta_DateTimeOriginal',
            sprintf(__('Show metadata: %s'), __('Date')),
            0,
            'check'
        )
        ->setting(
            'showmeta_Make',
            sprintf(__('Show metadata: %s'), __('Manufacturer')),
            0,
            'check'
        )
        ->setting(
            'showmeta_Model',
            sprintf(__('Show metadata: %s'), __('Model')),
            1,
            'check'
        )
        ->setting(
            'showmeta_Lens',
            sprintf(__('Show metadata: %s'), __('Lens')),
            1,
            'check'
        )
        ->setting(
            'showmeta_ExposureProgram',
            sprintf(__('Show metadata: %s'), __('Exposure program')),
            0,
            'check'
        )
        ->setting(
            'showmeta_Exposure',
            sprintf(__('Show metadata: %s'), __('Exposure time')),
            1,
            'check'
        )
        ->setting(
            'showmeta_FNumber',
            sprintf(__('Show metadata: %s'), __('Aperture')),
            1,
            'check'
        )
        ->setting(
            'showmeta_ISOSpeedRatings',
            sprintf(__('Show metadata: %s'), __('Iso speed rating')),
            1,
            'check'
        )
        ->setting(
            'showmeta_FocalLength',
            sprintf(__('Show metadata: %s'), __('Focal length')),
            1,
            'check'
        )
        ->setting(
            'showmeta_ExposureBiasValue',
            sprintf(__('Show metadata: %s'), __('Exposure bias value')),
            0,
            'check'
        )
        ->setting(
            'showmeta_MeteringMode',
            sprintf(__('Show metadata: %s'), __('Metering mode')),
            0,
            'check'
        )
        ->setting(
            'showmeta',
            __('Show empty metadata'),
            0,
            'check'
        )
        ->setting(
            'category',
            __('Category limit:'),
            '',
            'combo',
            $categories_combo
        )
        ->setting(
            'thumbsize',
            __('Thumbnail size:'),
            't',
            'combo',
            $thumbnail_combo
        )
        ->addContentOnly()
        ->addClass()
        ->addOffline();
    }

    public static function renderWidget(WidgetsElement $w): string
    {
        // Widget is offline
        if ($w->get('offline') || !App::blog()->isDefined()) {
            return '';
        }

        $ctx = App::frontend()->context();

        // Not in post context
        if (!$ctx->exists('posts') || !($ctx->__get('posts') instanceof MetaRecord) || !$ctx->__get('posts')->intField('post_id')) {
            return '';
        }

        // Not supported post type
        if (!in_array($ctx->__get('posts')->strField('post_type'), self::$supported_post_type)) {
            return '';
        }

        // Category limit
        if ($w->get('category') == 'null' && $ctx->__get('posts')->f('cat_id') !== null
         || $w->get('category') != 'null' && $w->get('category') != '' && $w->get('category') != $ctx->__get('posts')->f('cat_id')) {
            return '';
        }

        # Content lookup
        $text = $ctx->__get('posts')->strField('post_excerpt_xhtml') . ' ' . $ctx->__get('posts')->strField('post_content_xhtml');

        # Find source images
        $images = self::getImageSource($text, is_string($w->get('thumbsize')) ? $w->get('thumbsize') : 't');

        # No images
        if (empty($images)) {
            return '';
        }

        $contents = '';

        # Loop through images
        foreach ($images as $img) {
            # List metas
            $metas = isset($img['source']) && is_string($img['source']) ? self::getImageMeta($img['source']) : [];

            $content = '';
            foreach ($metas as $k => $v) {
                # Don't show unwanted metadata or empty metadata
                if (!$w->get('showmeta_' . $k) || !$w->get('showmeta') && empty($v[1])) {
                    continue;
                }
                $content .= sprintf(self::$widget_text, $k, $v[0], $v[1]);
            }

            # No meta for this image, check next one
            if (empty($content)) {
                continue;
            }

            # Thumbnail
            if (is_string($img['title']) && is_string($img['thumb'])) {
                $content = sprintf(self::$widget_thumb, $img['title'], $img['thumb']) .
                $content;
            }
            $contents .= $content;
        }

        # Paste widget
        return $w->renderDiv(
            (bool) $w->get('content_only'),
            'photoExifWidget ' . (is_string($w->get('class')) ? $w->get('class') : ''),
            '',
            (is_string($w->get('title')) && !empty($w->get('title')) ? $w->renderTitle(Html::escapeHTML($w->get('title'))) : '') .
            sprintf(self::$widget_content, $contents)
        );
    }

    /**
     * @return  array<int, array<string, mixed>>
     */
    public static function getImageSource(string $subject, string $size = ''): array
    {
        if (!App::blog()->isDefined()) {
            return [];
        }

        # Path and url
        $p_url  = App::blog()->settings()->get('system')->getStr('public_url', false);
        $p_site = (string) preg_replace('#^(.+?//.+?)/(.*)$#', '$1', App::blog()->url());
        $p_root = App::blog()->publicPath();

        # Image pattern
        $pattern = '(?:' . preg_quote($p_site, '/') . ')?' . preg_quote($p_url, '/');
        $pattern = sprintf('/<img.+?src="%s(.*?\.(?:jpg|jpeg|png|gif))"[^>]+/msu', $pattern);

        # No image
        if (!preg_match_all($pattern, $subject, $m)) {
            return [];
        }

        $res         = $duplicate = [];
        $allowed_ext = ['.jpg', '.JPG', '.jpeg', '.JPEG', '.png', '.PNG', '.gif', '.GIF'];

        # Loop through images
        foreach ($m[1] as $i => $img) {
            $src  = $thb = $alt = false;
            $info = Path::info($img);
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
                'title'  => $alt,
            ];
        }

        return $res;
    }

    /**
     * @return  array<string, array<int|string, string>>
     */
    public static function getImageMeta(?string $src): array
    {
        if (!App::blog()->isDefined()) {
            return [];
        }

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
            'MeteringMode'      => [__('Metering mode:'), ''],
        ];

        $exp_prog = [
            0 => __('Not defined'),
            1 => __('Manual'),
            2 => __('Normal program'),
            3 => __('Aperture priority'),
            4 => __('Shutter priority'),
            5 => __('Creative program'),
            6 => __('Action program'),
            7 => __('Portrait mode'),
            8 => __('Landscape mode'),
        ];

        $met_mod = [
            0 => __('Unknown'),
            1 => __('Average'),
            2 => __('Center-weighted average'),
            3 => __('Spot'),
            4 => __('Multi spot'),
            5 => __('Pattern'),
            6 => __('Partial'),
            7 => __('Other'),
        ];

        if (!$src || !file_exists($src)) {
            return $metas;
        }

        $m = ImageMeta::readMeta($src);

        # Title
        if (!empty($m['Title']) && is_string($m['Title'])) {
            $metas['Title'][1] = Html::escapeHTML($m['Title']);
        }

        # Description
        if (!empty($m['Description'])) {
            if (!empty($m['Title']) && is_string($m['Description']) && $m['Title'] != $m['Description']) {
                $metas['Description'][1] = Html::escapeHTML($m['Description']);
            }
        }

        # Location
        if (!empty($m['City']) && is_string($m['City'])) {
            $metas['Location'][1] .= Html::escapeHTML($m['City']);
        }
        if (!empty($m['City']) && !empty($m['Country'])) {
            $metas['Location'][1] .= ', ';
        }
        if (!empty($m['Country']) && is_string($m['Country'])) {
            $metas['Location'][1] .= Html::escapeHTML($m['Country']);
        }

        # DateTimeOriginal
        if (!empty($m['DateTimeOriginal']) && is_string($m['DateTimeOriginal'])) {
            $dt_ft = App::blog()->settings()->get('system')->getStr('date_format', false) . ', ' . App::blog()->settings()->get('system')->getStr('time_format', false);
            // EXIF date is already the local time of the shot, no time zone conversion
            $metas['DateTimeOriginal'][1] = Date::dt2str($dt_ft, $m['DateTimeOriginal']);
        }

        # Make
        if (isset($m['Make']) && is_string($m['Make'])) {
            $metas['Make'][1] = Html::escapeHTML($m['Make']);
        }

        # Model
        if (isset($m['Model']) && is_string($m['Model'])) {
            $metas['Model'][1] = Html::escapeHTML($m['Model']);
        }

        # Lens
        if (isset($m['Lens']) && is_string($m['Lens'])) {
            $metas['Lens'][1] = Html::escapeHTML($m['Lens']);
        } elseif (function_exists('exif_read_data')) {
            // Core only reads lens from XMP, try EXIF LensModel tag
            $exif = @exif_read_data($src);
            $lens = is_array($exif) ? ($exif['UndefinedTag:0xA434'] ?? ($exif['LensModel'] ?? null)) : null;
            if (is_string($lens) && trim($lens) !== '') {
                $metas['Lens'][1] = Html::escapeHTML(trim($lens));
            }
        }

        # ExposureProgram
        if (isset($m['ExposureProgram']) && is_scalar($m['ExposureProgram'])) {
            $metas['ExposureProgram'][1] = $exp_prog[(int) $m['ExposureProgram']] ?? Html::escapeHTML((string) $m['ExposureProgram']);
        }

        # Exposure
        if (!empty($m['Exposure']) && is_string($m['Exposure'])) {
            $metas['Exposure'][1] = $m['Exposure'] . 's';
        }

        # FNumber
        if (!empty($m['FNumber']) && is_string($m['FNumber'])) {
            $ap                  = sscanf($m['FNumber'], '%d/%d');
            $metas['FNumber'][1] = $ap ? 'f/' . ($ap[0] / $ap[1]) : $m['FNumber'];
        }

        # ISOSpeedRatings
        if (!empty($m['ISOSpeedRatings']) && is_scalar($m['ISOSpeedRatings'])) {
            $metas['ISOSpeedRatings'][1] = Html::escapeHTML((string) $m['ISOSpeedRatings']);
        } elseif (!empty($m['ISOSpeedRatings']) && is_array($m['ISOSpeedRatings']) && is_scalar($iso = reset($m['ISOSpeedRatings']))) {
            $metas['ISOSpeedRatings'][1] = Html::escapeHTML((string) $iso);
        }

        # FocalLength
        if (!empty($m['FocalLength']) && is_string($m['FocalLength'])) {
            $fl                      = sscanf($m['FocalLength'], '%d/%d');
            $metas['FocalLength'][1] = $fl ? $fl[0] / $fl[1] . 'mm' : $m['FocalLength'];
        }

        # ExposureBiasValue
        if (isset($m['ExposureBiasValue']) && is_scalar($m['ExposureBiasValue'])) {
            $metas['ExposureBiasValue'][1] = Html::escapeHTML((string) $m['ExposureBiasValue']);
        }

        # MeteringMode
        if (isset($m['MeteringMode']) && is_scalar($m['MeteringMode'])) {
            $metas['MeteringMode'][1] = $met_mod[(int) $m['MeteringMode']] ?? Html::escapeHTML((string) $m['MeteringMode']);
        }

        return $metas;
    }
}
