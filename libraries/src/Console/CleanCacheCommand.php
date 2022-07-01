<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\CMS\Factory;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command for cleaning the system cache
 *
 * @since  4.0.0
 */
class CleanCacheCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'cache:clean';

    /**
     * Internal function to execute the command.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  integer  The command exit code
     *
     * @since   4.0.0
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $symfonyStyle->title('Cleaning System Cache');

        $cache = $this->getApplication()->bootComponent('com_cache')->getMVCFactory();
        /** @var Joomla\Component\Cache\Administrator\Model\CacheModel $model */
        $model = $cache->createModel('Cache', 'Administrator', ['ignore_request' => true]);

        if ($input->getArgument('expired')) {
            if (!$model->purge()) {
                $symfonyStyle->error('Expired Cache not cleaned');

                return Command::FAILURE;
            }

            $symfonyStyle->success('Expired Cache cleaned');

            return Command::SUCCESS;
        }

        if (!$model->clean()) {
            $symfonyStyle->error('Cache not cleaned');

            return Command::FAILURE;
        }

        $symfonyStyle->success('Cache cleaned');

        return Command::SUCCESS;
    }

    /**
     * Configure the command.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function configure(): void
    {
        $help = "<info>%command.name%</info> will clear entries from the system cache
		\nUsage: <info>php %command.full_name%</info>";

        $this->addArgument('expired', InputArgument::OPTIONAL, 'will clear expired entries from the system cache');
        $this->setDescription('Clean cache entries');
        $this->setHelp($help);
    }
}
