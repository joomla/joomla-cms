<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

/**
 * Legacy helper file for backward compatibility
 *
 * This file provides backward compatibility by loading the modern
 * PSR-4 compliant helper class from the src/Helper directory.
 *
 * @deprecated Use Joomla\Module\PerformanceInspector\Administrator\Helper\PerformanceInspectorHelper instead
 *
 * @since  5.0.0
 */

// Load the modern helper class
require_once __DIR__ . '/src/Helper/PerformanceInspectorHelper.php';

// Create an alias for backward compatibility
use Joomla\Module\PerformanceInspector\Administrator\Helper\PerformanceInspectorHelper;

// For old-style function calls, create a simple wrapper if needed
// This allows legacy code to still work with the module
