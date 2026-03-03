<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Media
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Uri\Uri;

if (empty($file = $field->value) || empty($field->value['file'])) {
    return;
}
$fileUrl = MediaHelper::getCleanMediaFieldValue($field->value['file']);
// detect local file path
$isLocalFile = false;
if (empty((new Uri($fileUrl))->getHost())) {
    $fileUrl     = JPATH_SITE . DIRECTORY_SEPARATOR . $fileUrl;
    $isLocalFile = true;
}
if ($isLocalFile && !\is_file($fileUrl)) {
    return;
}

$class   = $fieldParams->get('css_class');
$options = [];

if ($class) {
    $options['class'] = $class;
}
$linkText = $field->value['linktext'] ?? Text::_('JLIB_FORM_FIELD_PARAM_ACCESSIBLEMEDIA_PARAMS_LINKTEXT_DEFAULT_VALUE');
$fileUrl  = $isLocalFile ? $field->value['file'] : $fileUrl;
if (str_contains($linkText, '{filename}')) {
    $linkText = str_replace('{filename}', basename($fileUrl), $linkText);
}
echo HTMLHelper::link($fileUrl, $linkText, $options);
