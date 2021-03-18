<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Console;

use Joomla\Application\AbstractApplication;
use Joomla\Application\ApplicationEvents;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Console\Command\HelpCommand;
use Joomla\Console\Event\ApplicationErrorEvent;
use Joomla\Console\Event\BeforeCommandExecuteEvent;
use Joomla\Console\Event\CommandErrorEvent;
use Joomla\Console\Event\TerminateEvent;
use Joomla\Console\Exception\NamespaceNotFoundException;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputAwareInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Terminal;
use Symfony\Component\ErrorHandler\ErrorHandler;

/**
 * Base application class for a Joomla! command line application.
 *
 * @since  __DEPLOY_VERSION__
 */
class Application extends AbstractApplication
{
	/**
	 * Flag indicating the application should automatically exit after the command is run.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $autoExit = true;

	/**
	 * Flag indicating the application should catch and handle Throwables.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $catchThrowables = true;

	/**
	 * The available commands.
	 *
	 * @var    AbstractCommand[]
	 * @since  __DEPLOY_VERSION__
	 */
	private $commands = [];

	/**
	 * The command loader.
	 *
	 * @var    Loader\LoaderInterface|null
	 * @since  __DEPLOY_VERSION__
	 */
	private $commandLoader;

	/**
	 * Console input handler.
	 *
	 * @var    InputInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $consoleInput;

	/**
	 * Console output handler.
	 *
	 * @var    OutputInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $consoleOutput;

	/**
	 * The default command for the application.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $defaultCommand = 'list';

	/**
	 * The application input definition.
	 *
	 * @var    InputDefinition|null
	 * @since  __DEPLOY_VERSION__
	 */
	private $definition;

	/**
	 * The application helper set.
	 *
	 * @var    HelperSet|null
	 * @since  __DEPLOY_VERSION__
	 */
	private $helperSet;

	/**
	 * Internal flag tracking if the command store has been initialised.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $initialised = false;

	/**
	 * The name of the application.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $name = '';

	/**
	 * Reference to the currently running command.
	 *
	 * @var    AbstractCommand|null
	 * @since  __DEPLOY_VERSION__
	 */
	private $runningCommand;

	/**
	 * The console terminal helper.
	 *
	 * @var    Terminal
	 * @since  __DEPLOY_VERSION__
	 */
	private $terminal;

	/**
	 * The version of the application.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $version = '';

	/**
	 * Internal flag tracking if the user is seeking help for the given command.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $wantsHelp = false;

	/**
	 * Class constructor.
	 *
	 * @param   InputInterface   $input   An optional argument to provide dependency injection for the application's input object.  If the argument is
	 *                                    an InputInterface object that object will become the application's input object, otherwise a default input
	 *                                    object is created.
	 * @param   OutputInterface  $output  An optional argument to provide dependency injection for the application's output object.  If the argument
	 *                                    is an OutputInterface object that object will become the application's output object, otherwise a default
	 *                                    output object is created.
	 * @param   Registry         $config  An optional argument to provide dependency injection for the application's config object.  If the argument
	 *                                    is a Registry object that object will become the application's config object, otherwise a default config
	 *                                    object is created.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(?InputInterface $input = null, ?OutputInterface $output = null, ?Registry $config = null)
	{
		// Close the application if we are not executed from the command line.
		if (!\defined('STDOUT') || !\defined('STDIN') || !isset($_SERVER['argv']))
		{
			$this->close();
		}

		$this->consoleInput  = $input ?: new ArgvInput;
		$this->consoleOutput = $output ?: new ConsoleOutput;
		$this->terminal      = new Terminal;

		// Call the constructor as late as possible (it runs `initialise`).
		parent::__construct($config);
	}

	/**
	 * Adds a command object.
	 *
	 * If a command with the same name already exists, it will be overridden. If the command is not enabled it will not be added.
	 *
	 * @param   AbstractCommand  $command  The command to add to the application.
	 *
	 * @return  AbstractCommand
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  LogicException
	 */
	public function addCommand(AbstractCommand $command): AbstractCommand
	{
		$this->initCommands();

		if (!$command->isEnabled())
		{
			return $command;
		}

		$command->setApplication($this);

		try
		{
			$command->getDefinition();
		}
		catch (\TypeError $exception)
		{
			throw new LogicException(sprintf('Command class "%s" is not correctly initialised.', \get_class($command)), 0, $exception);
		}

		if (!$command->getName())
		{
			throw new LogicException(sprintf('The command class "%s" does not have a name.', \get_class($command)));
		}

		$this->commands[$command->getName()] = $command;

		foreach ($command->getAliases() as $alias)
		{
			$this->commands[$alias] = $command;
		}

		return $command;
	}

