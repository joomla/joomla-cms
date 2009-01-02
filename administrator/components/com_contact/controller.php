<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.controller' );

/**
 * Contact Contact Controller
 *
 * @package		Joomla
 * @subpackage	Contact
 * @since 1.5
 */
class ContactsController extends JController
{
	function __construct($config = array())
	{
		parent::__construct($config);

		// Register Extra tasks
		$this->registerTask( 'add',  'display' );
		$this->registerTask( 'edit', 'display' );
	}

	function display( )
	{
		switch($this->getTask())
		{
			case 'add':
			{
				JRequest::setVar( 'hidemainmenu', 1 );
				JRequest::setVar( 'view'  , 'contact');
				JRequest::setVar( 'edit', false );

				// Checkout the contact
				$model = $this->getModel('contact');
				$model->checkout();
			} break;
			case 'edit':
			{
				JRequest::setVar( 'hidemainmenu', 1 );
				JRequest::setVar( 'view'  , 'contact');
				JRequest::setVar( 'edit', true );

				// Checkout the contact
				$model = $this->getModel('contact');
				$model->checkout();
			} break;
		}

		parent::display();
	}

	function save()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$post	= JRequest::get('post');
		$cid	= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$post['id'] = (int) $cid[0];

		$model = $this->getModel('contact');

		if ($model->store($post)) {
			$msg = JText::_( 'Contact Saved' );
		} else {
			$msg = JText::_( 'Error Saving Contact' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();
		$link = 'index.php?option=com_contact';
		$this->setRedirect($link, $msg);
	}

	function remove()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to delete' ) );
		}

		$model = $this->getModel('contact');
		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_contact' );
	}


	function publish()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
		}

		$model = $this->getModel('contact');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_contact' );
	}


	function unpublish()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('contact');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_contact' );
	}

	function cancel()
	{
		// Checkin the contact
		$model = $this->getModel('contact');
		$model->checkin();

		$this->setRedirect( 'index.php?option=com_contact' );
	}


	function orderup()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$model = $this->getModel('contact');
		$model->move(-1);

		$this->setRedirect( 'index.php?option=com_contact');
	}

	function orderdown()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$model = $this->getModel('contact');
		$model->move(1);

		$this->setRedirect( 'index.php?option=com_contact');
	}

	function saveorder()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$order 	= JRequest::getVar( 'order', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($order);

		$model = $this->getModel('contact');
		$model->saveorder($cid, $order);

		$msg = 'New ordering saved';
		$this->setRedirect( 'index.php?option=com_contact', $msg );
	}

	function accesspublic()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$msg = '';
		$model = $this->getModel('contact');
		if(!$model->setAccess($cid, 0)) {
			$msg = $model->getError();
		}

		$this->setRedirect( 'index.php?option=com_contact', $msg );
	}

	function accessregistered()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$msg = '';
		$model = $this->getModel('contact');
		if(!$model->setAccess($cid, 1)) {
			$msg = $model->getError();
		}

		$this->setRedirect( 'index.php?option=com_contact', $msg );
	}

	function accessspecial()
	{
		// Check for request forgeries.
		$token = JUtility::getToken();
		if (!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$msg = '';
		$model = $this->getModel('contact');
		if(!$model->setAccess($cid, 2)) {
			$msg = $model->getError();
		}

		$this->setRedirect( 'index.php?option=com_contact', $msg );
	}

}