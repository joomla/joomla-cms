<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Field to select a user ID from a modal list.
 *
 * @since  3.6
 */
class JFormFieldModal_User extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	public $type = 'Modal_User';

	/**
	 * Filtering groups
	 *
	 * @var  array
	 */
	protected $groups = null;

	/**
	 * Users to exclude from the list of users
	 *
	 * @var  array
	 */
	protected $excluded = null;

	/**
	 * Layout to render
	 *
	 * @var  string
	 */
	protected $layout = 'joomla.form.field.modal.user';

	/**
	 * Method to get the user field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.6
	 */
	protected function getInput()
	{
		// Load language
		JFactory::getLanguage()->load('com_users', JPATH_ADMINISTRATOR);

		if (empty($this->layout))
		{
			throw new UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
		}

		return $this->getRenderer($this->layout)->render($this->getLayoutData());

	}

	/**
	 * Get the data that is going to be passed to the layout
	 *
	 * @return  array
	 *
	 * @since   3.6
	 */
	public function getLayoutData()
	{
		// Get the basic field data
		$data = parent::getLayoutData();

		// Load the current username if available.
		$table = JTable::getInstance('user');

		if (is_numeric($this->value))
		{
			$table->load($this->value);
		}
		// Handle the special case for "current".
		elseif (strtoupper($this->value) == 'CURRENT')
		{
			// 'CURRENT' is not a reasonable value to be placed in the html
			$this->value = JFactory::getUser()->id;
			$data['value'] = $this->value;
			$table->load($this->value);
		}
		else
		{
			$table->name = JText::_('JLIB_FORM_SELECT_USER');
		}

		$extraData = array(
				'userName'   => $table->name,
				'groups'     => $this->getGroups(),
				'excluded'   => $this->getExcluded(),
				'basetype'   => $this->getBaseType(),
				'allowClear' => $this->getAllowClear(),
				'allowEdit'  => $this->getAllowEdit(),
				'allowNew'   => $this->getAllowNew(),
		);

		return array_merge($data, $extraData);
	}

	/**
	 * Method to get the filtering groups (null means no filtering)
	 *
	 * @return  mixed  array of filtering groups or null.
	 *
	 * @since   3.6
	 */
	protected function getGroups()
	{
		if (isset($this->element['groups']))
		{
			return explode(',', $this->element['groups']);
		}

		return null;
	}

	/**
	 * Method to get the users to exclude from the list of users
	 *
	 * @return  mixed  Array of users to exclude or null to to not exclude them
	 *
	 * @since   3.6
	 */
	protected function getExcluded()
	{
		return explode(',', $this->element['exclude']);
	}

	/**
	 * Method to get the base type (null means no base type)
	 *
	 * @return  mixed  Base type or null if not set
	 *
	 * @since   3.6
	 */
	protected function getBaseType()
	{
		if (isset($this->element['basetype']))
		{
			return trim((string) $this->element['basetype']);
		}

		return null;
	}

	/**
	 * Method to get if allow to clear form field input
	 *
	 * @return  boolean
	 *
	 * @since   3.6
	 */
	protected function getAllowClear()
	{
		if ((string) $this->element['required'] == 'true')
		{
			return false;
		}
		elseif ((string) $this->element['clear'] != 'false')
		{
			return true;
		}

		return false;
	}

	/**
	 * Method to get if allow to edit the active user
	 *
	 * @return  boolean
	 *
	 * @since   3.6
	 */
	protected function getAllowEdit()
	{
		return ((string) $this->element['edit'] == 'true') ? true : false;
	}

	/**
	 * Method to get if allow to create a new user
	 *
	 * @return  boolean
	 *
	 * @since   3.6
	 */
	protected function getAllowNew()
	{
		return ((string) $this->element['new'] == 'true') ? true : false;
	}
}
