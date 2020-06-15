<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$app       = Factory::getApplication();
$form      = $displayData->getForm();
$fieldSets = $form->getFieldsets();
$helper    = $displayData->get('useCoreUI', false) ? 'uitab' : 'bootstrap';

if (empty($fieldSets))
{
	return;
}

$ignoreFieldsets = $displayData->get('ignore_fieldsets') ?: array();
$outputFieldsets = $displayData->get('output_fieldsets') ?: array();
$ignoreFieldsetFields = $displayData->get('ignore_fieldset_fields') ?: array();
$ignoreFields    = $displayData->get('ignore_fields') ?: array();
$extraFields     = $displayData->get('extra_fields') ?: array();
$tabName         = $displayData->get('tab_name') ?: 'myTab';

// These are required to preserve data on save when fields are not displayed.
$hiddenFieldsets = $displayData->get('hiddenFieldsets') ?: array();

// These are required to configure showing and hiding fields in the editor.
$configFieldsets = $displayData->get('configFieldsets') ?: array();

// Handle the hidden fieldsets when show_options is set false
if (!$displayData->get('show_options', 1))
{
	// The HTML buffer
	$html   = array();

	// Loop over the fieldsets
	foreach ($fieldSets as $name => $fieldSet)
	{
		// Check if the fieldset should be ignored
		if (in_array($name, $ignoreFieldsets, true))
		{
			continue;
		}

		// If it is a hidden fieldset, render the inputs
		if (in_array($name, $hiddenFieldsets))
		{
			// Loop over the fields
			foreach ($form->getFieldset($name) as $field)
			{
				// Add only the input on the buffer
				$html[] = $field->input;
			}

			// Make sure the fieldset is not rendered twice
			$ignoreFieldsets[] = $name;
		}

		// Check if it is the correct fieldset to ignore
		if (strpos($name, 'basic') === 0)
		{
			// Ignore only the fieldsets which are defined by the options not the custom fields ones
			$ignoreFieldsets[] = $name;
		}
	}

	// Echo the hidden fieldsets
	echo implode('', $html);
}

$opentab = false;

$xml = $form->getXml();

// Loop again over the fieldsets
foreach ($fieldSets as $name => $fieldSet)
{
	// Ensure any fieldsets we don't want to show are skipped (including repeating formfield fieldsets)
	if ((isset($fieldSet->repeat) && $fieldSet->repeat === true)
		|| in_array($name, $ignoreFieldsets)
		|| (!empty($configFieldsets) && in_array($name, $configFieldsets, true))
		|| (!empty($hiddenFieldsets) && in_array($name, $hiddenFieldsets, true))
	)
	{
		continue;
	}

	// Determine the label
	if (!empty($fieldSet->label))
	{
		$label = Text::_($fieldSet->label);
	}
	else
	{
		$label = strtoupper('JGLOBAL_FIELDSET_' . $name);
		if (Text::_($label) === $label)
		{
			$label = strtoupper($app->input->get('option') . '_' . $name . '_FIELDSET_LABEL');
		}
		$label = Text::_($label);
	}

	$hasChildren  = $xml->xpath('//fieldset[@name="' . $name . '"]//fieldset[not(ancestor::field/form/*)]');
	$hasParent    = $xml->xpath('//fieldset//fieldset[@name="' . $name . '"]');
	$isGrandchild = $xml->xpath('//fieldset//fieldset//fieldset[@name="' . $name . '"]');

	if (!$isGrandchild && $hasParent)
	{
		echo '<fieldset id="fieldset-' . $name . '" class="options-form ' . (!empty($fieldSet->class) ? $fieldSet->class : '') . '">';
		echo '<legend>' . $label . '</legend>';
		echo '<div class="column-count-md-2 column-count-lg-3">';
	}
	// Tabs
	elseif (!$hasParent)
	{
		if ($opentab)
		{
			if ($opentab > 1)
			{
				echo '</div>';
				echo '</fieldset>';
			}

			// End previous tab
			echo HTMLHelper::_($helper . '.endTab');
		}

		// Start the tab
		echo HTMLHelper::_($helper . '.addTab', $tabName, 'attrib-' . $name, $label);

		$opentab = 1;

		// Directly add a fieldset if we have no children
		if (!$hasChildren)
		{
			echo '<fieldset id="fieldset-' . $name . '" class="options-form ' . (!empty($fieldSet->class) ? $fieldSet->class : '') . '">';
			echo '<legend>' . $label . '</legend>';

			// Include the description when available
			if (isset($fieldSet->description) && trim($fieldSet->description))
			{
				echo '<div class="alert alert-info">' . $this->escape(Text::_($fieldSet->description)) . '</div>';
			}

			echo '<div class="column-count-md-2 column-count-lg-3">';

			$opentab = 2;
		}
	}

	// We're on the deepest level => output fields
	if (!$hasChildren)
	{
		// The name of the fieldset to render
		$displayData->fieldset = $name;

		// Force to show the options
		$displayData->showOptions = true;

		// Render the fieldset
		echo LayoutHelper::render('joomla.edit.fieldset', $displayData);
	}

	// Close open fieldset
	if (!$isGrandchild && $hasParent)
	{
		echo '</div>';
		echo '</fieldset>';
	}
}

if ($opentab)
{
	if ($opentab > 1)
	{
		echo '</div>';
		echo '</fieldset>';
	}

	// End previous tab
	echo HTMLHelper::_($helper . '.endTab');
}
