<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Extension;
use Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command for managing the update channel for Joomla
 *
 * @since  5.1.0
 */
class CoreUpdateChannelCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  5.1.0
     */
    protected static $defaultName = 'core:update:channel';

    /**
     * @var    DatabaseInterface
     * @since  5.1.0
     */
    private $db;

    /**
     * CoreUpdateChannelCommand constructor.
     *
     * @param   DatabaseInterface  $db  Database Instance
     *
     * @since 5.1.0
     */
    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;

        parent::__construct();
    }

    /**
     * Initialise the command.
     *
     * @return  void
     *
     * @since   5.1.0
     */
    protected function configure(): void
    {
        $help = "<info>%command.name%</info> allows to manage the update channel for Joomla core updates. Returns the currently selected channel when called without any parameters, otherwise sets it.
		\nUsage: <info>php %command.full_name%</info>";

        $this->setDescription('Manage the update channel for Joomla core updates');
        $this->setHelp($help);

        $this->addArgument('channel', InputArgument::OPTIONAL, 'Name of the update channel [default, next, custom]');
        $this->addOption('url', null, InputOption::VALUE_OPTIONAL, 'URL to update source. Only for custom update channel');
    }

    /**
     * Internal function to execute the command.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  integer  The command exit code
     *
     * @since   5.1.0
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $params  = ComponentHelper::getParams('com_joomlaupdate');
        $channel = $input->getArgument('channel');

        if (!$channel) {
            switch ($params->get('updatesource', 'default')) {
                case 'default':
                case 'next':
                    $symfonyStyle->writeln('You are on the "' . $params->get('updatesource', 'default') . '" update channel.');
                    break;
                case 'custom':
                    $symfonyStyle->writeln('You are on a "custom" update channel with the URL ' . $params->get('customurl') . '.');
                    break;
                default:
                    $symfonyStyle->error('The update channel is set to the invalid value \'' . $params->get('updatesource') . '\'!');
                    return Command::FAILURE;
            }

            return Command::SUCCESS;
        }

        if (!\in_array($channel, ['default', 'next', 'custom'])) {
            $symfonyStyle->error('The given update channel is invalid. Please only choose from [default, next, custom].');

            return Command::FAILURE;
        }

        $params->set('updatesource', $channel);

        if ($channel == 'custom') {
            $url = $input->getOption('url');

            if (!$url) {
                $symfonyStyle->error('When using the custom update channel, you have to provide a valid URL.');

                return Command::FAILURE;
            }

            $params->set('customurl', $url);
        }

        // Storing the parameters in the DB
        $table = new Extension($this->db);
        $table->load(['type' => 'component', 'element' => 'com_joomlaupdate']);
        $table->params = $params->toString();
        $table->store();

        /** @var UpdateModel $updatemodel */
        $app         = $this->getApplication();
        $updatemodel = $app->bootComponent('com_joomlaupdate')->getMVCFactory($app)->createModel('Update', 'Administrator');
        $updatemodel->applyUpdateSite();

        if ($channel == 'custom') {
            $symfonyStyle->success('The update channel for this site has been set to the custom url "' . $params->get('customurl') . '".');
        } else {
            $symfonyStyle->success('The update channel for this site has been set to "' . $params->get('updatesource', 'default') . '".');
        }

        return Command::SUCCESS;
    }
}
