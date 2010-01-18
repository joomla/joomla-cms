<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * The JXtended Comments block controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_comments
 * @since		2.0
 */
class CommentsControllerBlock extends JController
{
	/**
	 * Dummy method to redirect the display method.
	 *
	 * @return	void
	 * @since	1.0
	 */
	public function display()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_comments'));
	}

	/**
	 * Method to block an IP or email address from posting.
	 *
	 * @return	void
	 * @since	2.0
	 */
	public function ip()
	{
		$type	= JRequest::getWord('block');
		$cid	= JRequest::getVar('cid', null, '', 'array');

		$model	= &$this->getModel('config');
		$result	= $model->block($type, $cid);
		if (JError::isError($result)) {
			$msg = $result->getMessage();
		} else {
			$msg = JText::sprintf('Comments_Items_Blocked', count($cid));
		}
		$this->setRedirect('index.php?option=com_comments&view=comments', $msg);
	}

	/**
	 * Method to block an IP or email address from posting.
	 *
	 * @return	void
	 * @since	2.0
	 */
	public function user()
	{
		$type	= JRequest::getWord('block');
		$cid	= JRequest::getVar('cid', null, '', 'array');

		$model	= &$this->getModel('config');
		$result	= $model->block($type, $cid);
		if (JError::isError($result)) {
			$msg = $result->getMessage();
		} else {
			$msg = JText::sprintf('Comments_Items_Blocked', count($cid));
		}
		$this->setRedirect('index.php?option=com_comments&view=comments', $msg);
	}
}