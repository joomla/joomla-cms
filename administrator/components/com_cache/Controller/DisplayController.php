<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cache\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Cache Controller
 *
 * @since  1.6
 */
class DisplayController extends BaseController
{
	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $default_view = 'cache';

	/**
	 * Method to delete a list of cache groups.
	 *
	 * @return  void
	 */
	public function delete()
	{
		// Check for request forgeries
		$this->checkToken();

		$cid = $this->input->post->get('cid', array(), 'array');

		if (empty($cid))
		{
			$this->app->enqueueMessage(Text::_('JERROR_NO_ITEMS_SELECTED'), 'warning');
		}
		else
		{
			$result = $this->getModel('cache')->cleanlist($cid);

			if ($result !== array())
			{
				$this->app->enqueueMessage(Text::sprintf('COM_CACHE_EXPIRED_ITEMS_DELETE_ERROR', implode(', ', $result)), 'error');
			}
			else
			{
				$this->app->enqueueMessage(Text::_('COM_CACHE_EXPIRED_ITEMS_HAVE_BEEN_DELETED'), 'message');
			}
		}

		$this->setRedirect('index.php?option=com_cache');
	}

	/**
	 * Method to delete all cache groups.
	 *
	 * @return  void
	 *
	 * @since  3.6.0
	 */
	public function deleteAll()
	{
		// Check for request forgeries
		$this->checkToken();

		$app        = $this->app;
		$model      = $this->getModel('cache');
		$allCleared = true;

		$mCache = $model->getCache();

		foreach ($mCache->getAll() as $cache)
		{
			if ($mCache->clean($cache->group) === false)
			{
				$app->enqueueMessage(
					Text::sprintf(
						'COM_CACHE_EXPIRED_ITEMS_DELETE_ERROR', Text::_('JADMINISTRATOR') . ' > ' . $cache->group
					), 'error'
				);
				$allCleared = false;
			}
		}

		if ($allCleared)
		{
			$app->enqueueMessage(Text::_('COM_CACHE_MSG_ALL_CACHE_GROUPS_CLEARED'), 'message');
		}
		else
		{
			$app->enqueueMessage(Text::_('COM_CACHE_MSG_SOME_CACHE_GROUPS_CLEARED'), 'warning');
		}

		$app->triggerEvent('onAfterPurge', array());
		$this->setRedirect('index.php?option=com_cache&view=cache');
	}

	/**
	 * Purge the cache.
	 *
	 * @return  void
	 */
	public function purge()
	{
		// Check for request forgeries
		$this->checkToken();

		if (!$this->getModel('cache')->purge())
		{
			$this->app->enqueueMessage(Text::_('COM_CACHE_EXPIRED_ITEMS_PURGING_ERROR'), 'error');
		}
		else
		{
			$this->app->enqueueMessage(Text::_('COM_CACHE_EXPIRED_ITEMS_HAVE_BEEN_PURGED'), 'message');
		}

		$this->setRedirect('index.php?option=com_cache&view=cache');
	}
}
