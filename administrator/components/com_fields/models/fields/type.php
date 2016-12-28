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

JLoader::register('JFolder', JPATH_LIBRARIES . '/joomla/filesystem/folder.php');

/**
 * Fields Type
 *
 * @since  3.7.0
 */
class JFormFieldType extends JFormAbstractlist
{
	public $type = 'Type';

	public static $BLACKLIST = array('moduleposition', 'aliastag');

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.7.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		$this->onchange = 'typeHasChanged(this);';

		return $return;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.7.0
	 */
	protected function getOptions()
	{
		$options = parent::getOptions();

		FieldsHelper::loadPlugins();
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
				$name = str_replace('.php', '', basename($filePath));

				if (in_array(strtolower($name), self::$BLACKLIST))
				{
					continue;
				}

				$className = JFormHelper::loadFieldClass($name);

				if ($className === false)
				{
					continue;
				}

				// Check if the field implements JFormField and JFormDomFieldInterface
				if (!is_subclass_of($className, 'JFormField') || !is_subclass_of($className, 'JFormDomfieldinterface'))
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
		usort(
			$options,
			function ($a, $b)
			{
				return strcmp($a->text, $b->text);
			}
		);

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
