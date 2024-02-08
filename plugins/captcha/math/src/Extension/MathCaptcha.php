<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Cpatcha.match
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Captcha\Math\Extension;

use Joomla\CMS\Event\Captcha\CaptchaSetupEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\Captcha\Math\Provider\MathCaptchaProvider;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Math captcha Plugin
 *
 * @since  __DEPLOY_VERSION__
 */
final class MathCaptcha extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this plugin will listen to.
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onCaptchaSetup' => 'onCaptchaSetup',
        ];
    }

    /**
     * Register Captcha instance
     *
     * @param CaptchaSetupEvent $event
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function onCaptchaSetup(CaptchaSetupEvent $event)
    {
        $this->loadLanguage();
        $event->getCaptchaRegistry()->add(new MathCaptchaProvider($this->getApplication()));
    }
}
