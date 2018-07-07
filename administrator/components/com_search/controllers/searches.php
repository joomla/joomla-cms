<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Methods supporting a list of search terms.
 *
 * @since  1.6
 */
class SearchControllerSearches extends JControllerLegacy
{
	/**
	 * Method to reset the search log table.
	 *
	 * @return  boolean
	 */
	public function reset()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('Searches');

		if (!$model->reset())
		{
			JError::raiseWarning(500, $model->getError());
		}

		$this->setRedirect('index.php?option=com_search&view=searches');
	}

	/**
	 * Method to toggle the view of results.
	 *
	 * @return  boolean
	 */
	public function toggleResults()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		if ($this->getModel('Searches')->getState('show_results', 1, 'int') === 0)
		{
			$this->setRedirect('index.php?option=com_search&view=searches&show_results=1');
		}
		else
		{
			$this->setRedirect('index.php?option=com_search&view=searches&show_results=0');
		}
	}
}
