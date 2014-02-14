<?php
/**
 * @package    Joomla.CLI
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Configure error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Switch register_argc_argv on
@ini_set('register_argc_argv', 1);

/**
 * This is a command-line script, that notifies about new extension updates.
 * Perfect as a daily cronjob, to keep the whole site up to date.
 *
 * Recipient mail address is taken from the global configuration
 * but can be overritten by a calling argument to notifiy directly
 * a/the responsible webmaster/web-agency:
 *
 * Without mail override:
 * /path/to/php /path/to/site/cli/updatesnotifier_cron.php
 *
 * With mail override:
 * /path/to/php /path/to/site/cli/updatesnotifier_cron.php webmaster@mysite.org
 *
 * (just add the address after a space)
 */

// Set flag that this is a parent file.
define('_JEXEC', 1);

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(dirname(__FILE__)));
	require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_LIBRARIES . '/import.php';
require_once JPATH_LIBRARIES . '/cms.php';

/**
 * Class to search for new updates for all
 * installed 'one-click-updateable' extensions
 * and notify per mail if any detected
 *
 * @package  Joomla.CLI
 * @since    2.5
 */
class Updatesnotifier extends JApplicationCli
{
	/**
	 * Updates-Container
	 * @var array
	 */
	protected $updates 			= array();

	/**
	 * Mail-Address to be notified
	 * @var string
	 */
	protected $mailRecipient	= '';

	/**
	 * the constructor
	 *
	 * @since   2.5
	 */
	public function __construct()
	{
		parent::__construct();
		$file = $this->input->executable;
		$mailOverride	= !empty($this->input->args[0]) ? $this->input->args[0] : false;
		$this->mailRecipient	= $mailOverride ? $mailOverride : $this->config->get('mailfrom');

		// Load the language
		$lang = JFactory::getLanguage();
		$lang->load('cli_updatesnotifier_cron', JPATH_SITE, null, false, false)
			|| $lang->load('cli_updatesnotifier_cron', JPATH_SITE, null, true);

		// Setting up the Logger
		$logger	= array(
						/* Logger type */
						'logger' 			=> 'formattedtext',
						/* Filename: one log file per month */
						'text_file' 		=> strftime('%b') . '_log.php',
						/* Setting the log-path */
						'text_file_path' 	=> $this->config->get('log_path') . '/updatesnotifier_cron'
		);
		JLog::addLogger($logger, JLog::ALL, array('updatesnotifier_cron'));
		JLog::add(
			JText::sprintf(
			'CLI_UPDATESNOTIFIER_LOG_INFO_SETUP', $this->mailRecipient, ($mailOverride) ? JText::_('JYES') : JText::_('JNO')
			),
			JLog::INFO, 'updatesnotifier_cron'
		);

		// Filename-Check:
		if (false === strpos($this->input->executable, 'updatesnotifier_cron.php'))
		{
			JLog::add(JText::_('CLI_UPDATESNOTIFIER_LOG_ERROR_FILERENAME'), JLog::ERROR, 'updatesnotifier_cron');
			JLog::add(JText::_('CLI_UPDATESNOTIFIER_LOG_ERROR_SCRIPT_ABORTET'), JLog::NOTICE, 'updatesnotifier_cron');
			die;
		}
	}

