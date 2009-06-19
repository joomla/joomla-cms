<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @link		http://www.theartofjoomla.com
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
		JRequest::checkToken() or die('Invalid Token');

		// Get items to remove from the request.
		$ids	= JRequest::getVar('cid', array(), '', 'array');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('Select an item to delete'));
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