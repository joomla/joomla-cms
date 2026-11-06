<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Dotenv\Command\DotenvDumpCommand as SymfonyDotenvDumpCommand;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command to compile .env files into a PHP-optimized file called .env.local.php.
 *
 * @since  __DEPLOY_VERSION__
 */
class DotenvDumpCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected static $defaultName = 'config-dotenv:dump';

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
        $input2 = new ArrayInput(['env' => $input->getArgument('env')]);

        // Create and run Symfony Dotenv DebugCommand
        $cmd    = new SymfonyDotenvDumpCommand(JPATH_ROOT, $_ENV['JOOMLA_ENV'] ?? 'prod');
        $result = $cmd->run($input2, $output);

        // Make a few adjustment
        if ($result === 0 && is_file(JPATH_ROOT . '/.env.local.php')) {
            $content = file_get_contents(JPATH_ROOT . '/.env.local.php');
            $content = str_replace([
                // Adjust command example
                '"php bin/console dotenv:dump',
                // Adjust ENV name
                '\'APP_ENV\' =>',
            ], [
                '"php cli/joomla.php config-dotenv:dump',
                '\'JOOMLA_ENV\' =>',
            ], $content);

            file_put_contents(JPATH_ROOT . '/.env.local.php', $content, \LOCK_EX);
        }

        return $result;
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
        $help = "The <info>%command.name%</info> command compiles .env files into a PHP-optimized file called .env.local.php.
        \n<info>%command.full_name%</info>
        \nTo set specific application environment for dump result:
        \n<info>php %command.full_name% prod</info>";

        $this->setDescription('Compile .env files into a PHP-optimized file called .env.local.php');
        $this->addArgument('env', InputArgument::OPTIONAL, 'The application environment to dump .env files for - e.g. "prod".');
        $this->setHelp($help);
    }
}
