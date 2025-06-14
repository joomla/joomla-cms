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
     * @since   5.0.0
     */
    protected $name;

    /**
     * Button properties.
     *
     * @return array
     * @since   5.0.0
     */
    protected $props = [];

    /**
     * Button options.
     *
     * @return array
     * @since   5.0.0
     */
    protected $options = [];

    /**
     * Class constructor;
     *
     * @param string $name    The button name
     * @param array  $props   The button properties.
     * @param array  $options The button options.
     *
     * @since   5.0.0
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
     * @since   5.0.0
     */
    public function getButtonName(): string
    {
        return $this->name;
    }

    /**
     * Return Button property.
     *
     * @param string $name    Property name
     * @param string $default Default value
     *
     * @return mixed
     * @since   5.0.0
     */
    public function get(string $name, $default = null)
    {
        if ($name === 'options') {
            @trigger_error(
                'Accessing options property is deprecated. To access the Button options use getOptions() method.',
                \E_USER_DEPRECATED
            );

            return $this->getOptions();
        }

        return \array_key_exists($name, $this->props) ? $this->props[$name] : $default;
    }

    /**
     * Set Button property.
     *
     * @param string $name  Property name
     * @param mixed  $value Property value
     *
     * @return ButtonInterface
     * @since   5.0.0
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
     * @since   5.0.0
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
     * @since   5.0.0
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
     * @since       5.0.0
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
     * @since       5.0.0
     * @deprecated  6.0  This is a B/C proxy for deprecated write accesses
     */
    public function __set($name, $value)
    {
        @trigger_error('Property access is deprecated in Joomla\CMS\Editor\Button class, use get/set methods.', \E_USER_DEPRECATED);

        $this->set($name, $value);
    }
}
