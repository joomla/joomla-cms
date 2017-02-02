<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_userlogs
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field to load a list of content authors
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldLogCreator extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'LogCreator';

	/**
	 * Cached array of the category items.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $options = array();

	/**
	 * Method to get the options to populate list
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getOptions()
	{
		// Accepted modifiers
		$hash = md5($this->element);

		if (!isset(static::$options[$hash]))
		{
			static::$options[$hash] = parent::getOptions();

			$options = array();

			$db = JFactory::getDbo();

			// Construct the query
			$query = $db->getQuery(true)
				->select($db->quoteName('u.id', 'value'))
				->select($db->quoteName('u.name', 'text'))
				->from($db->quoteName('#__users', 'u'))
				->join('INNER', $db->quoteName('#__user_logs', 'c') . ' ON ' . $db->quoteName('c.user_id') . ' = ' . $db->quoteName('u.id'))
				->group($db->quoteName('u.id'))
				->group($db->quoteName('u.name'))
				->order($db->quoteName('u.name'));

			// Setup the query
			$db->setQuery($query);

			// Return the result
			if ($options = $db->loadObjectList())
			{
				static::$options[$hash] = array_merge(static::$options[$hash], $options);
			}
		}

		return static::$options[$hash];
	}
}
