<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();
$form = $displayData->getForm();
$input = $app->input;
$component = $input->getCmd('option', 'com_content');
if ($component == 'com_categories')
{
	$extension	= $input->getCmd('extension', 'com_content');
	$parts		= explode('.', $extension);
	$component	= $parts[0];
}
$saveHistory = JComponentHelper::getParams($component)->get('save_history', 0);

$fields = $displayData->get('fields') ?: array(
	array('category', 'catid'),
	array('parent', 'parent_id'),
	'tags',
	array('published', 'state', 'enabled'),
	'featured',
	'sticky',
	'access',
	'language',
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

			$html[] = $form->renderField($f);
			break;
		}
	}
}

$html[] = '</fieldset>';

echo implode('', $html);
