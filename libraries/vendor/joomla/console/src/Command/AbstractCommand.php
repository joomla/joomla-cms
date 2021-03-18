<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Console\Command;

use Joomla\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base command class for a Joomla! command line application.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string|null
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $defaultName;

	/**
	 * The command's aliases.
	 *
	 * @var    string[]
	 * @since  __DEPLOY_VERSION__
	 */
	private $aliases = [];

	/**
	 * The application running this command.
	 *
	 * @var    Application|null
	 * @since  __DEPLOY_VERSION__
	 */
	private $application;

	/**
	 * Flag tracking whether the application definition has been merged to this command.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $applicationDefinitionMerged = false;

	/**
	 * Flag tracking whether the application definition with arguments has been merged to this command.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $applicationDefinitionMergedWithArgs = false;

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
	 * @var    HelperSet|null
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
	private $name;

	/**
	 * The command's synopses.
	 *
	 * @var    string[]
	 * @since  __DEPLOY_VERSION__
	 */
	private $synopsis = [];

	/**
	 * Command constructor.
	 *
	 * @param   string|null  $name  The name of the command; if the name is empty and no default is set, a name must be set in the configure() method
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(?string $name = null)
	{
		$this->definition = new InputDefinition;

		if ($name !== null || null !== $name = static::getDefaultName())
		{
			$this->setName($name);
		}

		$this->configure();
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
	public function addArgument(string $name, ?int $mode = null, string $description = '', $default = null): self
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
	public function addOption(string $name, $shortcut = null, ?int $mode = null, $description = '', $default = null): self
	{
		$this->definition->addOption(new InputOption($name, $shortcut, $mode, $description, $default));

		return $this;
	}

	/**
	 * Configure the command.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function configure(): void
	{
	}

	/**
	 * Internal function to execute the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	abstract protected function doExecute(InputInterface $input, OutputInterface $output): int;

	/**
	 * Executes the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function execute(InputInterface $input, OutputInterface $output): int
	{
		// Force the creation of the synopsis before the merge with the app definition
		$this->getSynopsis(true);
		$this->getSynopsis(false);

		// Add the application arguments and options
		$this->mergeApplicationDefinition();

		// Bind the input against the command specific arguments/options
		$input->bind($this->getDefinition());

		$this->initialise($input, $output);

		// Ensure that the command has a `command` argument so it does not fail validation
		if ($input->hasArgument('command') && $input->getArgument('command') === null)
		{
			$input->setArgument('command', $this->getName());
		}

		$input->validate();

		return $this->doExecute($input, $output);
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
		if ($this->application)
		{
			return $this->application;
		}

		throw new \UnexpectedValueException('Application not set in ' . \get_class($this));
	}

	/**
	 * Get the default command name for this class.
	 *
	 * This allows a command name to defined and referenced without instantiating the full command class.
	 *
	 * @return  string|null
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getDefaultName(): ?string
	{
		$class = \get_called_class();
		$r     = new \ReflectionProperty($class, 'defaultName');

		return $class === $r->class ? static::$defaultName : null;
	}

	/**
	 * Gets the InputDefinition attached to this command.
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
	 * @return  HelperSet|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getHelperSet(): ?HelperSet
	{
		return $this->helperSet;
	}

	/**
	 * Get the command's name.
	 *
	 * @return  string|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getName(): ?string
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
			$_SERVER['PHP_SELF'] . ' ' . $name,
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

		if (!isset($this->synopsis[$key]))
		{
			$this->synopsis[$key] = trim(sprintf('%s %s', $this->getName(), $this->getDefinition()->getSynopsis($short)));
		}

		return $this->synopsis[$key];
	}

	/**
	 * Internal hook to initialise the command after the input has been bound and before the input is validated.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function initialise(InputInterface $input, OutputInterface $output): void
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
	 * Merges the application definition with the command definition.
	 *
	 * @param   boolean  $mergeArgs  Flag indicating whether the application's definition arguments should be merged
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @internal  This method should not be relied on as part of the public API
	 */
	final public function mergeApplicationDefinition(bool $mergeArgs = true): void
	{
		if (!$this->application || ($this->applicationDefinitionMerged && ($this->applicationDefinitionMergedWithArgs || !$mergeArgs)))
		{
			return;
		}

		$this->getDefinition()->addOptions($this->getApplication()->getDefinition()->getOptions());

		$this->applicationDefinitionMerged = true;

		if ($mergeArgs)
		{
			$currentArguments = $this->getDefinition()->getArguments();
			$this->getDefinition()->setArguments($this->getApplication()->getDefinition()->getArguments());
			$this->getDefinition()->addArguments($currentArguments);

			$this->applicationDefinitionMergedWithArgs = true;
		}
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
	public function setAliases(array $aliases): void
	{
		$this->aliases = $aliases;
	}

	/**
	 * Set the command's application.
	 *
	 * @param   Application  $application  The command's application
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setApplication(?Application $application = null): void
	{
		$this->application = $application;

		if ($application)
		{
			$this->setHelperSet($application->getHelperSet());
		}
		else
		{
			$this->helperSet = null;
		}
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
	public function setDefinition($definition): void
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
	public function setDescription(string $description): void
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
	public function setHelp(string $help): void
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
	public function setHelperSet(HelperSet $helperSet): void
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
	public function setHidden(bool $hidden): void
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
	public function setName(string $name): void
	{
		$this->name = $name;
	}
}
