<?php

/**
 * @package    Joomla.Build
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// We are a valid entry point.
const _JEXEC = 1;

// Import namespaced classes
use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Version;
use Joomla\Console\Application;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Mediawiki\Http;
use Joomla\Mediawiki\Mediawiki;
use Joomla\Registry\Registry;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Define the application's minimum supported PHP version as a constant so it can be referenced within the application.
 */
const JOOMLA_MINIMUM_PHP = '8.1.0';

if (!\defined('_JDEFINES')) {
    \define('JPATH_BASE', \dirname(__DIR__));
    require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_BASE . '/includes/framework.php';

$command = new class () extends AbstractCommand {
    /**
     * The default command name
     *
     * @var  string
     */
    protected static $defaultName = 'build-help-toc';

    /**
     * Initialise the command.
     *
     * @return  void
     */
    protected function configure(): void
    {
        $this->setDescription('Generates the help system table of contents file');
    }

    /**
     * Internal function to execute the command.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  integer  The command exit code
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!class_exists(Http::class)) {
            $io->error(
                'The `joomla/mediawiki` package is not installed. To use this script, you must run `composer install` to install development'
                . ' dependencies not tracked in this repo.'
            );

            return 1;
        }

        // Set up HTTP driver for MediaWiki
        $http = new Http([], HttpFactory::getAvailableDriver());

        // Set up options for the Mediawiki class
        $options = new Registry();
        $options->set('api.url', 'https://docs.joomla.org');

        $mediawiki = new Mediawiki($options, $http);

        $io->comment('Fetching data from docs wiki');

        $cleanMembers = [];

        $io->comment(\sprintf('Loop through docs wiki categories since Joomla Version %d.0 - Takes a while ...', Version::MAJOR_VERSION));

        // Loop through the Versions since 5.0 to get all HelpTocs - Temporary fix
        for ($helpScreenMinor=Version::MINOR_VERSION; $helpScreenMinor >= 0; $helpScreenMinor--) {

            // Get the category members (local hack)
            $categoryMembers = $mediawiki->categories->getCategoryMembers(
                \sprintf('Category:Help_screen_%s.%s', Version::MAJOR_VERSION, $helpScreenMinor),
                null,
                'max'
            );

            $members = [];

            // Loop through the result objects to get every document
            foreach ($categoryMembers->query->categorymembers as $catmembers) {
                foreach ($catmembers as $member) {
                    $members[] = (string) $member['title'];
                }
            }

            /*
            * Now we start fancy processing so we can get the language key for the titles
            */

            // Strip the namespace prefix off the titles and replace spaces with underscores
            $namespace = \sprintf('Help%d.x:', Version::MAJOR_VERSION);

            foreach ($members as $member) {
                $cleanMembers[str_replace([$namespace, ' '], ['', '_'], $member)] = trim(str_replace($namespace, ' ', $member));
            }
        }

        // Make sure we only have an array of unique values before continuing

        $cleanMembers = array_unique($cleanMembers);

        // Get the language object
        $language = Factory::getLanguage();

        // Load the admin com_admin language file
        $language->load('com_admin', JPATH_ADMINISTRATOR);

        $toc     = [];
        $missing = [];

        // filter for translated Media-Wiki articles
        $translationLanguages = ['/de', '/en', '/fr', '/nl', '/pt-br', '/es', '/pt', '/it'];

        foreach ($cleanMembers as $key => $value) {
            $string = strtoupper($key);

            // Validate the key exists
            $io->comment(\sprintf('Validating key COM_ADMIN_HELP_%s', $string));

            if ($language->hasKey('COM_ADMIN_HELP_' . $string)) {
                $io->comment(\sprintf('Adding %s', $string));

                $toc[$key] = $string;
            } else {
                // We check the string for words in singular/plural form and check again
                $io->comment(\sprintf('Inflecting %s', $string));

                $inflected = '';

                if (strpos($string, '_CATEGORIES') !== false) {
                    $inflected = str_replace('_CATEGORIES', '_CATEGORY', $string);
                } elseif (strpos($string, '_USERS') !== false) {
                    $inflected = str_replace('_USERS', '_USER', $string);
                } elseif (strpos($string, '_CATEGORY') !== false) {
                    $inflected = str_replace('_CATEGORY', '_CATEGORIES', $string);
                } elseif (strpos($string, '_USER') !== false) {
                    $inflected = str_replace('_USER', '_USERS', $string);
                }

                if ($inflected === '' && !\in_array(substr($value, strrpos($value, '/')), $translationLanguages)) {
                    $missing[$string] = $value;
                }

                // Now try to validate the key
                if ($inflected !== '') {
                    $io->comment(\sprintf('Validating key COM_ADMIN_HELP_%s', $inflected));

                    if ($language->hasKey('COM_ADMIN_HELP_' . $inflected)) {
                        $io->comment(\sprintf('Adding %s', $inflected));

                        $toc[$key] = $inflected;
                    }
                }
            }
        }

        $io->comment(\sprintf('Number of strings: %d', \count($toc)));

        // JSON encode the file and write it to JPATH_ADMINISTRATOR/help/en-GB/toc.json
        file_put_contents(JPATH_ADMINISTRATOR . '/help/en-GB/toc.json', json_encode($toc));

        if (\count($missing)) {

            $str_missing = '';

            foreach ($missing as $string => $value) {
                $str_missing .= 'COM_ADMIN_HELP_' . $string . '="' . $value . '"'. PHP_EOL;
            }

            // write missing strings to JPATH_BASE/tmp/missing-helptoc.txt
            file_put_contents(JPATH_BASE . '/tmp/missing-helptoc.txt', $str_missing);

            $io->caution(\sprintf('Number of media-wiki articles without string: %d', \count($missing)));

            $io->note(\sprintf('Missing strings are saved in: %s and should be revised and added to %s', 'tmp/missing-helptoc.txt', 'administrator/language/en-GB/com_admin.ini'));

            $io->caution('TODO: For a complete TOC, please run this script again after adding the missing language strings!');

        }

        $io->success('Help Screen TOC written');

        return 0;
    }
};

$input = new ArrayInput(
    [
        'command' => $command::getDefaultName(),
    ]
);

$app = new class ($input) extends Application {
    /**
     * Retrieve the application configuration object.
     *
     * @return  Registry
     */
    public function getConfig()
    {
        return $this->config;
    }
};
$app->addCommand($command);

// Register the application to the factory
Factory::$application = $app;

$app->execute();
