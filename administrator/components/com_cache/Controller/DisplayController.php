<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Cache\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Component\Cache\Administrator\Helper\CacheHelper;
use Joomla\CMS\Language\Text;


/**
 * Cache Controller
 *
 * @since  1.6
 */
class DisplayController extends BaseController
{
	/**
	 * Display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return  static  This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Get the document object.
		$document = \JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName   = $this->input->get('view', 'cache');
		$vFormat = $document->getType();
		$lName   = $this->input->get('layout', 'default', 'string');

		// Get and render the view.
		if ($view = $this->getView($vName, $vFormat))
		{
			switch ($vName)
			{
				case 'purge':
					$this->app->enqueueMessage(Text::_('COM_CACHE_RESOURCE_INTENSIVE_WARNING'), 'warning');
					break;
				case 'cache':
				default:
					$model = $this->getModel($vName);
					$view->setModel($model, true);
					break;
			}

			$view->setLayout($lName);

			// Push document object into the view.
			$view->document = $document;

			// Load the submenu.
			CacheHelper::addSubmenu($this->input->get('view', 'cache'));

			$view->display();
		}
	}

	/**
	 * Method to delete a list of cache groups.
	 *
	 * @return  void
	 */
	public function delete()
	{
		// Check for request forgeries
		\JSession::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

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
		\JSession::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

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
		\JSession::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		if (!$this->getModel('cache')->purge())
		{
			$this->app->enqueueMessage(Text::_('COM_CACHE_EXPIRED_ITEMS_PURGING_ERROR'), 'error');
		}
		else
		{
			$this->app->enqueueMessage(Text::_('COM_CACHE_EXPIRED_ITEMS_HAVE_BEEN_PURGED'), 'message');
		}

		$this->setRedirect('index.php?option=com_cache&view=purge');
	}
}
