<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Updater\Updater;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Installer\Administrator\Model\UpdateModel;

/**
 * Installer Update Controller
 *
 * @since  1.6
 */
class UpdateController extends BaseController
{
	/**
	 * Update a set of extensions.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function update()
	{
		// Check for request forgeries.
		$this->checkToken();

		/** @var UpdateModel $model */
		$model = $this->getModel('update');

		$uid = (array) $this->input->get('cid', array(), 'int');

		// Remove zero values resulting from input filter
		$uid = array_filter($uid);

		// Get the minimum stability.
		$params        = ComponentHelper::getComponent('com_installer')->getParams();
		$minimum_stability = (int) $params->get('minimum_stability', Updater::STABILITY_STABLE);

		$model->update($uid, $minimum_stability);

		$app          = $this->app;
		$redirect_url = $app->getUserState('com_installer.redirect_url');

		// Don't redirect to an external URL.
		if (!Uri::isInternal($redirect_url))
		{
			$redirect_url = '';
		}

		if (empty($redirect_url))
		{
			$redirect_url = Route::_('index.php?option=com_installer&view=update', false);
		}
		else
		{
			// Wipe out the user state when we're going to redirect.
			$app->setUserState('com_installer.redirect_url', '');
			$app->setUserState('com_installer.message', '');
			$app->setUserState('com_installer.extension_message', '');
		}

		$this->setRedirect($redirect_url);
	}

	/**
	 * Find new updates.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function find()
	{
		$this->checkToken('request');

		// Get the caching duration.
		$params        = ComponentHelper::getComponent('com_installer')->getParams();
		$cache_timeout = (int) $params->get('cachetimeout', 6);
		$cache_timeout = 3600 * $cache_timeout;

		// Get the minimum stability.
		$minimum_stability = (int) $params->get('minimum_stability', Updater::STABILITY_STABLE);

		// Find updates.
		/** @var UpdateModel $model */
		$model = $this->getModel('update');

		// Purge the table before checking again
		$model->purge();

		$disabledUpdateSites = $model->getDisabledUpdateSites();

		if ($disabledUpdateSites)
		{
			$updateSitesUrl = Route::_('index.php?option=com_installer&view=updatesites');
			$this->app->enqueueMessage(Text::sprintf('COM_INSTALLER_MSG_UPDATE_SITES_COUNT_CHECK', $updateSitesUrl), 'warning');
		}

		$model->findUpdates(0, $cache_timeout, $minimum_stability);

		if (0 === $model->getTotal())
		{
			$this->app->enqueueMessage(Text::_('COM_INSTALLER_MSG_UPDATE_NOUPDATES'), 'info');
		}

		$this->setRedirect(Route::_('index.php?option=com_installer&view=update', false));
	}

	/**
	 * Fetch and report updates in \JSON format, for AJAX requests
	 *
	 * @return void
	 *
	 * @since 2.5
	 */
	public function ajax()
	{
		$app = $this->app;

		if (!Session::checkToken('get'))
		{
			$app->setHeader('status', 403, true);
			$app->sendHeaders();
			echo Text::_('JINVALID_TOKEN_NOTICE');
			$app->close();
		}

		// Close the session before we make a long running request
		$app->getSession()->abort();

		$eid               = $this->input->getInt('eid', 0);
		$skip              = $this->input->get('skip', array(), 'array');
		$cache_timeout     = $this->input->getInt('cache_timeout', 0);
		$minimum_stability = $this->input->getInt('minimum_stability', -1);

		$params        = ComponentHelper::getComponent('com_installer')->getParams();

		if ($cache_timeout == 0)
		{
			$cache_timeout = (int) $params->get('cachetimeout', 6);
			$cache_timeout = 3600 * $cache_timeout;
		}

		if ($minimum_stability < 0)
		{
			$minimum_stability = (int) $params->get('minimum_stability', Updater::STABILITY_STABLE);
		}

		/** @var UpdateModel $model */
		$model = $this->getModel('update');
		$model->findUpdates($eid, $cache_timeout, $minimum_stability);

		$model->setState('list.start', 0);
		$model->setState('list.limit', 0);

		if ($eid != 0)
		{
			$model->setState('filter.extension_id', $eid);
		}

		$updates = $model->getItems();

		if (!empty($skip))
		{
			$unfiltered_updates = $updates;
			$updates            = array();

			foreach ($unfiltered_updates as $update)
			{
				if (!in_array($update->extension_id, $skip))
				{
					$updates[] = $update;
				}
			}
		}

		echo json_encode($updates);

		$app->close();
	}

	/**
	 * Provide the data for a badge in a menu item via JSON
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function getMenuBadgeData()
	{
		if (!$this->app->getIdentity()->authorise('core.manage', 'com_installer'))
		{
			throw new \Exception(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'));
		}

		$model = $this->getModel('Update');

		echo new JsonResponse($model->getTotal());
	}
}