	/**
	 * Configures the console input and output instances for the process.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function configureIO(): void
	{
		if ($this->consoleInput->hasParameterOption(['--ansi'], true))
		{
			$this->consoleOutput->setDecorated(true);
		}
		elseif ($this->consoleInput->hasParameterOption(['--no-ansi'], true))
		{
			$this->consoleOutput->setDecorated(false);
		}

		if ($this->consoleInput->hasParameterOption(['--no-interaction', '-n'], true))
		{
			$this->consoleInput->setInteractive(false);
		}

		if ($this->consoleInput->hasParameterOption(['--quiet', '-q'], true))
		{
			$this->consoleOutput->setVerbosity(OutputInterface::VERBOSITY_QUIET);
			$this->consoleInput->setInteractive(false);
		}
		else
		{
			if ($this->consoleInput->hasParameterOption('-vvv', true)
				|| $this->consoleInput->hasParameterOption('--verbose=3', true)
				|| $this->consoleInput->getParameterOption('--verbose', false, true) === 3
			)
			{
				$this->consoleOutput->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
			}
			elseif ($this->consoleInput->hasParameterOption('-vv', true)
				|| $this->consoleInput->hasParameterOption('--verbose=2', true)
				|| $this->consoleInput->getParameterOption('--verbose', false, true) === 2
			)
			{
				$this->consoleOutput->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
			}
			elseif ($this->consoleInput->hasParameterOption('-v', true)
				|| $this->consoleInput->hasParameterOption('--verbose=1', true)
				|| $this->consoleInput->hasParameterOption('--verbose', true)
				|| $this->consoleInput->getParameterOption('--verbose', false, true)
			)
			{
				$this->consoleOutput->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
			}
		}
	}

	/**
	 * Method to run the application routines.
	 *
	 * @return  integer  The exit code for the application
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Throwable
	 */
	protected function doExecute(): int
	{
		$input  = $this->consoleInput;
		$output = $this->consoleOutput;

		// If requesting the version, short circuit the application and send the version data
		if ($input->hasParameterOption(['--version', '-V'], true))
		{
			$output->writeln($this->getLongVersion());

			return 0;
		}

		$name = $this->getCommandName($input);

		// Redirect to the help command if requested
		if ($input->hasParameterOption(['--help', '-h'], true))
		{
			// If no command name was given, use the help command with a minimal input; otherwise flag the request for processing later
			if (!$name)
			{
				$name  = 'help';
				$input = new ArrayInput(['command_name' => $this->defaultCommand]);
			}
			else
			{
				$this->wantsHelp = true;
			}
		}

		// If we still do not have a command name, then the user has requested the application's default command
		if (!$name)
		{
			$name       = $this->defaultCommand;
			$definition = $this->getDefinition();

			// Overwrite the default value of the command argument with the default command name
			$definition->setArguments(
				array_merge(
					$definition->getArguments(),
					[
						'command' => new InputArgument(
							'command',
							InputArgument::OPTIONAL,
							$definition->getArgument('command')->getDescription(),
							$name
						),
					]
				)
			);
		}

		try
		{
			$this->runningCommand = null;

			$command = $this->getCommand($name);
		}
		catch (\Throwable $e)
		{
			if ($e instanceof CommandNotFoundException && !($e instanceof NamespaceNotFoundException))
			{
				(new SymfonyStyle($input, $output))->block(sprintf("\nCommand \"%s\" is not defined.\n", $name), null, 'error');
			}

			$event = new CommandErrorEvent($e, $this);

			$this->dispatchEvent(ConsoleEvents::COMMAND_ERROR, $event);

			if ($event->getExitCode() === 0)
			{
				return 0;
			}

			$e = $event->getError();

			throw $e;
		}

		$this->runningCommand = $command;
		$exitCode             = $this->runCommand($command, $input, $output);
		$this->runningCommand = null;

		return $exitCode;
	}

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Throwable
	 */
	public function execute()
	{
		putenv('LINES=' . $this->terminal->getHeight());
		putenv('COLUMNS=' . $this->terminal->getWidth());

		$this->configureIO();

		$renderThrowable = function (\Throwable $e)
		{
			$this->renderThrowable($e);
		};

		if ($phpHandler = set_exception_handler($renderThrowable))
		{
			restore_exception_handler();

			if (!\is_array($phpHandler) || !$phpHandler[0] instanceof ErrorHandler)
			{
				$errorHandler = true;
			}
			elseif ($errorHandler = $phpHandler[0]->setExceptionHandler($renderThrowable))
			{
				$phpHandler[0]->setExceptionHandler($errorHandler);
			}
		}

		try
		{
			$this->dispatchEvent(ApplicationEvents::BEFORE_EXECUTE);

			// Perform application routines.
			$exitCode = $this->doExecute();

			$this->dispatchEvent(ApplicationEvents::AFTER_EXECUTE);
		}
		catch (\Throwable $throwable)
		{
			if (!$this->shouldCatchThrowables())
			{
				throw $throwable;
			}

			$renderThrowable($throwable);

			$event = new ApplicationErrorEvent($throwable, $this, $this->runningCommand);

			$this->dispatchEvent(ConsoleEvents::APPLICATION_ERROR, $event);

			$exitCode = $event->getExitCode();

			if (is_numeric($exitCode))
			{
				$exitCode = (int) $exitCode;

				if ($exitCode === 0)
				{
					$exitCode = 1;
				}
			}
			else
			{
				$exitCode = 1;
			}
		}
		finally
		{
			// If the exception handler changed, keep it; otherwise, unregister $renderThrowable
			if (!$phpHandler)
			{
				if (set_exception_handler($renderThrowable) === $renderThrowable)
				{
					restore_exception_handler();
				}

				restore_exception_handler();
			}
			elseif (!$errorHandler)
			{
				$finalHandler = $phpHandler[0]->setExceptionHandler(null);

				if ($finalHandler !== $renderThrowable)
				{
					$phpHandler[0]->setExceptionHandler($finalHandler);
				}
			}

			if ($this->shouldAutoExit() && isset($exitCode))
			{
				$exitCode = $exitCode > 255 ? 255 : $exitCode;
				$this->close($exitCode);
			}
		}
	}

