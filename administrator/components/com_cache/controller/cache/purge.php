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
class CacheControllerCachePurge extends JControllerBase
{
	/**
	 * Method to purge items.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JInvalid_Token'));

		$model = new CacheModelCache;
		$ret = $model->purge();

		$msg = JText::_('COM_CACHE_EXPIRED_ITEMS_HAVE_BEEN_PURGED');
		$msgType = 'message';

		if ($ret === false)
		{
			$msg = JText::_('COM_CACHE_EXPIRED_ITEMS_PURGING_ERROR');
			$msgType = 'error';
		}

		$app = JFactory::getApplication();
		$app->redirect('index.php?option=com_cache&view=cache&layout=purge', $msg, $msgType);
	}
}
