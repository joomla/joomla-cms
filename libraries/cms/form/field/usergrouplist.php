<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Field to load a drop down list of available user groups
 *
 * @since  3.2
 */
class JFormFieldUserGroupList extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   3.2
	 */
	protected $type = 'UserGroupList';

	/**
	 * Cached array of the category items.
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected static $options = array();

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

		if (!isset(static::$options[$hash]))
		{
			static::$options[$hash] = parent::getOptions();

			$options = array();

			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('a.id AS value')
				->select('a.title AS text')
				->select('COUNT(DISTINCT b.id) AS level')
				->from('#__usergroups as a')
				->join('LEFT', '#__usergroups  AS b ON a.lft > b.lft AND a.rgt < b.rgt')
				->group('a.id, a.title, a.lft, a.rgt')
				->order('a.lft ASC');
			$db->setQuery($query);

			if ($options = $db->loadObjectList())
			{
				foreach ($options as &$option)
				{
					$option->text = str_repeat('- ', $option->level) . $option->text;
				}

				static::$options[$hash] = array_merge(static::$options[$hash], $options);
			}
		}

		return static::$options[$hash];
	}
}
