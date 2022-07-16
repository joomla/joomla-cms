<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Console;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Installation\Model\ChecksModel;
use Joomla\CMS\Installation\Model\DatabaseModel;
use Joomla\CMS\Installation\Model\SetupModel;
use Joomla\CMS\Installation\Application\CliInstallationApplication;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command for installing Joomla
 *
 * @since  __DEPLOY_VERSION__
 */
class InstallCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected static $defaultName = 'install';

    /**
     * @var  SymfonyStyle
     * @since  __DEPLOY_VERSION__
     */
    protected $ioStyle;

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

        $this->ioStyle->title('Install Joomla');

        /* @var CliInstallationApplication $app */
        $app = $this->getApplication();

        /** @var ChecksModel $checkModel */
        $checkModel = $app->getMVCFactory()->createModel('Checks', 'Installation');
        $this->ioStyle->write('Check system requirements...');

        if (!$checkModel->getPhpOptionsSufficient()) {
            $options = $checkModel->getPhpOptions();

            foreach ($options as $option) {
                if (!$option->state) {
                    $this->ioStyle->error($option->notice);

                    return Command::FAILURE;
                }
            }
        }

        $this->ioStyle->writeln('OK');

        // Collect the configuration
        $this->ioStyle->write('Collect configuration...');
        $cfg = $this->getCLIOptions();
        $cfg['db_pass_plain'] = $cfg['db_pass'];
        $cfg['admin_password_plain'] = $cfg['admin_password'];
        $this->ioStyle->writeln('OK');

        /** @var SetupModel $setupModel */
        $setupModel = $app->getMVCFactory()->createModel('Setup', 'Installation');

        // Validate DB connection
        $this->ioStyle->write('Validate DB connection...');
        $setupModel->storeOptions($cfg);
        $setupModel->validateDbConnection();
        $this->ioStyle->writeln('OK');
die;
        /** @var DatabaseModel $databaseModel */
        $databaseModel = $app->getMVCFactory()->createModel('Database', 'Installation');

        // Validate DB connection
        $databaseModel->createDatabase();
        $db = $databaseModel->initialise();

        $files = [
            'populate1' => 'base',
            'populate2' => 'supports',
            'populate3' => 'extensions',
            'custom1' => 'localise',
            'custom2' => 'custom'
        ];

        foreach ($files as $step => $schema) {
            $serverType = $db->getServerType();

            if (in_array($step, ['custom1', 'custom2']) && !is_file('sql/' . $serverType . '/' . $schema . '.sql')) {
                continue;
            }

            $databaseModel->createTables($schema);
        }

        /** @var \Joomla\CMS\Installation\Model\ConfigurationModel $configurationModel */
        $configurationModel = $app->getMVCFactory()->createModel('Configuration', 'Installation');

        // Attempt to setup the configuration.
        $configurationModel->setup($cfg);

        $this->ioStyle->success('Joomla has been successfully installed');

        return Command::SUCCESS;
    }

    protected function getCLIOptions()
    {
        /* @var CliInstallationApplication $app */
        $app = $this->getApplication();

        /* @var SetupModel $setupmodel */
        $setupmodel = $app->getMVCFactory()->createModel('Setup', 'Installation');
        $form       = $setupmodel->getForm('setup');
        $cfg        = [];

        foreach ($form->getFieldset() as $field) {
            if (\in_array($field->fieldname, ['language', 'db_old'])) {
                continue;
            }

            if ($field->showon) {
                $conditions = FormHelper::parseShowOnConditions($field->showon, $field->formControl, $field->group);
                $show       = false;

                foreach ($conditions as $cond) {
                    // remove jform[] from the name
                    $f    = rtrim(substr($cond['field'], 6), ']');
                    $temp = false;

                    if ($cond['sign'] == '=' && \in_array($cfg[$f], $cond['values'])) {
                        $temp = true;
                    } elseif ($cond['sign'] == '!=' && !\in_array($cfg[$f], $cond['values'])) {
                        $temp = true;
                    }

                    if ($cond['op'] == '' || $cond['op'] == 'OR') {
                        $show |= $temp;
                    } else {
                        $show &= $temp;
                    }
                }

                if ($show) {
                    $cfg[$field->fieldname] = $this->getStringFromOption(
                    	$field->fieldname,
                    	(string) $field->getAttribute('clilabel'),
                    	$field
                    );
                } else {
                    $cfg[$field->fieldname] = $field->filter($field->default);
                }
            } else {
                $cfg[$field->fieldname] = $field->filter($this->getStringFromOption(
                	$field->fieldname,
                	(string) $field->getAttribute('clilabel'),
                	$field
                ));
            }
        }

        return $cfg;
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
        $help = "<info>%command.name%</info> will install Joomla
		\nUsage: <info>php %command.full_name%</info>";

        /* @var CliInstallationApplication $app */
        $app = Factory::getApplication();

        /* @var SetupModel $setupmodel */
        $setupmodel = $app->getMVCFactory()->createModel('Setup', 'Installation');
        $form       = $setupmodel->getForm('setup');

        $this->setDescription('Install the Joomla CMS');

        foreach ($form->getFieldset() as $field) {
            if (\in_array($field->fieldname, ['language', 'db_old'])) {
                continue;
            }

            $this->addOption(
            	$field->fieldname,
            	null,
            	$field->required ? InputOption::VALUE_REQUIRED : InputOption::VALUE_OPTIONAL,
            	(string) $field->getAttribute('clilabel'),
            	$field->getAttribute('default')
            );
        }

        $this->setHelp($help);
    }

    /**
     * Method to get a value from option
     *
     * @param   string     $option    set the option name
     * @param   string     $question  set the question if user enters no value to option
     * @param   FormField  $field     Field to validate against
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception
     */
    protected function getStringFromOption($option, $question, $field): string
    {
        $answer = $this->getApplication()->getConsoleInput()->getOption($option);

        if (!\is_null($answer)) {
            $valid = $field->validate($answer);

            if ($valid instanceof \Exception) {
                throw new \Exception('Value for ' . $option . ' is wrong: ' . $valid->getMessage());
            }
        }

        while (\is_null($answer) || $answer === false) {
            $answer = $this->ioStyle->ask($question);

            $valid = $field->validate($answer);

            if ($valid instanceof \Exception) {
                $this->ioStyle->warning('Value for ' . $option . ' is wrong: ' . $valid->getMessage());
                $answer = false;
            }
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
     * @since   4.0.0
     */
    private function configureIO(InputInterface $input, OutputInterface $output)
    {
        $this->cliInput = $input;
        $this->ioStyle  = new SymfonyStyle($input, $output);
    }
}
