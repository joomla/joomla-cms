<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();
$form = $displayData->get('form');

$fields = $displayData->get('fields');
$fields = !empty($fields) ? $fields : array(
	array('category', 'catid'),
	array('parent', 'parent_id'),
	'ordering',
	'tags',
	array('published', 'state'),
	'featured',
	'access',
	'language',
	'note'
);

$hidden_fields = $displayData->get('hidden_fields');
$hidden_fields = !empty($hidden_fields) ? $hidden_fields : array();

if (!isset($app->languages_enabled))
{
	$hidden_fields[] = 'language';
}

$html = array();
$html[] = '<fieldset class="form-vertical">';

foreach ($fields as $field)
{
	$field = is_array($field) ? $field : array($field);
	foreach ($field as $f)
	{
		if ($form->getField($f))
		{
			if (in_array($f, $hidden_fields))
			{
				$form->setFieldAttribute($f, 'type', 'hidden');
			}

			$html[] = $form->getControlGroup($f);
			break;
		}
	}
}

$html[] = '</fieldset>';

echo implode('', $html);
