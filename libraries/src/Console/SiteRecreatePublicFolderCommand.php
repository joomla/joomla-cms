<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\CMS\Helper\PublicFolderGeneratorHelper;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Filter\InputFilter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command for recreating a public folder
 *
 * @since  __DEPLOY_VERSION__
 */
class SiteRecreatePublicFolderCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected static $defaultName = 'site:recreate-public-folder';

    /**
     * SymfonyStyle Object
     * @var   object
     * @since __DEPLOY_VERSION__
     */
    private $ioStyle;

    /**
     * Stores the Input Object
     * @var   object
     * @since __DEPLOY_VERSION__
     */
    private $cliInput;

    /**
     * The public folder path (absolute)
     *
     * @var    string
     *
     * @since  __DEPLOY_VERSION__
     */
    private $publicFolder;

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
        $this->configureIO($input, $output);
        $this->ioStyle->title('Recreate a public folder');

        $this->publicFolder = $this->getStringFromOption('public-folder', 'Please enter the absolute path to the public folder', true);

        // Remove the last (Windows || NIX) slash
        $this->publicFolder = rtrim((new InputFilter())->clean($this->publicFolder, 'PATH'), '/');
        $this->publicFolder = rtrim($this->publicFolder, '\\');

        $generatorClass = new PublicFolderGeneratorHelper();

        if (is_dir($this->publicFolder)) {
            if (!($generatorClass->cleanPublicFolder($this->publicFolder))) {
                return Command::FAILURE;
            }
        }

        if (!($generatorClass->createPublicFolder($this->publicFolder))) {
            return Command::FAILURE;
        }

        $this->ioStyle->success("Public folder recreated!");

        return Command::SUCCESS;
    }

    /**
     * Method to get a value from option
     *
     * @param   string  $option    set the option name
     * @param   string  $question  set the question if user enters no value to option
     * @param   bool    $required  is it required
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getStringFromOption($option, $question, $required = true): string
    {
        $answer = (string) $this->cliInput->getOption($option);

        while (!$answer && $required) {
            $answer = (string) $this->ioStyle->ask($question);
        }

        if (!$required) {
            $answer = (string) $this->ioStyle->ask($question);
        }

        return $answer;
    }

    /**
     * Configure the IO.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function configureIO(InputInterface $input, OutputInterface $output)
    {
        $this->cliInput = $input;
        $this->ioStyle  = new SymfonyStyle($input, $output);
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
        $help = "<info>%command.name%</info> will recreate a public folder
		\nUsage: <info>php %command.full_name%</info>";

        $this->addOption('public-folder', null, InputOption::VALUE_REQUIRED, 'public folder absolute path');
        $this->setDescription('Recreate a public folder');
        $this->setHelp($help);
    }
}
