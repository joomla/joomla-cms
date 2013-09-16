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
class CacheControllerCacheCleanlist extends JControllerCmsbase
{
	/*
	 * Option to send to the model
	 *
	 * @var  string
	 */
	public $option;

	/**
	 * Method to delete items.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Get the application
		$app = $this->getApplication();

		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JInvalid_Token'));

		$cid = $this->input->post->get('cid', array(), 'array');

		$model = new CacheModelCache;

		if (empty($cid) && empty($this->option))
		{
			$app->enqueueMessage(JText::_('JERROR_NO_ITEMS_SELECTED'));
		}
		else
		{
			$model->cleanlist($cid, $this->option);
		}

		if (!empty($this->option) && $this->option == 'purge')
		{
			$app->enqueueMessage(JText::_('COM_CACHE_EXPIRED_ITEMS_HAVE_BEEN_PURGED'));
			$app->redirect('index.php?option=com_cache&view=cache&layout=purge');
		}
		else
		{
			$app->enqueueMessage(JText::_('COM_CACHE_CACHE_CLEARED'));
			$app->redirect('index.php?option=com_cache&view=cache');
		}

	}
}