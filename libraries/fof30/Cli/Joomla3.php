<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

// Do not put the JEXEC or die check on this file

use FOF30\Cli\Traits\CGIModeAware;
use FOF30\Cli\Traits\CustomOptionsAware;
use FOF30\Cli\Traits\JoomlaConfigAware;
use FOF30\Cli\Traits\MemStatsAware;
use FOF30\Cli\Traits\MessageAware;
use FOF30\Cli\Traits\TimeAgoAware;
use FOF30\Utils\CliSessionHandler;
use Joomla\CMS\Application\CliApplication;
use Joomla\CMS\Input\Cli;

// Load the legacy Joomla! include files (Joomla! 3 only)
include_once JPATH_LIBRARIES . '/import.legacy.php';

// Load the CMS import file if it exists (newer Joomla! 3 versions and Joomla! 4)
$cmsImportFilePath = JPATH_LIBRARIES . '/cms.php';

if (@file_exists($cmsImportFilePath))
{
	@include_once $cmsImportFilePath;
}

/**
 * Base class for a Joomla! command line application. Adapted from JCli / JApplicationCli
 */
abstract class FOFCliApplicationJoomla3 extends CliApplication
{
	use CGIModeAware, CustomOptionsAware, JoomlaConfigAware, MemStatsAware, TimeAgoAware, MessageAware;

	private $allowedToClose = false;

	public static function getInstance($name = null)
	{
		// Load the Joomla global configuration in JFactory. This must happen BEFORE loading FOF.
		JFactory::getConfig(JPATH_CONFIGURATION . '/configuration.php');

		// Load FOF
		if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
		{
			throw new RuntimeException('Cannot load FOF', 500);
		}

		// Create a CLI-specific session
		JFactory::$session = JSession::getInstance('none', [
			'expire' => 84400,
		], new CliSessionHandler());

		$instance = parent::getInstance($name);

		JFactory::$application = $instance;

		return $instance;
	}

	public function __construct(Cli $input = null, \Joomla\Registry\Registry $config = null, \JEventDispatcher $dispatcher = null)
	{
		// Some servers only provide a CGI executable. While not ideal for running CLI applications we can make do.
		$this->detectAndWorkAroundCGIMode();

		// Initialize custom options handling which is a bit more straightforward than Input\Cli.
		$this->initialiseCustomOptions();

		parent::__construct($input, $config, $dispatcher);

		/**
		 * Allow the application to close.
		 *
		 * This is required to allow CliApplication to execute under CGI mode. The checks performed in the parent
		 * constructor will call close() if the application does not run pure CLI mode. However, some hosts only provide
		 * the PHP CGI binary for executing CLI scripts. While wrong it will work in most cases. By default close() will
		 * do nothing, thereby allowing the parent constructor to call it without a problem. Finally, we set this flag
		 * to true to allow doExecute() to call close() and actually close the application properly. Yeehaw!
		 */
		$this->allowedToClose = true;
	}

	/**
	 * Method to close the application.
	 *
	 * See the constructor for details on why it works the way it works.
	 *
	 * @param   integer  $code  The exit code (optional; default is 0).
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 * @since   1.0
	 */
	public function close($code = 0)
	{
		// See the constructor for details
		if (!$this->allowedToClose)
		{
			return;
		}

		exit($code);
	}

	/**
	 * Gets the name of the current running application.
	 *
	 * @return  string  The name of the application.
	 *
	 * @since   4.0.0
	 */
	public function getName()
	{
		return get_class($this);
	}

	/**
	 * Get the menu object.
	 *
	 * @param   string  $name     The application name for the menu
	 * @param   array   $options  An array of options to initialise the menu with
	 *
	 * @return  \Joomla\CMS\Menu\AbstractMenu|null  A AbstractMenu object or null if not set.
	 *
	 * @since   4.0.0
	 */
	public function getMenu($name = null, $options = [])
	{
		return null;
	}
}
