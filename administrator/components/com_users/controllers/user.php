<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_users
 */
class UserControllerUser extends JController
{
	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();

		$this->registerTask( 'save2copy',	'save' );
		$this->registerTask( 'save2new',	'save' );
		$this->registerTask( 'apply',		'save' );
		$this->registerTask( 'unpublish',	'publish' );
		$this->registerTask( 'trash',		'publish' );
		$this->registerTask( 'orderup',		'ordering' );
		$this->registerTask( 'orderdown',	'ordering' );
	}

	/**
	 * Display the view
	 */
	function display()
	{
		JError::raiseWarning( 500, 'This controller does not implement a display method' );
	}

	/**
	 * Proxy for getModel
	 */
	function &getModel()
	{
		return parent::getModel( 'User', 'UserModel', array( 'ignore_request' => true ) );
	}

	/**
	 * Method to edit a object
	 *
	 * Sets object ID in the session from the request, checks the item out, and then redirects to the edit page.
	 *
	 * @access	public
	 * @return	void
	 */
	function edit()
	{
		$cid = JRequest::getVar( 'cid', array(), '', 'array' );
		$id  = JRequest::getInt( 'id', @$cid[0] );

		$session = &JFactory::getSession();
		$session->set( 'users.user.id', $id );

		if ($id) {
			// Checkout item
			//$model = $this->getModel();
			//$model->checkout( $id );
		}
		$this->setRedirect( JRoute::_( 'index.php?option=com_users&view=user&layout=edit', false ) );
	}

	/**
	 * Method to cancel an edit
	 *
	 * Checks the item in, sets item ID in the session to null, and then redirects to the list page.
	 *
	 * @access	public
	 * @return	void
	 */
	function cancel()
	{
		$session = &JFactory::getSession();
		// Clear the session of the item
		$session->set( 'users.user.id', null );

		$this->setRedirect( JRoute::_('index.php?option=com_users&view=users', false ) );
	}

	/**
	 * Save the record
	 */
	function save()
	{
		// Check for request forgeries.
		JRequest::checkToken();

		// Get posted form variables.
		$input = JRequest::get('post');

		// Override the automatic filters
		$input['username']	= JRequest::getVar('username', '', 'post', 'username');
		$input['password']	= JRequest::getVar('password', '', 'post', 'string', JREQUEST_ALLOWRAW);
		$input['password2']	= JRequest::getVar('password2', '', 'post', 'string', JREQUEST_ALLOWRAW);

		if (!empty( $input['password'] ) AND !empty( $input['password2'] )) {
			if ($input['password'] !== $input['password2']) {
				$this->setMessage( JText::_( '@todo Find string for p[asswords dont match' ) );
				$this->setRedirect(JRoute::_('index.php?option=com_users&view=user&layout=edit', false));
				return;
			}
		}

		// Clear static values
		// @todo Look at moving these to the table bind method (but check how new user values are handled)
		unset( $input['registerDate'] );
		unset( $input['lastvisitDate'] );
		unset( $input['activation'] );

		// Get the id of the item out of the session.
		$session	= &JFactory::getSession();
		$id			= (int) $session->get('users.user.id');
		$input['id'] = $id;

		// Get the extensions model and set the post request in its state.
		$model	= &$this->getModel();
		$result	= $model->save( $input );
		$msg	= JError::isError( $result ) ? $result->message : 'Saved';

		if ($this->_task == 'apply') {
			$session->set( 'users.redirect.id', $model->getState( 'id' ) );
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=user&layout=edit', false), JText::_($msg));
		}
		else if ($this->_task == 'save2new') {
			$session->set( 'users.user.id', null );
			$model->checkin($id);

			$this->setRedirect(JRoute::_('index.php?option=com_users&view=user&layout=edit', false), JText::_($msg));
		}
		else {
			$session->set( 'users.user.id', null );
			$model->checkin($id);

			$this->setRedirect(JRoute::_('index.php?option=com_users&view=users', false), JText::_($msg));
		}
	}

	/**
	 * Deletes a user
	 */
	function delete()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		// Get items from the request.
		$cid = JRequest::getVar('cid', array(), '', 'array');

		if (empty( $cid )) {
			JError::raiseWarning(500, JText::_( 'No items selected' ));
		}
		else {
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			jimport( 'joomla.utilities.arrayhelper' );
			JArrayHelper::toInteger( $cid );

			// Remove the items.
			if (!$model->delete($cid)) {
				JError::raiseWarning( 500, $model->getError() );
			}
		}

		$this->setRedirect( 'index.php?option=com_users&view=users' );
	}

	/**
	 * Force logout a user
	 *
	 * @request		array	'cid'		An array of ids
	 * @request		mixed	'client'	The client id. If empty, all logout of all clients
	 */
	function logout()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		// Get items from the request.
		$cid = JRequest::getVar('cid', array(), '', 'array');
		$client = JRequest::getVar( 'client' );

		if (empty( $cid )) {
			JError::raiseWarning(500, JText::_( 'No items selected' ));
		}
		else {
			if (is_numeric( $client )) {
				$options['clientid'][] = $client;
			}
			else {
				// Log the user out of all clients
				$options['clientid'][] = 0;
				$options['clientid'][] = 1;
			}

			// Make sure the item ids are integers
			jimport( 'joomla.utilities.arrayhelper' );
			JArrayHelper::toInteger( $cid );

			foreach ($cids as $cid) {
				$mainframe->logout( $cid, $options );
			}

			$this->setMessage( JText::_( 'User session ended' ) );
		}

		$this->setRedirect( 'index.php?option=com_users&view=users' );
	}

	/**
	 * Method to run batch opterations.
	 *
	 * @access	public
	 * @return	void
	 * @since	1.0
	 */
	function batch()
	{
		// Get variables from the request.
		$vars	= JRequest::getVar( 'batch', array(), 'post', 'array' );
		$cid	= JRequest::getVar( 'cid', null, 'post', 'array' );

		$model	= &$this->getModel();
		$model->batch( $vars, $cid );

		$this->setRedirect( 'index.php?option=com_users&view=users' );
	}
}
