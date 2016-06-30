<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
JLoader::import('joomla.filesystem.folder');

/**
 * Fields Section
 *
 * @since  3.7
 */
class JFormFieldSection extends JFormAbstractlist
{

	public $type = 'Section';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions ()
	{
		$options = parent::getOptions();

		foreach (JHtmlSidebar::getEntries() as $entry)
		{
			if (strpos($entry[1], 'com_categories') === false || strpos($entry[1], '.fields') === false)
			{
				continue;
			}

			$uri = JUri::getInstance($entry[1]);
			$extension = $uri->getVar('extension');

			if ($extension)
			{
				$options[] = JHtml::_('select.option', $extension . '.category', JText::_('JCategory'));
			}
		}

		return $options;
	}

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
	 * @since   11.1
	 */
	public function setup (SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		// Onchange must always be the change context function
		$this->onchange = 'fieldsChangeContext(jQuery(this).val());';

		return $return;
	}

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput ()
	{
		// Add the change context function to the document
		JFactory::getDocument()->addScriptDeclaration(
				"function fieldsChangeContext(context)
{
	var regex = new RegExp(\"([?;&])context[^&;]*[;&]?\");
	var url = window.location.href;
    var query = url.replace(regex, \"$1\").replace(/&$/, '');

    window.location.href = (query.length > 2 ? query + \"&\" : \"?\") + (context ? \"context=\" + context : '');
}");

		return parent::getInput();
	}
}
