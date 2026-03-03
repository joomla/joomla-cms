<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Module\PerformanceInspector\Administrator\Helper\PerformanceInspectorHelper;

// Get module parameters

/** @var \stdClass $module */
$params = $module->params;

// Check if module is enabled
if (
    !$params->get('show_execution_time', 1) && !$params->get('show_queries', 1)
    && !$params->get('show_memory', 1) && !$params->get('show_cache_info', 1)
) {
    // All display options are disabled
    return;
}

// Get performance metrics
$metrics = PerformanceInspectorHelper::getPerformanceMetrics($params);

// Get configuration options
$showExecutionTime = (bool) $params->get('show_execution_time', 1);
$showQueries = (bool) $params->get('show_queries', 1);
$showMemory = (bool) $params->get('show_memory', 1);
$showSlowestQuery = (bool) $params->get('show_slowest_query', 1);
$showCacheInfo = (bool) $params->get('show_cache_info', 1);
$debugMode = (bool) $params->get('debug_mode', 0);
$positionX = $params->get('position_x', 'right');
$positionY = $params->get('position_y', 'top');

// Load language strings
$lang = Factory::getApplication()->getLanguage();
//$lang->load('mod_performance_inspector', JPATH_ADMINISTRATOR . '/modules/mod_performance_inspector');

// Show diagnostics in debug mode
if ($debugMode) {
    $diagnostics = PerformanceInspectorHelper::getDiagnostics();
}

// Include the template
require JModuleHelper::getLayoutPath('mod_performance_inspector', 'default');
