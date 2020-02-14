<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

$app       = JFactory::getApplication();
$form      = $displayData->getForm();
$input     = $app->input;
$component = $input->getCmd('option', 'com_content');

if ($component === 'com_categories')
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
$html[] = '<fieldset class="form-vertical form-no-margin">';

foreach ($fields as $field)
{
	foreach ((array) $field as $f)
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
