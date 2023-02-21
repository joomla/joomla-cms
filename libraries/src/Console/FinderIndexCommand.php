<?php

/**
 * Joomla! CLI
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command Purges and rebuilds the index (search filters are preserved)
 *
 * @since  4.0.0
 */
class FinderIndexCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'finder:index';

    /**
     * Stores the Input Object
     *
     * @var    InputInterface
     * @since  4.0.0
     */
    private $cliInput;

    /**
     * SymfonyStyle Object
     *
     * @var    SymfonyStyle
     * @since  4.0.0
     */
    private $ioStyle;

    /**
     * Database connector
     *
     * @var    DatabaseInterface
     * @since  4.0.0
     */
    private $db;

    /**
     * Start time for the index process
     *
     * @var    string
     * @since  2.5
     */
    private $time;

    /**
     * Start time for each batch
     *
     * @var    string
     * @since  2.5
     */
    private $qtime;

    /**
     * Static filters information.
     *
     * @var    array
     * @since  3.3
     */
    private $filters = [];

    /**
     * Pausing type or defined pause time in seconds.
     * One pausing type is implemented: 'division' for dynamic calculation of pauses
     *
     * Defaults to 'division'
     *
     * @var    string|integer
     * @since  3.9.12
     */
    private $pause = 'division';

    /**
     * The divisor of the division: batch-processing time / divisor.
     * This is used together with --pause=division in order to pause dynamically
     * in relation to the processing time
     * Defaults to 5
     *
     * @var    integer
     * @since  3.9.12
     */
    private $divisor = 5;

    /**
     * Minimum processing time in seconds, in order to apply a pause
     * Defaults to 1
     *
     * @var    integer
     * @since  3.9.12
     */
    private $minimumBatchProcessingTime = 1;

    /**
     * Instantiate the command.
     *
     * @param   DatabaseInterface  $db  Database connector
     *
     * @since   4.0.0
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
     * @since   4.0.0
     */
    protected function configure(): void
    {
        $this->addArgument('purge', InputArgument::OPTIONAL, 'Purge the index and rebuilds');
        $this->addOption('minproctime', null, InputOption::VALUE_REQUIRED, 'Minimum processing time in seconds, in order to apply a pause', 1);
        $this->addOption('pause', null, InputOption::VALUE_REQUIRED, 'Pausing type or defined pause time in seconds', 'division');
        $this->addOption('divisor', null, InputOption::VALUE_REQUIRED, 'The divisor of the division: batch-processing time / divisor', 5);
        $help = <<<'EOF'
The <info>%command.name%</info> Purges and rebuilds the index (search filters are preserved).

  <info>php %command.full_name%</info>
EOF;
        $this->setDescription('Purges and rebuild the index');
        $this->setHelp($help);
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

        // Initialize the time value.
        $this->time = microtime(true);
        $this->configureIO($input, $output);

        $this->ioStyle->writeln(
            [
            '<info>Finder Indexer</>',
            '<info>==========================</>',
            '',
            ]
        );

        if ($this->cliInput->getOption('minproctime')) {
            $this->minimumBatchProcessingTime = $this->cliInput->getOption('minproctime');
        }

        if ($this->cliInput->getOption('pause')) {
            $this->pause = $this->cliInput->getOption('pause');
        }

        if ($this->cliInput->getOption('divisor')) {
            $this->divisor = $this->cliInput->getOption('divisor');
        }

        if ($this->cliInput->getArgument('purge')) {
            // Taxonomy ids will change following a purge/index, so save filter information first.
            $this->getFilters();

            // Purge the index.
            $this->purge();

            // Run the indexer.
            $this->index();

            // Restore the filters again.
            $this->putFilters();
        } else {
            $this->index();
        }

        $this->ioStyle->newLine(1);

        // Total reporting.
        $this->ioStyle->writeln(
            [
            '<info>' . Text::sprintf('FINDER_CLI_PROCESS_COMPLETE', round(microtime(true) - $this->time, 3)) . '</>',
            '<info>' . Text::sprintf('FINDER_CLI_PEAK_MEMORY_USAGE', number_format(memory_get_peak_usage(true))) . '</>',
            ]
        );

        $this->ioStyle->newLine(1);

        return Command::SUCCESS;
    }

    /**
     * Configures the IO
     *
     * @param   InputInterface   $input   Console Input
     * @param   OutputInterface  $output  Console Output
     *
     * @return void
     *
     * @since 4.0.0
     *
     */
    private function configureIO(InputInterface $input, OutputInterface $output): void
    {
        $this->cliInput = $input;
        $this->ioStyle  = new SymfonyStyle($input, $output);
        $language       = Factory::getLanguage();
        $language->load('', JPATH_ADMINISTRATOR, null, false, false) ||
        $language->load('', JPATH_ADMINISTRATOR, null, true);
        $language->load('finder_cli', JPATH_SITE, null, false, false) ||
        $language->load('finder_cli', JPATH_SITE, null, true);
    }

    /**
     * Save static filters.
     *
     * Since a purge/index cycle will cause all the taxonomy ids to change,
     * the static filters need to be updated with the new taxonomy ids.
     * The static filter information is saved prior to the purge/index
     * so that it can later be used to update the filters with new ids.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function getFilters(): void
    {
        $this->ioStyle->text(Text::_('FINDER_CLI_SAVE_FILTERS'));

        // Get the taxonomy ids used by the filters.
        $db    = $this->db;
        $query = $db->getQuery(true);
        $query
            ->select('filter_id, title, data')
            ->from($db->quoteName('#__finder_filters'));
        $filters = $db->setQuery($query)->loadObjectList();

        // Get the name of each taxonomy and the name of its parent.
        foreach ($filters as $filter) {
            // Skip empty filters.
            if ($filter->data === '') {
                continue;
            }

            // Get taxonomy records.
            $query = $db->getQuery(true);
            $query
                ->select('t.title, p.title AS parent')
                ->from($db->quoteName('#__finder_taxonomy') . ' AS t')
                ->leftJoin($db->quoteName('#__finder_taxonomy') . ' AS p ON p.id = t.parent_id')
                ->where($db->quoteName('t.id') . ' IN (' . $filter->data . ')');
            $taxonomies = $db->setQuery($query)->loadObjectList();

            // Construct a temporary data structure to hold the filter information.
            foreach ($taxonomies as $taxonomy) {
                $this->filters[$filter->filter_id][] = [
                    'filter' => $filter->title,
                    'title'  => $taxonomy->title,
                    'parent' => $taxonomy->parent,
                ];
            }
        }

        $this->ioStyle->text(Text::sprintf('FINDER_CLI_SAVE_FILTER_COMPLETED', count($filters)));
    }

    /**
     * Purge the index.
     *
     * @return  void
     *
     * @since   3.3
     */
    private function purge()
    {
        $this->ioStyle->text(Text::_('FINDER_CLI_INDEX_PURGE'));

        // Load the model.
        $app   = $this->getApplication();
        $model = $app->bootComponent('com_finder')->getMVCFactory($app)->createModel('Index', 'Administrator');

        // Attempt to purge the index.
        $return = $model->purge();

        // If unsuccessful then abort.
        if (!$return) {
            $message = Text::_('FINDER_CLI_INDEX_PURGE_FAILED', $model->getError());
            $this->ioStyle->error($message);
            exit();
        }

        $this->ioStyle->text(Text::_('FINDER_CLI_INDEX_PURGE_SUCCESS'));
    }

    /**
     * Run the indexer.
     *
     * @return  void
     *
     * @since   2.5
     */
    private function index()
    {

        // Disable caching.
        $app = $this->getApplication();
        $app->set('caching', 0);
        $app->set('cache_handler', 'file');

        // Reset the indexer state.
        Indexer::resetState();

        // Import the plugins.
        PluginHelper::importPlugin('system');
        PluginHelper::importPlugin('finder');

        // Starting Indexer.
        $this->ioStyle->text(Text::_('FINDER_CLI_STARTING_INDEXER'));

        // Trigger the onStartIndex event.
        $app->triggerEvent('onStartIndex');

        // Remove the script time limit.
        @set_time_limit(0);

        // Get the indexer state.
        $state = Indexer::getState();

        // Setting up plugins.
        $this->ioStyle->text(Text::_('FINDER_CLI_SETTING_UP_PLUGINS'));

        // Trigger the onBeforeIndex event.
        $app->triggerEvent('onBeforeIndex');

        // Startup reporting.
        $this->ioStyle->text(Text::sprintf('FINDER_CLI_SETUP_ITEMS', $state->totalItems, round(microtime(true) - $this->time, 3)));

        // Get the number of batches.
        $t = (int) $state->totalItems;
        $c = (int) ceil($t / $state->batchSize);
        $c = $c === 0 ? 1 : $c;

        try {
            // Process the batches.
            for ($i = 0; $i < $c; $i++) {
                // Set the batch start time.
                $this->qtime = microtime(true);

                // Reset the batch offset.
                $state->batchOffset = 0;

                // Trigger the onBuildIndex event.
                Factory::getApplication()->triggerEvent('onBuildIndex');

                // Batch reporting.
                $text = Text::sprintf('FINDER_CLI_BATCH_COMPLETE', $i + 1, $processingTime = round(microtime(true) - $this->qtime, 3));
                $this->ioStyle->text($text);

                if ($this->pause !== 0) {
                    // Pausing Section
                    $skip  = !($processingTime >= $this->minimumBatchProcessingTime);
                    $pause = 0;

                    if ($this->pause === 'division' && $this->divisor > 0) {
                        if (!$skip) {
                            $pause = round($processingTime / $this->divisor);
                        } else {
                            $pause = 1;
                        }
                    } elseif ($this->pause > 0) {
                        $pause = $this->pause;
                    }

                    if ($pause > 0 && !$skip) {
                        $this->ioStyle->text(Text::sprintf('FINDER_CLI_BATCH_PAUSING', $pause));
                        sleep($pause);
                        $this->ioStyle->text(Text::_('FINDER_CLI_BATCH_CONTINUING'));
                    }

                    if ($skip) {
                        $this->ioStyle->text(
                            Text::sprintf(
                                'FINDER_CLI_SKIPPING_PAUSE_LOW_BATCH_PROCESSING_TIME',
                                $processingTime,
                                $this->minimumBatchProcessingTime
                            )
                        );
                    }

                    // End of Pausing Section
                }
            }
        } catch (Exception $e) {
            // Display the error
            $this->ioStyle->error($e->getMessage());

            // Reset the indexer state.
            Indexer::resetState();

            // Close the app
            $app->close($e->getCode());
        }

        // Reset the indexer state.
        Indexer::resetState();
    }

    /**
     * Restore static filters.
     *
     * Using the saved filter information, update the filter records
     * with the new taxonomy ids.
     *
     * @return  void
     *
     * @since   3.3
     */
    private function putFilters()
    {
        $this->ioStyle->text(Text::_('FINDER_CLI_RESTORE_FILTERS'));

        $db = $this->db;

        // Use the temporary filter information to update the filter taxonomy ids.
        foreach ($this->filters as $filter_id => $filter) {
            $tids = [];

            foreach ($filter as $element) {
                // Look for the old taxonomy in the new taxonomy table.
                $query = $db->getQuery(true);
                $query
                    ->select('t.id')
                    ->from($db->quoteName('#__finder_taxonomy') . ' AS t')
                    ->leftJoin($db->quoteName('#__finder_taxonomy') . ' AS p ON p.id = t.parent_id')
                    ->where($db->quoteName('t.title') . ' = ' . $db->quote($element['title']))
                    ->where($db->quoteName('p.title') . ' = ' . $db->quote($element['parent']));
                $taxonomy = $db->setQuery($query)->loadResult();

                // If we found it then add it to the list.
                if ($taxonomy) {
                    $tids[] = $taxonomy;
                } else {
                    $text = Text::sprintf('FINDER_CLI_FILTER_RESTORE_WARNING', $element['parent'], $element['title'], $element['filter']);
                    $this->ioStyle->text($text);
                }
            }

            // Construct a comma-separated string from the taxonomy ids.
            $taxonomyIds = empty($tids) ? '' : implode(',', $tids);

            // Update the filter with the new taxonomy ids.
            $query = $db->getQuery(true);
            $query
                ->update($db->quoteName('#__finder_filters'))
                ->set($db->quoteName('data') . ' = ' . $db->quote($taxonomyIds))
                ->where($db->quoteName('filter_id') . ' = ' . (int) $filter_id);
            $db->setQuery($query)->execute();
        }

        $this->ioStyle->text(Text::sprintf('FINDER_CLI_RESTORE_FILTER_COMPLETED', count($this->filters)));
    }
}
