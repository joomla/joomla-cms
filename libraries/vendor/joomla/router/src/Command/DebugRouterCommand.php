<?php
/**
 * Part of the Joomla Framework Router Package
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Router\Command;

use Joomla\Console\Command\AbstractCommand;
use Joomla\Router\RouterInterface;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command listing information about the application's router.
 *
 * @since  __DEPLOY_VERSION__
 */
class DebugRouterCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $defaultName = 'debug:router';

	/**
	 * The application router.
	 *
	 * @var    RouterInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $router;

	/**
	 * Instantiate the command.
	 *
	 * @param   RouterInterface  $router  The application router.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(RouterInterface $router)
	{
		$this->router = $router;

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
		$this->setDescription("Displays information about the application's routes");
		$this->addOption('show-controllers', null, InputOption::VALUE_NONE, 'Show the controller for a route in the overview');
		$this->setHelp(<<<'EOF'
The <info>%command.name%</info> command lists all of the application's routes:

  <info>php %command.full_name%</info>

To show the controllers that handle each route, use the <info>--show-controllers</info> option:

  <info>php %command.full_name% --show-controllers</info>
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

		$showControllers = $input->getOption('show-controllers');

		$io->title(sprintf('%s Router Information', $this->getApplication()->getName()));

		if (empty($this->router->getRoutes()))
		{
			$io->warning('The router has no routes.');

			return 0;
		}

		$tableHeaders = [
			'Methods',
			'Pattern',
			'Rules',
		];

		$tableRows = [];

		if ($showControllers)
		{
			$tableHeaders[] = 'Controller';
		}

		foreach ($this->router->getRoutes() as $route)
		{
			$row = [];
			$row[] = $route->getMethods() ? implode('|', $route->getMethods()) : 'ANY';
			$row[] = $route->getPattern();

			$rules = $route->getRules();

			if (empty($rules))
			{
				$row[] = 'N/A';
			}
			else
			{
				ksort($rules);

				$rulesAsString = '';

				foreach ($rules as $key => $value)
				{
					$rulesAsString .= sprintf("%s: %s\n", $key, $this->formatValue($value));
				}

				$row[] = new TableCell(rtrim($rulesAsString), ['rowspan' => count($rules)]);
			}

			if ($showControllers)
			{
				$row[] = $this->formatCallable($route->getController());
			}

			$tableRows[] = $row;
		}

		$io->table($tableHeaders, $tableRows);

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

			if ($class = $r->getClosureScopeClass())
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
	 * Formats a value as string.
	 *
	 * @param   mixed  $value  A value to format
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 * @note    This method is based on \Symfony\Bundle\FrameworkBundle\Console\Descriptor\Descriptor::formatValue()
	 */
	private function formatValue($value): string
	{
		if (\is_object($value))
		{
			return sprintf('object(%s)', \get_class($value));
		}

		if (\is_string($value))
		{
			return $value;
		}

		return preg_replace("/\n\s*/s", '', var_export($value, true));
	}
}
