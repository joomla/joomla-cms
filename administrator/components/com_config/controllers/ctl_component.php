<?php
/**
 * @version $Id: admin.config.php 3566 2006-05-20 14:57:33Z stingrey $
 * @package Joomla
 * @subpackage Config
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport( 'joomla.application.controller' );

/**
 * Note: this view is intended only to be opened in a popup
 * @package Joomla
 * @subpackage Config
 */
class JConfigComponentController extends JController 
{
	var $_name		= 'component';

	var $_option	= 'com_config';

	/**
	 * Custom Constructor
	 */
	function __constuct( $default )
	{
		parent::__construct( $default );
		$this->registerTask( 'apply', 'save' );
	}

	/**
	 * Show the configuration edit form
	 * @param string The URL option
	 */
	function edit()
	{
		$component = JRequest::getVar( 'component' );

		if (empty( $component ))
		{
			JError::raiseWarning( 500, 'Not a valid component' );
			return false;
		}
		
		$model = &JModel::getInstance( 'JConfigComponentModel' );
		$table = &$model->getTable();
		if (!$table->loadByOption( $component ))
		{
			JError::raiseWarning( 500, 'Not a valid component' );
			return false;
		}
		$view = new JConfigComponentEditView( $this );
		$view->setModel( $model, true );
		$view->display();
	}

	/**
	 * Save the configuration
	 */
	function save() {
		$model = &JModel::getInstance( 'JConfigComponentModel' );
		$table = &$model->getTable();
		
		$table->bind( $_POST );
		// reset the option
		$table->option = null;

		// pre-save checks
		if (!$table->check()) {
			JError::raiseWarning( 500, $row->getError() );
			return false;
		}
	
		// save the changes
		if (!$table->store()) {
			JError::raiseWarning( 500, $row->getError() );
			return false;
		}

		//$this->setRedirect( 'index2.php?option=com_config', $msg );
		$this->edit();
	}

	/**
	 * Cancel operation
	 */
	function cancel()
	{
		$this->setRedirect( 'index2.php' );
	}
}
?>