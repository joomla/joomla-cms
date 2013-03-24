<?php
/**
 * @package    Joomla.CLI
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Make sure we're being called from the command line, not a web interface
if (array_key_exists('REQUEST_METHOD', $_SERVER))
{
  die();
}

// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);

// Switch register_argc_argv on
@ini_set('register_argc_argv', 1);

// Set flag that this is a parent file.
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

// Load system defines
if (file_exists(dirname(dirname(__FILE__)) . '/defines.php'))
{
	require_once dirname(dirname(__FILE__)) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(dirname(__FILE__)));
	require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_LIBRARIES . '/import.php';
require_once JPATH_LIBRARIES . '/cms.php';


// Import and Load Library language
jimport('joomla.language.language');

/**
 * This is a command-line script, that notifies about new updates and db-errors.
 * Mail is sending only if somthing was found to notfy.
 * Otherwise in debug mode it sends on every call a mail.
 * 
 * Perfect as a daily cronjob, to keep the site up to date. 
 * Recipient mail address is taken from the global configuration
 * but can be overritten by a calling argument e.g.:
 * 
 * Without mail override:
 * /path/to/php /path/to/site/cli/updatesnotifier_cron.php
 * 
 * With mail-override (depends on the server-os):
 * /path/to/php /path/to/site/cli/updatesnotifier_cron.php example@example.org
 * 
 * Is the override mail address invalid (JMailHelper::isEmailAddress())
 * it switches back to global conf. address
 *
 * @package  Joomla.CLI
 * @since    2.5
 */
class Updatesnotifier extends JApplicationCli
{

	/**
	 * Message-Container
	 * @var array
	 */
	protected $messages = array();

	/**
	 * Script-Error: true if a script error occurs
	 * @var bool
	 */
	protected $scriptError = false;

	/**
	 * the constructor
	 *
	 * @since   2.5
	 */
	public function __construct()
	{
		parent::__construct();

		// Get the commandline arguments
		$this->input = $_SERVER['argv'];

		// Load the language
		$lang = JFactory::getLanguage();
		$lang->load('cli_updatesnotifier_cron', JPATH_SITE, null, false, false) || $lang->load('cli_updatesnotifier_cron', JPATH_SITE, null, true);

		// Set debug constant if needed
		if (!defined('JDEBUG'))
		{
			define('JDEBUG', $this->config->get('debug', 0));
		}

		// If we are in debug-mode, communicate that
		if (JDEBUG)
		{
			$this->addMessage(JText::_('CLI_UPDATESNOTIFIER_DEBUG_INFO_DEBUG_MODE_RUNNING'));
		}

		/* Checks the scriptname: For securirity reason, file rename are allowed, but only as
		   additional filename-prefix, to keep the possibility of batch processing
		   by shellscripts/directory-parsing
		*/
		if (!empty($this->input[0]) && false === strpos($this->input[0], 'updatesnotifier_cron.php'))
		{
			$this->addMessage(JText::_('CLI_UPDATESNOTIFIER_FILE_ERROR_RENAME'));
			$this->scriptError = true;
		}

		// Looking for a mail-recipient override
		if (!empty($this->input[1]))
		{
			jimport('joomla.mail.helper');
			$mailOverride = $this->input[1];
			$isValidMailaddress = JMailHelper::isEmailAddress($mailOverride);

			if (true === $isValidMailaddress)
			{
				// Override the global mail address
				$this->config->set('mailfrom', $this->input[1]);

				if (JDEBUG)
				{
					$this->addMessage(JText::sprintf('CLI_UPDATESNOTIFIER_DEBUG_INFO_MAIL_OVERRIDE', $this->config->get('mailfrom')));
				}
			}
			elseif (JDEBUG)
			{
				$this->addMessage(JText::sprintf('CLI_UPDATESNOTIFIER_DEBUG_INFO_MAIL_OVERRIDE_INVALID', $mailOverride));
			}
		}
	}

	/**
	 * execute the 'script'
	 * 
	 * @return void
	 */
	public function doExecute()
	{
		// Find all updates and process the result
		$this->getExtensionsUpdates();
		$this->getJoomlaUpdate();
		$this->getDbErrors();
		$this->proceedMessages();
	}

	/**
	 * Handles the messages
	 * 
	 * @return void
	 */
	protected function proceedMessages()
	{
		if (!empty($this->messages))
		{
			// Proceed mail
			$this->proceedMail();

			// Proceed console output
			$this->out(implode("\n", $this->messages));

			// 2Do: Good point to log some script-errors
			// if(true === $this->scriptError)
		}
	}

