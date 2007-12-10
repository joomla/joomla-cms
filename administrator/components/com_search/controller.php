<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Search
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights
 * reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * @package		Joomla
 * @subpackage	Search
 */
class SearchController extends JController
{
	/**
	 * Show Search Statistics
	 */
	function display()
	{
		$model	=& $this->getModel( 'Search' );
		$view   =& $this->getView( 'Search' );
		$view->setModel( $model, true );
		$view->display();
	}

	/**
	 * Reset Statistics
	 */
	function reset()
	{
		$model	=& $this->getModel( 'Search' );
		$model->reset();
		$this->setRedirect('index.php?option=com_search');
	}
}