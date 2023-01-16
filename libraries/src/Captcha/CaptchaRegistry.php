<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Captcha;

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;

/**
 * Captcha Registry class
 * @since   __DEPLOY_VERSION__
 */
class CaptchaRegistry implements DispatcherAwareInterface
{
    use DispatcherAwareTrait;

    /**
     * List of registered elements
     *
     * @var    CaptchaProviderInterface[]
     * @since   __DEPLOY_VERSION__
     */
    private array $registry = [];

    /**
     * Internal flag of initialisation
     *
     * @var    boolean
     * @since   __DEPLOY_VERSION__
     */
    private bool $initialised = false;

    /**
     * Return list of all registered elements
     *
     * @return CaptchaProviderInterface[]
     * @since    __DEPLOY_VERSION__
     */
    public function getElements(): array
    {
        $this->initRegistry();

        return $this->registry;
    }

    /**
     * Return element by name.
     *
     * @param   string  $name  Element name
     *
     * @return  false|CaptchaProviderInterface
     * @since    __DEPLOY_VERSION__
     */
    public function getElement(string $name)
    {
        $this->initRegistry();

        return $this->registry[$name] ?? false;
    }

    /**
     * Register element in registry, add new or override existing.
     *
     * @param   string  $name
     * @param   CaptchaProviderInterface $instance
     *
     * @return  static
     * @since    __DEPLOY_VERSION__
     */
    public function registerElement(string $name, CaptchaProviderInterface $instance)
    {
        $this->registry[$name] = $instance;

        return $this;
    }

    /**
     * Trigger event to allow register the element through plugins.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function initRegistry()
    {
        if (!$this->initialised) {
            PluginHelper::importPlugin('captcha');

            $event = new CaptchaSetupEvent('onCaptchaSetup', ['subject' => $this]);
            $this->getDispatcher()->dispatch($event->getName(), $event);
        }

        $this->initialised = true;
    }
}
