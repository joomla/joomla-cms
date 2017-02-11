<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_login
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Login Model
 *
 * @since  1.5
 */
class LoginModelLogin extends JModelLegacy
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();

		$input = $app->input;
		$method = $input->getMethod();

		$credentials = array(
			'username' => $input->$method->get('username', '', 'USERNAME'),
			'password' => $input->$method->get('passwd', '', 'RAW'),
			'secretkey' => $input->$method->get('secretkey', '', 'RAW'),
		);
		$this->setState('credentials', $credentials);

		// Check for return URL from the request first.
		if ($return = $input->$method->get('return', '', 'BASE64'))
		{
			$return = base64_decode($return);

			if (!JUri::isInternal($return))
			{
				$return = '';
			}
		}

		// Set the return URL if empty.
		if (empty($return))
		{
			$return = 'index.php';
		}

		$this->setState('return', $return);
	}

	/**
	 * Get the administrator login module by name (real, eg 'login' or folder, eg 'mod_login').
	 *
	 * @param   string  $name   The name of the module.
	 * @param   string  $title  The title of the module, optional.
	 *
	 * @return  object  The Module object.
	 *
	 * @since   11.1
	 */
	public static function getLoginModule($name = 'mod_login', $title = null)
	{
		$result = null;
		$modules = self::_load($name);
		$total = count($modules);

		for ($i = 0; $i < $total; $i++)
		{
			// Match the title if we're looking for a specific instance of the module.
			if (!$title || $modules[$i]->title == $title)
			{
				$result = $modules[$i];
				break;
			}
		}

		// If we didn't find it, and the name is mod_something, create a dummy object.
		if (is_null($result) && substr($name, 0, 4) == 'mod_')
		{
			$result = new stdClass;
			$result->id = 0;
			$result->title = '';
			$result->module = $name;
			$result->position = '';
			$result->content = '';
			$result->showtitle = 0;
			$result->control = '';
			$result->params = '';
			$result->user = 0;
		}

		return $result;
	}

	/**
	 * Load login modules.
	 *
	 * Note that we load regardless of state or access level since access
	 * for public is the only thing that makes sense since users are not logged in
	 * and the module lets them log in.
	 * This is put in as a failsafe to avoid super user lock out caused by an unpublished
	 * login module or by a module set to have a viewing access level that is not Public.
	 *
	 * @param   string  $module  The name of the module.
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	protected static function _load($module)
	{
		static $clean;

		if (isset($clean))
		{
			return $clean;
		}

		$app      = JFactory::getApplication();
		$lang     = JFactory::getLanguage()->getTag();
		$clientId = (int) $app->getClientId();

		/** @var JCacheControllerCallback $cache */
		$cache = JFactory::getCache('com_modules', 'callback');

		$loader = function () use ($app, $lang, $module) {
			$db = JFactory::getDbo();

			$query = $db->getQuery(true)
				->select('m.id, m.title, m.module, m.position, m.showtitle, m.params')
				->from('#__modules AS m')
				->where('m.module =' . $db->quote($module) . ' AND m.client_id = 1')
				->join('LEFT', '#__extensions AS e ON e.element = m.module AND e.client_id = m.client_id')
				->where('e.enabled = 1');

			// Filter by language.
			if ($app->isClient('site') && $app->getLanguageFilter())
			{
				$query->where('m.language IN (' . $db->quote($lang) . ',' . $db->quote('*') . ')');
			}

			$query->order('m.position, m.ordering');

			// Set the query.
			$db->setQuery($query);

			return $db->loadObjectList();
		};

		try
		{
			return $clean = $cache->get($loader, array(), md5(serialize(array($clientId, $lang))));
		}
		catch (JCacheExceptionConnecting $cacheException)
		{
			try
			{
				return $loader();
			}
			catch (JDatabaseExceptionExecuting $databaseException)
			{
				JError::raiseWarning(500, JText::sprintf('JLIB_APPLICATION_ERROR_MODULE_LOAD', $databaseException->getMessage()));

				return array();
			}
		}
		catch (JCacheExceptionUnsupported $cacheException)
		{
			try
			{
				return $loader();
			}
			catch (JDatabaseExceptionExecuting $databaseException)
			{
				JError::raiseWarning(500, JText::sprintf('JLIB_APPLICATION_ERROR_MODULE_LOAD', $databaseException->getMessage()));

				return array();
			}
		}
		catch (JDatabaseExceptionExecuting $databaseException)
		{
			JError::raiseWarning(500, JText::sprintf('JLIB_APPLICATION_ERROR_MODULE_LOAD', $databaseException->getMessage()));

			return array();
		}
	}
}
