<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_login
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Login\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Cache\Exception\CacheExceptionInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\Exception\ExecutionFailureException;

/**
 * Login Model
 *
 * @since  1.5
 */
class LoginModel extends BaseDatabaseModel
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
		$input = Factory::getApplication()->input->getInputForRequestMethod();

		$credentials = array(
			'username'  => $input->get('username', '', 'USERNAME'),
			'password'  => $input->get('passwd', '', 'RAW'),
			'secretkey' => $input->get('secretkey', '', 'RAW'),
		);

		$this->setState('credentials', $credentials);

		// Check for return URL from the request first.
		if ($return = $input->get('return', '', 'BASE64'))
		{
			$return = base64_decode($return);

			if (!Uri::isInternal($return))
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
	 * @since   1.7.0
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
			$result = new \stdClass;
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
	 * @since   1.7.0
	 */
	protected static function _load($module)
	{
		static $clean;

		if (isset($clean))
		{
			return $clean;
		}

		$app      = Factory::getApplication();
		$lang     = Factory::getLanguage()->getTag();
		$clientId = (int) $app->getClientId();

		/** @var \Joomla\CMS\Cache\Controller\CallbackController $cache */
		$cache = Factory::getCache('com_modules', 'callback');

		$loader = function () use ($app, $lang, $module) {
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->select(
					$db->quoteName(
						[
							'm.id',
							'm.title',
							'm.module',
							'm.position',
							'm.showtitle',
							'm.params'
						]
					)
				)
				->from($db->quoteName('#__modules', 'm'))
				->where($db->quoteName('m.module') . ' = :module')
				->where($db->quoteName('m.client_id') . ' = 1')
				->join(
					'LEFT',
					$db->quoteName('#__extensions', 'e'),
					$db->quoteName('e.element') . ' = ' . $db->quoteName('m.module') .
					' AND ' . $db->quoteName('e.client_id') . ' = ' . $db->quoteName('m.client_id')
				)
				->where($db->quoteName('e.enabled') . ' = 1')
				->bind(':module', $module);

			// Filter by language.
			if ($app->isClient('site') && $app->getLanguageFilter())
			{
				$query->whereIn($db->quoteName('m.language'), [$lang, '*']);
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
		catch (CacheExceptionInterface $cacheException)
		{
			try
			{
				return $loader();
			}
			catch (ExecutionFailureException $databaseException)
			{
				Factory::getApplication()->enqueueMessage(
					Text::sprintf('JLIB_APPLICATION_ERROR_MODULE_LOAD', $databaseException->getMessage()),
					'error'
				);

				return array();
			}
		}
		catch (ExecutionFailureException $databaseException)
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('JLIB_APPLICATION_ERROR_MODULE_LOAD', $databaseException->getMessage()), 'error');

			return array();
		}
	}
}
