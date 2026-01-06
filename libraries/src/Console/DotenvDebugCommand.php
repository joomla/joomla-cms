<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Dotenv\Command\DebugCommand;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command to debug current dotenv files with variables and values.
 *
 * @since  __DEPLOY_VERSION__
 */
class DotenvDebugCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected static $defaultName = 'config-dotenv:debug';

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
        // Prepare Input for Symfony Dotenv DebugCommand
        $input2 = new ArrayInput(['filter' => $input->getArgument('filter')]);

        // Create and run Symfony Dotenv DebugCommand
        $cmd = new DebugCommand($_ENV['JOOMLA_ENV'] ?? 'prod', JPATH_ROOT);
        $cmd->setHelperSet(new HelperSet([
            'formatter' => new FormatterHelper(),
        ]));

        return $cmd->run($input2, $output);
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
        $help = "The <info>%command.name%</info> command displays all the environment variables configured by dotenv:
        \n<info>php %command.full_name%</info>
        \nTo get specific variables, specify its full or partial name:
        \n<info>php %command.full_name% FOO_BAR</info>";

        $this->setDescription('Debug current dotenv files with variables and values');
        $this->addArgument('filter', InputArgument::OPTIONAL, 'The name of an environment variable for a filter.');
        $this->setHelp($help);
    }
}
