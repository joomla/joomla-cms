<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Captcha.POWCaptcha
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Captcha\POWCaptcha\Extension;

use Joomla\CMS\Event\Captcha\CaptchaSetupEvent;
use Joomla\CMS\Event\Plugin\AjaxEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\Captcha\POWCaptcha\Provider\POWCaptchaProvider;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Proof of work captcha Plugin
 * Based on the ALTCHA captcha library
 *
 * @since __DEPLOY_VERSION__
 */
final class POWCaptcha extends CMSPlugin implements SubscriberInterface
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    public static function getSubscribedEvents(): array
    {
        return [
            'onAjaxPowcaptcha' => 'handleAjaxRequest',
            'onCaptchaSetup'   => 'setupCaptcha',
        ];
    }

    /**
     * Register Captcha instance
     *
     * @param CaptchaSetupEvent $event
     *
     * @return void
     */
    public function setupCaptcha(CaptchaSetupEvent $event)
    {
        $event->getCaptchaRegistry()->add($this->getProvider());
    }

    /**
     * Handles the ajax request triggered by altcha to fetch the challenge code
     *
     * @param AjaxEvent $event
     */
    public function handleAjaxRequest(AjaxEvent $event)
    {
        // Altcha expects its challenge code in a specific syntax that is not compatible with com_ajax, raw output
        @ob_end_clean();
        header('Content-Type: application/json');

        // CRSF Token check
        if (!Session::checkToken('get')) {
            echo json_encode([]);
            $this->getApplication()->close();
        }

        $challenge = $this->getProvider()->getChallenge();

        echo json_encode($challenge);

        $this->getApplication()->close();
    }

    /**
     * Returns the actual captcha provider instance
     *
     * @return POWCaptchaProvider
     */
    protected function getProvider(): POWCaptchaProvider
    {
        return new POWCaptchaProvider(
            $this->params,
            $this->getApplication()
        );
    }
}
