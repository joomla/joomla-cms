<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

\defined('JPATH_PLATFORM') or die;

/**
 * Switcher Form Field class.
 *
 * @since  __DEPLOY_VERSION__
 */
class SwitcherField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'Switcher';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $layout = 'joomla.form.field.switcher';

	/**
	 * Method to get the radio button field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @throws  \UnexpectedValueException
	 */
	protected function getInput()
	{
		if ($this->layout === '')
		{
			throw new \UnexpectedValueException(sprintf('%s has no layout assigned.', $this->name));
		}

		return parent::getInput();
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since  __DEPLOY_VERSION__
	 *
	 * @throws  \UnexpectedValueException
	 */
	protected function getOptions()
	{
		$options = parent::getOptions();

		if (\count($options) !== 2)
		{
			throw new \UnexpectedValueException(sprintf('%s field of type %s must have exactly 2 options.', $this->name, $this->type));
		}

		return $options;
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getLayoutData()
	{
		$data            = parent::getLayoutData();
		$data['options'] = $this->getOptions();
		$data['value']   = $this->value;

		return $data;
	}
}
