<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Actionlogs\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;

/**
 * Field to load a list of all users that have logged actions
 *
 * @since  3.9.0
 */
class LogcreatorField extends ListField
{
	/**
	 * Cached array of the category items.
	 *
	 * @var    array
	 * @since  3.9.0
	 */
	protected static $options = [];

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.9.0
	 */
	protected $type = 'LogCreator';

	/**
	 * Method to get the options to populate list
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.9.0
	 */
	protected function getOptions()
	{
		// Accepted modifiers
		$hash = md5($this->element);

		if (!isset(static::$options[$hash]))
		{
			static::$options[$hash] = parent::getOptions();

			$db = Factory::getDbo();

			// Construct the query
			$query = $db->getQuery(true)
				->select($db->quoteName('u.id', 'value'))
				->select($db->quoteName('u.username', 'text'))
				->from($db->quoteName('#__users', 'u'))
				->join('INNER', $db->quoteName('#__action_logs', 'c') . ' ON ' . $db->quoteName('c.user_id') . ' = ' . $db->quoteName('u.id'))
				->group($db->quoteName('u.id'))
				->group($db->quoteName('u.username'))
				->order($db->quoteName('u.username'));

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
