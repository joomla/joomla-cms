<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Editor\Button;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
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
     * Button options.
     *
     * @return array
     * @since   __DEPLOY_VERSION__
     */
    protected $options = [];

    /**
     * Class constructor;
     *
     * @param string $name    The button name
     * @param array  $props   The button properties.
     * @param array  $options The button options.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(string $name, array $props = [], array $options = [])
    {
        $this->name    = $name;
        $this->props   = $props;
        $this->options = $options;
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
        if ($name === 'options') {
            @trigger_error(
                'Accessing options property is deprecated. To access the Button options use getOptions() method.',
                \E_USER_DEPRECATED
            );

            return $this->getOptions();
        }

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
        if ($name === 'options') {
            @trigger_error(
                'Accessing options property is deprecated. To set the Button options use setOptions() method.',
                \E_USER_DEPRECATED
            );

            return $this->setOptions($value);
        }

        $this->props[$name] = $value;

        return $this;
    }

    /**
     * Return Button options.
     *
     * @return array
     * @since   __DEPLOY_VERSION__
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set Button options.
     *
     * @param array  $options The button options.
     *
     * @return ButtonInterface
     * @since   __DEPLOY_VERSION__
     */
    public function setOptions(array $options): ButtonInterface
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Magic method to access a property.
     *
     * @param  string  $name  The name of the property.
     *
     * @return string|null A value if the property name is valid, null otherwise.
     *
     * @since       __DEPLOY_VERSION__
     * @deprecated  6.0  This is a B/C proxy for deprecated read accesses
     */
    public function __get($name)
    {
        @trigger_error('Property access is deprecated in Joomla\CMS\Editor\Button class, use get/set methods.', \E_USER_DEPRECATED);

        return $this->get($name);
    }

    /**
     * Magic method to access property.
     *
     * @param  string  $name   The name of the property.
     * @param  mixed   $value  The value of the property.
     *
     * @return void
     *
     * @since       __DEPLOY_VERSION__
     * @deprecated  6.0  This is a B/C proxy for deprecated write accesses
     */
    public function __set($name, $value)
    {
        @trigger_error('Property access is deprecated in Joomla\CMS\Editor\Button class, use get/set methods.', \E_USER_DEPRECATED);

        $this->set($name, $value);
    }
}
