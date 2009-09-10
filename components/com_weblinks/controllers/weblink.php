<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Weblinks Weblink Controller
 *
 * @package		Joomla.Site
 * @subpackage	Weblinks
 * @since 1.5
 */
class WeblinksControllerWeblink extends WeblinksController
{
	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param	string	The model name. Optional.
	 * @param	string	The class prefix. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	object	The model.
	 * @since	1.6
	 */
	public function &getModel($name = 'Weblink', $prefix = 'WeblinksModel')
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Go to a weblink
	 */
	function go()
	{
		// Get the ID from the request
		$id		= JRequest::getInt('id');

		// Get the model, requiring published items
		$modelLink	= &$this->getModel();
		$modelLink->setState('filter.published', 1);

		// Get the item
		$link	= &$modelLink->getItem($id);

		// Make sure the item was found.
		if (empty($link)) {
			return JError::raiseWarning(404, JText::_('Weblinks_Error_Weblink_not_found'));
		}

		// Check whether item access level allows access.
		$user	= &JFactory::getUser();
		$groups	= $user->authorisedLevels();
		if (!in_array($link->access, $groups)) {
			return JError::raiseError(403, JText::_("ALERTNOTAUTH"));
		}

		// Check whether category access level allows access.
		$modelCat = &$this->getModel('Category');
		$modelCat->setState('filter.published', 1);

		// Get the category
		$category = &$modelCat->getCategory($link->catid);

		// Make sure the category was found.
		if (empty($category)) {
			return JError::raiseWarning(404, JText::_('Weblinks_Error_Weblink_not_found'));
		}

		// Check whether item access level allows access.
		if (!in_array($category->access, $groups)) {
			return JError::raiseError(403, JText::_("ALERTNOTAUTH"));
		}

		// Redirect to the URL
		// TODO: Probably should check for a valid http link
		if ($link->url)
		{
			$modelLink->hit($id);
			JFactory::getApplication()->redirect($link->url);
		}
		else {
			return JError::raiseWarning(404, JText::_('Weblinks_Error_Weblink_url_invalid'));
		}
	}

	/**
	* Edit a weblink and show the edit form
	*
	* @acces public
	* @since 1.5
	*/
	function edit()
	{
		$user	= & JFactory::getUser();

		// Make sure you are logged in
		if (!$user->authorise('com_weblink.weblink.edit')) {
			JError::raiseError(403, JText::_('ALERTNOTAUTH'));
			return;
		}

		JRequest::setVar('view', 'weblink');
		JRequest::setVar('layout', 'form');

		$model = &$this->getModel('weblink');
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
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Get some objects from the JApplication
		$db		= &JFactory::getDbo();
		$user	= &JFactory::getUser();

		// Must be logged in
		if ($user->get('id') < 1) {
			JError::raiseError(403, JText::_('ALERTNOTAUTH'));
			return;
		}

		//get data from the request
		$post = JRequest::getVar('jform', array(), 'post', 'array');

		$model = $this->getModel('weblink');

		if ($model->store($post)) {
			$msg = JText::_('Weblink Saved');
		} else {
			$msg = JText::_('Error Saving Weblink');
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();

		// Get the user groups setup to receive notifications of new weblinks.
		$access = new JAccess();
		$groups = $access->getAuthorisedUsergroups('com_weblinks.submit.notify');
		$groups = count($groups) ? implode(',', $groups) : '0';

		// list of admins
		$query = 'SELECT u.email, u.name' .
				' FROM #__users AS u' .
				' JOIN #__users_usergroup_map AS m ON m.group_id IN ('.$groups.')' .
				' AND u.sendEmail = 1';
		$db->setQuery($query);
		if (!$db->query()) {
			JError::raiseError(500, $db->stderr(true));
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
			JError::raiseError(403, JText::_('ALERTNOTAUTH'));
			return;
		}

		// Checkin the weblink
		$model = $this->getModel('weblink');
		$model->checkin();

		$this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=categories', false));
	}
}

?>
