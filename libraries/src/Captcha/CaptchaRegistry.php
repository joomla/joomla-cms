<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Captcha;

use Joomla\CMS\Captcha\Exception\CaptchaNotFoundException;
use Joomla\CMS\Event\Captcha\CaptchaSetupEvent;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Captcha Registry class
 * @since   5.0.0
 */
final class CaptchaRegistry implements DispatcherAwareInterface
{
    use DispatcherAwareTrait;

    /**
     * List of registered elements
     *
     * @var    CaptchaProviderInterface[]
     * @since   5.0.0
     */
    private $registry = [];

    /**
     * Internal flag of initialisation
     *
     * @var    boolean
     * @since   5.0.0
     */
    private $initialised = false;

    /**
     * Return list of all registered elements
     *
     * @return CaptchaProviderInterface[]
     * @since    5.0.0
     */
    public function getAll(): array
    {
        return array_values($this->registry);
    }

    /**
     * Check whether the element exists in the registry.
     *
     * @param   string  $name  Element name
     *
     * @return  bool
     * @since    5.0.0
     */
    public function has(string $name): bool
    {
        return !empty($this->registry[$name]);
    }

    /**
     * Return element by name.
     *
     * @param   string  $name  Element name
     *
     * @return  CaptchaProviderInterface
     * @throws  CaptchaNotFoundException
     * @since    5.0.0
     */
    public function get(string $name): CaptchaProviderInterface
    {
        if (empty($this->registry[$name])) {
            throw new CaptchaNotFoundException(\sprintf('Captcha element "%s" not found in the registry.', $name));
        }

        return $this->registry[$name];
    }

    /**
     * Register element in registry, add new or override existing.
     *
     * @param   CaptchaProviderInterface $instance
     *
     * @return  static
     * @since    5.0.0
     */
    public function add(CaptchaProviderInterface $instance)
    {
        $this->registry[$instance->getName()] = $instance;

        return $this;
    }

    /**
     * Trigger event to allow register the element through plugins.
     *
     * @return  static
     * @since   5.0.0
     */
    public function initRegistry()
    {
        if (!$this->initialised) {
            $this->initialised = true;

            $event = new CaptchaSetupEvent('onCaptchaSetup', ['subject' => $this]);
            $this->getDispatcher()->dispatch($event->getName(), $event);
        }

        return $this;
    }
}
