<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Captcha.invisible_recaptcha
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Captcha\InvisibleReCaptcha\Extension;

use Joomla\CMS\Captcha\CaptchaRegistry;
use Joomla\CMS\Event\Captcha\CaptchaSetupEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\Captcha\InvisibleReCaptcha\Provider\InvisibleReCaptchaProvider;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Invisible reCAPTCHA Plugin.
 *
 * @since  3.9.0
 */
final class InvisibleReCaptcha extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onCaptchaSetup' => 'onCaptchaSetup',
            'onPrivacyCollectAdminCapabilities' => 'onPrivacyCollectAdminCapabilities',
        ];
    }

    /**
     * Register Captcha instance
     *
     * @param CaptchaSetupEvent $event
     *
     * @return void
     * @since   __DEPLOY_VERSION__
     */
    public function onCaptchaSetup(CaptchaSetupEvent $event)
    {
        $this->loadLanguage();

        /** @var CaptchaRegistry $subject */
        $subject = $event['subject'];

        $subject->add(new InvisibleReCaptchaProvider($this->params, $this->getApplication()));
    }

    /**
     * Reports the privacy related capabilities for this plugin to site administrators.
     *
     * @return  array
     *
     * @since   3.9.0
     */
    public function onPrivacyCollectAdminCapabilities()
    {
        $this->loadLanguage();

        return array(
            Text::_('PLG_CAPTCHA_RECAPTCHA_INVISIBLE') => array(
                Text::_('PLG_RECAPTCHA_INVISIBLE_PRIVACY_CAPABILITY_IP_ADDRESS'),
            ),
        );
    }
}
