<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
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
		$user = & JFactory::getUser();

		// Make sure you are logged in
		if ($user->get('aid', 0) < 1) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		JRequest::setVar('view', 'weblink');
		JRequest::setVar('layout', 'form');

		$model =& $this->getModel('weblink');
		$model->checkout();

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
		global $mainframe;

		//check the token before we do anything else
		$token	= JUtility::getToken();
		if(!JRequest::getInt($token, 0, 'post')) {
			JError::raiseError(403, 'Request Forbidden');
		}

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
			JUtility::sendAdminMail($adminRow->name, $adminRow->email, '', 'Weblink', $post['title'], $user->get('username'), JURI::base());
		}

		$this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=weblink', false), $msg);
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
