<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

require_once dirname(__FILE__).'/articles.php';

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
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

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
				JError::raiseNotice(403, JText::_('JERROR_CORE_DELETE_NOT_PERMITTED'));
			}
		}

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('JERROR_NO_ITEMS_SELECTED'));
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

	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function &getModel($name = 'Feature', $prefix = 'ContentModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}
