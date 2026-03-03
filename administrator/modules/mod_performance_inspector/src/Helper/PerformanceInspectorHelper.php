<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\PerformanceInspector\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Profiler\Profiler;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Performance Inspector Module Helper
 *
 * This helper class collects performance metrics from the Joomla application
 * including execution time, database queries, memory usage, and cache information.
 *
 * Data Collected:
 * - Total execution time (via JProfiler)
 * - Number of database queries and total time
 * - Peak memory usage (current and peak)
 * - Slowest executed query (with duration)
 * - Cache hit/miss statistics (if available)
 *
 * Performance Impact:
 * - Minimal overhead (data collection, no processing)
 * - Uses existing Joomla profiler data
 * - Does not add queries or cache operations
 * - Executes only if module is enabled
 *
 * @since  5.0.0
 */
class PerformanceInspectorHelper
{
    /**
     * Get all performance metrics
     *
     * Collects comprehensive performance data from the Joomla application.
     * Returns structured array with all available metrics.
     *
     * @param   \stdClass  $params  Module parameters
     *
     * @return  array Performance metrics array
     *
     * @since   5.0.0
     */
    public static function getPerformanceMetrics($params)
    {
        $metrics = [
            'execution_time'    => 0,
            'db_queries'        => 0,
            'db_query_time'     => 0,
            'peak_memory'       => 0,
            'current_memory'    => 0,
            'slowest_query'     => null,
            'cache_hits'        => 0,
            'cache_misses'      => 0,
            'timestamp'         => date('Y-m-d H:i:s'),
        ];

        // Get execution time from profiler
        if ($params->get('show_execution_time', 1)) {
            $metrics['execution_time'] = self::getExecutionTime();
        }

        // Get database query information
        if ($params->get('show_queries', 1)) {
            $queryData = self::getDatabaseQueryData();
            $metrics['db_queries'] = $queryData['count'];
            $metrics['db_query_time'] = $queryData['time'];
            $metrics['slowest_query'] = $queryData['slowest'];
        }

        // Get memory usage
        if ($params->get('show_memory', 1)) {
            $memoryData = self::getMemoryUsage();
            $metrics['peak_memory'] = $memoryData['peak'];
            $metrics['current_memory'] = $memoryData['current'];
        }

        // Get cache information (if available)
        if ($params->get('show_cache_info', 1)) {
            $cacheData = self::getCacheInfo();
            $metrics['cache_hits'] = $cacheData['hits'];
            $metrics['cache_misses'] = $cacheData['misses'];
        }

        return $metrics;
    }

