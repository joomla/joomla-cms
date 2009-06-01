<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

//Import the JToolBar library
jimport('joomla.html.toolbar');

// Get the JComponent instance of JToolBar
$bar = & JToolBar::getInstance('toolbar');

// Render the toolbar
echo $bar->render('toolbar');