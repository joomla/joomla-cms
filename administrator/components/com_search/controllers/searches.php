<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_search
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.controller');

/**
 * Methods supporting a list of search terms.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_search
 * @since		1.6
 */
class SearchControllerSearches extends JController
{
	/**
	 * Method to reset the seach log table.
	 *
	 * @return	boolean
	 */
	public function reset()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$model = $this->getModel('Searches');

		if (!$model->reset()) {
			JError::raiseWarning(500, $model->getError());
		}

		$this->setRedirect('index.php?option=com_search&view=searches');
	}
}