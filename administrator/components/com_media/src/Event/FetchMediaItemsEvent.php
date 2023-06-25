<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Event;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event object for fetch media items.
 *
 * @since  4.1.0
 */
final class FetchMediaItemsEvent extends AbstractMediaItemValidationEvent
{
    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     *
     * @since  4.1.0
     */
    public function __construct($name, array $arguments = [])
    {
        parent::__construct($name, $arguments);

        // Check for required arguments
        if (!\array_key_exists('items', $arguments) || !is_array($arguments['items'])) {
            throw new \BadMethodCallException("Argument 'items' of event $name is not of the expected type");
        }
    }

    /**
     * Validate $item to be an array
     *
     * @param   array  $items  The value to set
     *
     * @return array
     *
     * @since   4.1.0
     */
    protected function setItems(array $items): array
    {
        $result = [];

        foreach ($items as $item) {
            $clone = clone $item;

            $this->validate($clone);

            $result[] = $clone;
        }

        return $result;
    }

    /**
     * Returns the items.
     *
     * @param   array  $items  The value to set
     *
     * @return array
     *
     * @since   4.1.0
     */
    protected function getItems(array $items): array
    {
        $result = [];

        foreach ($items as $item) {
            $result[] = clone $item;
        }

        return $result;
    }
}