	/**
	 * Finds a registered namespace by a name.
	 *
	 * @param   string  $namespace  A namespace to search for
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  NamespaceNotFoundException When namespace is incorrect or ambiguous
	 */
	public function findNamespace(string $namespace): string
	{
		$allNamespaces = $this->getNamespaces();

		$expr = preg_replace_callback(
			'{([^:]+|)}',
			function ($matches)
			{
				return preg_quote($matches[1]) . '[^:]*';
			},
			$namespace
		);

		$namespaces = preg_grep('{^' . $expr . '}', $allNamespaces);

		if (empty($namespaces))
		{
			throw new NamespaceNotFoundException(sprintf('There are no commands defined in the "%s" namespace.', $namespace));
		}

		$exact = \in_array($namespace, $namespaces, true);

		if (\count($namespaces) > 1 && !$exact)
		{
			throw new NamespaceNotFoundException(sprintf('The namespace "%s" is ambiguous.', $namespace));
		}

		return $exact ? $namespace : reset($namespaces);
	}

	/**
	 * Gets all commands, including those available through a command loader, optionally filtered on a command namespace.
	 *
	 * @param   string  $namespace  An optional command namespace to filter by.
	 *
	 * @return  AbstractCommand[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getAllCommands(string $namespace = ''): array
	{
		$this->initCommands();

		if ($namespace === '')
		{
			$commands = $this->commands;

			if (!$this->commandLoader)
			{
				return $commands;
			}

			foreach ($this->commandLoader->getNames() as $name)
			{
				if (!isset($commands[$name]))
				{
					$commands[$name] = $this->getCommand($name);
				}
			}

			return $commands;
		}

		$commands = [];

		foreach ($this->commands as $name => $command)
		{
			if ($namespace === $this->extractNamespace($name, substr_count($namespace, ':') + 1))
			{
				$commands[$name] = $command;
			}
		}

		if ($this->commandLoader)
		{
			foreach ($this->commandLoader->getNames() as $name)
			{
				if (!isset($commands[$name]) && $namespace === $this->extractNamespace($name, substr_count($namespace, ':') + 1))
				{
					$commands[$name] = $this->getCommand($name);
				}
			}
		}

		return $commands;
	}

	/**
	 * Returns a registered command by name or alias.
	 *
	 * @param   string  $name  The command name or alias
	 *
	 * @return  AbstractCommand
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  CommandNotFoundException
	 */
	public function getCommand(string $name): AbstractCommand
	{
		$this->initCommands();

		if (!$this->hasCommand($name))
		{
			throw new CommandNotFoundException(sprintf('The command "%s" does not exist.', $name));
		}

		// If the command isn't registered, pull it from the loader if registered
		if (!isset($this->commands[$name]) && $this->commandLoader)
		{
			$this->addCommand($this->commandLoader->get($name));
		}

		$command = $this->commands[$name];

		// If the user requested help, we'll fetch the help command now and inject the user's command into it
		if ($this->wantsHelp)
		{
			$this->wantsHelp = false;

			/** @var HelpCommand $helpCommand */
			$helpCommand = $this->getCommand('help');
			$helpCommand->setCommand($command);

			return $helpCommand;
		}

		return $command;
	}

