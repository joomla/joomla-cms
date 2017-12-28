<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\User\User;

/**
 * Field to select a user ID from a modal list.
 *
 * @since  1.6
 */
class UserField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'User';

	/**
	 * Filtering groups
	 *
	 * @var   array
	 * @since 3.5
	 */
	protected $groups = null;

	/**
	 * Users to exclude from the list of users
	 *
	 * @var   array
	 * @since 3.5
	 */
	protected $excluded = null;

	/**
	 * Layout to render
	 *
	 * @var   string
	 * @since 3.5
	 */
	protected $layout = 'joomla.form.field.user';

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
	 * @since   3.7.0
	 *
	 * @see     JFormField::setup()
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		// If user can't access com_users the field should be readonly.
		if ($return && !$this->readonly)
		{
			$this->readonly = !Factory::getUser()->authorise('core.manage', 'com_users');
		}

		return $return;
	}

	/**
	 * Method to get the user field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		if (empty($this->layout))
		{
			throw new \UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
		}

		return $this->getRenderer($this->layout)->render($this->getLayoutData());

	}

	/**
	 * Get the data that is going to be passed to the layout
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	public function getLayoutData()
	{
		// Get the basic field data
		$data = parent::getLayoutData();

		// Initialize value
		$name = \JText::_('JLIB_FORM_SELECT_USER');

		if (is_numeric($this->value))
		{
			$name = User::getInstance($this->value)->name;
		}
		// Handle the special case for "current".
		elseif (strtoupper($this->value) === 'CURRENT')
		{
			// 'CURRENT' is not a reasonable value to be placed in the html
			$current = Factory::getUser();

			$this->value = $current->id;

			$data['value'] = $this->value;

			$name = $current->name;
		}

		// User lookup went wrong, we assign the value instead.
		if ($name === null && $this->value)
		{
			$name = $this->value;
		}

		$extraData = array(
			'userName'  => $name,
			'groups'    => $this->getGroups(),
			'excluded'  => $this->getExcluded(),
		);

		return array_merge($data, $extraData);
	}

	/**
	 * Method to get the filtering groups (null means no filtering)
	 *
	 * @return  mixed  Array of filtering groups or null.
	 *
	 * @since   1.6
	 */
	protected function getGroups()
	{
		if (isset($this->element['groups']))
		{
			return explode(',', $this->element['groups']);
		}

		return;
	}

	/**
	 * Method to get the users to exclude from the list of users
	 *
	 * @return  mixed  Array of users to exclude or null to to not exclude them
	 *
	 * @since   1.6
	 */
	protected function getExcluded()
	{
		if (isset($this->element['exclude']))
		{
			return explode(',', $this->element['exclude']);
		}

		return;
	}
}
