<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Console;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Installation\Application\CliInstallationApplication;
use Joomla\CMS\Installation\Model\ChecksModel;
use Joomla\CMS\Installation\Model\CleanupModel;
use Joomla\CMS\Installation\Model\DatabaseModel;
use Joomla\CMS\Installation\Model\SetupModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command for installing Joomla
 *
 * @since  4.3.0
 */
class InstallCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  4.3.0
     */
    protected static $defaultName = 'install';

    /**
     * @var  SymfonyStyle
     * @since  4.3.0
     */
    protected $ioStyle;

    /**
     * @var  InputInterface
     * @since  4.3.0
     */
    protected $cliInput;

    /**
     * Internal function to execute the command.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  integer  The command exit code
     *
     * @since   4.3.0
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $this->cliInput = $input;
        $this->ioStyle  = new SymfonyStyle($input, $output);

        $this->ioStyle->title('Install Joomla');

        if (file_exists(JPATH_ROOT . '/configuration.php')) {
            $this->ioStyle->error('configuration.php already present! Nothing to install, exiting.');
            return Command::FAILURE;
        }

        /* @var CliInstallationApplication $app */
        $app = $this->getApplication();

        /** @var ChecksModel $checkModel */
        $checkModel = $app->getMVCFactory()->createModel('Checks', 'Installation');
        $this->ioStyle->write('Checking system requirements...');

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
        $this->ioStyle->write('Collecting configuration...');
        $cfg                         = $this->getCLIOptions();
        $cfg['db_pass_plain']        = $cfg['db_pass'];
        $cfg['admin_password_plain'] = $cfg['admin_password'];
        $cfg['language']             = 'en-GB';
        $cfg['helpurl']              = 'https://help.joomla.org/proxy?keyref=Help{major}{minor}:{keyref}&lang={langcode}';
        $this->ioStyle->writeln('OK');

        /** @var SetupModel $setupModel */
        $setupModel = $app->getMVCFactory()->createModel('Setup', 'Installation');

        // Validate DB connection
        $this->ioStyle->write('Validating DB connection...');

        try {
            $setupModel->storeOptions($cfg);
            $setupModel->validateDbConnection();
        } catch (\Exception $e) {
            $this->ioStyle->error($e->getMessage());

            return Command::FAILURE;
        }
        $this->ioStyle->writeln('OK');

        /** @var DatabaseModel $databaseModel */
        $databaseModel = $app->getMVCFactory()->createModel('Database', 'Installation');

        // Create and populate database
        $this->ioStyle->write('Creating and populating the database...');
        $databaseModel->createDatabase();
        $db = $databaseModel->initialise();

        // Set the character set to UTF-8 for pre-existing databases.
        try {
            $db->alterDbCharacterSet($cfg['db_name']);
        } catch (\RuntimeException $e) {
            // Continue Anyhow
        }

        // Backup any old database.
        if (!$databaseModel->backupDatabase($db, $cfg['db_prefix'])) {
            return Command::FAILURE;
        }

        $files = [
            'populate1' => 'base',
            'populate2' => 'supports',
            'populate3' => 'extensions',
            'custom1'   => 'localise',
            'custom2'   => 'custom',
        ];

        foreach ($files as $step => $schema) {
            $serverType = $db->getServerType();

            if (\in_array($step, ['custom1', 'custom2']) && !is_file('sql/' . $serverType . '/' . $schema . '.sql')) {
                continue;
            }

            $databaseModel->createTables($schema);
        }

        $this->ioStyle->writeln('OK');

        /** @var \Joomla\CMS\Installation\Model\ConfigurationModel $configurationModel */
        $configurationModel = $app->getMVCFactory()->createModel('Configuration', 'Installation');

        // Attempt to setup the configuration.
        $this->ioStyle->write('Writing configuration.php and additional setup ...');
        $configurationModel->setup($cfg);
        $this->ioStyle->writeln('OK');

        if (!(new Version())->isInDevelopmentState()) {
            $this->ioStyle->write('Deleting /installation folder...');

            /** @var CleanupModel $cleanupModel */
            $cleanupModel = $app->getMVCFactory()->createModel('Cleanup', 'Installation');

            if (!$cleanupModel->deleteInstallationFolder()) {
                return Command::FAILURE;
            }

            $this->ioStyle->writeln('OK');
        }

        $this->ioStyle->success('Joomla has been installed');

        return Command::SUCCESS;
    }

    /**
     * Retrieve all necessary options either from CLI options
     * or from interactive mode.
     *
     * @return  array  Array of configuration options
     *
     * @throws  \Exception
     * @since   4.3.0
     */
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
                        str_replace('_', '-', $field->fieldname),
                        Text::_((string)$field->getAttribute('label')),
                        $field
                    );
                } else {
                    $cfg[$field->fieldname] = $field->filter($field->default);
                }
            } else {
                $cfg[$field->fieldname] = $field->filter(
                    $this->getStringFromOption(
                        str_replace('_', '-', $field->fieldname),
                        Text::_((string)$field->getAttribute('label')),
                        $field
                    )
                );
            }
        }

        return $cfg;
    }

    /**
     * Configure the command.
     *
     * @return  void
     *
     * @since   4.3.0
     */
    protected function configure(): void
    {
        /* @var CliInstallationApplication $app */
        $app = Factory::getApplication();

        $app->getLanguage()->load('joomla.cli');
        $help = "<info>%command.name%</info> will install Joomla
		\nUsage: <info>php %command.full_name%</info>";

        /* @var SetupModel $setupmodel */
        $setupmodel = $app->getMVCFactory()->createModel('Setup', 'Installation');
        $form       = $setupmodel->getForm('setup');

        $this->setDescription('Install the Joomla CMS');

        foreach ($form->getFieldset() as $field) {
            if (\in_array($field->fieldname, ['language', 'db_old'])) {
                continue;
            }

            $default = $field->getAttribute('default');

            if ($field->fieldname == 'db_prefix') {
                // Create the random prefix.
                $prefix  = '';
                $size    = 5;
                $chars   = range('a', 'z');
                $numbers = range(0, 9);

                // We want the fist character to be a random letter.
                shuffle($chars);
                $prefix .= $chars[0];

                // Next we combine the numbers and characters to get the other characters.
                $symbols = array_merge($numbers, $chars);
                shuffle($symbols);

                for ($i = 0, $j = $size - 1; $i < $j; ++$i) {
                    $prefix .= $symbols[$i];
                }

                // Add in the underscore.
                $prefix .= '_';
                $default = $prefix;
            }

            $this->addOption(
                str_replace('_', '-', $field->fieldname),
                null,
                $field->required ? InputOption::VALUE_REQUIRED : InputOption::VALUE_OPTIONAL,
                Text::_(((string)$field->getAttribute('label')) . '_SHORT'),
                $default
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
     * @throws  \Exception
     * @since   4.3.0
     */
    protected function getStringFromOption($option, $question, FormField $field): string
    {
        // The symfony console unfortunately does not allow to check for parameters given by CLI without the defaults
        $givenOption = false;
        $answer      = null;

        foreach ($_SERVER['argv'] as $arg) {
            if ($arg == '--' . $option || strpos($arg, $option . '=')) {
                $givenOption = true;
            }
        }

        // If an option is given via CLI, we validate that value and return it.
        if ($givenOption || !$this->cliInput->isInteractive()) {
            $answer = $this->getApplication()->getConsoleInput()->getOption($option);

            if (!is_string($answer)) {
                throw new \Exception($option . ' has been declared, but has not been given!');
            }

            $valid  = $field->validate($answer);

            if ($valid instanceof \Exception) {
                throw new \Exception('Value for ' . $option . ' is wrong: ' . $valid->getMessage());
            }

            return (string) $answer;
        }

        // We don't have a CLI option and now interactively get that from the user.
        while (\is_null($answer) || $answer === false) {
            if (in_array($option, ['admin-password', 'db-pass'])) {
                $answer = $this->ioStyle->askHidden($question);
            } else {
                $answer = $this->ioStyle->ask(
                    $question,
                    $this->getApplication()->getConsoleInput()->getOption($option)
                );
            }

            $valid = $field->validate($answer);

            if ($valid instanceof \Exception) {
                $this->ioStyle->warning('Value for ' . $option . ' is incorrect: ' . $valid->getMessage());
                $answer = false;
            }

            if ($option == 'db-pass' && $valid && $answer == null) {
                return '';
            }
        }

        return $answer;
    }
}
