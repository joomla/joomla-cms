<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$debug = empty($displayData['debug']) ? false : $displayData['debug'];

// Load jQuery
JHtml::_('jquery.framework');
JHtml::_('script', 'jui/bootstrap.min.js', false, true, false, false, $debug);
JHtml::_('script', 'system/bootstrap-init.min.js', false, true, false, false, $debug);
