<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

require_once dirname(__FILE__).DS.'articles.php';

/**
 * @package		Joomla.Administrator
 * @subpackage	com_content
 */
class ContentControllerFeatured extends ContentControllerArticles
{
	/**
	 * Removes an item
	 */
	function delete()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$user	= JFactory::getUser();
		$ids	= JRequest::getVar('cid', array(), '', 'array');

		// Access checks.
		foreach ($ids as $i => $id)
		{
			if (!$user->authorise('core.delete', 'com_content.article.'.(int) $id))
			{
				// Prune items that you can't delete.
				unset($ids[$i]);
				JError::raiseNotice(403, JText::_('JError_Core_Delete_not_permitted'));
			}
		}

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('JError_No_items_selected'));
		}
		else {
			// Get the model.
			$model = $this->getModel();

			// Remove the items.
			if (!$model->featured($ids, 0)) {
				JError::raiseWarning(500, $model->getError());
			}
		}

		$this->setRedirect('index.php?option=com_content&view=featured');
	}

	/**
	 * Method to publish a list of articles.
	 *
	 * @return	void
	 * @since	1.0
	 */
	function publish()
	{
		parent::publish();

		$this->setRedirect('index.php?option=com_content&view=featured');
	}
}