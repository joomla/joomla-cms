<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Editor\Button;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

final class Button implements ButtonInterface
{
    /**
     * Button name, CMD string.
     *
     * @return string
     * @since   __DEPLOY_VERSION__
     */
    protected $name;

    /**
     * Button properties.
     *
     * @return array
     * @since   __DEPLOY_VERSION__
     */
    protected $props = [];

    /**
     * Class constructor;
     *
     * @param string $name  The button name
     * @param array  $props The button properties.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(string $name, array $props = [])
    {
        $this->name  = $name;
        $this->props = $props;
    }

    /**
     * Return Button name, CMD string.
     *
     * @return string
     * @since   __DEPLOY_VERSION__
     */
    public function getButtonName(): string
    {
        return $this->name;
    }

    /**
     * Return Button property or null.
     *
     * @param string $name Property name
     *
     * @return mixed
     * @since   __DEPLOY_VERSION__
     */
    public function get(string $name)
    {
        return array_key_exists($name, $this->props) ? $this->props[$name] : null;
    }

    /**
     * Set Button property.
     *
     * @param string $name  Property name
     * @param mixed  $value Property value
     *
     * @return ButtonInterface
     * @since   __DEPLOY_VERSION__
     */
    public function set(string $name, $value): ButtonInterface
    {
        $this->props[$name] = $value;

        return $this;
    }
}
