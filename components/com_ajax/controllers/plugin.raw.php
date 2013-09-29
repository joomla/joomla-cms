<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The Plugin Controller for RAW format
 *
 * The plugin event triggered is onAjaxFoo, where 'foo' is
 * the value of the 'name' variable passed via the URL
 * Example: index.php?option=com_ajax&task=plugin.call&name=foo&format=raw
 *
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @since   3.2
 */
include_once __DIR__ . '/plugin.php';
