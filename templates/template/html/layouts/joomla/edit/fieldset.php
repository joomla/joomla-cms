<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

$app = JFactory::getApplication();
$form = $displayData->getForm();

$name = $displayData->get('fieldset');
$fieldSet = $form->getFieldset($name);

if (empty($fieldSet))
{
	return;
}

$ignoreFields = $displayData->get('ignore_fields') ? : array();
$extraFields = $displayData->get('extra_fields') ? : array();

if (!empty($displayData->showOptions) || $displayData->get('show_options', 1))
{
	if (isset($extraFields[$name]))
	{
		foreach ($extraFields[$name] as $f)
		{
			if (in_array($f, $ignoreFields))
			{
				continue;
			}
			if ($form->getField($f))
			{
				$fieldSet[] = $form->getField($f);
			}
		}
	}

	$html = array();

	foreach ($fieldSet as $field)
	{
		$html[] = $field->renderField();
	}

	echo implode('', $html);
}
else
{
	$html = array();
	$html[] = '<div style="display:none;">';
	foreach ($fieldSet as $field)
	{
		$html[] = $field->input;
	}
	$html[] = '</div>';

	echo implode('', $html);
}