	/**
	 * purge the cache and checks for updates
	 * for the installed extensions
	 * (those who have implemented the update feature)
	 * 
	 * @return void
	 */
	protected function getExtensionsUpdates()
	{
		try
		{
			$model = new InstallerModelUpdateCli;

			if (!$model->purge() and JDEBUG)
			{
				$this->addMessage(JText::_('CLI_UPDATESNOTIFIER_DEBUG_INFO_EXTENSIONS_CACHE_NOT_PURGED'));
				$this->scriptError = true;
			}
			$getUpdates = $model->findUpdates();
			$updatesFound = $model->getItems();
		}
		catch (Exception $e)
		{
			$this->addMessage(JText::_('CLI_UPDATESNOTIFIER_EXTENSIONS_EXCEPTION'));
			$this->scriptError = true;

			return;
		}

		if (is_array($updatesFound) && !empty($updatesFound))
		{
			$this->addMessage(JText::_('CLI_UPDATESNOTIFIER_EXTENSIONS_NEW_UPDATES_FOUND'));

			return;
		}

		if (JDEBUG)
		{
			$this->addMessage(JText::_('CLI_UPDATESNOTIFIER_EXTENSIONS_NO_UPDATES_FOUND'));
		}
	}

	/**
	 * checks for a new Joomla! core update
	 * 
	 * @return void
	 */
	protected function getJoomlaUpdate()
	{
		try
		{
			require_once JPATH_ADMINISTRATOR . '/components/com_joomlaupdate/models/default.php';
			$model = new JoomlaupdateModelDefault;
			$result = $model->getUpdateInformation();
			$updateFound = !empty($result['object']);
		}
		catch (Exception $e)
		{
			$this->addMessage(JText::_('CLI_UPDATESNOTIFIER_JOOMLAUPDATE_EXCEPTION'));
			$this->scriptError = true;

			return;
		}

		if (true === $updateFound)
		{
			$this->addMessage(JText::_('CLI_UPDATESNOTIFIER_JOOMLAUPDATE_NEW_UPDATE_FOUND'));
		}
		elseif (JDEBUG)
		{
			$this->addMessage(JText::_('CLI_UPDATESNOTIFIER_JOOMLAUPDATE_NO_UPDATE_FOUND'));
		}
	}

	/**
	 * compares db scheme and checks for 
	 * db errors
	 * 
	 * @return void
	 */
	protected function getDbErrors()
	{
		try
		{
			require_once JPATH_ADMINISTRATOR . '/components/com_installer/models/database.php';
			$model = new InstallerModelDatabase;
			$changeSet = $model->getItems();
			$dbErrors = count($changeSet->check());
			$schemaVersion = $model->getSchemaVersion();
			$filterParams = $model->getDefaultTextFilters();
			$updateVersion = $model->getUpdateVersion();

			if (!(strncmp($schemaVersion, JVERSION, 5) === 0) || !$filterParams || $updateVersion != JVERSION)
			{
				$dbErrors++;
			}
		}
		catch (Exception $e)
		{
			$this->addMessage(JText::_('CLI_UPDATESNOTIFIER_DATABASE_EXCEPTION'));
			$this->scriptError = true;

			return;
		}

		if ($dbErrors > 0)
		{
			$this->addMessage(JText::_('CLI_UPDATESNOTIFIER_DATABASE_NEW_ERROR'));
		}
		elseif (JDEBUG)
		{
			$this->addMessage(JText::_('CLI_UPDATESNOTIFIER_DATABASE_NO_ERROR'));
		}
	}

	/**
	 * function to send a notification mail, if
	 * new updates are available or a script error
	 * occurs
	 * 
	 * @return void
	 */
	protected function proceedMail()
	{
		try
		{
			$recipient = $this->config->get('mailfrom');
			$siteName = $this->config->get('sitename');
			$mailer = JFactory::getMailer();
			$body = JText::sprintf('CLI_UPDATESNOTIFIER_MAIL_BODY_INTRO', $siteName);
			$body .= implode("\n", $this->messages);
			$body .= JText::_('CLI_UPDATESNOTIFIER_MAIL_BODY_OUTRO');
			$mailer->sendMail($recipient, $siteName, $recipient, 'updates@' . $siteName, $body, 0, null, null, null, $recipient, $siteName);
		}
		catch (Exception $e)
		{
			$this->addMessage('CLI_UPDATESNOTIFIER_MAIL_MESSAGE_SENT_ERROR');
			$this->scriptError = true;

			return;
		}
		$this->addMessage(JText::sprintf('CLI_UPDATESNOTIFIER_MAIL_MESSAGE_SENT_SUCCESS', $recipient));
	}

	/**
	 * Adds a message to the message-stack
	 * 
	 * @var string $message
	 * @return void
	 */
	protected function addMessage($message)
	{
		$this->messages[] = $message;
	}
}

require_once JPATH_ADMINISTRATOR . '/components/com_installer/models/update.php';
/**
 * Inherits the InstallerModelUpdate for method overloading
 * 
 * @package  Joomla.CLI
 * @since    2.5
 */
class InstallerModelUpdateCli extends InstallerModelUpdate
{
	/**
	 * overrides the unneeded populate state method
	 * cause there is no propper JUri Instance
	 * and thus throws an error
	 * 
	 * @return void
	 */
	public function populateState()
	{
	}

	/**
	 * overrides the getListQuery method
	 * cause here exists no extension-id filter
	 * 
	 * @return JQuery Object
	 */
	protected function getListQuery()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Grab updates ignoring new installs and joomla core
		$query->select('*')
			->from('#__updates')
			->where('extension_id != 0')
			->where($db->nq('extension_id') . ' != ' . $db->q(700));

		return $query;
	}
}

JApplicationCli::getInstance('Updatesnotifier')->execute();
