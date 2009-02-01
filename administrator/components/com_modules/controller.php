<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Modules
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class ModulesController extends JController
{
	/**
	 * Constructor
	 */
	function __construct($config = array())
	{
		parent::__construct($config);

		// Register Extra tasks
		$this->registerTask('apply', 			'save');
		$this->registerTask('unpublish', 		'publish');
		$this->registerTask('orderup', 		'reorder');
		$this->registerTask('orderdown', 		'reorder');
		$this->registerTask('accesspublic', 	'access');
		$this->registerTask('accessregistered','access');
		$this->registerTask('accessspecial',	'access');
	}

	/**
	* Compiles information to add or edit a module
	* @param string The current GET/POST option
	* @param integer The unique id of the record to edit
	*/
	function copy()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

		$cid	= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);
		$n		= count($cid);

		if ($n == 0) {
			return JError::raiseWarning(500, JText::_('No items selected'));
		}

		$model = $this->getModel('module');

		if ($model->copy($cid)) {
			$msg = JText::sprintf('Items Copied', $n);
		} else {
			$msg = JText::_('Error Copying Module(s)');
		}

		$this->setRedirect('index.php?option=com_modules&client='. $client->id, $msg);
	}

	/**
	 * Saves the module after an edit form submit
	 */
	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$cache = & JFactory::getCache();
		$cache->clean('com_content');

		$post	= JRequest::get('post');
		// fix up special html fields
		$post['content']   = JRequest::getVar('content', '', 'post', 'string', JREQUEST_ALLOWRAW);
		//$post['client_id'] = $client->id;

		$model = $this->getModel('module');

		if ($model->store($post)) {
			$msg = JText::_('Module Saved');
		} else {
			$msg = JText::_('Error Saving Module');
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();

		$client = $model->getClient();
		$this->setMessage(JText::_('Item saved'));
		switch ($this->getTask())
		{
			case 'save':
				$this->setRedirect('index.php?option=com_modules&client='. $client->id);
				break;

			case 'apply':
				$this->setRedirect('index.php?option=com_modules&client='. $client->id .'&task=edit&id='. $model->_id);
				break;
		}
	}

	/**
	* Compiles information to edit a module
	*/
	function edit()
	{
		JRequest::setVar('hidemainmenu', 1);
		JRequest::setVar('view', 'module');
		JRequest::setVar('edit', true);

		// Checkout the module
		$model = $this->getModel('module');
		$model->checkout();

		parent::display();
	}

	/**
	* Displays a list to select the creation of a new module
	*/
	function add()
	{
		JRequest::setVar('hidemainmenu', 1);
		JRequest::setVar('view', 'selecttype');
		JRequest::setVar('edit', false);

		// Checkout the module
		$model = $this->getModel('module');
		$model->checkout();

		parent::display();
		return;
	}

	/**
	* Deletes one or more modules
	*
	* Also deletes associated entries in the #__module_menu table.
	* @param array An array of unique category id numbers
	*/
	function remove()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$cid	= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);
		$n		= count($cid);

		if ($n == 0) {
			return JError::raiseWarning(500, 'No items selected');
		}

		$model = $this->getModel('module');

		if ($model->delete($cid)) {
			$msg = JText::sprintf('Items removed', $n);
		} else {
			$msg = JText::_('Error Deleting');
		}

		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$this->setRedirect('index.php?option=com_modules&client='.$client->id, $msg);
	}

	/**
	* Publishes or Unpublishes one or more modules
	*/
	function publish()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialize some variables
		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$task	= $this->getTask();
		$publish	= ($task == 'publish');

		$cache = & JFactory::getCache();
		$cache->clean('com_content');

		$cid 	= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);
		if (empty($cid)) {
			return JError::raiseWarning(500, 'No items selected');
		}

		$model = $this->getModel('module');
		if (!$model->publish($cid, $publish)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect('index.php?option=com_modules&client='.$client->id);
	}

	/**
	 * Cancels an edit operation
	 */
	function cancel()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('module');
		$model->checkin();

		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$this->setRedirect('index.php?option=com_modules&client='.$client->id);
	}

	/**
	 * Moves the order of a record
	 */
	function reorder()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$cid 	= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		$task	= $this->getTask();
		$inc	= ($task == 'orderup' ? -1 : 1);

		if (empty($cid)) {
			return JError::raiseWarning(500, 'No items selected');
		}

		$model = $this->getModel('module');

		if (!$model->move($inc)) {
			$msg = $model->getError();
		}

		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$this->setRedirect('index.php?option=com_modules&client='.$client->id, $msg);
	}

	/**
	 * Changes the access level of a record
	 */
	function access()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialize some variables
		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

		$cid 	= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		if (empty($cid)) {
			return JError::raiseWarning(500, 'No items selected');
		}

		$task	= JRequest::getCmd('task');
		switch ($task)
		{
			case 'accesspublic':
				$access = 0;
				break;

			case 'accessregistered':
				$access = 1;
				break;

			case 'accessspecial':
				$access = 2;
				break;
		}

		$msg = '';
		$model = $this->getModel('module');
		if (!$model->setAccess($cid, $access)) {
			$msg = $model->getError();
		}

		$this->setRedirect('index.php?option=com_modules&client='.$client->id, $msg);
	}

	/**
	 * Saves the orders of the supplied list
	 */
	function saveOrder()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialize some variables

		$cid 	= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		if (empty($cid)) {
			return JError::raiseWarning(500, 'No items selected');
		}

		$order 		= JRequest::getVar('order', array(0), 'post', 'array');
		JArrayHelper::toInteger($order);

		$model = $this->getModel('module');
		$model->saveorder($cid, $order);

		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$msg = JText::_('New ordering saved');
		$this->setRedirect('index.php?option=com_modules&client='.$client->id, $msg);
	}

	function preview()
	{
		JRequest::setVar('view', 'preview');

		$document =& JFactory::getDocument();
		$document->setTitle(JText::_('Module Preview'));

		parent::display();
	}
}