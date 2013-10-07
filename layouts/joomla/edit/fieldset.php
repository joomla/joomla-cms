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

$name = $displayData->get('fieldset');
$fieldSet = $form->getFieldset($name);

if (empty($fieldSet))
{
	return;
}

$ignoreFields = $displayData->get('ignore_fields') ? : array();
$extraFields = $displayData->get('extra_fields') ? : array();

if ($displayData->get('show_options', 1))
{
	if (isset($fieldSet->description) && trim($fieldSet->description))
	{
		echo '<p class="alert alert-info">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
	}

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

	$split = ($displayData->get('split', 1) && count($fieldSet) > 10) ? ceil(count($fieldSet) / 2) : 0;
	$count = 0;

	$html = array();
	$html[] = '<div class="row-fluid' . ($split ? ' form-horizontal-desktop' : '') . '">';
	$html[] = '<div class="span' . ($split ? 6 : 12) . '">';

	foreach ($fieldSet as $field)
	{
		if ($count == $split && $field->getAttribute('type') == 'spacer' && $field->getAttribute('hr'))
		{
			continue;
		}

		$html[] = $field->getControlGroup();

		if (++$count == $split)
		{
			$html[] = '</div><div class="span6">';
		}
	}

	$html[] = '</div>';
	$html[] = '</div>';

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
