<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command for discovering extensions
 *
 * @since  4.0.0
 */
class ExtensionDiscoverCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     *
     * @since  4.0.0
     */
    protected static $defaultName = 'extension:discover';

    /**
     * Stores the Input Object
     *
     * @var    InputInterface
     *
     * @since  4.0.0
     */
    private $cliInput;

    /**
     * SymfonyStyle Object
     *
     * @var    SymfonyStyle
     *
     * @since  4.0.0
     */
    private $ioStyle;

    /**
     * Configures the IO
     *
     * @param   InputInterface   $input   Console Input
     * @param   OutputInterface  $output  Console Output
     *
     * @return  void
     *
     * @since   4.0.0
     *
     */
    private function configureIO(InputInterface $input, OutputInterface $output): void
    {
        $this->cliInput = $input;
        $this->ioStyle = new SymfonyStyle($input, $output);
    }

    /**
     * Initialise the command.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function configure(): void
    {
        $help = "<info>%command.name%</info> is used to discover extensions
		\nUsage:
		\n  <info>php %command.full_name%</info>";

        $this->setDescription('Discover extensions');
        $this->setHelp($help);
    }

    /**
     * Used for discovering extensions
     *
     * @return  integer  The count of discovered extensions
     *
     * @throws  \Exception
     *
     * @since   4.0.0
     */
    public function processDiscover(): int
    {
        $app = $this->getApplication();

        $mvcFactory = $app->bootComponent('com_installer')->getMVCFactory();

        $model = $mvcFactory->createModel('Discover', 'Administrator');

        return $model->discover();
    }

    /**
     * Used for finding the text for the note
     *
     * @param   int  $count   The count of installed Extensions
     *
     * @return  string  The text for the note
     *
     * @since   4.0.0
     */
    public function getNote(int $count): string
    {
        if ($count < 1) {
            return 'No extensions were discovered.';
        } elseif ($count === 1) {
            return $count . ' extension has been discovered.';
        } else {
            return $count . ' extensions have been discovered.';
        }
    }

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
        $this->configureIO($input, $output);

        $count = $this->processDiscover();
        $this->ioStyle->title('Discover Extensions');
        $this->ioStyle->note($this->getNote($count));

        return Command::SUCCESS;
    }
}
