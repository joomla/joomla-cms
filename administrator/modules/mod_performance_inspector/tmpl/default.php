<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\Module\PerformanceInspector\Administrator\Helper\PerformanceInspectorHelper;
use Joomla\CMS\HTML\HTMLHelper;

// Check that required variables are available
if (!isset($metrics) || !isset($module)) {
    return;
}
?>
<div class="performance-inspector" style="
    position: fixed;
    <?php echo $positionX; ?>: 20px;
    <?php echo $positionY; ?>: 80px;
    width: 420px;
    background: #f5f5f5;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    z-index: 9999;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    font-size: 12px;
    color: #333;
">
    <!-- Header -->
    <div style="
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        padding: 12px;
        border-radius: 4px 4px 0 0;
        border-bottom: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
    ">
        <div style="font-weight: bold; font-size: 13px;">
            📊 Performance Inspector
        </div>
        <div style="font-size: 11px; opacity: 0.8;">
            <?php echo $metrics['timestamp']; ?>
        </div>
    </div>

    <!-- Content -->
    <div style="padding: 15px; max-height: 600px; overflow-y: auto;">

        <!-- Execution Time -->
        <?php if ($showExecutionTime) : ?>
            <div style="margin-bottom: 15px; padding-bottom: 12px; border-bottom: 1px solid #e0e0e0;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 500; color: #666;">⏱️ Execution Time:</span>
                    <span class="<?php echo PerformanceInspectorHelper::getPerformanceClass('execution_time', $metrics['execution_time']); ?>"
                        style="
                        font-weight: bold;
                        padding: 2px 8px;
                        border-radius: 3px;
                        font-size: 11px;
                    ">
                        <?php echo PerformanceInspectorHelper::formatTime($metrics['execution_time']); ?>
                    </span>
                </div>
                <div style="font-size: 11px; color: #999; margin-top: 4px;">
                    Total time from application boot
                </div>
            </div>
        <?php endif; ?>

        <!-- Database Queries -->
        <?php if ($showQueries) : ?>
            <div style="margin-bottom: 15px; padding-bottom: 12px; border-bottom: 1px solid #e0e0e0;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 500; color: #666;">🗂️ Database Queries:</span>
                    <span class="performance-info" style="
                        background: #e3f2fd;
                        color: #1976d2;
                        font-weight: bold;
                        padding: 2px 8px;
                        border-radius: 3px;
                        font-size: 11px;
                    ">
                        <?php echo $metrics['db_queries']; ?> queries
                    </span>
                </div>

                <?php if ($metrics['db_query_time'] > 0) : ?>
                    <div style="display: flex; justify-content: space-between; margin-top: 6px; padding: 6px 0; font-size: 11px; color: #666;">
                        <span>Query Time:</span>
                        <span class="<?php echo PerformanceInspectorHelper::getPerformanceClass('query_time', $metrics['db_query_time']); ?>"
                            style="font-weight: bold;">
                            <?php echo PerformanceInspectorHelper::formatTime($metrics['db_query_time']); ?>
                        </span>
                    </div>
                <?php endif; ?>

                <!-- Slowest Query -->
                <?php if ($showSlowestQuery && $metrics['slowest_query'] !== null) : ?>
                    <div style="
                        background: #fff3cd;
                        border-left: 3px solid #ffc107;
                        padding: 8px;
                        margin-top: 8px;
                        border-radius: 2px;
                    ">
                        <div style="font-weight: 500; color: #856404; margin-bottom: 4px;">
                            🐢 Slowest Query:
                        </div>
                        <div style="
                            background: #fff;
                            padding: 6px;
                            border-radius: 2px;
                            font-family: 'Courier New', monospace;
                            font-size: 10px;
                            color: #d32f2f;
                            word-break: break-word;
                            max-height: 80px;
                            overflow: hidden;
                        ">
                            <?php echo htmlspecialchars($metrics['slowest_query']->sql); ?>
                        </div>
                        <div style="margin-top: 4px; color: #856404; font-size: 10px;">
                            Duration: <strong><?php echo $metrics['slowest_query']->time; ?> ms</strong>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Memory Usage -->
        <?php if ($showMemory) : ?>
            <div style="margin-bottom: 15px; padding-bottom: 12px; border-bottom: 1px solid #e0e0e0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="font-weight: 500; color: #666;">💾 Memory Usage:</span>
                    <span class="<?php echo PerformanceInspectorHelper::getPerformanceClass('memory', $metrics['current_memory']); ?>"
                        style="
                        font-weight: bold;
                        padding: 2px 8px;
                        border-radius: 3px;
                        font-size: 11px;
                    ">
                        <?php echo PerformanceInspectorHelper::formatBytes($metrics['current_memory'] * 1024 * 1024, 2); ?>
                    </span>
                </div>

                <!-- Memory Progress Bar -->
                <?php
                $memoryPercent = min(100, ($metrics['current_memory'] / 128) * 100);
                $progressColor = $memoryPercent < 50 ? '#4caf50' : ($memoryPercent < 80 ? '#ff9800' : '#f44336');
                ?>
                <div style="
                    background: #e0e0e0;
                    height: 6px;
                    border-radius: 3px;
                    overflow: hidden;
                    margin: 6px 0;
                ">
                    <div style="
                        background: <?php echo $progressColor; ?>;
                        height: 100%;
                        width: <?php echo $memoryPercent; ?>%;
                        transition: width 0.3s ease;
                    "></div>
                </div>

                <div style="display: flex; justify-content: space-between; font-size: 10px; color: #999;">
                    <span>Current: <?php echo PerformanceInspectorHelper::formatBytes($metrics['current_memory'] * 1024 * 1024, 1); ?></span>
                    <span>Peak: <?php echo PerformanceInspectorHelper::formatBytes($metrics['peak_memory'] * 1024 * 1024, 1); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Cache Information -->
        <?php if ($showCacheInfo) : ?>
            <div style="margin-bottom: 15px; padding-bottom: 12px; border-bottom: 1px solid #e0e0e0;">
                <div style="font-weight: 500; color: #666; margin-bottom: 8px;">
                    ⚡ Cache Status:
                </div>

                <div style="display: flex; gap: 8px;">
                    <!-- Cache Hits -->
                    <div style="
                        flex: 1;
                        background: #e8f5e9;
                        border: 1px solid #4caf50;
                        padding: 8px;
                        border-radius: 3px;
                        text-align: center;
                    ">
                        <div style="font-size: 10px; color: #666; margin-bottom: 4px;">
                            Hits
                        </div>
                        <div style="
                            font-size: 18px;
                            font-weight: bold;
                            color: #4caf50;
                        ">
                            <?php echo $metrics['cache_hits']; ?>
                        </div>
                    </div>

                    <!-- Cache Misses -->
                    <div style="
                        flex: 1;
                        background: #ffebee;
                        border: 1px solid #f44336;
                        padding: 8px;
                        border-radius: 3px;
                        text-align: center;
                    ">
                        <div style="font-size: 10px; color: #666; margin-bottom: 4px;">
                            Misses
                        </div>
                        <div style="
                            font-size: 18px;
                            font-weight: bold;
                            color: #f44336;
                        ">
                            <?php echo $metrics['cache_misses']; ?>
                        </div>
                    </div>
                </div>

                <!-- Cache Hit Ratio -->
                <?php
                $totalCacheOps = $metrics['cache_hits'] + $metrics['cache_misses'];
                $hitRatio = $totalCacheOps > 0 ? round(($metrics['cache_hits'] / $totalCacheOps) * 100) : 0;
                ?>
                <div style="margin-top: 8px; text-align: center; font-size: 11px; color: #999;">
                    Hit Ratio: <strong><?php echo $hitRatio; ?>%</strong>
                </div>
            </div>
        <?php endif; ?>

        <!-- Debug Information -->
        <?php if ($debugMode && isset($diagnostics)) : ?>
            <div style="
                background: #f0f4c3;
                border-left: 3px solid #cddc39;
                padding: 8px;
                border-radius: 2px;
            ">
                <div style="font-weight: 500; color: #9e9d24; margin-bottom: 6px;">
                    🔧 Debug Info:
                </div>
                <table style="width: 100%; font-size: 10px; color: #666;">
                    <tr style="border-bottom: 1px solid #e0e0e0;">
                        <td style="padding: 2px; font-weight: bold;">PHP:</td>
                        <td style="padding: 2px; text-align: right;"><?php echo $diagnostics['php_version']; ?></td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e0e0e0;">
                        <td style="padding: 2px; font-weight: bold;">Joomla:</td>
                        <td style="padding: 2px; text-align: right;"><?php echo $diagnostics['joomla_version']; ?></td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e0e0e0;">
                        <td style="padding: 2px; font-weight: bold;">Cache Handler:</td>
                        <td style="padding: 2px; text-align: right;"><?php echo $diagnostics['cache_handler']; ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 2px; font-weight: bold;">Memory Limit:</td>
                        <td style="padding: 2px; text-align: right;"><?php echo $diagnostics['memory_limit']; ?></td>
                    </tr>
                </table>
            </div>
        <?php endif; ?>

    </div>

    <!-- Footer -->
    <div style="
        background: #f0f0f0;
        border-top: 1px solid #ddd;
        padding: 10px;
        border-radius: 0 0 4px 4px;
        text-align: center;
        font-size: 10px;
        color: #999;
    ">
        📈 Real-time Performance Monitoring
    </div>
</div>

<!-- Inline Styles for Performance Classes -->
<style>
    .performance-good {
        background: #c8e6c9 !important;
        color: #2e7d32 !important;
    }

    .performance-acceptable {
        background: #fff9c4 !important;
        color: #fbc02d !important;
    }

    .performance-concerning {
        background: #ffccbc !important;
        color: #d84315 !important;
    }
</style>