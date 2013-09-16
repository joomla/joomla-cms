<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Plugins component helper.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 * @since       1.6
 */
JLog::add('helpers/PluginHelper is deprecated. Use helper/PluginsHelperPlugins instead.', JLog::WARNING, 'deprecated');
include_once JPATH_ADMINISTRATOR . '/components/com_plugins/helper/plugins.php';
