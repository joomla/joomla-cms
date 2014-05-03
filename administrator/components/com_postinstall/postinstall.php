<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_postinstall
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load the RAD layer
if (!defined('FOF_INCLUDED'))
{
	require_once JPATH_LIBRARIES . '/fof/include.php';
}

// Dispatch the component
FOFDispatcher::getTmpInstance('com_postinstall')->dispatch();