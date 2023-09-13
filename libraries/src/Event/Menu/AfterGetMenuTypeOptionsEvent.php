<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Menu;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\ReshapeArgumentsAware;
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
    use ReshapeArgumentsAware;

    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.0.0
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['items', 'subject'];

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
        // Reshape the arguments array to preserve b/c with legacy listeners
        if ($this->legacyArgumentsOrder) {
            $arguments = $this->reshapeArguments($arguments, $this->legacyArgumentsOrder);
        }

        parent::__construct($name, $arguments);

        if (!\array_key_exists('items', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'items' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('subject', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'subject' of event {$name} is required but has not been provided");
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
    protected function setSubject(BaseModel $value): BaseModel
    {
        return $value;
    }

    /**
     * Setter for the items argument.
     *
     * @param   array|\ArrayAccess  $value  The value to set
     *
     * @return  array|\ArrayAccess
     *
     * @since  5.0.0
     */
    protected function setItems(array|\ArrayAccess $value): array|\ArrayAccess
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
     * @return  array|\ArrayAccess
     *
     * @since  5.0.0
     */
    public function getItems(): array|\ArrayAccess
    {
        return $this->arguments['items'];
    }
}
