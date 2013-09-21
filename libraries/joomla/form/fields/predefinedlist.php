<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field to load a list of predefined values
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 * @since       3.2
 */
class JFormFieldPredefinedList extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $type = 'PredefinedList';

	/**
	 * Cached array of the category items.
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected static $options = array();

	/**
	 * Available predefined options
	 *
	 * @var  array
	 * @since  3.2
	 */
	protected $predefinedOptions = array();

	/**
	 * Translate options labels ?
	 *
	 * @var  boolean
	 * @since  3.2
	 */
	protected $translate = true;

	/**
	 * Method to get the options to populate list
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.2
	 */
	protected function getOptions()
	{
		// Hash for caching
		$hash = md5($this->element);

		if (!isset(static::$options[$hash]) && !empty($this->predefinedOptions))
		{
			static::$options[$hash] = parent::getOptions();

			$options = array();

			foreach ($this->predefinedOptions as $value => $text)
			{
				$text = $this->translate ? JText::_($text) : $text;

				$options[] = (object) array(
					'value' => $value,
					'text'  => $text
				);
			}

			static::$options[$hash] = array_merge(static::$options[$hash], $options);
		}

		return static::$options[$hash];
	}
}
