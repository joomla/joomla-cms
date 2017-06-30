<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Initialize Joomla framework
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
require_once JPATH_BASE . '/includes/framework.php';

/**
 * Cron job to trash expired cache data.
 *
 * @since  2.5
 */
class GarbageCron extends \Joomla\CMS\Application\CliApplication
{
	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function doExecute()
	{
		JFactory::getCache()->gc();
	}
}

/** @var \Joomla\DI\Container $container */
$container = require JPATH_LIBRARIES . '/container.php';

// Set up the container
$container->share(
	'GarbageCron',
	function (\Joomla\DI\Container $container)
	{
		$app = new GarbageCron(
			null,
			null,
			null,
			null,
			$container->get(\Joomla\Event\DispatcherInterface::class),
			$container
		);

		\Joomla\CMS\Factory::$application = $app;

		return $app;
	},
	true
);

// Get the application from the container
$app = $container->get('GarbageCron');

// Execute the application.
$app->execute();
