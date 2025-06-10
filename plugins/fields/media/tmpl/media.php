<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Media
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Filesystem\File;
use Joomla\Uri\Uri;

if (empty($field->value) || empty($field->value['imagefile'])) {
    return;
}

$fileUrl = MediaHelper::getCleanMediaFieldValue($field->value['imagefile']);
// detect local file path
$isLocalFile = false;
if (empty((new Uri($fileUrl))->getHost())) {
    $fileUrl     = JPATH_SITE . DIRECTORY_SEPARATOR . $fileUrl;
    $isLocalFile = true;
}
if ($isLocalFile && !\is_file($fileUrl)) {
    return;
}
$class = $fieldParams->get('image_class');
$comMediaParams = ComponentHelper::getParams('com_media');

$audiosExt    = array_map(
    'trim',
    explode(
        ',',
        $comMediaParams->get(
            'audio_extensions',
            'mp3,m4a,mp4a,ogg'
        )
    )
);
$videosExt    = array_map(
    'trim',
    explode(
        ',',
        $comMediaParams->get(
            'video_extensions',
            'mp4,mp4v,mpeg,mov,webm'
        )
    )
);
$documentsExt = array_map(
    'trim',
    explode(
        ',',
        $comMediaParams->get(
            'doc_extensions',
            'doc,odg,odp,ods,odt,pdf,ppt,txt,xcf,xls,csv'
        )
    )
);

$fileExtension = File::getExt($fileUrl);

$options = [];

if ($class) {
    $options['class'] = $class;
}

if (MediaHelper::isImage($fileUrl) || ($isLocalFile && MediaHelper::getMimeType($fileUrl) === 'image/svg+xml')) {
    $options = [
        'src' => $field->value['imagefile'],
        'alt' => empty($field->value['alt_text']) && empty($field->value['alt_empty']) ? false : $field->value['alt_text'],
    ];

    echo LayoutHelper::render('joomla.html.image', $options);
} elseif (\in_array($fileExtension, $audiosExt)) {
    $options['src']      = $isLocalFile ? $field->value['imagefile'] : $fileUrl;
    $options['controls'] = 'controls';

    echo LayoutHelper::render('joomla.html.audio', $options);
} elseif (\in_array($fileExtension, $videosExt)) {
    $options['src']      = $isLocalFile ? $field->value['imagefile'] : $fileUrl;
    $options['controls'] = 'controls';

    echo LayoutHelper::render('joomla.html.video', $options);
} else {
    $linkText = $field->value['linktext'] ?? Text::_('JLIB_FORM_FIELD_PARAM_ACCESSIBLEMEDIA_PARAMS_LINKTEXT_DEFAULT_VALUE');
    $fileUrl = $isLocalFile ? $field->value['imagefile'] : $fileUrl;
    if(str_contains($linkText, '{filename}')) {
        $linkText = str_replace('{filename}', $fileUrl, $linkText);
    }
    echo HTMLHelper::link($fileUrl, $linkText, $options);
}
