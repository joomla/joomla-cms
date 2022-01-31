<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Model
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installation\Helper\DatabaseHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Version;
use Joomla\Database\DatabaseDriver;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Configuration setup model for the Joomla Core Installer.
 *
 * @since  3.1
 */
class ConfigurationModel extends BaseInstallationModel
{
	/**
	 * The generated user ID.
	 *
	 * @var    integer
	 * @since  4.0.0
	 */
	protected static $userId = 0;

	/**
	 * Method to setup the configuration file
	 *
	 * @param   array  $options  The session options
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.1
	 */
	public function setup($options)
	{
		// Get the options as an object for easier handling.
		$options = ArrayHelper::toObject($options);

		// Get a database object.
		try
		{
			$db = DatabaseHelper::getDbo(
				$options->db_type,
				$options->db_host,
				$options->db_user,
				$options->db_pass_plain,
				$options->db_name,
				$options->db_prefix,
				true,
				DatabaseHelper::getEncryptionSettings($options)
			);
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('INSTL_ERROR_CONNECT_DB', $e->getMessage()), 'error');

			return false;
		}

		// Attempt to create the configuration.
		if (!$this->createConfiguration($options))
		{
			return false;
		}

		$serverType = $db->getServerType();

		// Attempt to update the table #__schema.
		$pathPart = JPATH_ADMINISTRATOR . '/components/com_admin/sql/updates/' . $serverType . '/';

		$files = Folder::files($pathPart, '\.sql$');

		if (empty($files))
		{
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_ERROR_INITIALISE_SCHEMA'), 'error');

			return false;
		}

		$version = '';

		foreach ($files as $file)
		{
			if (version_compare($version, File::stripExt($file)) < 0)
			{
				$version = File::stripExt($file);
			}
		}

