<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Menu;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\MVC\Model\BaseModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for menu events
 *
 * @since  5.0.0
 */
class AfterGetMenuTypeOptionsEvent extends AbstractImmutableEvent
{
    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     *
     * @since   5.0.0
     */
    public function __construct($name, array $arguments = [])
    {
        parent::__construct($name, $arguments);

        if (!\array_key_exists('items', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'items' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('subject', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'subject' of event {$name} is required but has not been provided");
        }

        if (key($arguments) === 0) {
            $this->arguments['items'] = $arguments[0];
        } elseif (\array_key_exists('items', $arguments)) {
            $this->arguments['items'] = $arguments['items'];
        }
    }

    /**
     * Setter for the subject argument.
     *
     * @param   BaseModel  $value  The value to set
     *
     * @return  BaseModel
     *
     * @since  5.0.0
     */
    protected function onSetSubject(BaseModel $value): BaseModel
    {
        return $value;
    }

    /**
     * Setter for the items argument.
     *
     * @param   array  $value  The value to set
     *
     * @return  array
     *
     * @since  5.0.0
     */
    protected function onSetItems(array $value): array
    {
        return $value;
    }

    /**
     * Getter for the model.
     *
     * @return  BaseModel
     *
     * @since  5.0.0
     */
    public function getModel(): BaseModel
    {
        return $this->arguments['subject'];
    }

    /**
     * Getter for the items.
     *
     * @return  array
     *
     * @since  5.0.0
     */
    public function getItems(): array
    {
        return $this->arguments['items'];
    }

    /**
     * Update the items.
     *
     * @param   array  $value  The value to set
     *
     * @return  static
     *
     * @since  5.0.0
     */
    public function updateItems(array $value): static
    {
        $this->arguments['items'] = $this->onSetItems($value);

        return $this;
    }
}
