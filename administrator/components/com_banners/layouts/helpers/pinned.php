<?php
/**
 * @package     Joomla.Admin
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

extract($displayData);

JHtml::_('bootstrap.tooltip');

// Create the batch selector to change the client on a selection list.
echo JHtml::_('jgrid.state', $states, $value, $i, 'banners.', $enabled, true, $checkbox);

