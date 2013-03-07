<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('tag');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 * @since       3.1
 */
class JFormFieldTagNested extends JFormFieldTag
{
	/**
	 * A flexible tag list that respects access controls
	 *
	 * @var    string
	 * @since  3.1
	 */
	public $type = 'TagNested';

	/**
	 * An array of tags
	 *
	 * @var    string
	 * @since  3.1
	 */
	public $tags;

		/**
	 * Method to get the field input for a tag field.
	 *
	 * @return  string  The field input.
	 *
	 * @since   3.1
	 */
	protected function getInput()
	{
		// This value is replaced with JTags object to fake parent tag formfield, othervise it is set to array ()
		$newValue = new JTags;
		$newValue->tags = $this->value;
		$this->value = $newValue;

		$input = parent::getInput();

		return $input;
	}

	/**
	 * Method to get a list of tags
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.1
	 */
	protected function getOptions()
	{
		$options = parent::getOptions();

		// Add "-" before nested tags, depending on level
		foreach ($options as &$option)
		{
			$repeat = (isset($option->level) && $option->level - 1 >= 0) ? $option->level - 1 : 0;
			$option->text = str_repeat('- ', $repeat) . $option->text;
		}

		return $options;
	}
}
