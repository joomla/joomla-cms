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
 * Form Field to load a list of states
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 * @since       3.2
 */
class JFormFieldStatus extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $type = 'Status';

	/**
	 * Cached array of the category items.
	 *
	 * @var    array
	 * @since  1.2
	 */
	protected static $options = array();

	/**
	 * Available statuses
	 *
	 * @var  array
	 */
	protected $availableStatuses = array(
		'-2' =>	'JTRASHED',
		'0'  => 'JUNPUBLISHED',
		'1'  => 'JPUBLISHED',
		'2'  => 'JARCHIVED',
		'*'  => 'JALL'
	);

	/**
	 * Method to get the options to populate list
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.2
	 */
	protected function getOptions()
	{
		// Accepted modifiers
		$hash = md5($this->element);

		if (!isset(static::$options[$hash]))
		{
			static::$options = parent::getOptions();

			$options = array();

			// Statuses to show
			$statuses = isset($this->element['statuses']) ? $this->element['statuses'] : '0,1';

			if (!empty($statuses) && !empty($this->availableStatuses))
			{
				$statuses = explode(',', $statuses);

				foreach ($statuses as $status)
				{
					if (isset($this->availableStatuses[$status]))
					{
						$options[] = (object) array(
							'value' => $status,
							'text' => JText::_($this->availableStatuses[$status])
						);
					}
				}

				static::$options = array_merge(static::$options, $options);
			}
		}

		return static::$options;
	}
}
