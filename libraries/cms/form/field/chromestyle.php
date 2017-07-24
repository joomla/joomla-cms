<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('groupedlist');

/**
 * Chrome Styles field.
 *
 * @since  3.0
 */
class JFormFieldChromeStyle extends JFormFieldGroupedList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.0
	 */
	public $type = 'ChromeStyle';

	/**
	 * The client ID.
	 *
	 * @var    integer
	 * @since  3.2
	 */
	protected $clientId;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to get the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'clientId':
				return $this->clientId;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to get the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'clientId':
				$this->clientId = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$result = parent::setup($element, $value, $group);

		if ($result === true)
		{
			// Get the client id.
			$clientId = $this->element['client_id'];

			if (!isset($clientId))
			{
				$clientName = $this->element['client'];

				if (isset($clientName))
				{
					$client = JApplicationHelper::getClientInfo($clientName, true);
					$clientId = $client->id;
				}
			}

			if (!isset($clientId) && $this->form instanceof JForm)
			{
				$clientId = $this->form->getValue('client_id');
			}

			$this->clientId = (int) $clientId;
		}

		return $result;
	}


	/**
	 * Method to get the list of template chrome style options
	 * grouped by template.
	 *
	 * @return  array  The field option objects as a nested array in groups.
	 *
	 * @since   3.0
	 */
	protected function getGroups()
	{
		$groups = array();

		// Add Module Style Field
		$tmp = '---' . JText::_('JLIB_FORM_VALUE_FROM_TEMPLATE') . '---';
		$groups[$tmp][] = JHtml::_('select.option', '0', JText::_('JLIB_FORM_VALUE_INHERITED'));

		$templateStyles = $this->getTemplateModuleStyles();

		// Create one new option object for each available style, grouped by templates
		foreach ($templateStyles as $template => $styles)
		{
			$template = ucfirst($template);
			$groups[$template] = array();

			foreach ($styles as $style)
			{
				$tmp = JHtml::_('select.option', $template . '-' . $style, $style);
				$groups[$template][] = $tmp;
			}
		}

		reset($groups);

		return $groups;
	}

	/**
	 * Method to get the templates module styles.
	 *
	 * @return  array  The array of styles, grouped by templates.
	 *
	 * @since   3.0
	 */
	protected function getTemplateModuleStyles()
	{
		$moduleStyles = array();

		$templates = array($this->getSystemTemplate());
		$templates = array_merge($templates, $this->getTemplates());
		$path      = JPATH_ADMINISTRATOR;

		if ($this->clientId === 0)
		{
			$path = JPATH_SITE;
		}

		foreach ($templates as $template)
		{
			$modulesFilePath = $path . '/templates/' . $template->element . '/html/modules.php';

			// Is there modules.php for that template?
			if (file_exists($modulesFilePath))
			{
				$modulesFileData = file_get_contents($modulesFilePath);

				preg_match_all('/function[\s\t]*modChrome\_([a-z0-9\-\_]*)[\s\t]*\(/i', $modulesFileData, $styles);

				if (!array_key_exists($template->element, $moduleStyles))
				{
					$moduleStyles[$template->element] = array();
				}

				$moduleStyles[$template->element] = $styles[1];
			}
		}

		return $moduleStyles;
	}

	/**
	 * Method to get the system template as an object.
	 *
	 * @return  array  The object of system template.
	 *
	 * @since   3.0
	 */
	protected function getSystemTemplate()
	{
		$template = new stdClass;
		$template->element = 'system';
		$template->name    = 'system';
		$template->enabled = 1;

		return $template;
	}

	/**
	 * Return a list of templates
	 *
	 * @return  array  List of templates
	 *
	 * @since   3.2.1
	 */
	protected function getTemplates()
	{
		$db = JFactory::getDbo();

		// Get the database object and a new query object.
		$query = $db->getQuery(true);

		// Build the query.
		$query->select('element, name, enabled')
			->from('#__extensions')
			->where('client_id = ' . $this->clientId)
			->where('type = ' . $db->quote('template'));

		// Set the query and load the templates.
		$db->setQuery($query);

		return $db->loadObjectList('element');
	}
}
