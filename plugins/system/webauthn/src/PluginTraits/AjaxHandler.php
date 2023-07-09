<?php

/**
 * @package         Joomla.Plugin
 * @subpackage      System.Webauthn
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Webauthn\PluginTraits;

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Event\GenericEvent;
use Joomla\CMS\Event\Plugin\System\Webauthn\Ajax;
use Joomla\CMS\Event\Plugin\System\Webauthn\Ajax as PlgSystemWebauthnAjax;
use Joomla\CMS\Event\Plugin\System\Webauthn\AjaxChallenge as PlgSystemWebauthnAjaxChallenge;
use Joomla\CMS\Event\Plugin\System\Webauthn\AjaxCreate as PlgSystemWebauthnAjaxCreate;
use Joomla\CMS\Event\Plugin\System\Webauthn\AjaxDelete as PlgSystemWebauthnAjaxDelete;
use Joomla\CMS\Event\Plugin\System\Webauthn\AjaxInitCreate as PlgSystemWebauthnAjaxInitCreate;
use Joomla\CMS\Event\Plugin\System\Webauthn\AjaxLogin as PlgSystemWebauthnAjaxLogin;
use Joomla\CMS\Event\Plugin\System\Webauthn\AjaxSaveLabel as PlgSystemWebauthnAjaxSaveLabel;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\Event;
use RuntimeException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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
    public function onAjaxWebauthn(Ajax $event): void
    {
        $input = $this->getApplication()->getInput();

        // Get the return URL from the session
        $returnURL = $this->getApplication()->getSession()->get('plg_system_webauthn.returnUrl', Uri::base());
        $result    = null;

        try {
            Log::add("Received AJAX callback.", Log::DEBUG, 'webauthn.system');

            if (!($this->getApplication() instanceof CMSApplication)) {
                Log::add("This is not a CMS application", Log::NOTICE, 'webauthn.system');

                return;
            }

            $akaction = $input->getCmd('akaction');

            if (!$this->getApplication()->checkToken('request')) {
                throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'));
            }

            // Empty action? No bueno.
            if (empty($akaction)) {
                throw new RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_AJAX_INVALIDACTION'));
            }

            // Call the plugin event onAjaxWebauthnSomething where Something is the akaction param.
            /** @var AbstractEvent|ResultAwareInterface $triggerEvent */
            $eventName    = 'onAjaxWebauthn' . ucfirst($akaction);

            switch ($eventName) {
                case 'onAjaxWebauthn':
                    $eventClass = PlgSystemWebauthnAjax::class;
                    break;

                case 'onAjaxWebauthnChallenge':
                    $eventClass = PlgSystemWebauthnAjaxChallenge::class;
                    break;

                case 'onAjaxWebauthnCreate':
                    $eventClass = PlgSystemWebauthnAjaxCreate::class;
                    break;

                case 'onAjaxWebauthnDelete':
                    $eventClass = PlgSystemWebauthnAjaxDelete::class;
                    break;

                case 'onAjaxWebauthnInitcreate':
                    $eventClass = PlgSystemWebauthnAjaxInitCreate::class;
                    break;

                case 'onAjaxWebauthnLogin':
                    $eventClass = PlgSystemWebauthnAjaxLogin::class;
                    break;

                case 'onAjaxWebauthnSavelabel':
                    $eventClass = PlgSystemWebauthnAjaxSaveLabel::class;
                    break;

                default:
                    $eventClass = GenericEvent::class;
                    break;
            }

            $triggerEvent = new $eventClass($eventName, []);
            $result       = $this->getApplication()->getDispatcher()->dispatch($eventName, $triggerEvent);
            $results      = ($result instanceof ResultAwareInterface) ? ($result['result'] ?? []) : [];
            $result       = array_reduce(
                $results,
                function ($carry, $result) {
                    return $carry ?? $result;
                },
                null
            );
        } catch (Exception $e) {
            Log::add("Callback failure, redirecting to $returnURL.", Log::DEBUG, 'webauthn.system');
            $this->getApplication()->getSession()->set('plg_system_webauthn.returnUrl', null);
            $this->getApplication()->enqueueMessage($e->getMessage(), 'error');
            $this->getApplication()->redirect($returnURL);

            return;
        }

        if (!\is_null($result)) {
            switch ($input->getCmd('encoding', 'json')) {
                case 'raw':
                    Log::add("Callback complete, returning raw response.", Log::DEBUG, 'webauthn.system');
                    echo $result;

                    break;

                case 'redirect':
                    $modifiers = '';

                    if (isset($result['message'])) {
                        $type = $result['type'] ?? 'info';
                        $this->getApplication()->enqueueMessage($result['message'], $type);

                        $modifiers = " and setting a system message of type $type";
                    }

                    if (isset($result['url'])) {
                        Log::add("Callback complete, performing redirection to {$result['url']}{$modifiers}.", Log::DEBUG, 'webauthn.system');
                        $this->getApplication()->redirect($result['url']);
                    }

                    Log::add("Callback complete, performing redirection to {$result}{$modifiers}.", Log::DEBUG, 'webauthn.system');
                    $this->getApplication()->redirect($result);

                    return;

                default:
                    Log::add("Callback complete, returning JSON.", Log::DEBUG, 'webauthn.system');
                    echo json_encode($result);

                    break;
            }

            $this->getApplication()->close(200);
        }

        Log::add("Null response from AJAX callback, redirecting to $returnURL", Log::DEBUG, 'webauthn.system');
        $this->getApplication()->getSession()->set('plg_system_webauthn.returnUrl', null);

        $this->getApplication()->redirect($returnURL);
    }
}
