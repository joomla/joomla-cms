<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * Comments model for the Comments package.
 *
 * @package		JXtended.Comments
 * @subpackage	com_comments
 * @since		1.3
 */
class CommentsModelThread extends JModel
{
	/**
	 * Method to delete rows.
	 *
	 * @param	array	An array of item ids.
	 *
	 * @return	boolean	Returns true on success, false on failure.
	 */
	public function delete(&$pks)
	{
		// Initialise variable.
		$db		= JFactory::getDbo();
		$pks	= (array) $pks;

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			// Delete the comments.
			if (!$this->resetComments($pks))
			{
				return false;
			}

			// Delete the ratings.
			if (!$this->resetRatings($pks))
			{
				return false;
			}

			// Delete the thread.
			$db->setQuery(
				'DELETE FROM #__jxcomments_threads' .
				' WHERE id IN ('.implode(',', $pks).')'
			);
			if (!$db->query())
			{
				$this->setError($db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to reset the comments on a thread.
	 *
	 * @param	array	An array of item ids.
	 *
	 * @return	boolean	Returns true on success, false on failure.
	 */
	public function resetComments(&$pks)
	{
		// Initialise variable.
		$db		= JFactory::getDbo();
		$pks	= (array) $pks;

		if (empty($pks))
		{
			$this->setError(JText::_('JError_No_items_selected'));
			return false;
		}

		JArrayHelper::toInteger($pks);

		$db->setQuery(
			'DELETE FROM #__jxcomments_comments' .
			' WHERE thread_id IN ('.implode(',', $pks).')'
		);
		if (!$db->query())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Method to reset the ratings on a thread.
	 *
	 * @param	array	An array of item ids.
	 *
	 * @return	boolean	Returns true on success, false on failure.
	 */
	public function resetRatings(&$pks)
	{
		// Initialise variable.
		$db		= JFactory::getDbo();
		$pks	= (array) $pks;

		if (empty($pks))
		{
			$this->setError(JText::_('JError_No_items_selected'));
			return false;
		}

		JArrayHelper::toInteger($pks);

		$db->setQuery(
			'DELETE FROM #__jxcomments_ratings' .
			' WHERE thread_id IN ('.implode(',', $pks).')'
		);
		if (!$db->query())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}

		return true;
	}
}
