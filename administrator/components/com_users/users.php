<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Access Check
$user = & JFactory::getUser();
if (!$user->authorize( 'com_users', 'manage' )) {
	$mainframe->redirect( 'index.php', JText::_('ALERTNOTAUTH') );
}

// Import library dependencies
jimport('joomla.application.component.controller');
jimport('joomla.application.component.model');
JTable::addIncludePath( JPATH_COMPONENT.DS.'tables' );

/**
 * Component Controller
 *
 * @package		Users
 * @subpackage	com_user
 */
class UserController extends JController
{
	function display()
	{
		$document	= &JFactory::getDocument();

		// Set the default view name and format from the Request
		$vName		= JRequest::getWord( 'view', 'users' );
		$vFormat	= $document->getType();
		$lName		= JRequest::getWord( 'layout', 'default' );

		if ($view = &$this->getView( $vName, $vFormat ))
		{
			switch ($vName)
			{
				case 'user':
				case 'users':
					$model = $this->getModel( 'user' );
					break;

				case 'group':
				case 'groups':
					$acl		= &JFactory::getACL();
					$parentId	= $acl->get_group_id( 'USERS' );
					$model		= $this->getModel( 'group' );
					$model->setState( 'type', 'aro' );
					$model->setState( 'parent_id', $parentId );
					$model->setState( 'show.tree', 1 );
					break;

				case 'level':
				case 'levels':
					$model = $this->getModel( 'group' );
					$model->setState( 'type', 'axo' );
					break;

			}

			// Push the model into the view (as default)
			$view->setModel( $model, true );
			$view->setLayout( $lName );
			$view->assignRef( 'document', $document );

			JHTML::addIncludePath( JPATH_COMPONENT.DS.'helpers'.DS.'html' );
			$view->display();
		}

		// Set up the Linkbar
		JSubMenuHelper::addEntry( JText::_( 'Link Users' ),			'index.php?option=com_users&view=users',	$vName == 'users' );
		JSubMenuHelper::addEntry( JText::_( 'Link Groups' ),		'index.php?option=com_users&view=groups',	$vName == 'groups' );
		JSubMenuHelper::addEntry( JText::_( 'Link Access Levels' ),	'index.php?option=com_users&view=levels',	$vName == 'levels' );
	}
}

// Determine the request protocol
$protocol = JRequest::getWord( 'protocol' );

// Get task command from the request
$cmd = JRequest::getVar( 'task', null );

// If it was a multiple option post get the selected option
if (is_array( $cmd )) {
	$cmd = array_pop( array_keys( $cmd ) );
}

// Filter the command and instantiate the appropriate controller
$cmd = JFilterInput::clean($cmd,'cmd');
if (strpos($cmd, '.') != false) {
	// We have a defined controller/task pair -- lets split them out
	list($controllerName, $task) = explode('.', $cmd);

	// Define the controller name and path
	$controllerName	= strtolower($controllerName);
	$controllerFile = ($protocol) ? $controllerName.'.'.$protocol : $controllerName;
	$controllerPath	= JPATH_COMPONENT.DS.'controllers'.DS.$controllerFile.'.php';

	// If the controller file path exists, include it ... else lets die with a 500 error
	if (file_exists( $controllerPath )) {
		require_once $controllerPath;
	}
	else {
		JError::raiseError(500, 'Invalid Controller');
	}
}
else {
	// Base controller, just set the task :)
	$controllerName = null;
	$task = $cmd;
}

// Set the name for the controller and instantiate it
$controllerClass = 'UserController'.ucfirst( $controllerName );

if (class_exists( $controllerClass )) {
	$controller = new $controllerClass();
}
else {
	JError::raiseError(500, 'Invalid Controller Class');
}

// Perform the Request task
$controller->execute( $task );

// Redirect if set by the controller
$controller->redirect();
