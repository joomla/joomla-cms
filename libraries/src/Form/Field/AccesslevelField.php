<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;

/**
 * Form Field class for the Joomla Platform.
 * Provides a list of access levels. Access levels control what users in specific
 * groups can see.
 *
 * @see    JAccess
 * @since  1.7.0
 */
class AccesslevelField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $type = 'Accesslevel';

	/**
	 * Cached array of the access levels.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $options;

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getOptions()
	{
		if (static::$options === null)
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select([$db->quoteName('id', 'value'), $db->quoteName('title', 'text')])
				->from($db->quoteName('#__viewlevels'))
				->group($db->quoteName(['id', 'title', 'ordering']))
				->order($db->quoteName('ordering') . ' ASC')
				->order($db->quoteName('title') . ' ASC');

			// Get the options.
			$db->setQuery($query);
			static::$options = $db->loadObjectList();
		}

		return array_merge(parent::getOptions(), static::$options);
	}
}
