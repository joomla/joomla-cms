<?php
/**
 * @version		$Id: controller.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @subpackage	Search
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * @package		Joomla.Administrator
 * @subpackage	Search
 */
class SearchController extends JController
{
	/**
	 * Show Search Statistics
	 */
	function display()
	{
		$model	= &$this->getModel('Search');
		$view   = &$this->getView('Search');
		$view->setModel($model, true);
		$view->display();
	}

	/**
	 * Reset Statistics
	 */
	function reset()
	{
		$model	= &$this->getModel('Search');
		$model->reset();
		$this->setRedirect('index.php?option=com_search');
	}
}