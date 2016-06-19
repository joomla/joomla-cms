<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\String\StringHelper;

JFormHelper::loadFieldClass('list');
JLoader::import('joomla.filesystem.folder');

class JFormFieldType extends JFormFieldList
{

	public $type = 'Type';

	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);
		$this->onchange = "typeHasChanged(this);";
		return $return;
	}

	protected function getOptions()
	{
		$options = parent::getOptions();

		JFormHelper::addFieldPath(JPATH_LIBRARIES . '/cms/form/field');
		$paths = JFormHelper::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_fields/models/fields');

		$component = null;

		$parts = FieldsHelper::extract(JFactory::getApplication()->input->get('context'));
		if ($parts)
		{
			$component = $parts[0];
			$paths[] = JPATH_ADMINISTRATOR . '/components/' . $component . '/models/fields';
			JFactory::getLanguage()->load($component, JPATH_ADMINISTRATOR);
			JFactory::getLanguage()->load($component, JPATH_ADMINISTRATOR . '/components/' . $component);
		}

		foreach ($paths as $path)
		{
			if (!JFolder::exists($path))
			{
				continue;
			}
			// Looping trough the types
			foreach (JFolder::files($path, 'php', true, true) as $filePath)
			{
				$className = $this->getClassNameFromFile($filePath);
				if ($className === false)
				{
					continue;
				}

				// Adjust the name
				$name = strtolower(str_replace('JFormField', '', $className));

				$label = StringHelper::ucfirst($name);
				if (JFactory::getLanguage()->hasKey('COM_FIELDS_TYPE_' . strtoupper($name)))
				{
					$label = 'COM_FIELDS_TYPE_' . strtoupper($name);
				}
				if ($component && JFactory::getLanguage()->hasKey(strtoupper($component) . '_FIELDS_TYPE_' . strtoupper($name)))
				{
					$label = strtoupper($component) . '_FIELDS_TYPE_' . strtoupper($name);
				}
				$options[] = JHtml::_('select.option', $name, JText::_($label));
			}
		}

		// Sorting the fields based on the text which is displayed
		usort($options, function ($a, $b)
		{
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

	/**
	 * Parses the file with the given path. If it is a class starting with the
	 * name JFormField and implementing JFormDomfieldinterface, then the class name is returned.
	 *
	 * @param string $path
	 * @return string|boolean
	 */
	private function getClassNameFromFile($path)
	{
		$tokens = token_get_all(JFile::read($path));

		$className = null;
		for ($i = 2; $i < count($tokens); $i ++)
		{
			if ($tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING &&
					 strpos($tokens[$i][1], 'JFormField') !== false)
			{
				$className = $tokens[$i][1];
			}
			if ($tokens[$i - 2][0] == T_IMPLEMENTS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][1] == 'JFormDomfieldinterface')
			{
				return $className;
			}
		}
		return false;
	}
}
