<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Installation\Helper\DatabaseHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

/**
 * Setup model for the Joomla Core Installer.
 *
 * @since  3.1
 */
class SetupModel extends BaseInstallationModel
{
	/**
	 * Get the current setup options from the session.
	 *
	 * @return  array  An array of options from the session.
	 *
	 * @since   3.1
	 */
	public function getOptions()
	{
		if (!empty(Factory::getSession()->get('setup.options', array())))
		{
			return Factory::getSession()->get('setup.options', array());
		}
	}

	/**
	 * Store the current setup options in the session.
	 *
	 * @param   array  $options  The installation options.
	 *
	 * @return  array  An array of options from the session.
	 *
	 * @since   3.1
	 */
	public function storeOptions($options)
	{
		// Get the current setup options from the session.
		$old = (array) $this->getOptions();

		// Ensure that we have language
		if (!isset($options['language']) || empty($options['language']))
		{
			$options['language'] = Factory::getLanguage()->getTag();
		}

		// Store passwords as a separate key that is not used in the forms
		foreach (array('admin_password', 'db_pass', 'ftp_pass') as $passwordField)
		{
			if (isset($options[$passwordField]))
			{
				$plainTextKey = $passwordField . '_plain';

				$options[$plainTextKey] = $options[$passwordField];

				unset($options[$passwordField]);
			}
		}

		// Get the session
		$session = Factory::getSession();
		$options['helpurl'] = $session->get('setup.helpurl', null);

		// Merge the new setup options into the current ones and store in the session.
		$options = array_merge($old, (array) $options);
		$session->set('setup.options', $options);

		return $options;
	}

