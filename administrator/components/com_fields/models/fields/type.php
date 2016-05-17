<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 * 
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');
JLoader::import('joomla.filesystem.folder');

class JFormFieldType extends JFormFieldList
{

	public $type = 'Type';

	public function setup (SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);
		$this->onchange = "typeHasChanged(this);";
		return $return;
	}

	protected function getOptions ()
	{
		$options = parent::getOptions();

		$paths = array();
		$paths[] = JPATH_ADMINISTRATOR . '/components/com_fields/models/types';

		if ($this->element['component'])
		{
			$paths[] = JPATH_ADMINISTRATOR . '/components/' . $this->element['component'] . '/models/types';
		}

		foreach ($paths as $path)
		{
			if (! JFolder::exists($path))
			{
				continue;
			}
			// Looping trough the types
			foreach (JFolder::files($path, 'php', false, true) as $filePath)
			{
				$name = str_replace('.php', '', basename($filePath));
				if ($name == 'base')
				{
					continue;
				}

				$label = 'COM_FIELDS_TYPE_' . strtoupper($name);
				if (! JFactory::getLanguage()->hasKey($label))
				{
					$label = JString::ucfirst($name);
				}
				$options[] = JHtml::_('select.option', $name, JText::_($label));
			}
		}

		// Sorting the fields based on the text which is displayed
		usort($options, function  ($a, $b) {
			return strcmp($a->text, $b->text);
		});

		// Reload the page when the type changes
		$uri = clone JUri::getInstance('index.php');

		// Removing the catid parameter from the actual url and set it as
		// return
		$returnUri = clone JUri::getInstance();
		$returnUri->setVar('catid', null);
		$uri->setVar('return', base64_encode($returnUri->toString()));

		// Setting the options
		$uri->setVar('option', 'com_fields');
		$uri->setVar('task', 'field.storeform');
		$uri->setVar('context', 'com_fields.field');
		$uri->setVar('formcontrol', $this->form->getFormControl());
		$uri->setVar('userstatevariable', 'com_fields.edit.field.data');
		$uri->setVar('view', null);
		$uri->setVar('layout', null);
		JFactory::getDocument()->addScriptDeclaration(
				"function typeHasChanged(element){
				var cat = jQuery(element);
				jQuery('input[name=task]').val('field.storeform');
				element.form.action='" . $uri . "';
				element.form.submit();
			}");

		return $options;
	}
}
