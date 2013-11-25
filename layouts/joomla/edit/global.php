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
$form = $displayData->getForm();
$input = $app->input;
$saveHistory = JComponentHelper::getParams($input->getCmd('extension', 'com_content'))->get('save_history', 0);

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
	'version_note'
);

$hiddenFields = $displayData->get('hidden_fields') ?: array();

// Multilanguage check:
/*if (!JLanguageMultilang::isEnabled())
{
	$hiddenFields[] = 'language';
}*/
if (!$saveHistory)
{
	$hiddenFields[] = 'version_note';
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
			if (in_array($f, $hiddenFields))
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
