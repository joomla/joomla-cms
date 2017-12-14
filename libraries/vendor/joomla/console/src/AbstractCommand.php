<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Console;

use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

/**
 * Base class for a console command.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class AbstractCommand implements CommandInterface
{
	/**
	 * The command's aliases.
	 *
	 * @var    string[]
	 * @since  __DEPLOY_VERSION__
	 */
	private $aliases = [];

	/**
	 * The application object.
	 *
	 * @var    Application
	 * @since  __DEPLOY_VERSION__
	 */
	private $app;

	/**
	 * Flag tracking whether the application definition has been merged to this command.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $applicationDefinitionMerged = false;

	/**
	 * The command's input definition.
	 *
	 * @var    InputDefinition
	 * @since  __DEPLOY_VERSION__
	 */
	private $definition;

	/**
	 * The command's description.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $description = '';

	/**
	 * The command's help.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $help = '';

	/**
	 * The command's input helper set.
	 *
	 * @var    HelperSet
	 * @since  __DEPLOY_VERSION__
	 */
	private $helperSet;

	/**
	 * Flag tracking whether the command is hidden from the command listing.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $hidden = false;

	/**
	 * The command's name.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $name = '';

	/**
	 * The command's synopses.
	 *
	 * @var    string[]
	 * @since  __DEPLOY_VERSION__
	 */
	private $synopsis = ['short' => '', 'long' => ''];

	/**
	 * Constructor.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct()
	{
		$this->definition = new InputDefinition;

		$this->initialise();
	}

	/**
	 * Adds an argument to the input definition.
	 *
	 * @param   string   $name         The argument name
	 * @param   integer  $mode         The argument mode: InputArgument::REQUIRED or InputArgument::OPTIONAL
	 * @param   string   $description  A description text
	 * @param   mixed    $default      The default value (for InputArgument::OPTIONAL mode only)
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function addArgument($name, $mode = null, $description = '', $default = null)
	{
		$this->definition->addArgument(new InputArgument($name, $mode, $description, $default));

		return $this;
	}

	/**
	 * Adds an option to the input definition.
	 *
	 * @param   string        $name         The option name
	 * @param   string|array  $shortcut     The shortcuts, can be null, a string of shortcuts delimited by | or an array of shortcuts
	 * @param   integer       $mode         The option mode: One of the VALUE_* constants
	 * @param   string        $description  A description text
	 * @param   mixed         $default      The default value (must be null for InputOption::VALUE_NONE)
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function addOption($name, $shortcut = null, $mode = null, $description = '', $default = null)
	{
		$this->definition->addOption(new InputOption($name, $shortcut, $mode, $description, $default));

		return $this;
	}

	/**
	 * Get the command's aliases.
	 *
	 * @return  string[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getAliases(): array
	{
		return $this->aliases;
	}

	/**
	 * Get the application object.
	 *
	 * @return  Application  The application object.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \UnexpectedValueException if the application has not been set.
	 */
	public function getApplication(): Application
	{
		if ($this->app)
		{
			return $this->app;
		}

		throw new \UnexpectedValueException('Application not set in ' . get_class($this));
	}

	/**
	 * Get the command's input definition.
	 *
	 * @return  InputDefinition
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getDefinition(): InputDefinition
	{
		return $this->definition;
	}

	/**
	 * Get the command's description.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getDescription(): string
	{
		return $this->description;
	}

	/**
	 * Get the command's help.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getHelp(): string
	{
		return $this->help;
	}

	/**
	 * Get the command's input helper set.
	 *
	 * @return  HelperSet
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getHelperSet(): HelperSet
	{
		return $this->helperSet;
	}

	/**
	 * Get the command's name.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Returns the processed help for the command.
	 *
	 * This method is used to replace placeholders in commands with the real values.
	 * By default, this supports `%command.name%` and `%command.full_name`.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getProcessedHelp(): string
	{
		$name = $this->getName();

		$placeholders = [
			'%command.name%',
			'%command.full_name%',
		];

		$replacements = [
			$name,
			$this->getApplication()->input->server->getRaw('PHP_SELF', '') . ' ' . $name,
		];

		return str_replace($placeholders, $replacements, $this->getHelp() ?: $this->getDescription());
	}

	/**
	 * Get the command's synopsis.
	 *
	 * @param   boolean  $short  Flag indicating whether the short or long version of the synopsis should be returned
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getSynopsis(bool $short = false): string
	{
		$key = $short ? 'short' : 'long';

		if ($this->synopsis[$key] === '')
		{
			$this->synopsis[$key] = trim(sprintf('%s %s', $this->getName(), $this->getDefinition()->getSynopsis($short)));
		}

		return $this->synopsis[$key];
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function initialise()
	{
	}

	/**
	 * Check if the command is enabled in this environment.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isEnabled(): bool
	{
		return true;
	}

	/**
	 * Check if the command is hidden from the command listing.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isHidden(): bool
	{
		return $this->hidden;
	}

	/**
	 * Merges the definition from the application to this command.
	 *
	 * @param   InputDefinition  $definition  The InputDefinition from the application to be merged.
	 * @param   boolean          $mergeArgs   Flag indicating whether the application's definition arguments should be merged
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @internal  This method should not be relied on as part of the public API
	 */
	final public function mergeApplicationDefinition(InputDefinition $definition, bool $mergeArgs = true)
	{
		if ($this->applicationDefinitionMerged)
		{
			return;
		}

		$this->definition->addOptions($definition->getOptions());

		if ($mergeArgs)
		{
			$currentArguments = $this->definition->getArguments();
			$this->definition->setArguments($definition->getArguments());
			$this->definition->addArguments($currentArguments);
		}

		$this->applicationDefinitionMerged = true;
	}

	/**
	 * Set the command's aliases.
	 *
	 * @param   string[]  $aliases  The command aliases
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setAliases(array $aliases)
	{
		$this->aliases = $aliases;
	}

	/**
	 * Set the application object.
	 *
	 * @param   Application  $app  The application object.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setApplication(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * Sets the input definition for the command.
	 *
	 * @param   array|InputDefinition  $definition  Either an InputDefinition object or an array of objects to write to the definition.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setDefinition($definition)
	{
		if ($definition instanceof InputDefinition)
		{
			$this->definition = $definition;
		}
		else
		{
			$this->definition->setDefinition($definition);
		}

		$this->applicationDefinitionMerged = false;
	}

	/**
	 * Sets the description for the command.
	 *
	 * @param   string  $description  The description for the command
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setDescription(string $description)
	{
		$this->description = $description;
	}

	/**
	 * Sets the help for the command.
	 *
	 * @param   string  $help  The help for the command
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setHelp(string $help)
	{
		$this->help = $help;
	}

	/**
	 * Set the command's input helper set.
	 *
	 * @param   HelperSet  $helperSet  The helper set.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setHelperSet(HelperSet $helperSet)
	{
		$this->helperSet = $helperSet;
	}

	/**
	 * Set whether this command is hidden from the command listing.
	 *
	 * @param   boolean  $hidden  Flag if this command is hidden.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setHidden(bool $hidden)
	{
		$this->hidden = $hidden;
	}

	/**
	 * Set the command's name.
	 *
	 * @param   string  $name  The command name
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setName(string $name)
	{
		$this->name = $name;
	}
}
