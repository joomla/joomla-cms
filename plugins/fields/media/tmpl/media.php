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

if (empty($field->value) || empty($field->value['imagefile']))
{
	return;
}

$options        = [];
$options['src'] = $field->value['imagefile'];
$options['alt'] = empty($field->value['alt_text']) && empty($field->value['alt_empty']) ? false : $field->value['alt_text'];
$class          = $fieldParams->get('image_class');

if ($class)
{
	$options['class'] = $class;
}

echo LayoutHelper::render('joomla.html.image', $options);
