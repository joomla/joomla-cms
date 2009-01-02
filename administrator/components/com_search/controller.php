<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Search
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

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