<?php
/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Event\Command;

use Joomla\Console\Command\AbstractCommand;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\DispatcherInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command listing information about the application's event dispatcher.
 *
 * @since  __DEPLOY_VERSION__
 */
class DebugEventDispatcherCommand extends AbstractCommand implements DispatcherAwareInterface
{
	use DispatcherAwareTrait;

	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $defaultName = 'debug:event-dispatcher';

	/**
	 * Instantiate the command.
	 *
	 * @param   DispatcherInterface  $dispatcher  The application event dispatcher.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(DispatcherInterface $dispatcher)
	{
		$this->setDispatcher($dispatcher);

		parent::__construct();
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
		$this->setDescription("Displays information about the application's event dispatcher");
		$this->addArgument('event', InputArgument::OPTIONAL, 'Show the listeners for a specific event');
		$this->setHelp(<<<'EOF'
The <info>%command.name%</info> command lists all of the registered event handlers in an application's event dispatcher:

  <info>php %command.full_name%</info>

To get specific listeners for an event, specify its name:

  <info>php %command.full_name% application.before_execute</info>
EOF
		);
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
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		if ($event = $input->getArgument('event'))
		{
			$listeners = $this->dispatcher->getListeners($event);

			if (empty($listeners))
			{
				$io->warning(sprintf('The event "%s" does not have any registered listeners.', $event));

				return 0;
			}

			$io->title(sprintf('%s Registered Listeners for "%s" Event', $this->getApplication()->getName(), $event));

			$this->renderEventListenerTable($listeners, $io);

			return 0;
		}

		$listeners = $this->dispatcher->getListeners();

		if (empty($listeners))
		{
			$io->comment('There are no listeners registered to the event dispatcher.');

			return 0;
		}

		$io->title(sprintf('%s Registered Listeners Grouped By Event', $this->getApplication()->getName()));

		ksort($listeners);

		foreach ($listeners as $subscribedEvent => $eventListeners)
		{
			$io->section(sprintf('"%s" event', $subscribedEvent));

			$this->renderEventListenerTable($eventListeners, $io);
		}

		return 0;
	}

	/**
	 * Formats a callable resource to be displayed in the console output
	 *
	 * @param   callable  $callable  A callable resource to format
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \ReflectionException
	 * @note    This method is based on \Symfony\Bundle\FrameworkBundle\Console\Descriptor\TextDescriptor::formatCallable()
	 */
	private function formatCallable($callable): string
	{
		if (\is_array($callable))
		{
			if (\is_object($callable[0]))
			{
				return sprintf('%s::%s()', \get_class($callable[0]), $callable[1]);
			}

			return sprintf('%s::%s()', $callable[0], $callable[1]);
		}

		if (\is_string($callable))
		{
			return sprintf('%s()', $callable);
		}

		if ($callable instanceof \Closure)
		{
			$r = new \ReflectionFunction($callable);

			if (strpos($r->name, '{closure}') !== false)
			{
				return 'Closure()';
			}

			if (null !== $class = $r->getClosureScopeClass())
			{
				return sprintf('%s::%s()', $class->name, $r->name);
			}

			return $r->name . '()';
		}

		if (method_exists($callable, '__invoke'))
		{
			return sprintf('%s::__invoke()', \get_class($callable));
		}

		throw new \InvalidArgumentException('Callable is not describable.');
	}

	/**
	 * Renders the table of listeners for an event
	 *
	 * @param   array         $eventListeners  The listeners for an event
	 * @param   SymfonyStyle  $io              The I/O helper
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function renderEventListenerTable(array $eventListeners, SymfonyStyle $io): void
	{
		$tableHeaders = ['Order', 'Callable'];
		$tableRows    = [];

		foreach ($eventListeners as $order => $listener)
		{
			$tableRows[] = [
				sprintf('#%d', $order + 1),
				$this->formatCallable($listener),
			];
		}

		$io->table($tableHeaders, $tableRows);
	}
}