		$query = $db->getQuery(true)
			->select('extension_id')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('name') . ' = ' . $db->quote('files_joomla'));
		$db->setQuery($query);
		$eid = $db->loadResult();

		$query->clear()
			->insert($db->quoteName('#__schemas'))
			->columns(
				array(
					$db->quoteName('extension_id'),
					$db->quoteName('version_id')
				)
			)
			->values($eid . ', ' . $db->quote($version));
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		// Attempt to refresh manifest caches.
		$query->clear()
			->select('*')
			->from('#__extensions');
		$db->setQuery($query);

		$return = true;

		try
		{
			$extensions = $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			$return = false;
		}

		Factory::$database = $db;
		$installer = Installer::getInstance();

		foreach ($extensions as $extension)
		{
			if (!$installer->refreshManifestCache($extension->extension_id))
			{
				Factory::getApplication()->enqueueMessage(
					Text::sprintf('INSTL_DATABASE_COULD_NOT_REFRESH_MANIFEST_CACHE', $extension->name),
					'error'
				);

				return false;
			}
		}

		// Handle default backend language setting. This feature is available for localized versions of Joomla.
		$languages = Factory::getApplication()->getLocaliseAdmin($db);

		if (in_array($options->language, $languages['admin']) || in_array($options->language, $languages['site']))
		{
			// Build the language parameters for the language manager.
			$params = array();

			// Set default administrator/site language to sample data values.
			$params['administrator'] = 'en-GB';
			$params['site']          = 'en-GB';

			if (in_array($options->language, $languages['admin']))
			{
				$params['administrator'] = $options->language;
			}

			if (in_array($options->language, $languages['site']))
			{
				$params['site'] = $options->language;
			}

			$params = json_encode($params);

			// Update the language settings in the language manager.
			$query->clear()
				->update($db->quoteName('#__extensions'))
				->set($db->quoteName('params') . ' = ' . $db->quote($params))
				->where($db->quoteName('element') . ' = ' . $db->quote('com_languages'));
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (\RuntimeException $e)
			{
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

				$return = false;
			}
		}

		// Attempt to create the root user.
		if (!$this->createRootUser($options, $db))
		{
			$this->deleteConfiguration();

			return false;
		}

		// Update the cms data user ids.
		$this->updateUserIds($db);

		// Check for testing sampledata plugin.
		$this->checkTestingSampledata($db);

		return $return;
	}

	/**
	 * Retrieves the default user ID and sets it if necessary.
	 *
	 * @return  integer  The user ID.
	 *
	 * @since   3.1
	 */
	public static function getUserId()
	{
		if (!self::$userId)
		{
			self::$userId = self::generateRandUserId();
		}

		return self::$userId;
	}

	/**
	 * Generates the user ID.
	 *
	 * @return  integer  The user ID.
	 *
	 * @since   3.1
	 */
	protected static function generateRandUserId()
	{
		$session    = Factory::getSession();
		$randUserId = $session->get('randUserId');

		if (empty($randUserId))
		{
			// Create the ID for the root user only once and store in session.
			$randUserId = mt_rand(1, 1000);
			$session->set('randUserId', $randUserId);
		}

		return $randUserId;
	}

	/**
	 * Resets the user ID.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public static function resetRandUserId()
	{
		self::$userId = 0;

		Factory::getSession()->set('randUserId', self::$userId);
	}

	/**
	 * Method to update the user id of sql data content to the new rand user id.
	 *
	 * @param   DatabaseDriver  $db  Database connector object $db*.
	 *
	 * @return  void
	 *
	 * @since   3.6.1
	 */
	protected function updateUserIds($db)
	{
		// Create the ID for the root user.
		$userId = self::getUserId();

		// Update all core tables created_by fields of the tables with the random user id.
		$updatesArray = array(
			'#__banners'         => array('created_by', 'modified_by'),
			'#__categories'      => array('created_user_id', 'modified_user_id'),
			'#__contact_details' => array('created_by', 'modified_by'),
			'#__content'         => array('created_by', 'modified_by'),
			'#__fields'          => array('created_user_id', 'modified_by'),
			'#__finder_filters'  => array('created_by', 'modified_by'),
			'#__newsfeeds'       => array('created_by', 'modified_by'),
			'#__tags'            => array('created_user_id', 'modified_user_id'),
			'#__ucm_content'     => array('core_created_user_id', 'core_modified_user_id'),
			'#__history'         => array('editor_user_id'),
			'#__user_notes'      => array('created_user_id', 'modified_user_id'),
			'#__workflows'       => array('created_by', 'modified_by'),
		);

		foreach ($updatesArray as $table => $fields)
		{
			foreach ($fields as $field)
			{
				$query = $db->getQuery(true)
					->update($db->quoteName($table))
					->set($db->quoteName($field) . ' = ' . $db->quote($userId))
					->where($db->quoteName($field) . ' != 0')
					->where($db->quoteName($field) . ' IS NOT NULL');

				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (\RuntimeException $e)
				{
					Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
				}
			}
		}
	}

	/**
	 * Method to check for the testing sampledata plugin.
	 *
	 * @param   DatabaseDriver  $db  Database connector object $db*.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function checkTestingSampledata($db)
	{
		$version = new Version;

		if (!$version->isInDevelopmentState() || !is_file(JPATH_PLUGINS . '/sampledata/testing/testing.php'))
		{
			return;
		}

		$testingPlugin = new \stdClass;
		$testingPlugin->extension_id = null;
		$testingPlugin->name = 'plg_sampledata_testing';
		$testingPlugin->type = 'plugin';
		$testingPlugin->element = 'testing';
		$testingPlugin->folder = 'sampledata';
		$testingPlugin->client_id = 0;
		$testingPlugin->enabled = 1;
		$testingPlugin->access = 1;
		$testingPlugin->manifest_cache = '';
		$testingPlugin->params = '{}';
		$testingPlugin->custom_data = '';

		$db->insertObject('#__extensions', $testingPlugin, 'extension_id');

		$installer = new Installer;

		if (!$installer->refreshManifestCache($testingPlugin->extension_id))
		{
			Factory::getApplication()->enqueueMessage(
				Text::sprintf('INSTL_DATABASE_COULD_NOT_REFRESH_MANIFEST_CACHE', $testingPlugin->name),
				'error'
			);
		}
	}

	/**
	 * Method to create the configuration file
	 *
	 * @param   \stdClass  $options  The session options
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.1
	 */
	public function createConfiguration($options)
	{
		// Create a new registry to build the configuration options.
		$registry = new Registry;

		// Site settings.
		$registry->set('offline', false);
		$registry->set('offline_message', Text::_('INSTL_STD_OFFLINE_MSG'));
		$registry->set('display_offline_message', 1);
		$registry->set('offline_image', '');
		$registry->set('sitename', $options->site_name);
		$registry->set('editor', 'tinymce');
		$registry->set('captcha', '0');
		$registry->set('list_limit', 20);
		$registry->set('access', 1);

		// Debug settings.
		$registry->set('debug', false);
		$registry->set('debug_lang', false);
		$registry->set('debug_lang_const', true);

		// Database settings.
		$registry->set('dbtype', $options->db_type);
		$registry->set('host', $options->db_host);
		$registry->set('user', $options->db_user);
		$registry->set('password', $options->db_pass_plain);
		$registry->set('db', $options->db_name);
		$registry->set('dbprefix', $options->db_prefix);
		$registry->set('dbencryption', $options->db_encryption);
		$registry->set('dbsslverifyservercert', $options->db_sslverifyservercert);
		$registry->set('dbsslkey', $options->db_sslkey);
		$registry->set('dbsslcert', $options->db_sslcert);
		$registry->set('dbsslca', $options->db_sslca);
		$registry->set('dbsslcipher', $options->db_sslcipher);

		// Server settings.
		$registry->set('force_ssl', 0);
		$registry->set('live_site', '');
		$registry->set('secret', UserHelper::genRandomPassword(16));
		$registry->set('gzip', false);
		$registry->set('error_reporting', 'default');
		$registry->set('helpurl', $options->helpurl);

		// Locale settings.
		$registry->set('offset', 'UTC');

		// Mail settings.
		$registry->set('mailonline', true);
		$registry->set('mailer', 'mail');
		$registry->set('mailfrom', $options->admin_email);
		$registry->set('fromname', $options->site_name);
		$registry->set('sendmail', '/usr/sbin/sendmail');
		$registry->set('smtpauth', false);
		$registry->set('smtpuser', '');
		$registry->set('smtppass', '');
		$registry->set('smtphost', 'localhost');
		$registry->set('smtpsecure', 'none');
		$registry->set('smtpport', 25);

		// Cache settings.
		$registry->set('caching', 0);
		$registry->set('cache_handler', 'file');
		$registry->set('cachetime', 15);
		$registry->set('cache_platformprefix', false);

		// Meta settings.
		$registry->set('MetaDesc', '');
		$registry->set('MetaAuthor', true);
		$registry->set('MetaVersion', false);
		$registry->set('robots', '');

		// SEO settings.
		$registry->set('sef', true);
		$registry->set('sef_rewrite', false);
		$registry->set('sef_suffix', false);
		$registry->set('unicodeslugs', false);

		// Feed settings.
		$registry->set('feed_limit', 10);
		$registry->set('feed_email', 'none');

		$registry->set('log_path', JPATH_ADMINISTRATOR . '/logs');
		$registry->set('tmp_path', JPATH_ROOT . '/tmp');

		// Session setting.
		$registry->set('lifetime', 15);
		$registry->set('session_handler', 'database');
		$registry->set('shared_session', false);
		$registry->set('session_metadata', true);

		// Generate the configuration class string buffer.
		$buffer = $registry->toString('PHP', array('class' => 'JConfig', 'closingtag' => false));

		// Build the configuration file path.
		$path = JPATH_CONFIGURATION . '/configuration.php';

		// Determine if the configuration file path is writable.
		if (file_exists($path))
		{
			$canWrite = is_writable($path);
		}
		else
		{
			$canWrite = is_writable(JPATH_CONFIGURATION . '/');
		}

		/*
		 * If the file exists but isn't writable OR if the file doesn't exist and the parent directory
		 * is not writable the user needs to fix this.
		 */
		if ((file_exists($path) && !is_writable($path)) || (!file_exists($path) && !is_writable(dirname($path) . '/')))
		{
			return false;
		}

		// Get the session
		$session = Factory::getSession();

		if ($canWrite)
		{
			file_put_contents($path, $buffer);
			$session->set('setup.config', null);
		}
		else
		{
			// If we cannot write the configuration.php, setup fails!
			return false;
		}

		return true;
	}

	/**
	 * Method to create the root user for the site.
	 *
	 * @param   object          $options  The session options.
	 * @param   DatabaseDriver  $db       Database connector object $db*.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 */
	private function createRootUser($options, $db)
	{
		$cryptpass = UserHelper::hashPassword($options->admin_password_plain);

		// Take the admin user id - we'll need to leave this in the session for sample data install later on.
		$userId = self::getUserId();

		// Create the admin user.
		date_default_timezone_set('UTC');
		$installdate = date('Y-m-d H:i:s');

		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__users'))
			->where($db->quoteName('id') . ' = ' . $db->quote($userId));

		$db->setQuery($query);

		try
		{
			$result = $db->loadResult();
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		if ($result)
		{
			$query->clear()
				->update($db->quoteName('#__users'))
				->set($db->quoteName('name') . ' = ' . $db->quote(trim($options->admin_user)))
				->set($db->quoteName('username') . ' = ' . $db->quote(trim($options->admin_username)))
				->set($db->quoteName('email') . ' = ' . $db->quote($options->admin_email))
				->set($db->quoteName('password') . ' = ' . $db->quote($cryptpass))
				->set($db->quoteName('block') . ' = 0')
				->set($db->quoteName('sendEmail') . ' = 1')
				->set($db->quoteName('registerDate') . ' = ' . $db->quote($installdate))
				->set($db->quoteName('lastvisitDate') . ' = NULL')
				->set($db->quoteName('activation') . ' = ' . $db->quote('0'))
				->set($db->quoteName('params') . ' = ' . $db->quote(''))
				->where($db->quoteName('id') . ' = ' . $db->quote($userId));
		}
		else
		{
			$columns = array(
				$db->quoteName('id'),
				$db->quoteName('name'),
				$db->quoteName('username'),
				$db->quoteName('email'),
				$db->quoteName('password'),
				$db->quoteName('block'),
				$db->quoteName('sendEmail'),
				$db->quoteName('registerDate'),
				$db->quoteName('lastvisitDate'),
				$db->quoteName('activation'),
				$db->quoteName('params')
			);
			$query->clear()
				->insert('#__users', true)
				->columns($columns)
				->values(
					$db->quote($userId) . ', ' . $db->quote(trim($options->admin_user)) . ', ' . $db->quote(trim($options->admin_username)) . ', ' .
					$db->quote($options->admin_email) . ', ' . $db->quote($cryptpass) . ', ' .
					$db->quote('0') . ', ' . $db->quote('1') . ', ' . $db->quote($installdate) . ', NULL, ' .
					$db->quote('0') . ', ' . $db->quote('')
				);
		}

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		// Map the super user to the Super Users group
		$query->clear()
			->select($db->quoteName('user_id'))
			->from($db->quoteName('#__user_usergroup_map'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userId));

		$db->setQuery($query);

		if ($db->loadResult())
		{
			$query->clear()
				->update($db->quoteName('#__user_usergroup_map'))
				->set($db->quoteName('user_id') . ' = ' . $db->quote($userId))
				->set($db->quoteName('group_id') . ' = 8');
		}
		else
		{
			$query->clear()
				->insert($db->quoteName('#__user_usergroup_map'), false)
				->columns(array($db->quoteName('user_id'), $db->quoteName('group_id')))
				->values($db->quote($userId) . ', 8');
		}

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Method to erase the configuration file.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function deleteConfiguration()
	{
		// The configuration file path.
		$path = JPATH_CONFIGURATION . '/configuration.php';

		if (file_exists($path))
		{
			File::delete($path);
		}
	}
}
