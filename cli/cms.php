<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
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

// Make sure the app config is loaded to JFactory
JFactory::getConfig(JPATH_CONFIGURATION . '/configuration.php');

/**
 * A command line job runner for the Joomla! CMS
 *
 * @since  3.5
 */
class JoomlaCmsCli extends JApplicationCli
{
	/**
	 * Constructor
	 *
	 * @since   3.5
	 */
	public function __construct()
	{
		parent::__construct();

		JFactory::$application = $this;
	}

	/**
	 * Method to run the application routines.
	 *
	 * @return  void
	 *
	 * @since   3.5
	 * @throws  RuntimeException
	 */
	public function doExecute()
	{
		// Make sure there's a command set...
		if (!isset($this->input->args[0]))
		{
			throw new RuntimeException('No command was given to execute.');
		}

		$command = explode(':', $this->input->args[0]);

		// If the command only has a single part, then it is a core command
		if (count($command) === 1)
		{
			$class = 'CliCommand' . ucfirst($command[0]);
			$path  = __DIR__ . '/commands/' . strtolower($command[0]) . '.php';
		}
		else
		{
			/*
			 * For two parts or greater, the command exists in a component.  Assemble the class name and folder path lookup.
			 * Command classes are stored in a "commands" folder within the admin component path.
			 *
			 * The class should be named in the convention of <component>Command<command_name> where:
			 *
			 * - <component> is the component's name (similar to how MVC classes are prefixed)
			 * - <command_name> is the command's name
			 *
			 * The <command_name> is assembled by piecing together all remaining parts after the first part of the command.
			 * The : separator acts as a directory separator, enabling commands to be grouped into folders.
			 *
			 * For example, assuming com_content has the following commands, these would be the resulting class and path names:
			 *
			 * content:publish - ContentCommandPublish - JPATH_ADMINISTRATOR . /components/com_content/commands/publish.php
			 * content:delete:category - ContentCommandDeleteCategory - JPATH_ADMINISTRATOR . /components/com_content/commands/delete/category.php
			 */

			$component = $command[0];

			// Validate the component exists
			if (!is_dir(JPATH_ADMINISTRATOR . '/components/com_' . strtolower($component)))
			{
				throw new RuntimeException(sprintf('The "%s" component does not exist.', 'com_' . strtolower(($component))));
			}

			$class = ucfirst($component) . 'Command';
			$path  = JPATH_ADMINISTRATOR . '/components/com_' . strtolower($component) . '/commands';

			foreach ($command as $part)
			{
				$class .= ucfirst(strtolower($part));
				$path  .= '/' . strtolower($part);
			}

			$path .= '.php';
		}

		// Verify the path to the command file exists
		if (!file_exists($path))
		{
			throw new RuntimeException(sprintf('A command class was not found at "%s".', $path));
		}

		include_once $path;

		// Verify the command's class exists
		if (!class_exists($class))
		{
			throw new RuntimeException(sprintf('The "%1$s" class was not found for the "%2$s" command.', $class, implode(':', $command)));
		}

		// Execute the command
		/** @var JController $command */
		$command = new $class;
		$command->execute();
	}
}

// Execute the application
try
{
	JApplicationCli::getInstance('JoomlaCmsCli')->execute();
}
catch (\Exception $e)
{
	fwrite(STDOUT, "\nAn error occurred while executing the application: " . get_class($e) . " - {$e->getMessage()}\n");
	fwrite(STDOUT, "\n" . $e->getTraceAsString() . "\n");

	if ($e->getPrevious())
	{
		$prev = $e->getPrevious();

		fwrite(STDOUT, "\nPrevious Exception: " . get_class($prev) . " - {$prev->getMessage()}\n");
		fwrite(STDOUT, "\n{$prev->getTraceAsString()}\n");
	}

	exit(1);
}
