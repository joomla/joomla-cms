<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Event;

/**
 * Event object for fetch media item.
 *
 * @since  4.1.0
 */
final class FetchMediaItemEvent extends AbstractMediaItemValidationEvent
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
    public function __construct($name, array $arguments = array())
    {
        parent::__construct($name, $arguments);

        // Check for required arguments
        if (!\array_key_exists('item', $arguments) || !is_object($arguments['item'])) {
            throw new \BadMethodCallException("Argument 'item' of event $name is not of the expected type");
        }
    }

    /**
     * Validate $item to have all attributes with a valid type
     *
     * Validation based on \Joomla\Component\Media\Administrator\Adapter\AdapterInterface::getFile()
     *
     * Properties validated:
     * - type:          The type can be file or dir
     * - name:          The name of the item
     * - path:          The relative path to the root
     * - extension:     The file extension
     * - size:          The size of the file
     * - create_date:   The date created
     * - modified_date: The date modified
     * - mime_type:     The mime type
     * - width:         The width, when available
     * - height:        The height, when available
     *
     * Generation based on \Joomla\Plugin\Filesystem\Local\Adapter\LocalAdapter::getPathInformation()
     *
     * Properties generated:
     * - created_date_formatted:  DATE_FORMAT_LC5 formatted string based on create_date
     * - modified_date_formatted: DATE_FORMAT_LC5 formatted string based on modified_date
     *
     * @param   \stdClass  $item  The item to set
     *
     * @return \stdClass
     *
     * @since   4.1.0
     *
     * @throws \BadMethodCallException
     */
    protected function setItem(\stdClass $item): \stdClass
    {
        // Make immutable object
        $item = clone $item;

        $this->validate($item);

        return $item;
    }
}