	/**
	 * Method to purge the update cache and find
	 * updates for installed extensions
	 *
	 * @return void
	 */
	protected function getUpdates()
	{

		try
		{
			JLoader::register('installerModelUpdate', JPATH_ADMINISTRATOR . '/components/com_installer/models/update.php');
			$model = new InstallerModelUpdate(array('ignore_request' => true));

			if (!$model->purge())
			{
				Jlog::add(JText::_('CLI_UPDATESNOTIFIER_LOG_INFO_DB_CACHE_PURGED'), JLog::INFO, 'updatesnotifier_cron');
			}

			if (!$model->findUpdates())
			{
				Jlog::add(JText::_('CLI_UPDATESNOTIFIER_LOG_ERROR_FIND_UPDATES'), JLog::ERROR, 'updatesnotifier_cron');
				JLog::add(JText::_('CLI_UPDATESNOTIFIER_LOG_ERROR_SCRIPT_ABORTET'), JLog::NOTICE, 'updatesnotifier_cron');

				return;
			}

			// Set some model states
			$model->setState('list.ordering', 'name');
			$model->setState('list.direction', 'ASC');

			// Get joomla update
			$model->setState('filter.extension_id', 700);
			$joomlaUpdate = ($model->getItems());

			// Change the store_id to prevent caching
			$model->setState('list.ordering', 'type');

			// Get updates from all others installed extensions
			$model->setState('filter.extension_id', 0);
			$extensionUpdates = $model->getItems();

		}
		catch (Exception $e)
		{
			JLog::add(JText::sprintf('CLI_UPDATESNOTIFIER_LOG_ERROR_FIND_UPDATES', $e->getMessage()), JLog::ERROR, 'updatesnotifier_cron');
		}

		$updates = array_merge($joomlaUpdate, $extensionUpdates);

		if (count($updates))
		{
			$this->updates = $updates;
			Jlog::add(JText::sprintf('CLI_UPDATESNOTIFIER_LOG_INFO_UPDATES_FOUND', count($updates)), JLog::INFO, 'updatesnotifier_cron');
		}
		else
		{
			Jlog::add(JText::_('CLI_UPDATESNOTIFIER_LOG_INFO_NO_NEW_UPDATES_FOUND'), JLog::INFO, 'updatesnotifier_cron');
		}
	}

	/**
	 * Sends the notification
	 *
	 * @return void
	 */
	protected function proceedNotification()
	{
		$nl			= "\n";
		$cfg		= $this->config;
		$subject	= JText::sprintf('CLI_UPDATESNOTIFIER_MAIL_SUBJECT_UPDATES_FOUND', $cfg->get('sitename'));
		$mailBody	= JText::_('CLI_UPDATESNOTIFIER_MAIL_BODY_INTRO_I') . ' "' . $cfg->get('sitename') . '"';
		$mailBody	.= JText::_('CLI_UPDATESNOTIFIER_MAIL_BODY_INTRO_II');

		for ($i = 0; $i < count($this->updates); $i++)
		{
			$textLine	= $i + 1 . '. ' . $this->updates[$i]->name . ' (' . $this->updates[$i]->version . ')';
			$mailBody .= $textLine . $nl;
			Jlog::add($textLine, JLog::INFO, 'updatesnotifier_cron');
		}

		$mailBody 	.= $nl . $nl . JText::_('CLI_UPDATESNOTIFIER_MAIL_BODY_OUTRO');
		$mailer 	= JFactory::getMailer();
		$mailSent 	= $mailer->sendMail(
							$cfg->get('mailfrom'), $cfg->get('fromname'), $this->mailRecipient,
							$subject, $mailBody, false, null, null, null, $cfg->get('mailfrom'), $cfg->get('fromname')
							);

		if ($mailSent)
		{
			Jlog::add(
					JText::sprintf('CLI_UPDATESNOTIFIER_MAIL_SENT_SUCCESS', $this->mailRecipient),
					JLog::INFO, 'updatesnotifier_cron'
					);

			return;
		}
		Jlog::add(
			JText::sprintf('CLI_UPDATESNOTIFIER_MAIL_SENT_ERROR', $this->mailRecipient),
			JLog::INFO, 'updatesnotifier_cron'
		);
	}

	/**
	 * execute method of the 'script'
	 *
	 * @return void
	 */
	protected function doExecute()
	{
		JLog::add(JText::_('CLI_UPDATESNOTIFIER_LOG_INFO_START'), JLog::INFO, 'updatesnotifier_cron');

		$this->getUpdates();

		if (count($this->updates))
		{
			$this->proceedNotification();
		}

		JLog::add(JText::_('CLI_UPDATESNOTIFIER_LOG_INFO_END'), JLog::INFO, 'updatesnotifier_cron');
	}
}

	// Load and execute the class
	JApplicationCli::getInstance('Updatesnotifier')->execute();
