<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\UCM\UCMType;

/**
 * Ordering field.
 *
 * @since  3.2
 */
class OrderingField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   3.2
	 */
	protected $type = 'Ordering';

	/**
	 * The form field content type.
	 *
	 * @var		string
	 * @since   3.2
	 */
	protected $contentType;

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
			case 'contentType':
				return $this->contentType;
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
			case 'contentType':
				$this->contentType = (string) $value;
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
			$this->contentType = (string) $this->element['content_type'];
		}

		return $result;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 *
	 * @since   3.2
	 */
	protected function getInput()
	{
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= $this->disabled ? ' disabled' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';

		// Initialize JavaScript field attributes.
		$attr .= !empty($this->onchange) ? ' onchange="' . $this->onchange . '"' : '';

		$itemId = (int) $this->getItemId();

		$query = $this->getQuery();

		// Create a read-only list (no name) with a hidden input to store the value.
		if ($this->readonly)
		{
			$html[] = \JHtml::_('list.ordering', '', $query, trim($attr), $this->value, $itemId ? 0 : 1);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';
		}
		else
		{
			// Create a regular list.
			$html[] = \JHtml::_('list.ordering', $this->name, $query, trim($attr), $this->value, $itemId ? 0 : 1);
		}

		return implode($html);
	}

	/**
	 * Builds the query for the ordering list.
	 *
	 * @return  \JDatabaseQuery  The query for the ordering form field
	 *
	 * @since   3.2
	 */
	protected function getQuery()
	{
		$categoryId   = (int) $this->form->getValue('catid');
		$ucmType      = new UCMType;
		$ucmRow       = $ucmType->getType($ucmType->getTypeId($this->contentType));
		$ucmMapCommon = json_decode($ucmRow->field_mappings)->common;

		if (is_object($ucmMapCommon))
		{
			$ordering = $ucmMapCommon->core_ordering;
			$title    = $ucmMapCommon->core_title;
		}
		elseif (is_array($ucmMapCommon))
		{
			$ordering = $ucmMapCommon[0]->core_ordering;
			$title    = $ucmMapCommon[0]->core_title;
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array($db->quoteName($ordering, 'value'), $db->quoteName($title, 'text')))
			->from($db->quoteName(json_decode($ucmRow->table)->special->dbtable))
			->where($db->quoteName('catid') . ' = ' . (int) $categoryId)
			->order('ordering');

		return $query;
	}

	/**
	 * Retrieves the current Item's Id.
	 *
	 * @return  integer  The current item ID
	 *
	 * @since   3.2
	 */
	protected function getItemId()
	{
		return (int) $this->form->getValue('id');
	}
}