	/**
	 * Get the name of the command to run.
	 *
	 * @param   InputInterface  $input  The input to read the argument from
	 *
	 * @return  string|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getCommandName(InputInterface $input): ?string
	{
		return $input->getFirstArgument();
	}

	/**
	 * Get the registered commands.
	 *
	 * This method only retrieves commands which have been explicitly registered.  To get all commands including those from a
	 * command loader, use the `getAllCommands()` method.
	 *
	 * @return  AbstractCommand[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getCommands(): array
	{
		return $this->commands;
	}

	/**
	 * Get the console input handler.
	 *
	 * @return  InputInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getConsoleInput(): InputInterface
	{
		return $this->consoleInput;
	}

	/**
	 * Get the console output handler.
	 *
	 * @return  OutputInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getConsoleOutput(): OutputInterface
	{
		return $this->consoleOutput;
	}

	/**
	 * Get the commands which should be registered by default to the application.
	 *
	 * @return  AbstractCommand[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getDefaultCommands(): array
	{
		return [
			new Command\ListCommand,
			new Command\HelpCommand,
		];
	}

	/**
	 * Builds the defauilt input definition.
	 *
	 * @return  InputDefinition
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getDefaultInputDefinition(): InputDefinition
	{
		return new InputDefinition(
			[
				new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),
				new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display the help information'),
				new InputOption('--quiet', '-q', InputOption::VALUE_NONE, 'Flag indicating that all output should be silenced'),
				new InputOption(
					'--verbose',
					'-v|vv|vvv',
					InputOption::VALUE_NONE,
					'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'
				),
				new InputOption('--ansi', '', InputOption::VALUE_NONE, 'Force ANSI output'),
				new InputOption('--no-ansi', '', InputOption::VALUE_NONE, 'Disable ANSI output'),
				new InputOption('--no-interaction', '-n', InputOption::VALUE_NONE, 'Flag to disable interacting with the user'),
			]
		);
	}

	/**
	 * Builds the default helper set.
	 *
	 * @return  HelperSet
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getDefaultHelperSet(): HelperSet
	{
		return new HelperSet(
			[
				new FormatterHelper,
				new DebugFormatterHelper,
				new ProcessHelper,
				new QuestionHelper,
			]
		);
	}

	/**
	 * Gets the InputDefinition related to this Application.
	 *
	 * @return  InputDefinition
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getDefinition(): InputDefinition
	{
		if (!$this->definition)
		{
			$this->definition = $this->getDefaultInputDefinition();
		}

		return $this->definition;
	}

	/**
	 * Get the helper set associated with the application.
	 *
	 * @return  HelperSet
	 */
	public function getHelperSet(): HelperSet
	{
		if (!$this->helperSet)
		{
			$this->helperSet = $this->getDefaultHelperSet();
		}

		return $this->helperSet;
	}

