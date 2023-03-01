<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Media
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

if (empty($field->value) || empty($field->value['imagefile'])) {
    return;
}

$class   = $fieldParams->get('image_class');
$options = [
    'src' => $field->value['imagefile'],
    'alt' => empty($field->value['alt_text']) && empty($field->value['alt_empty']) ? false : $field->value['alt_text'],
];

if ($class) {
    $options['class'] = $class;
}

echo LayoutHelper::render('joomla.html.image', $options);
