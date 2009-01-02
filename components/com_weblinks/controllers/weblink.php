<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Weblinks Weblink Controller
 *
 * @package		Joomla
 * @subpackage	Weblinks
 * @since 1.5
 */
class WeblinksControllerWeblink extends WeblinksController
{
	/**
	* Edit a weblink and show the edit form
	*
	* @acces public
	* @since 1.5
	*/
	function edit()
	{
		$app	=& JFactory::getApplication();
		$user	=& JFactory::getUser();

		// Make sure you are logged in
		if ($user->get('aid', 0) < 1) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		JRequest::setVar('view', 'weblink');
		JRequest::setVar('layout', 'form');

		$model =& $this->getModel('weblink');
		// fail if checked out not by 'me'
		if ($model->isCheckedOut( $user->get('id') )) {
			$msg = JText::sprintf( 'DESCBEINGEDITTED', JText::_( 'The weblink' ), $weblink->title );
			$app->redirect( 'index.php?option=com_weblinks', $msg );
		}

		parent::display();
	}

	/**
	* Saves the record on an edit form submit
	*
	* @acces public
	* @since 1.5
	*/
	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		// Get some objects from the JApplication
		$db		=& JFactory::getDBO();
		$user	=& JFactory::getUser();

		// Must be logged in
		if ($user->get('id') < 1) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		//get data from the request
		$post = JRequest::getVar('jform', array(), 'post', 'array');

		$model = $this->getModel('weblink');

		if ($model->store($post)) {
			$msg = JText::_( 'Weblink Saved' );
		} else {
			$msg = JText::_( 'Error Saving Weblink' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();

		// admin users gid
		$gid = 25;

		// list of admins
		$query = 'SELECT email, name' .
				' FROM #__users' .
				' WHERE gid = ' . $gid .
				' AND sendEmail = 1';
		$db->setQuery($query);
		if (!$db->query()) {
			JError::raiseError( 500, $db->stderr(true));
			return;
		}
		$adminRows = $db->loadObjectList();

		// send email notification to admins
		foreach ($adminRows as $adminRow) {
			JUtility::sendAdminMail($adminRow->name, $adminRow->email, '',  JText::_('Web Link'), $post['title']." URL link ".$post[url], $user->get('username'), JURI::base());
		}

		$this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=category&id='.$post['catid'], false), $msg);
	}

	/**
	* Cancel the editing of a web link
	*
	* @access	public
	* @since	1.5
	*/
	function cancel()
	{
		// Get some objects from the JApplication
		$user	= & JFactory::getUser();

		// Must be logged in
		if ($user->get('id') < 1) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		// Checkin the weblink
		$model = $this->getModel('weblink');
		$model->checkin();

		$this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=categories', false));
	}
}

?>
