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

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

/**
 * Installer controller for Joomla! installer class.
 *
 * @since  1.5
 */
class InstallController extends BaseController
{
	/**
	 * Install an extension.
	 *
	 * @return  mixed
	 *
	 * @since   1.5
	 */
	public function install()
	{
		// Check for request forgeries.
		$this->checkToken();

		if (!$this->app->getIdentity()->authorise('core.admin'))
		{
			throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		/** @var \Joomla\Component\Installer\Administrator\Model\InstallModel $model */
		$model = $this->getModel('install');

		// @todo: Reset the users acl here as well to kill off any missing bits.
		$result = $model->install();

		$app          = $this->app;
		$redirect_url = $app->getUserState('com_installer.redirect_url');
		$return       = $this->input->getBase64('return');

		if (!$redirect_url && $return)
		{
			$redirect_url = base64_decode($return);
		}

		// Don't redirect to an external URL.
		if ($redirect_url && !Uri::isInternal($redirect_url))
		{
			$redirect_url = '';
		}

		if (empty($redirect_url))
		{
			$redirect_url = Route::_('index.php?option=com_installer&view=install', false);
		}
		else
		{
			// Wipe out the user state when we're going to redirect.
			$app->setUserState('com_installer.redirect_url', '');
			$app->setUserState('com_installer.message', '');
			$app->setUserState('com_installer.extension_message', '');
		}

		$this->setRedirect($redirect_url);

		return $result;
	}

	/**
	 * Install an extension from drag & drop ajax upload.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function ajax_upload()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		if (!$this->app->getIdentity()->authorise('core.admin'))
		{
			throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$message = $this->app->getUserState('com_installer.message');

		// Do install
		$result = $this->install();

		// Get redirect URL
		$redirect = $this->redirect;

		// Push message queue to session because we will redirect page by \Javascript, not $app->redirect().
		// The "application.queue" is only set in redirect() method, so we must manually store it.
		$this->app->getSession()->set('application.queue', $this->app->getMessageQueue());

		header('Content-Type: application/json');

		echo new JsonResponse(array('redirect' => $redirect), $message, !$result);

		$this->app->close();
	}
}
