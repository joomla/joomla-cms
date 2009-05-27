<?php
/**
 * @version		$Id: mod_toolbar.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

//Import the JToolBar library
jimport('joomla.html.toolbar');

// Get the JComponent instance of JToolBar
$bar = & JToolBar::getInstance('toolbar');

// Render the toolbar
echo $bar->render('toolbar');