    /**
     * Get total execution time from JProfiler
     *
     * Uses the Joomla Profiler singleton to retrieve the total
     * execution time since application bootstrap.
     *
     * @return  float Execution time in milliseconds
     *
     * @since   5.0.0
     */
    private static function getExecutionTime()
    {
        try {
            $profiler = Profiler::getInstance('Application');

            if ($profiler !== null) {
                // Get microtime at application start
                $marks = $profiler->getMarks();

                // Calculate time from start
                if (isset($marks[0])) {
                    $startMark = array_shift($marks);
                    $endMark = end($marks);

                    if (isset($startMark['time']) && isset($endMark['time'])) {
                        $executionTime = ($endMark['time'] - $startMark['time']) * 1000;
                        return round($executionTime, 3);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::add('Error getting execution time: ' . $e->getMessage(), Log::WARNING, 'mod_performance_inspector');
        }

        return 0;
    }

    /**
     * Get database query information
     *
     * Retrieves the number of queries executed, total query time,
     * and identifies the slowest query.
     *
     * @return  array Database query data
     *
     * @since   5.0.0
     */
    private static function getDatabaseQueryData()
    {
        $data = [
            'count' => 0,
            'time' => 0,
            'slowest' => null,
        ];

        try {
            /** @var DatabaseInterface $db */
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            // Get the query log (if debug mode is enabled)
            if (method_exists($db, 'getQueryLog')) {
                $queryLog = $db->getQueryLog();
                $data['count'] = count($queryLog);

                // Calculate total query time and find slowest query
                $slowestQuery = null;
                $slowestTime = 0;
                $totalTime = 0;

                foreach ($queryLog as $query) {
                    if (isset($query['time'])) {
                        $time = $query['time'];
                        $totalTime += $time;

                        // Track slowest query
                        if ($time > $slowestTime) {
                            $slowestTime = $time;
                            $slowestQuery = $query;
                        }
                    }
                }

                $data['time'] = round($totalTime, 3);

                // Format slowest query information
                if ($slowestQuery !== null) {
                    $data['slowest'] = self::formatQueryInfo($slowestQuery);
                }
            } elseif (method_exists($db, 'getNumQueries')) {
                // Fallback: Get query count if log is not available
                $data['count'] = $db->getNumQueries();
            }
        } catch (\Exception $e) {
            Log::add('Error getting database data: ' . $e->getMessage(), Log::WARNING, 'mod_performance_inspector');
        }

        return $data;
    }

    /**
     * Format query information for display
     *
     * Takes a query log entry and formats it for display in the module,
     * including query time, SQL snippet, and other relevant info.
     *
     * @param   array  $queryInfo  Query log entry
     *
     * @return  object Formatted query info
     *
     * @since   5.0.0
     */
    private static function formatQueryInfo($queryInfo)
    {
        $maxLength = (int) Factory::getApplication()->getModule('mod_performance_inspector')->params->get('max_query_length', 200);

        $formatted = new \stdClass();
        $formatted->time = isset($queryInfo['time']) ? round($queryInfo['time'], 3) : 0;

        // Get SQL snippet
        if (isset($queryInfo['sql'])) {
            $sql = str_replace("\n", ' ', $queryInfo['sql']);
            $formatted->sql = (strlen($sql) > $maxLength) ? substr($sql, 0, $maxLength) . '...' : $sql;
        } else {
            $formatted->sql = 'N/A';
        }

        // Get connection information
        if (isset($queryInfo['connection'])) {
            $formatted->connection = $queryInfo['connection'];
        }

        return $formatted;
    }

    /**
     * Get memory usage information
     *
     * Retrieves both current memory usage and peak memory usage
     * from PHP's memory management functions.
     *
     * @return  array Memory usage data (peak and current in MB)
     *
     * @since   5.0.0
     */
    private static function getMemoryUsage()
    {
        $data = [
            'peak' => 0,
            'current' => 0,
        ];

        try {
            // Get current memory usage in MB
            $data['current'] = round(memory_get_usage(true) / 1024 / 1024, 2);

            // Get peak memory usage in MB
            $data['peak'] = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
        } catch (\Exception $e) {
            Log::add('Error getting memory data: ' . $e->getMessage(), Log::WARNING, 'mod_performance_inspector');
        }

        return $data;
    }

    /**
     * Get cache hit/miss information
     *
     * Attempts to retrieve cache statistics from Joomla's cache system.
     * Data availability depends on the cache handler and debug mode.
     *
     * @return  array Cache statistics
     *
     * @since   5.0.0
     */
    private static function getCacheInfo()
    {
        $data = [
            'hits' => 0,
            'misses' => 0,
        ];

        try {
            // Try to get cache information from APCu if available
            if (function_exists('apcu_cache_info')) {
                $cacheInfo = apcu_cache_info();

                $data['hits'] = isset($cacheInfo['num_hits']) ? (int) $cacheInfo['num_hits'] : 0;
                $data['misses'] = isset($cacheInfo['num_misses']) ? (int) $cacheInfo['num_misses'] : 0;
            }
            // Try Redis cache info (if using Redis)
            elseif (extension_loaded('redis')) {
                // Note: Direct Redis info requires connection, so we skip it
                // Cache hits/misses would need to be tracked separately
            }
        } catch (\Exception $e) {
            Log::add('Error getting cache data: ' . $e->getMessage(), Log::WARNING, 'mod_performance_inspector');
        }

        return $data;
    }

    /**
     * Format bytes to human-readable format
     *
     * Converts byte values to appropriate units (B, KB, MB, GB)
     * for easier reading and comparison.
     *
     * @param   int     $bytes   Bytes to format
     * @param   integer $precision Decimal precision
     *
     * @return  string Formatted size
     *
     * @since   5.0.0
     */
    public static function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Format milliseconds to a readable format
     *
     * Converts millisecond values to seconds when appropriate
     * for better readability.
     *
     * @param   float   $milliseconds Milliseconds to format
     * @param   integer $precision    Decimal precision
     *
     * @return  string Formatted time
     *
     * @since   5.0.0
     */
    public static function formatTime($milliseconds, $precision = 3)
    {
        if ($milliseconds >= 1000) {
            return round($milliseconds / 1000, $precision) . ' s';
        }

        return round($milliseconds, $precision) . ' ms';
    }

    /**
     * Get color coding for performance metrics
     *
     * Returns CSS class name for color-coding performance metrics.
     * Helps visualize whether metrics are good, acceptable, or concerning.
     *
     * @param   string  $metric  Metric type (execution_time, memory, query_time)
     * @param   float   $value   Metric value
     *
     * @return  string CSS class name for color coding
     *
     * @since   5.0.0
     */
    public static function getPerformanceClass($metric, $value)
    {
        switch ($metric) {
            case 'execution_time':
                // Execution time thresholds (in ms)
                if ($value < 500) {
                    return 'performance-good';
                } elseif ($value < 1000) {
                    return 'performance-acceptable';
                } else {
                    return 'performance-concerning';
                }

            case 'memory':
                // Memory thresholds (in MB)
                if ($value < 32) {
                    return 'performance-good';
                } elseif ($value < 64) {
                    return 'performance-acceptable';
                } else {
                    return 'performance-concerning';
                }

            case 'query_time':
                // Query time thresholds (in ms)
                if ($value < 100) {
                    return 'performance-good';
                } elseif ($value < 300) {
                    return 'performance-acceptable';
                } else {
                    return 'performance-concerning';
                }

            default:
                return '';
        }
    }

    /**
     * Check if Joomla debug mode is enabled
     *
     * Verifies if debug mode is active in Joomla configuration
     * to determine if detailed query logs are available.
     *
     * @return  boolean True if debug mode is enabled
     *
     * @since   5.0.0
     */
    public static function isDebugMode()
    {
        $config = Factory::getApplication()->getConfig();
        return (bool) $config->get('debug', 0);
    }

    /**
     * Get diagnostic information
     *
     * Gathers system and Joomla diagnostic info for troubleshooting
     * module functionality.
     *
     * @return  array Diagnostic data
     *
     * @since   5.0.0
     */
    public static function getDiagnostics()
    {
        $diagnostics = [
            'php_version' => phpversion(),
            'joomla_version' => defined('JVERSION') ? JVERSION : 'Unknown',
            'debug_mode' => self::isDebugMode(),
            'database_driver' => Factory::getContainer()->get(DatabaseInterface::class)->getDriver(),
            'cache_handler' => Factory::getApplication()->getConfig()->get('cache_handler', 'N/A'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
        ];

        return $diagnostics;
    }
}
