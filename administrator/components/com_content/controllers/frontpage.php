<?php
/**
 * @version		$Id: $
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
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

// Add Content model path - need article model to update article details
JController::addModelPath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_content'.DS.'models' );

/**
 * Content Component Frontpage Controller
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.6
 */
class ContentControllerFrontpage extends JController
{
	function display()
	{
		JRequest::setVar( 'view', 'frontpage' );
		parent::display();
	}

	function remove()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to delete' ) );
		}

		$model = $this->getModel();
		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$cache = & JFactory::getCache('com_content');
		$cache->clean();

		$this->setRedirect( 'index.php?option=com_content&controller=frontpage' );
	}

	function publish( )
	{
		$this->changeState(1);
	}

	function unpublish( )
	{
		$this->changeState(0);
	}

	function archive( )
	{
		$this->changeState(-1);
	}

	/**
	* Changes the state of one or more content pages
	*
	* @param integer 0 if unpublishing, 1 if publishing, -1 if archiving
	*/
	function changeState( $state = 0 )
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		$task		= $this->getTask();

		$total = count($cid);
		if ($total < 1) {
			$redirect	= JRequest::getVar( 'redirect', '', 'post', 'int' );
			$action		= ($state == 1) ? 'publish' : ($state == -1 ? 'archive' : 'unpublish');
			$msg		= JText::_('Select an item to') . ' ' . JText::_($action);
			$this->setRedirect('index.php?option=com_content&controller=frontpage', $msg, 'error');
			return;
		}

		$model = JModel::getInstance('article', 'ContentModel');
		if(!$model->setArticleState($cid, $state)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		switch ($state)
		{
			case -1 :
				$msg = JText::sprintf('Item(s) successfully Archived', $total);
				break;

			case 1 :
				$msg = JText::sprintf('Item(s) successfully Published', $total);
				break;

			case 0 :
			default :
				$msg = JText::sprintf('Item(s) successfully Unpublished', $total);
				break;
		}

		$cache = & JFactory::getCache('com_content');
		$cache->clean();

		$this->setRedirect('index.php?option=com_content&controller=frontpage', $msg);
	}

	function orderup()
	{
		// Check for request forgeries.
		JRequest::checkToken() or die( 'Invalid Token' );

		$cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );
		JArrayHelper::toInteger($cid, array(0));

		$model = $this->getModel('frontpage');
		$model->move(-1);

		$cache = & JFactory::getCache('com_content');
		$cache->clean();

		$this->setRedirect( 'index.php?option=com_content&controller=frontpage' );
	}

	function orderdown()
	{
		// Check for request forgeries.
		JRequest::checkToken() or die( 'Invalid Token' );

		$cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );
		JArrayHelper::toInteger($cid, array(0));

		$model = $this->getModel('frontpage');
		$model->move(1);

		$cache = & JFactory::getCache('com_content');
		$cache->clean();

		$this->setRedirect( 'index.php?option=com_content&controller=frontpage' );
	}

	function accesspublic()
	{
		// Check for request forgeries.
		JRequest::checkToken() or die( 'Invalid Token' );

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$msg = '';
		$model = $this->getModel('article');
		if(!$model->setAccess($cid, 0)) {
			$msg = $model->getError();
		}

		$cache = & JFactory::getCache('com_content');
		$cache->clean();

		$this->setRedirect( 'index.php?option=com_content&controller=frontpage', $msg );
	}

	function accessregistered()
	{
		// Check for request forgeries.
		JRequest::checkToken() or die( 'Invalid Token' );

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$msg = '';
		$model = $this->getModel('article');
		if(!$model->setAccess($cid, 1)) {
			$msg = $model->getError();
		}

		$cache = & JFactory::getCache('com_content');
		$cache->clean();

		$this->setRedirect( 'index.php?option=com_content&controller=frontpage', $msg );
	}

	function accessspecial()
	{
		// Check for request forgeries.
		JRequest::checkToken() or die( 'Invalid Token' );

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$msg = '';
		$model = $this->getModel('article');
		if(!$model->setAccess($cid, 2)) {
			$msg = $model->getError();
		}

		$cache = & JFactory::getCache('com_content');
		$cache->clean();

		$this->setRedirect( 'index.php?option=com_content&controller=frontpage', $msg );
	}

	function saveorder()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		// Initialize variables
		$db			= & JFactory::getDBO();

		$cid		= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$order		= JRequest::getVar( 'order', array (0), 'post', 'array' );

		JArrayHelper::toInteger($cid, array(0));
		JArrayHelper::toInteger($order, array(0));

		$model = $this->getModel('frontpage');
		$model->saveorder($cid, $order);

		$cache = & JFactory::getCache('com_content');
		$cache->clean();

		$msg = JText::_('New ordering saved');
		$this->setRedirect('index.php?option=com_content&controller=frontpage', $msg);
	}
}