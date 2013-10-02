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
 * The AJAX Module Controller for RAW format
 *
 * modFooHelper::getAjax() is called where 'foo' is the value
 * of the 'name' variable passed via the URL
 * Example: index.php?option=com_ajax&task=module.call&name=foo&format=raw)
 *
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @since   3.2
 */
include_once __DIR__ . '/module.php';
