<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Banners
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * @package		Joomla.Administrator
 * @subpackage	Banners
 */
class BannerControllerBanner extends JController
{
	/**
	 * Constructor
	 */
	function __construct($config = array())
	{
		parent::__construct($config);
		// Register Extra tasks
		$this->registerTask('add',			'display');
		$this->registerTask('edit',		'display');
		$this->registerTask('apply',		'save');
		$this->registerTask('resethits',	'save');
		$this->registerTask('unpublish',	'publish');
	}

	/**
	 * Display the list of banners
	 */
	function display()
	{
		$app	=& JFactory::getApplication();
		$user 	=& JFactory::getUser();

		switch($this->getTask())
		{
			case 'add':
				JRequest::setVar('hidemainmenu', 1);
				JRequest::setVar('view'  , 'banner');
				JRequest::setVar('edit', false);
				break;

			case 'edit':
				JRequest::setVar('hidemainmenu', 1);
				JRequest::setVar('view'  , 'banner');
				JRequest::setVar('edit', true);
				break;

			default:
				JRequest::setVar('view', 'banners');
		}

		parent::display();
	}

	/**
	 * Save method
	 */
	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$post	= JRequest::get('post');
		$bid	= JRequest::getVar('bid', array(0), 'post', 'array');
		$post['bid'] = (int) $bid[0];
		// fix up special html fields
		$post['custombannercode'] = JRequest::getVar('custombannercode', '', 'post', 'string', JREQUEST_ALLOWRAW);

		// Resets clicks when `Reset Clicks` button is used instead of `Save` button
		$task = JRequest::getCmd('task');
		if ($task == 'resethits')
			$post['clicks'] = 0;

		// Sets impressions to unlimited when `unlimited` checkbox ticked
		$unlimited = JRequest::getBool('unlimited');
		if ($unlimited) {
			$post['imptotal'] = 0;
		}

		$model = $this->getModel('banner');

		if ($model->store($post)) {
			if ($task == 'resethits')
				$msg = JText::_('Reset Banner clicks');
			else
				$msg = JText::_('Banner Saved');
		} else {
			$msg = JText::_('Error Saving Banner');
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();

		switch ($task)
		{
			case 'apply':
				$link = 'index.php?option=com_banners&task=edit&bid[]='. (int) $bid[0] ;
				break;

			case 'save':
			default:
				$link = 'index.php?option=com_banners';
				break;
		}

		$this->setRedirect($link, $msg);
	}

	function cancel()
	{
		// Checkin the data
		$model = $this->getModel('banner');
		$model->checkin();

		$this->setRedirect('index.php?option=com_banners');
	}

	/**
	 * Copies one or more banners
	 */
	function copy()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialize variables
		$bid		= JRequest::getVar('bid', array(), 'post', 'array');
		JArrayHelper::toInteger($bid);

		if (count($bid) < 1) {
			JError::raiseError(500, JText::_('Select an item to copy'));
		}

		$model = $this->getModel('banner');

		if (!$model->copy($bid)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect('index.php?option=com_banners', JText::sprintf('Items copied', count($cid)));
	}

	function publish()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialize variables
		$bid		= JRequest::getVar('bid', array(), 'post', 'array');
		$task		= JRequest::getCmd('task');
		$publish	= ($task == 'publish');
		JArrayHelper::toInteger($bid);

		if (count($bid) < 1) {
			JError::raiseError(500, JText::_('Select an item to publish'));
		}

		$model = $this->getModel('banner');
		if (!$model->publish($bid, $publish)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect('index.php?option=com_banners');
	}

	function remove()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$bid = JRequest::getVar('bid', array(), 'post', 'array');
		JArrayHelper::toInteger($bid);

		if (count($bid) < 1) {
			JError::raiseError(500, JText::_('Select an item to delete'));
		}

		$model = $this->getModel('banner');
		if (!$model->delete($bid)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect('index.php?option=com_banners');
	}

	function orderup()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('banner');
		$model->move(-1);

		$this->setRedirect('index.php?option=com_banners');
	}

	function orderdown()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('banner');
		$model->move(1);

		$this->setRedirect('index.php?option=com_banners');
	}

	/**
	 * Save the new order given by user
	 */
	function saveorder()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$cid 	= JRequest::getVar('cid', array(), 'post', 'array');
		$order 	= JRequest::getVar('order', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($order);

		$model = $this->getModel('banner');
		$model->saveorder($cid, $order);

		$msg = JText::_('New ordering saved');
		$this->setRedirect('index.php?option=com_banners', $msg);
	}
}