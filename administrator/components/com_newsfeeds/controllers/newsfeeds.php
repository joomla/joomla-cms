<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die;

/**
 * Newsfeeds controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_newsfeeds
 * @version		1.6
 */
class NewsfeedsControllerNewsfeeds extends JController
{
	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask('unpublish',	'publish');
		$this->registerTask('trash',		'publish');
		$this->registerTask('orderup',		'reorder');
		$this->registerTask('orderdown',	'reorder');
	}

	/**
	 * Method to delete item(s) from the database.
	 *
	 * @access	public
	 */
	public function delete()
	{
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));
		$app	= &JFactory::getApplication();
		$model	= &$this->getModel('Newsfeed');
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');

		// Sanitize the input.
		JArrayHelper::toInteger($cid);

		// Attempt to delete the newsfeeds
		$return = $model->delete($cid);

		// Delete the newsfeeds
		if ($return === false) {
			$message = JText::sprintf('AN ERROR HAS OCCURRED', $model->getError());
			$this->setRedirect('index.php?option=com_newsfeeds&view=newsfeeds', $message, 'error');
			return false;
		}
		else {
			$message = JText::_('ITEMS REMOVED');
			$this->setRedirect('index.php?option=com_newsfeeds&view=newsfeeds', $message);
			return true;
		}
	}

	/**
	 * Method to publish unpublished item(s).
	 *
	 * @return	void
	 */
	public function publish()
	{
		JRequest::checkToken() or jExit(JText::_('JInvalid_Token'));

		$model	= &$this->getModel('Newsfeeds');
		$cid	= JRequest::getVar('cid', null, 'post', 'array');

		JArrayHelper::toInteger($cid);

		// Check for items.
		if (count($cid) < 1) {
			$message = JText::_('NO ITEMS SELECTED');
			$this->setRedirect('index.php?option=com_newsfeeds&view=newsfeeds', $message, 'warning');
			return false;
		}

		// Attempt to publish the items.
		$task	= $this->getTask();
		$value = ($task == 'publish') ? 1 : 0;
		$return = $model->setStates($cid, $value);

		if ($return === false) {
			//print_r($cid);exit;
			
			$message = JText::sprintf('AN ERROR HAS OCCURRED', $model->getError());
			$this->setRedirect('index.php?option=com_newsfeeds&view=newsfeeds', $message, 'error');
			return false;
		}
		else {
			$message = $value ? JText::sprintf('ITEMS PUBLISHED', count($cid)) : JText::sprintf('ITEMS UNPUBLISHED', count($cid));
			$this->setRedirect('index.php?option=com_newsfeeds&view=newsfeeds', $message);
			return true;
		}
	}

	/**
	 * Method to reorder newsfeeds.
	 *
	 * @return	bool	False on failure or error, true on success.
	 */
	public function reorder()
	{
		JRequest::checkToken() or jExit(JText::_('JInvalid_Token'));
		// Initialize variables.
		$model	= &$this->getModel('Newsfeed');
		$cid	= JRequest::getVar('cid', null, 'post', 'array');

		// Get the newsfeed id.
		$newsfeedId = (int) $cid[0];

		// Attempt to move the row.
		$return = $model->reorder($newsfeedId, $this->getTask() == 'orderup' ? -1 : 1);

		if ($return === false) {
			// Move failed, go back to the newsfeed and display a notice.
			$message = JText::sprintf('JError_Reorder_failed', $model->getError());
			$this->setRedirect('index.php?option=com_newsfeeds&view=newsfeeds', $message, 'error');
			return false;
		}
		else {
			// Move succeeded, go back to the newsfeed and display a message.
			$message = JText::_('NEW ORDERING SAVED');
			$this->setRedirect('index.php?option=com_newsfeeds&view=newsfeeds', $message);
			return true;
		}
	}


	/**
	 * Method to save the current ordering arrangement.
	 *
	 * @return	void
	 */
	public function saveorder()
	{
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Get the input
		$cid	= JRequest::getVar('cid',	null,	'post',	'array');
		$order	= JRequest::getVar('order',	null,	'post',	'array');

		// Sanitize the input
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($order);

		// Get the model
		$model = &$this->getModel('Newsfeeds');

		// Save the ordering
		$model->saveorder($cid, $order);

		$message = JText::_('NEW ORDERING SAVED');
		$this->setRedirect('index.php?option=com_newsfeeds&view=newsfeeds', $message);
	}
}