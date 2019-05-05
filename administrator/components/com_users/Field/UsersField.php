<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;

/**
 * User Users field..
 *
 * @since  4.0
 */
class UsersField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since   4.0
	 */
	protected $type = 'Users';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.0.0
	 */
	public function getOptions()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array(
						'users.id',
						'users.name'
					),
					array(
						'value',
						'text'
					)
				)
			)
			->from($db->quoteName('#__users', 'users'))
			->where($db->quoteName('users.block') . ' = 0')
			->order($db->quoteName('users.name') . ' ASC');

		$db->setQuery($query);
		$options = $db->loadObjectList();

		return array_merge(parent::getOptions(), $options);
	}
}

