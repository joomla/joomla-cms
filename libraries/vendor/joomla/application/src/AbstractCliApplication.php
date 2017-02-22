<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application;

use Joomla\Input;
use Joomla\Registry\Registry;

/**
 * Base class for a Joomla! command line application.
 *
 * @since  1.0
 */
abstract class AbstractCliApplication extends AbstractApplication
{
	/**
	 * Output object
	 *
	 * @var    Cli\CliOutput
	 * @since  1.0
	 */
	protected $output;

	/**
	 * CLI Input object
	 *
	 * @var    Cli\CliInput
	 * @since  1.6.0
	 */
	protected $cliInput;

	/**
	 * Class constructor.
	 *
	 * @param   Input\Cli      $input     An optional argument to provide dependency injection for the application's input object.  If the
	 *                                    argument is an Input\Cli object that object will become the application's input object, otherwise
	 *                                    a default input object is created.
	 * @param   Registry       $config    An optional argument to provide dependency injection for the application's config object.  If the
	 *                                    argument is a Registry object that object will become the application's config object, otherwise
	 *                                    a default config object is created.
	 * @param   Cli\CliOutput  $output    An optional argument to provide dependency injection for the application's output object.  If the
	 *                                    argument is a Cli\CliOutput object that object will become the application's input object, otherwise
	 *                                    a default output object is created.
	 * @param   Cli\CliInput   $cliInput  An optional argument to provide dependency injection for the application's CLI input object.  If the
	 *                                    argument is a Cli\CliInput object that object will become the application's input object, otherwise
	 *                                    a default input object is created.
	 *
	 * @since   1.0
	 */
	public function __construct(Input\Cli $input = null, Registry $config = null, Cli\CliOutput $output = null, Cli\CliInput $cliInput = null)
	{
		// Close the application if we are not executed from the command line.
		// @codeCoverageIgnoreStart
		if (!defined('STDOUT') || !defined('STDIN') || !isset($_SERVER['argv']))
		{
			$this->close();
		}

		// @codeCoverageIgnoreEnd

		$this->output = ($output instanceof Cli\CliOutput) ? $output : new Cli\Output\Stdout;

		// Set the CLI input object.
		$this->cliInput = ($cliInput instanceof Cli\CliInput) ? $cliInput : new Cli\CliInput;

		// Call the constructor as late as possible (it runs `initialise`).
		parent::__construct($input instanceof Input\Input ? $input : new Input\Cli, $config);

		// Set the current directory.
		$this->set('cwd', getcwd());
	}

	/**
	 * Get an output object.
	 *
	 * @return  Cli\CliOutput
	 *
	 * @since   1.0
	 */
	public function getOutput()
	{
		return $this->output;
	}

	/**
	 * Get a CLI input object.
	 *
	 * @return  Cli\CliInput
	 *
	 * @since   1.6.0
	 */
	public function getCliInput()
	{
		return $this->cliInput;
	}

	/**
	 * Write a string to standard output.
	 *
	 * @param   string   $text  The text to display.
	 * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
	 *
	 * @return  AbstractCliApplication  Instance of $this to allow chaining.
	 *
	 * @since   1.0
	 */
	public function out($text = '', $nl = true)
	{
		$this->getOutput()->out($text, $nl);

		return $this;
	}

	/**
	 * Get a value from standard input.
	 *
	 * @return  string  The input string from standard input.
	 *
	 * @codeCoverageIgnore
	 * @since   1.0
	 */
	public function in()
	{
		return $this->getCliInput()->in();
	}
}
