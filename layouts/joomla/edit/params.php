<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();
$form = $displayData->getForm();

$fieldSets = $form->getFieldsets('params');
if (empty($fieldSets))
{
	$fieldSets = $form->getFieldsets('attribs');
}

if (empty($fieldSets))
{
	return;
}

$ignoreFieldsets = $displayData->get('ignore_fieldsets') ?: array();
$ignoreFields = $displayData->get('ignore_fields') ?: array();
$extraFields = $displayData->get('extra_fields') ?: array();

if (!empty($displayData->hiddenFieldsets))
{
	// These are required to preserve data on save when fields are not displayed.
	$hiddenFieldsets = $displayData->hiddenFieldsets ?: array();
}

if (!empty($displayData->configFieldsets))
{
	// These are required to configure showing and hiding fields in the editor.
	$configFieldsets = $displayData->configFieldsets ?: array();
}

if ($displayData->get('show_options', 1))
{
	foreach ($fieldSets as $name => $fieldSet)
	{
		// Ensure any fieldsets we don't want to show are skipped (including repeating formfield fieldsets)
		if (in_array($name, $ignoreFieldsets) || (!empty($configFieldsets) && in_array($name, $configFieldsets))
				|| !empty($hiddenFieldsets) && in_array($name, $hiddenFieldsets)
				|| (isset($fieldSet->repeat) && $fieldSet->repeat == true))
		{
			continue;
		}

		if (!empty($fieldSet->label))
		{
			$label = JText::_($fieldSet->label, true);
		}
		else
		{
			$label = strtoupper('JGLOBAL_FIELDSET_' . $name);
			if (JText::_($label, true) == $label)
			{
				$label = strtoupper($app->input->get('option') . '_' . $name . '_FIELDSET_LABEL');
			}
			$label = JText::_($label, true);
		}

		echo JHtml::_('bootstrap.addTab', 'myTab', 'attrib-' . $name, $label);

		if (isset($fieldSet->description) && trim($fieldSet->description))
		{
			echo '<p class="alert alert-info">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
		}

		$displayData->fieldset = $name;
		echo JLayoutHelper::render('joomla.edit.fieldset', $displayData);

		echo JHtml::_('bootstrap.endTab');
	}
}
else
{
	$html = array();
	$html[] = '<div style="display:none;">';
	foreach ($fieldSets as $name => $fieldSet)
	{
		if (in_array($name, $ignoreFieldsets))
		{
			continue;
		}

		if (in_array($name, $hiddenFieldsets))
		{
			foreach ($form->getFieldset($name) as $field)
			{
				echo $field->input;
			}
		}
	}
	$html[] = '</div>';

	echo implode('', $html);
}