	/**
	 * Method to get the form.
	 *
	 * @param   string  $view  The view being processed.
	 *
	 * @return  Form|boolean  JForm object on success, false on failure.
	 *
	 * @since   3.1
	 */
	public function getForm($view = null)
	{
		if (!$view)
		{
			$view = Factory::getApplication()->input->getWord('view', 'setup');
		}

		// Get the form.
		Form::addFormPath(JPATH_COMPONENT . '/forms');

		try
		{
			$form = Form::getInstance('jform', $view, array('control' => 'jform'));
		}
		catch (\Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		// Check the session for previously entered form data.
		$data = (array) $this->getOptions();

		// Bind the form data if present.
		if (!empty($data))
		{
			$form->bind($data);
		}

		return $form;
	}

	/**
	 * Method to check the form data.
	 *
	 * @param   string  $page  The view being checked.
	 *
	 * @return  array|boolean  Array with the validated form data or boolean false on a validation failure.
	 *
	 * @since   3.1
	 */
	public function checkForm($page = 'setup')
	{
		// Get the posted values from the request and validate them.
		$data   = Factory::getApplication()->input->post->get('jform', array(), 'array');
		$return = $this->validate($data, $page);

		// Attempt to save the data before validation.
		$form = $this->getForm();
		$data = $form->filter($data);

		$this->storeOptions($data);

		// Check for validation errors.
		if ($return === false)
		{
			return false;
		}

		// Store the options in the session.
		return $this->storeOptions($return);
	}

	/**
	 * Generate a panel of language choices for the user to select their language.
	 *
	 * @return  boolean True if successful.
	 *
	 * @since   3.1
	 */
	public function getLanguages()
	{
		// Detect the native language.
		$native = LanguageHelper::detectLanguage();

		if (empty($native))
		{
			$native = 'en-GB';
		}

		// Get a forced language if it exists.
		$forced = Factory::getApplication()->getLocalise();

		if (!empty($forced['language']))
		{
			$native = $forced['language'];
		}

		// Get the list of available languages.
		$list = LanguageHelper::createLanguageList($native);

		if (!$list || $list instanceof \Exception)
		{
			$list = array();
		}

		return $list;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   array   $data  The form data.
	 * @param   string  $view  The view.
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @since   3.1
	 */
	public function validate($data, $view = null)
	{
		// Get the form.
		$form = $this->getForm($view);

		// Check for an error.
		if ($form === false)
		{
			return false;
		}

		// Filter and validate the form data.
		$data   = $form->filter($data);
		$return = $form->validate($data);

		// Check for an error.
		if ($return instanceof \Exception)
		{
			Factory::getApplication()->enqueueMessage($return->getMessage(), 'warning');

			return false;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			$messages = array_reverse($form->getErrors());

			foreach ($messages as $message)
			{
				if ($message instanceof \Exception)
				{
					Factory::getApplication()->enqueueMessage($message->getMessage(), 'warning');
				}
				else
				{
					Factory::getApplication()->enqueueMessage($message, 'warning');
				}
			}

			return false;
		}

		return $data;
	}

	/**
	 * Method to validate the db connection properties.
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function validateDbConnection()
	{
		$options = $this->getOptions();

		// Get the options as an object for easier handling.
		$options = ArrayHelper::toObject($options);

		// Load the backend language files so that the DB error messages work.
		$lang = Factory::getLanguage();
		$currentLang = $lang->getTag();

		$optionsChanged = false;

		// Load the selected language
		if (LanguageHelper::exists($currentLang, JPATH_ADMINISTRATOR))
		{
			$lang->load('joomla', JPATH_ADMINISTRATOR, $currentLang, true);
		}
		// Pre-load en-GB in case the chosen language files do not exist.
		else
		{
			$lang->load('joomla', JPATH_ADMINISTRATOR, 'en-GB', true);
		}

		// Ensure a database type was selected.
		if (empty($options->db_type))
		{
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_INVALID_TYPE'), 'error');

			return false;
		}

		// Ensure that a hostname and user name were input.
		if (empty($options->db_host) || empty($options->db_user))
		{
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_INVALID_DB_DETAILS'), 'error');

			return false;
		}

		// Ensure that a database name was input.
		if (empty($options->db_name))
		{
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_EMPTY_NAME'), 'error');

			return false;
		}

		// Validate database table prefix.
		if (!preg_match('#^[a-zA-Z]+[a-zA-Z0-9_]*$#', $options->db_prefix))
		{
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_PREFIX_MSG'), 'error');

			return false;
		}

		// Validate length of database table prefix.
		if (strlen($options->db_prefix) > 15)
		{
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_FIX_TOO_LONG'), 'error');

			return false;
		}

		// Validate length of database name.
		if (strlen($options->db_name) > 64)
		{
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_NAME_TOO_LONG'), 'error');

			return false;
		}

		// Validate database name.
		if (in_array($options->db_type, ['pgsql', 'postgresql']) && !preg_match('#^[a-zA-Z_][0-9a-zA-Z_$]*$#', $options->db_name))
		{
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_NAME_MSG_POSTGRESQL'), 'error');

			return false;
		}

		if (in_array($options->db_type, ['mysql', 'mysqli']) && preg_match('#[\\\\\/\.]#', $options->db_name))
		{
			Factory::getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_NAME_MSG_MYSQL'), 'error');

			return false;
		}

		// Workaround for UPPERCASE table prefix for postgresql
		if (in_array($options->db_type, ['pgsql', 'postgresql']))
		{
			if (strtolower($options->db_prefix) != $options->db_prefix)
			{
				Factory::getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_FIX_LOWERCASE'), 'error');

				return false;
			}
		}

		// Validate database connection encryption options
		if ($options->db_encryption === 0)
		{
			// Reset unused options
			if (!empty($options->db_sslkey))
			{
				$options->db_sslkey = '';
				$optionsChanged     = true;
			}

			if (!empty($options->db_sslcert))
			{
				$options->db_sslcert = '';
				$optionsChanged      = true;
			}

			if ($options->db_sslverifyservercert)
			{
				$options->db_sslverifyservercert = false;
				$optionsChanged                  = true;
			}

			if (!empty($options->db_sslca))
			{
				$options->db_sslca = '';
				$optionsChanged    = true;
			}

			if (!empty($options->db_sslcipher))
			{
				$options->db_sslcipher = '';
				$optionsChanged        = true;
			}
		}
		else
		{
			// Check localhost
			if (strtolower($options->db_host) === 'localhost')
			{
				Factory::getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_ENCRYPTION_MSG_LOCALHOST'), 'error');

				return false;
			}

			// Check CA file and folder depending on database type if server certificate verification
			if ($options->db_sslverifyservercert)
			{
				if (empty($options->db_sslca))
				{
					Factory::getApplication()->enqueueMessage(
						Text::sprintf('INSTL_DATABASE_ENCRYPTION_MSG_FILE_FIELD_EMPTY', Text::_('INSTL_DATABASE_ENCRYPTION_CA_LABEL')),
						'error'
					);

					return false;
				}

				if (!File::exists(Path::clean($options->db_sslca)))
				{
					Factory::getApplication()->enqueueMessage(
						Text::sprintf('INSTL_DATABASE_ENCRYPTION_MSG_FILE_FIELD_BAD', Text::_('INSTL_DATABASE_ENCRYPTION_CA_LABEL')),
						'error'
					);

					return false;
				}
			}
			else
			{
				// Reset unused option
				if (!empty($options->db_sslca))
				{
					$options->db_sslca = '';
					$optionsChanged    = true;
				}
			}

			// Check key and certificate if two-way encryption
			if ($options->db_encryption === 2)
			{
				if (empty($options->db_sslkey))
				{
					Factory::getApplication()->enqueueMessage(
						Text::sprintf('INSTL_DATABASE_ENCRYPTION_MSG_FILE_FIELD_EMPTY', Text::_('INSTL_DATABASE_ENCRYPTION_KEY_LABEL')),
						'error'
					);

					return false;
				}

				if (!File::exists(Path::clean($options->db_sslkey)))
				{
					Factory::getApplication()->enqueueMessage(
						Text::sprintf('INSTL_DATABASE_ENCRYPTION_MSG_FILE_FIELD_BAD', Text::_('INSTL_DATABASE_ENCRYPTION_KEY_LABEL')),
						'error'
					);

					return false;
				}

				if (empty($options->db_sslcert))
				{
					Factory::getApplication()->enqueueMessage(
						Text::sprintf('INSTL_DATABASE_ENCRYPTION_MSG_FILE_FIELD_EMPTY', Text::_('INSTL_DATABASE_ENCRYPTION_CERT_LABEL')),
						'error'
					);

					return false;
				}

				if (!File::exists(Path::clean($options->db_sslcert)))
				{
					Factory::getApplication()->enqueueMessage(
						Text::sprintf('INSTL_DATABASE_ENCRYPTION_MSG_FILE_FIELD_BAD', Text::_('INSTL_DATABASE_ENCRYPTION_CERT_LABEL')),
						'error'
					);

					return false;
				}
			}
			else
			{
				// Reset unused options
				if (!empty($options->db_sslkey))
				{
					$options->db_sslkey = '';
					$optionsChanged     = true;
				}

				if (!empty($options->db_sslcert))
				{
					$options->db_sslcert = '';
					$optionsChanged      = true;
				}
			}
		}

		// Save options to session data if changed
		if ($optionsChanged)
		{
			$session = Factory::getSession();
			$optsArr = ArrayHelper::fromObject($options);
			$session->set('setup.options', $optsArr);
		}

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
				false,
				DatabaseHelper::getEncryptionSettings($options)
			);

			$db->connect();
		}
		catch (\RuntimeException $e)
		{
			if ($options->db_type === 'mysql' && strpos($e->getMessage(), '[1049] Unknown database') === 42
				|| $options->db_type === 'pgsql' && strpos($e->getMessage(), 'database "' . $options->db_name . '" does not exist'))
			{
				// Database doesn't exist: Skip the below checks, they will be done later at database creation
				return true;
			}

			Factory::getApplication()->enqueueMessage(Text::sprintf('INSTL_DATABASE_COULD_NOT_CONNECT', $e->getMessage()), 'error');

			return false;
		}

		$dbVersion = $db->getVersion();

		// Get required database version
		$minDbVersionRequired = DatabaseHelper::getMinimumServerVersion($db, $options);

		// Check minimum database version
		if (version_compare($dbVersion, $minDbVersionRequired) < 0)
		{
			if (in_array($options->db_type, ['mysql', 'mysqli']) && $db->isMariaDb())
			{
				$errorMessage = Text::sprintf(
					'INSTL_DATABASE_INVALID_MARIADB_VERSION',
					$minDbVersionRequired,
					$dbVersion
				);
			}
			else
			{
				$errorMessage = Text::sprintf(
					'INSTL_DATABASE_INVALID_' . strtoupper($options->db_type) . '_VERSION',
					$minDbVersionRequired,
					$dbVersion
				);
			}

			Factory::getApplication()->enqueueMessage($errorMessage, 'error');

			$db->disconnect();

			return false;
		}

		// Check database connection encryption
		if ($options->db_encryption !== 0 && empty($db->getConnectionEncryption()))
		{
			if ($db->isConnectionEncryptionSupported())
			{
				Factory::getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_ENCRYPTION_MSG_CONN_NOT_ENCRYPT'), 'error');
			}
			else
			{
				Factory::getApplication()->enqueueMessage(Text::_('INSTL_DATABASE_ENCRYPTION_MSG_SRV_NOT_SUPPORTS'), 'error');
			}

			$db->disconnect();

			return false;
		}

		return true;
	}
}
