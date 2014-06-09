<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Field to select a user ID from a modal list.
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 * @since       1.6
 */
class JFormFieldUser extends JFormField
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
	protected $layout = 'joomla.form.field.user';

	/**
	 * Method to get the user field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$layout = !empty($this->element['layout']) ? (string) $this->element['layout'] : $this->layout;

		// Ensure field meets the requirements
		if ($layout && $this->validateRequirements())
		{
			return $this->getRenderer($layout)->render(
				array(
					'id'        => $this->id,
					'element'   => $this->element,
					'field'     => $this,
					'name'      => $this->name,
					'required'  => $this->required,
					'value'     => $this->value,
					'class'     => $this->class,
					'size'      => $this->size,
					'groups'    => $this->getGroups(),
					'excluded'  => $this->getExcluded()
				)
			);
		}

		return;
	}

	/**
	 * Method to get the filtering groups (null means no filtering)
	 *
	 * @return  mixed  array of filtering groups or null.
	 *
	 * @since   1.6
	 */
	protected function getGroups()
	{
		return $this->groups;
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
		return $this->excluded;
	}
}
