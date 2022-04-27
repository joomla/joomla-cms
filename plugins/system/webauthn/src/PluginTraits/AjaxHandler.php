<?php
/**
 * @package         Joomla.Plugin
 * @subpackage      System.Webauthn
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Webauthn\PluginTraits;

// Protect from unauthorized access
\defined('_JEXEC') or die();

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Event\GenericEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\Event;
use Joomla\Plugin\System\Webauthn\Exception\AjaxNonCmsAppException;
use RuntimeException;

/**
 * Allows the plugin to handle AJAX requests in the backend of the site, where com_ajax is not
 * available when we are not logged in.
 *
 * @since   4.0.0
 */
trait AjaxHandler
{
	/**
	 * Processes the callbacks from the passwordless login views.
	 *
	 * Note: this method is called from Joomla's com_ajax or, in the case of backend logins,
	 * through the special onAfterInitialize handler we have created to work around com_ajax usage
	 * limitations in the backend.
	 *
	 * @param   Event  $event  The event we are handling
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since   4.0.0
	 */
	public function onAjaxWebauthn(Event $event): void
	{
		$input = $this->app->input;

		// Get the return URL from the session
		$returnURL = $this->app->getSession()->get('plg_system_webauthn.returnUrl', Uri::base());
		$result    = null;

		try
		{
			Log::add("Received AJAX callback.", Log::DEBUG, 'webauthn.system');

			if (!($this->app instanceof CMSApplication))
			{
				throw new AjaxNonCmsAppException;
			}

			$akaction = $input->getCmd('akaction');

			if (!$this->app->checkToken('request'))
			{
				throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'));
			}

			// Empty action? No bueno.
			if (empty($akaction))
			{
				throw new RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_AJAX_INVALIDACTION'));
			}

			// Call the plugin event onAjaxWebauthnSomething where Something is the akaction param.
			$eventName = 'onAjaxWebauthn' . ucfirst($akaction);
			$event     = new GenericEvent($eventName, []);
			$result    = $this->app->getDispatcher()->dispatch($eventName, $event);
			$results   = !isset($result['result']) || \is_null($result['result']) ? [] : $result['result'];
			$result    = null;
			$reducer   = function ($carry, $result)
			{
				return $carry ?? $result;
			};
			$result    = array_reduce($results, $reducer, null);
		}
		catch (AjaxNonCmsAppException $e)
		{
			Log::add("This is not a CMS application", Log::NOTICE, 'webauthn.system');
		}
		catch (Exception $e)
		{
			Log::add("Callback failure, redirecting to $returnURL.", Log::DEBUG, 'webauthn.system');
			$this->app->getSession()->set('plg_system_webauthn.returnUrl', null);
			$this->app->enqueueMessage($e->getMessage(), 'error');
			$this->app->redirect($returnURL);

			return;
		}

		if (!\is_null($result))
		{
			switch ($input->getCmd('encoding', 'json'))
			{
				case 'jsonhash':
					Log::add("Callback complete, returning JSON inside ### markers.", Log::DEBUG, 'webauthn.system');
					echo '###' . json_encode($result) . '###';

					break;

				case 'raw':
					Log::add("Callback complete, returning raw response.", Log::DEBUG, 'webauthn.system');
					echo $result;

					break;

				case 'redirect':
					$modifiers = '';

					if (isset($result['message']))
					{
						$type = $result['type'] ?? 'info';
						$this->app->enqueueMessage($result['message'], $type);

						$modifiers = " and setting a system message of type $type";
					}

					if (isset($result['url']))
					{
						Log::add("Callback complete, performing redirection to {$result['url']}{$modifiers}.", Log::DEBUG, 'webauthn.system');
						$this->app->redirect($result['url']);
					}

					Log::add("Callback complete, performing redirection to {$result}{$modifiers}.", Log::DEBUG, 'webauthn.system');
					$this->app->redirect($result);

					return;

				default:
					Log::add("Callback complete, returning JSON.", Log::DEBUG, 'webauthn.system');
					echo json_encode($result);

					break;
			}

			$this->app->close(200);
		}

		Log::add("Null response from AJAX callback, redirecting to $returnURL", Log::DEBUG, 'webauthn.system');
		$this->app->getSession()->set('plg_system_webauthn.returnUrl', null);

		$this->app->redirect($returnURL);
	}
}
