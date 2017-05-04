<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$app       = JFactory::getApplication();
$form      = $displayData->getForm();
$input     = $app->input;
$component = $input->getCmd('option', 'com_content');

if ($component == 'com_categories')
{
	$extension = $input->getCmd('extension', 'com_content');
	$parts     = explode('.', $extension);
	$component = $parts[0];
}

$saveHistory = JComponentHelper::getParams($component)->get('save_history', 0);

$fields = $displayData->get('fields') ?: array(
	array('parent', 'parent_id'),
	array('published', 'state', 'enabled'),
	array('category', 'catid'),
	'featured',
	'sticky',
	'access',
	'language',
	'tags',
	'note',
	'version_note',
);

$hiddenFields = $displayData->get('hidden_fields') ?: array();

if (!$saveHistory)
{
	$hiddenFields[] = 'version_note';
}

$html   = array();
$html[] = '<fieldset class="form-horizontal"><ul class="horizontal-buttons unstyled">';

foreach ($fields as $field)
{
	$field = is_array($field) ? $field : array($field);

	foreach ($field as $f)
	{
		if ($form->getField($f))
		{
			if (in_array($f, $hiddenFields))
			{
				$form->setFieldAttribute($f, 'type', 'hidden');
			}

			$html[] = '<li>' . $form->renderField($f) . '</li>';
			break;
		}
	}
}

$html[] = '</ul></fieldset>';

echo implode('', $html);
