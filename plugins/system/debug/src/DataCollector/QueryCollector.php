<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug\DataCollector;

use Joomla\Database\DatabaseDriver;
use DebugBar\DataCollector\AssetProvider;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\Monitor\DebugMonitor;
use Joomla\Plugin\System\Debug\AbstractDataCollector;
use Joomla\Registry\Registry;

/**
 * QueryDataCollector
 *
 * @since  4.0.0
 */
class QueryCollector extends AbstractDataCollector implements AssetProvider
{
    /**
     * Collector name.
     *
     * @since 4.0.0
     */
    private string $name = 'queries';

    /**
     * Accumulated Duration.
     *
     * @since 4.0.0
     */
    private int $accumulatedDuration = 0;

    /**
     * Accumulated Memory.
     *
     * @since 4.0.0
     */
    private int $accumulatedMemory = 0;

    /**
     * Constructor.
     *
     * @param   Registry      $params        Parameters.
     * @param   DebugMonitor  $queryMonitor  Query monitor.
     * @param   array         $profiles      Profile data.
     * @param   array         $explains      Explain data
     *
     * @since 4.0.0
     */
    public function __construct(Registry $params, private readonly DebugMonitor $queryMonitor, private readonly array $profiles, private readonly array $explains)
    {
        parent::__construct($params);
    }

    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @since  4.0.0
     *
     * @return array Collected data
     */
    public function collect(): array
    {
        $statements = $this->getStatements();

        return [
            'data'       => [
                'statements'               => $statements,
                'nb_statements'            => \count($statements),
                'accumulated_duration_str' => $this->getDataFormatter()->formatDuration($this->accumulatedDuration),
                'memory_usage_str'         => $this->getDataFormatter()->formatBytes($this->accumulatedMemory),
                'xdebug_link'              => $this->getXdebugLinkTemplate(),
                'root_path'                => JPATH_ROOT,
            ],
            'count'      => \count($this->queryMonitor->getLogs()),
        ];
    }

    /**
     * Returns the unique name of the collector
     *
     * @since  4.0.0
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns a hash where keys are control names and their values
     * an array of options as defined in {@see \DebugBar\JavascriptRenderer::addControl()}
     *
     * @since  4.0.0
     */
    public function getWidgets(): array
    {
        return [
            'queries'       => [
                'icon'    => 'database',
                'widget'  => 'PhpDebugBar.Widgets.SQLQueriesWidget',
                'map'     => $this->name . '.data',
                'default' => '[]',
            ],
            'queries:badge' => [
                'map'     => $this->name . '.count',
                'default' => 'null',
            ],
        ];
    }

    /**
     * Assets for the collector.
     *
     * @since  4.0.0
     */
    public function getAssets(): array
    {
        return [
            'css' => Uri::root(true) . '/media/plg_system_debug/widgets/sqlqueries/widget.min.css',
            'js' => Uri::root(true) . '/media/plg_system_debug/widgets/sqlqueries/widget.min.js',
        ];
    }

    /**
     * Prepare the executed statements data.
     *
     * @since  4.0.0
     */
    private function getStatements(): array
    {
        $statements    = [];
        $logs          = $this->queryMonitor->getLogs();
        $boundParams   = $this->queryMonitor->getBoundParams();
        $timings       = $this->queryMonitor->getTimings();
        $memoryLogs    = $this->queryMonitor->getMemoryLogs();
        $stacks        = $this->queryMonitor->getCallStacks();
        $collectStacks = $this->params->get('query_traces');

        foreach ($logs as $id => $item) {
            $queryTime   = 0;
            $queryMemory = 0;

            if ($timings && isset($timings[$id * 2 + 1])) {
                // Compute the query time.
                $queryTime                 = ($timings[$id * 2 + 1] - $timings[$id * 2]);
                $this->accumulatedDuration += $queryTime;
            }

            if ($memoryLogs && isset($memoryLogs[$id * 2 + 1])) {
                // Compute the query memory usage.
                $queryMemory             = ($memoryLogs[$id * 2 + 1] - $memoryLogs[$id * 2]);
                $this->accumulatedMemory += $queryMemory;
            }

            $trace          = [];
            $callerLocation = '';

            if (isset($stacks[$id])) {
                $cnt = 0;

                foreach ($stacks[$id] as $i => $stack) {
                    $class = $stack['class'] ?? '';
                    $file  = $stack['file'] ?? '';
                    $line  = $stack['line'] ?? '';

                    $caller   = $this->formatCallerInfo($stack);
                    $location = $file && $line ? "$file:$line" : 'same';

                    $isCaller = 0;

                    if (DatabaseDriver::class === $class && !str_contains((string) $file, 'DatabaseDriver.php')) {
                        $callerLocation = $location;
                        $isCaller       = 1;
                    }

                    if ($collectStacks) {
                        $trace[] = [(is_countable($stacks[$id]) ? \count($stacks[$id]) : 0) - $cnt, $isCaller, $caller, $file, $line];
                    }

                    $cnt++;
                }
            }

            $explain        = $this->explains[$id] ?? [];
            $explainColumns = [];

            // Extract column labels for Explain table
            if ($explain) {
                $explainColumns = array_keys(reset($explain));
            }

            $statements[] = [
                'sql'          => $item,
                'params'       => $boundParams[$id] ?? [],
                'duration_str' => $this->getDataFormatter()->formatDuration($queryTime),
                'memory_str'   => $this->getDataFormatter()->formatBytes($queryMemory),
                'caller'       => $callerLocation,
                'callstack'    => $trace,
                'explain'      => $explain,
                'explain_col'  => $explainColumns,
                'profile'      => $this->profiles[$id] ?? [],
            ];
        }

        return $statements;
    }
}
