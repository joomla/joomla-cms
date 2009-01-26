<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * Weblinks Weblink Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	Weblinks
 * @since		1.5
 */
class WeblinksController extends JController
{
	function __construct($config = array())
	{
		parent::__construct($config);

		// Register Extra tasks
		$this->registerTask('add',  'display');
		$this->registerTask('edit', 'display');
	}

	function display()
	{
		// Get the document object.
		$document = &JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName		= JRequest::getWord('view', 'weblinks');
		$vFormat	= $document->getType();
		$lName		= JRequest::getWord('layout', 'default');

		// Get and render the view.
		if ($view = &$this->getView($vName, $vFormat))
		{
			switch ($vName)
			{
				default:
					$model = &$this->getModel($vName);
					break;
			}

			// Push the model into the view (as default).
			$view->setModel($model, true);
			$view->setLayout($lName);

			// Push document object into the view.
			$view->assignRef('document', $document);

			$view->display();
		}
	}

	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('JInvalid_Token');

		$post	= JRequest::get('post');
		$cid	= JRequest::getVar('cid', array(0), 'post', 'array');
		$post['id'] = (int) $cid[0];

		$model = $this->getModel('weblink');

		if ($model->store($post)) {
			$msg = JText::_('Weblink Saved');
		} else {
			$msg = JText::_('Error Saving Weblink');
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();
		$link = 'index.php?option=com_weblinks';
		$this->setRedirect($link, $msg);
	}

	function remove()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('JInvalid_Token');

		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		if (count($cid) < 1) {
			JError::raiseError(500, JText::_('Select an item to delete'));
		}

		$model = $this->getModel('weblink');
		if (!$model->delete($cid)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect('index.php?option=com_weblinks');
	}


	function publish()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('JInvalid_Token');

		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		if (count($cid) < 1) {
			JError::raiseError(500, JText::_('Select an item to publish'));
		}

		$model = $this->getModel('weblink');
		if (!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect('index.php?option=com_weblinks');
	}


	function unpublish()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('JInvalid_Token');

		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		if (count($cid) < 1) {
			JError::raiseError(500, JText::_('Select an item to unpublish'));
		}

		$model = $this->getModel('weblink');
		if (!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect('index.php?option=com_weblinks');
	}

	function report()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('JInvalid_Token');

		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		if (count($cid) < 1) {
			JError::raiseError(500, JText::_('Select an item to report'));
		}

		$model = $this->getModel('weblink');
		if (!$model->report($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect('index.php?option=com_weblinks');
	}

	function cancel()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('JInvalid_Token');

		// Checkin the weblink
		$model = $this->getModel('weblink');
		$model->checkin();

		$this->setRedirect('index.php?option=com_weblinks');
	}


	function orderup()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('JInvalid_Token');

		$model = $this->getModel('weblink');
		$model->move(-1);

		$this->setRedirect('index.php?option=com_weblinks');
	}

	function orderdown()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('JInvalid_Token');

		$model = $this->getModel('weblink');
		$model->move(1);

		$this->setRedirect('index.php?option=com_weblinks');
	}

	function saveorder()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('JInvalid_Token');

		$cid 	= JRequest::getVar('cid', array(), 'post', 'array');
		$order 	= JRequest::getVar('order', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($order);

		$model = $this->getModel('weblink');
		$model->saveorder($cid, $order);

		$msg = 'New ordering saved';
		$this->setRedirect('index.php?option=com_weblinks', $msg);
	}
}