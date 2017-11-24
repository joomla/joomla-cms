<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('groupedlist');

/**
 * Supports a select grouped list of template styles
 *
 * @since  1.6
 */
class TemplatestyleField extends \JFormFieldGroupedList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'TemplateStyle';

	/**
	 * The client name.
	 *
	 * @var    mixed
	 * @since  3.2
	 */
	protected $clientName;

	/**
	 * The template.
	 *
	 * @var    mixed
	 * @since  3.2
	 */
	protected $template;

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
			case 'clientName':
			case 'template':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to set the value.
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
			case 'clientName':
			case 'template':
				$this->$name = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     FormField::setup()
	 * @since   3.2
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$result = parent::setup($element, $value, $group);

		if ($result === true)
		{
			// Get the clientName template.
			$this->clientName = $this->element['client'] ? (string) $this->element['client'] : 'site';
			$this->template = (string) $this->element['template'];
		}

		return $result;
	}

	/**
	 * Method to get the list of template style options grouped by template.
	 * Use the client attribute to specify a specific client.
	 * Use the template attribute to specify a specific template
	 *
	 * @return  array  The field option objects as a nested array in groups.
	 *
	 * @since   1.6
	 */
	protected function getGroups()
	{
		$groups = array();
		$lang = Factory::getLanguage();

		// Get the client and client_id.
		$client = ApplicationHelper::getClientInfo($this->clientName, true);

		// Get the template.
		$template = $this->template;

		// Get the database object and a new query object.
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		// Build the query.
		$query->select('s.id, s.title, e.name as name, s.template')
			->from('#__template_styles as s')
			->where('s.client_id = ' . (int) $client->id)
			->order('template')
			->order('title');

		if ($template)
		{
			$query->where('s.template = ' . $db->quote($template));
		}

		$query->join('LEFT', '#__extensions as e on e.element=s.template')
			->where('e.enabled = 1')
			->where($db->quoteName('e.type') . ' = ' . $db->quote('template'));

		// Set the query and load the styles.
		$db->setQuery($query);
		$styles = $db->loadObjectList();

		// Build the grouped list array.
		if ($styles)
		{
			foreach ($styles as $style)
			{
				$template = $style->template;
				$lang->load('tpl_' . $template . '.sys', $client->path, null, false, true)
					|| $lang->load('tpl_' . $template . '.sys', $client->path . '/templates/' . $template, null, false, true);
				$name = \JText::_($style->name);

				// Initialize the group if necessary.
				if (!isset($groups[$name]))
				{
					$groups[$name] = array();
				}

				$groups[$name][] = \JHtml::_('select.option', $style->id, $style->title);
			}
		}

		// Merge any additional groups in the XML definition.
		$groups = array_merge(parent::getGroups(), $groups);

		return $groups;
	}
}
