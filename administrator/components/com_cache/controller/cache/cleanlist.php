<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Delete Controller for cache
 *
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 * @since       3.2
 */
class CacheControllerCacheCleanlist extends JControllerBase
{
	/**
	 * Method to delete items.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function execute()
	{

		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JInvalid_Token'));

		$cid = $this->input->post->get('cid', array(), 'array');

		$model = $this->getModel('cache');

		if (empty($cid))
		{
			JError::raiseWarning(500, JText::_('JERROR_NO_ITEMS_SELECTED'));
		}
		else
		{
			$model->cleanlist($cid);
		}

		$app->redirect('index.php?option=com_cache&client=' . $model->getClient()->id);
	}
}