	/**
	 * Get the long version string for the application.
	 *
	 * Typically, this is the application name and version and is used in the application help output.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getLongVersion(): string
	{
		$name = $this->getName();

		if ($name === '')
		{
			$name = 'Joomla Console Application';
		}

		if ($this->getVersion() !== '')
		{
			return sprintf('%s <info>%s</info>', $name, $this->getVersion());
		}

		return $name;
	}

	/**
	 * Get the name of the application.
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
	 * Returns an array of all unique namespaces used by currently registered commands.
	 *
	 * Note that this does not include the global namespace which always exists.
	 *
	 * @return  string[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getNamespaces(): array
	{
		$namespaces = [];

		foreach ($this->getAllCommands() as $command)
		{
			$namespaces = array_merge($namespaces, $this->extractAllNamespaces($command->getName()));

			foreach ($command->getAliases() as $alias)
			{
				$namespaces = array_merge($namespaces, $this->extractAllNamespaces($alias));
			}
		}

		return array_values(array_unique(array_filter($namespaces)));
	}

	/**
	 * Get the version of the application.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getVersion(): string
	{
		return $this->version;
	}

	/**
	 * Check if the application has a command with the given name.
	 *
	 * @param   string  $name  The name of the command to check for existence.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function hasCommand(string $name): bool
	{
		$this->initCommands();

		// If command is already registered, we're good
		if (isset($this->commands[$name]))
		{
			return true;
		}

		// If there is no loader, we can't look for a command there
		if (!$this->commandLoader)
		{
			return false;
		}

		return $this->commandLoader->has($name);
	}

	/**
	 * Custom initialisation method.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function initialise(): void
	{
		// Set the current directory.
		$this->set('cwd', getcwd());
	}

	/**
	 * Renders an error message for a Throwable object
	 *
	 * @param   \Throwable  $throwable  The Throwable object to render the message for.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function renderThrowable(\Throwable $throwable): void
	{
		$output = $this->consoleOutput instanceof ConsoleOutputInterface ? $this->consoleOutput->getErrorOutput() : $this->consoleOutput;

		$output->writeln('', OutputInterface::VERBOSITY_QUIET);

		$this->doRenderThrowable($throwable, $output);

		if (null !== $this->runningCommand)
		{
			$output->writeln(
				sprintf(
					'<info>%s</info>',
					sprintf($this->runningCommand->getSynopsis(), $this->getName())
				),
				OutputInterface::VERBOSITY_QUIET
			);

			$output->writeln('', OutputInterface::VERBOSITY_QUIET);
		}
	}

	/**
	 * Handles recursively rendering error messages for a Throwable and all previous Throwables contained within.
	 *
	 * @param   \Throwable       $throwable  The Throwable object to render the message for.
	 * @param   OutputInterface  $output     The output object to send the message to.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function doRenderThrowable(\Throwable $throwable, OutputInterface $output): void
	{
		do
		{
			$message = trim($throwable->getMessage());

			if ($message === '' || OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity())
			{
				$class = \get_class($throwable);
				$class = 'c' === $class[0] && 0 === strpos($class, "class@anonymous\0") ? get_parent_class($class) . '@anonymous' : $class;
				$title = sprintf('  [%s%s]  ', $class, 0 !== ($code = $throwable->getCode()) ? ' (' . $code . ')' : '');
				$len   = Helper::strlen($title);
			}
			else
			{
				$len = 0;
			}

			if (strpos($message, "class@anonymous\0") !== false)
			{
				$message = preg_replace_callback(
					'/class@anonymous\x00.*?\.php0x?[0-9a-fA-F]++/',
					function ($m)
					{
						return class_exists($m[0], false) ? get_parent_class($m[0]) . '@anonymous' : $m[0];
					},
					$message
				);
			}

			$width = $this->terminal->getWidth() ? $this->terminal->getWidth() - 1 : PHP_INT_MAX;
			$lines = [];

			foreach ($message !== '' ? preg_split('/\r?\n/', $message) : [] as $line)
			{
				foreach ($this->splitStringByWidth($line, $width - 4) as $line)
				{
					// Pre-format lines to get the right string length
					$lineLength = StringHelper::strlen($line) + 4;
					$lines[]    = [$line, $lineLength];
					$len        = max($lineLength, $len);
				}
			}

			$messages = [];

			if (!$throwable instanceof ExceptionInterface || OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity())
			{
				$messages[] = sprintf(
					'<comment>%s</comment>',
					OutputFormatter::escape(
						sprintf(
							'In %s line %s:', basename($throwable->getFile()) ?: 'n/a', $throwable->getLine() ?: 'n/a'
						)
					)
				);
			}

			$messages[] = $emptyLine = sprintf('<error>%s</error>', str_repeat(' ', $len));

			if ($message === '' || OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity())
			{
				$messages[] = sprintf('<error>%s%s</error>', $title, str_repeat(' ', max(0, $len - Helper::strlen($title))));
			}

			foreach ($lines as $line)
			{
				$messages[] = sprintf('<error>  %s  %s</error>', OutputFormatter::escape($line[0]), str_repeat(' ', $len - $line[1]));
			}

			$messages[] = $emptyLine;
			$messages[] = '';

			$output->writeln($messages, OutputInterface::VERBOSITY_QUIET);

			if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity())
			{
				$output->writeln('<comment>Exception trace:</comment>', OutputInterface::VERBOSITY_QUIET);

				// Exception related properties
				$trace = $throwable->getTrace();
				array_unshift(
					$trace,
					[
						'function' => '',
						'file'     => $throwable->getFile() ?: 'n/a',
						'line'     => $throwable->getLine() ?: 'n/a',
						'args'     => [],
					]
				);

				for ($i = 0, $count = \count($trace); $i < $count; ++$i)
				{
					$class    = $trace[$i]['class'] ?? '';
					$type     = $trace[$i]['type'] ?? '';
					$function = $trace[$i]['function'] ?? '';
					$file     = $trace[$i]['file'] ?? 'n/a';
					$line     = $trace[$i]['line'] ?? 'n/a';

					$output->writeln(
						sprintf(
							' %s%s at <info>%s:%s</info>', $class, $function ? $type . $function . '()' : '', $file, $line
						),
						OutputInterface::VERBOSITY_QUIET
					);
				}

				$output->writeln('', OutputInterface::VERBOSITY_QUIET);
			}
		}
		while ($throwable = $throwable->getPrevious());
	}

	/**
	 * Splits a string for a specified width for use in an output.
	 *
	 * @param   string   $string  The string to split.
	 * @param   integer  $width   The maximum width of the output.
	 *
	 * @return  string[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function splitStringByWidth(string $string, int $width): array
	{
		/*
		 * The str_split function is not suitable for multi-byte characters, we should use preg_split to get char array properly.
		 * Additionally, array_slice() is not enough as some character has doubled width.
		 * We need a function to split string not by character count but by string width
		 */
		if (false === $encoding = mb_detect_encoding($string, null, true))
		{
			return str_split($string, $width);
		}

