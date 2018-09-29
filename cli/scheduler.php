<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Scheduler CLI.
 *
 * This is a command-line script that trigger job plugin event onExecuteScheduledTask
 *
 */

// We are a valid entry point.
const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Import the configuration.
require_once JPATH_CONFIGURATION . '/configuration.php';

// System configuration.
$config = new JConfig;
define('JDEBUG', $config->debug);

// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load Library language
$lang = JFactory::getLanguage();

// Try the scheduler_cli file in the current language (without allowing the loading of the file in the default language)
$lang->load('scheduler_cli', JPATH_SITE, null, false, false)
// Fallback to the scheduler_cli file in the default language
|| $lang->load('scheduler_cli', JPATH_SITE, null, true);

/**
 * A command line cron job to run the job plugins event.
 *
 * @since  __DEPLOY_VERSION__
 */
class SchedulerCli extends JApplicationCli
{
	/**
	 * Start time for the process
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $time = null;

	/**
	 * Entry point for the Scheduler CLI script
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function doExecute()
	{
		$options['format']    = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
		$options['text_file'] = 'joomla_cli.php';

		JLog::addLogger($options, JLog::INFO, array('scheduler'));

		try
		{
			JLog::add(
				'Starting Scheduler', JLog::INFO, 'scheduler'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}
		
		// Print a blank line.
		$this->out(JText::_('SCHEDULER_CLI'));
		$this->out('============================');

		// Initialize the time value.
		$this->time = microtime(true);

		// Remove the script time limit.
		@set_time_limit(0);

		// Fool the system into thinking we are running as JSite.
		$_SERVER['HTTP_HOST'] = 'domain.com';
		JFactory::getApplication('site');

		$this->triggerJobs();

		// Total reporting.
		$this->out(JText::sprintf('SCHEDULER_CLI_PROCESS_COMPLETE', round(microtime(true) - $this->time, 3)), true);
		$this->out(JText::sprintf('SCHEDULER_CLI_PEAK_MEMORY_USAGE', number_format(memory_get_peak_usage(true))));

		// Print a blank line at the end.
		$this->out();

		try
		{
			JLog::add(
				'Ending Scheduler:' . JText::sprintf('SCHEDULER_CLI_PROCESS_COMPLETE', round(microtime(true) - $this->time, 3)), JLog::INFO, 'scheduler'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}
	}

	/**
	 * Trigger the jobs
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function triggerJobs()
	{		
		// Unleash hell
		JPluginHelper::importPlugin('job');
		$dispatcher = \JEventDispatcher::getInstance();

		// Trigger the ExecuteTask event
		$dispatcher->trigger('onExecuteScheduledTask', array());

	}
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('SchedulerCli')->execute();
