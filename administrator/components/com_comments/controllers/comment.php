<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Comments controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_comments
 * @since		1.6
 */
class CommentsControllerComment extends JControllerForm
{
	/**
	 * Save a comment.
	 *
	 * @access	public
	 * @return	void
	 * @since	1.0
	 */
	function save()
	{
		// check for request forgeries.
		JRequest::checkToken();

		// get posted form variables.
		$values = JRequest::getVar('jxform', array(), 'post', 'array');
		$post = JRequest::get('post', JREQUEST_ALLOWHTML);

		// handle the post body
		$body = $post['jxform']['body'];

		// make sure that html special characters are encoded in code tags
		function codeEscape($matches)
		{
			return htmlspecialchars($matches[0]);
		}
		$body = preg_replace_callback('/\[code=(.+?)\](.+?)\[\/code\]/is', 'codeEscape', $body);

		// if html is not enabled, then lets filter it out
		$config = &JComponentHelper::getParams('com_comments');
		if (!$config->get('enable_html', 0)) {
			$body = strip_tags($body);
		}

		// reset the body field in the posted values array
		$values['body'] = $body;

		// get the id of the item out of the session.
		$session = &JFactory::getSession();
		$id = (int)$session->get('comments.comment.id');
		$values['id'] = $id;

		// Get the comment model and set the post request in its state.
		$model = &$this->getModel('comment');
		$model->setState('request', JRequest::get('post'));

		// get the items to moderate from the request
		$c_id = JRequest::getVar('moderate', array(), '', 'array');

		// moderate the items if set
		if (is_array($c_id) and !empty($c_id)) {
			// split out the keys and values for the request array so that we can clean them
			$keys = array_keys($c_id);
			$vals = array_values($c_id);

			// clean the keys and values from the request array using JArrayHelper
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($keys);
			JArrayHelper::toInteger($vals);

			// re-initialize the array and build it with cleaned data
			$c_id = array();
			for($i=0,$n=count($keys);$i < $n; $i++)
			{
				$cid[$keys[$i]] = $vals[$i];
			}

			// moderate the items.
			$model->moderate($c_id);
		}

		// save the comment and check for an error state
		$result	= $model->save($values);
		$msg	= JError::isError($result) ? $result->message : 'COMMENTS_SAVED';

		// redirect to the appropriate place based on the task
		if ($this->_task == 'apply') {
			$this->setRedirect(JRoute::_('index.php?option=com_comments&view=comment&layout=edit', false), JText::_($msg));
		} else {
			$session->set('comments.comment.id', null);
			$model->checkin($id);

			$this->setRedirect(JRoute::_('index.php?option=com_comments&view=comments', false), JText::_($msg));
		}
	}

	/**
	 * Set the moderation state of a set of comments.
	 *
	 * @access	public
	 * @return	void
	 * @since	1.0
	 */
	function moderate()
	{
		// get the items to moderate from the request
		$c_id = JRequest::getVar('moderate', array(), '', 'array');
		if (!is_array($c_id) or (count($c_id) < 1)) {
			JError::raiseWarning(500, JText::_('COMMENTS_SELECT_COMMENT_TO_MODERATE'));
		} else {
			// Get the model.
			$model = &$this->getModel('comment', 'CommentsModel');

			/*
			 * We have to split the array because we need to sanitize the array keys.
			 * JArrayHelper::toInteger() will only sanitize the array values.
			 */
			$keys = array_keys($c_id);
			$vals = array_values($c_id);

			// clean the keys and values from the request array using JArrayHelper
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($keys);
			JArrayHelper::toInteger($vals);

			// re-initialize the array and build it with cleaned data
			$c_id = array();
			for($i=0,$n=count($keys);$i < $n; $i++)
			{
				$c_id[$keys[$i]] = $vals[$i];
			}

			// Get the number of comments each action is being performed on.
			$publish	= count(array_keys($c_id, 1, true));
			$defer		= count(array_keys($c_id, 0, true));
			$delete		= count(array_keys($c_id, -1, true));
			$spam		= count(array_keys($c_id, 2, true));

			// Publish the items.
			if(!$model->moderate($c_id)) {
				$msg  = JText::_('COMMENTS_UNABLE_TO_MODERATE_COMMENTS');
				$type = 'notice';
			} else {
				$messages = array();

				if ($defer) {
					$messages[] = JText::sprintf('COMMENTS_MODERATE_NUM_DEFERRED', $defer);
				}
				if ($publish) {
					$messages[] = JText::sprintf('COMMENTS_MODERATE_NUM_PUBLISHED', $publish);
				}
				if ($delete) {
					$messages[] = JText::sprintf('COMMENTS_MODERATE_NUM_DELETED', $delete);
				}
				if ($spam) {
					$messages[] = JText::sprintf('COMMENTS_MODERATE_NUM_SPAMMED', $spam);
				}

				$msg = implode(' ', $messages);
				$type = 'message';
			}
		}

		// Flush the cache.
		$cache = &JFactory::getCache();
		$cache->clean(null, 'group');
		$cache->clean(null, 'notgroup');

		$this->setRedirect('index.php?option=com_comments&view=comments', $msg, $type);
	}
}