		$utf8String = mb_convert_encoding($string, 'utf8', $encoding);
		$lines      = [];
		$line       = '';
		$offset     = 0;

		while (preg_match('/.{1,10000}/u', $utf8String, $m, 0, $offset))
		{
			$offset += \strlen($m[0]);

			foreach (preg_split('//u', $m[0]) as $char)
			{
				// Test if $char could be appended to current line
				if (mb_strwidth($line . $char, 'utf8') <= $width)
				{
					$line .= $char;

					continue;
				}

				// If not, push current line to array and make a new line
				$lines[] = str_pad($line, $width);
				$line    = $char;
			}
		}

		$lines[] = \count($lines) ? str_pad($line, $width) : $line;
		mb_convert_variables($encoding, 'utf8', $lines);

		return $lines;
	}

	/**
	 * Run the given command.
	 *
	 * @param   AbstractCommand  $command  The command to run.
	 * @param   InputInterface   $input    The input to inject into the command.
	 * @param   OutputInterface  $output   The output to inject into the command.
	 *
	 * @return  integer
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \Throwable
	 */
	protected function runCommand(AbstractCommand $command, InputInterface $input, OutputInterface $output): int
	{
		if ($command->getHelperSet() !== null)
		{
			foreach ($command->getHelperSet() as $helper)
			{
				if ($helper instanceof InputAwareInterface)
				{
					$helper->setInput($input);
				}
			}
		}

		// If the application doesn't have an event dispatcher, we can short circuit and just execute the command
		try
		{
			$this->getDispatcher();
		}
		catch (\UnexpectedValueException $exception)
		{
			return $command->execute($input, $output);
		}

		// Bind before dispatching the event so the listeners have access to input options/arguments
		try
		{
			$command->mergeApplicationDefinition();
			$input->bind($command->getDefinition());
		}
		catch (ExceptionInterface $e)
		{
			// Ignore invalid options/arguments for now
		}

		$event     = new BeforeCommandExecuteEvent($this, $command);
		$exception = null;

		try
		{
			$this->dispatchEvent(ConsoleEvents::BEFORE_COMMAND_EXECUTE, $event);

			if ($event->isCommandEnabled())
			{
				$exitCode = $command->execute($input, $output);
			}
			else
			{
				$exitCode = BeforeCommandExecuteEvent::RETURN_CODE_DISABLED;
			}
		}
		catch (\Throwable $exception)
		{
			$event = new CommandErrorEvent($exception, $this, $command);

			$this->dispatchEvent(ConsoleEvents::COMMAND_ERROR, $event);

			$exception = $event->getError();
			$exitCode  = $event->getExitCode();

			if ($exitCode === 0)
			{
				$exception = null;
			}
		}

		$event = new TerminateEvent($exitCode, $this, $command);

		$this->dispatchEvent(ConsoleEvents::TERMINATE, $event);

		if ($exception !== null)
		{
			throw $exception;
		}

		return $event->getExitCode();
	}

	/**
	 * Set whether the application should auto exit.
	 *
	 * @param   boolean  $autoExit  The auto exit state.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setAutoExit(bool $autoExit): void
	{
		$this->autoExit = $autoExit;
	}

	/**
	 * Set whether the application should catch Throwables.
	 *
	 * @param   boolean  $catchThrowables  The catch Throwables state.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setCatchThrowables(bool $catchThrowables): void
	{
		$this->catchThrowables = $catchThrowables;
	}

	/**
	 * Set the command loader.
	 *
	 * @param   Loader\LoaderInterface  $loader  The new command loader.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setCommandLoader(Loader\LoaderInterface $loader): void
	{
		$this->commandLoader = $loader;
	}

	/**
	 * Set the application's helper set.
	 *
	 * @param   HelperSet  $helperSet  The new HelperSet.
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
	 * Set the name of the application.
	 *
	 * @param   string  $name  The new application name.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setName(string $name): void
	{
		$this->name = $name;
	}

	/**
	 * Set the version of the application.
	 *
	 * @param   string  $version  The new application version.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setVersion(string $version): void
	{
		$this->version = $version;
	}

	/**
	 * Get the application's auto exit state.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function shouldAutoExit(): bool
	{
		return $this->autoExit;
	}

	/**
	 * Get the application's catch Throwables state.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function shouldCatchThrowables(): bool
	{
		return $this->catchThrowables;
	}

	/**
	 * Returns all namespaces of the command name.
	 *
	 * @param   string  $name  The full name of the command
	 *
	 * @return  string[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function extractAllNamespaces(string $name): array
	{
		// -1 as third argument is needed to skip the command short name when exploding
		$parts      = explode(':', $name, -1);
		$namespaces = [];

		foreach ($parts as $part)
		{
			if (\count($namespaces))
			{
				$namespaces[] = end($namespaces) . ':' . $part;
			}
			else
			{
				$namespaces[] = $part;
			}
		}

		return $namespaces;
	}

	/**
	 * Returns the namespace part of the command name.
	 *
	 * @param   string   $name   The command name to process
	 * @param   integer  $limit  The maximum number of parts of the namespace
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function extractNamespace(string $name, ?int $limit = null): string
	{
		$parts = explode(':', $name);
		array_pop($parts);

		return implode(':', $limit === null ? $parts : \array_slice($parts, 0, $limit));
	}

	/**
	 * Internal function to initialise the command store, this allows the store to be lazy loaded only when needed.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function initCommands(): void
	{
		if ($this->initialised)
		{
			return;
		}

		$this->initialised = true;

		foreach ($this->getDefaultCommands() as $command)
		{
			$this->addCommand($command);
		}
	}
}
