<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\Exception\ExecutionFailureException;

/**
 * Supports a custom SQL select list
 *
 * @since  1.7.0
 */
class SqlField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $type = 'SQL';

	/**
	 * The keyField.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $keyField;

	/**
	 * The valueField.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $valueField;

	/**
	 * The translate.
	 *
	 * @var    boolean
	 * @since  3.2
	 */
	protected $translate = false;

	/**
	 * The query.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $query;

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
			case 'keyField':
			case 'valueField':
			case 'translate':
			case 'query':
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
			case 'keyField':
			case 'valueField':
			case 'translate':
			case 'query':
				$this->$name = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a Form object to the field.
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
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			// Check if its using the old way
			$this->query = (string) $this->element['query'];

			if (empty($this->query))
			{
				// Get the query from the form
				$query    = array();
				$defaults = array();

				$sql_select = (string) $this->element['sql_select'];
				$sql_from   = (string) $this->element['sql_from'];

				if ($sql_select && $sql_from)
				{
					$query['select'] = $sql_select;
					$query['from']   = $sql_from;
					$query['join']   = (string) $this->element['sql_join'];
					$query['where']  = (string) $this->element['sql_where'];
					$query['group']  = (string) $this->element['sql_group'];
					$query['order']  = (string) $this->element['sql_order'];

					// Get the filters
					$filters = isset($this->element['sql_filter']) ? explode(',', $this->element['sql_filter']) : '';

					// Get the default value for query if empty
					if (\is_array($filters))
					{
						foreach ($filters as $filter)
						{
							$name   = "sql_default_{$filter}";
							$attrib = (string) $this->element[$name];

							if (!empty($attrib))
							{
								$defaults[$filter] = $attrib;
							}
						}
					}

					// Process the query
					$this->query = $this->processQuery($query, $filters, $defaults);
				}
			}

			$this->keyField   = (string) $this->element['key_field'] ?: 'value';
			$this->valueField = (string) $this->element['value_field'] ?: (string) $this->element['name'];
			$this->translate  = (string) $this->element['translate'] ?: false;
			$this->header     = (string) $this->element['header'] ?: false;
		}

		return $return;
	}

	/**
	 * Method to process the query from form.
	 *
	 * @param   array   $conditions  The conditions from the form.
	 * @param   string  $filters     The columns to filter.
	 * @param   array   $defaults    The defaults value to set if condition is empty.
	 *
	 * @return  DatabaseQuery  The query object.
	 *
	 * @since   3.5
	 */
	protected function processQuery($conditions, $filters, $defaults)
	{
		// Get the database object.
		$db = Factory::getDbo();

		// Get the query object
		$query = $db->getQuery(true);

		// Select fields
		$query->select($conditions['select']);

		// From selected table
		$query->from($conditions['from']);

		// Join over the groups
		if (!empty($conditions['join']))
		{
			$query->join('LEFT', $conditions['join']);
		}

		// Where condition
		if (!empty($conditions['where']))
		{
			$query->where($conditions['where']);
		}

		// Group by
		if (!empty($conditions['group']))
		{
			$query->group($conditions['group']);
		}

		// Process the filters
		if (\is_array($filters))
		{
			$html_filters = Factory::getApplication()->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array');

			foreach ($filters as $k => $value)
			{
				if (!empty($html_filters[$value]))
				{
					$escape = $db->quote($db->escape($html_filters[$value]), false);

					$query->where("{$value} = {$escape}");
				}
				elseif (!empty($defaults[$value]))
				{
					$escape = $db->quote($db->escape($defaults[$value]), false);

					$query->where("{$value} = {$escape}");
				}
			}
		}

		// Add order to query
		if (!empty($conditions['order']))
		{
			$query->order($conditions['order']);
		}

		return $query;
	}

	/**
	 * Method to get the custom field options.
	 * Use the query attribute to supply a query to generate the list.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.7.0
	 */
	protected function getOptions()
	{
		$options = array();

		// Initialize some field attributes.
		$key   = $this->keyField;
		$value = $this->valueField;
		$header = $this->header;

		if ($this->query)
		{
			// Get the database object.
			$db = Factory::getDbo();

			// Set the query and get the result list.
			$db->setQuery($this->query);

			try
			{
				$items = $db->loadObjectList();
			}
			catch (ExecutionFailureException $e)
			{
				Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			}
		}

		// Add header.
		if (!empty($header))
		{
			$header_title = Text::_($header);
			$options[] = HTMLHelper::_('select.option', '', $header_title);
		}

		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				if ($this->translate == true)
				{
					$options[] = HTMLHelper::_('select.option', $item->$key, Text::_($item->$value));
				}
				else
				{
					$options[] = HTMLHelper::_('select.option', $item->$key, $item->$value);
				}
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
