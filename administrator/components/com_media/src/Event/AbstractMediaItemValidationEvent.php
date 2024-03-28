<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Event;

use Joomla\CMS\Event\AbstractImmutableEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event to validate media items.
 *
 * @since  4.1.0
 */
abstract class AbstractMediaItemValidationEvent extends AbstractImmutableEvent
{
    /**
     * Validate $item to have all attributes with a valid type.
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
     * Properties generated:
     * - created_date_formatted:  DATE_FORMAT_LC5 formatted string based on create_date
     * - modified_date_formatted: DATE_FORMAT_LC5 formatted string based on modified_date
     *
     * @param   \stdClass  $item  The item to set
     *
     * @return  void
     *
     * @since   4.1.0
     *
     * @throws \BadMethodCallException
     */
    protected function validate(\stdClass $item): void
    {
        // Only "dir" or "file" is allowed
        if (!isset($item->type) || ($item->type !== 'dir' && $item->type !== 'file')) {
            throw new \BadMethodCallException("Property 'type' of argument 'item' of event {$this->name} has a wrong item. Valid: 'dir' or 'file'");
        }

        // Non empty string
        if (!isset($item->name) || !\is_string($item->name) || trim($item->name) === '') {
            throw new \BadMethodCallException("Property 'name' of argument 'item' of event {$this->name} has a wrong item. Valid: non empty string");
        }

        // Non empty string
        if (!isset($item->path) || !\is_string($item->path) || trim($item->path) === '') {
            throw new \BadMethodCallException("Property 'path' of argument 'item' of event {$this->name} has a wrong item. Valid: non empty string");
        }

        // A string
        if ($item->type === 'file' && (!isset($item->extension) || !\is_string($item->extension))) {
            throw new \BadMethodCallException("Property 'extension' of argument 'item' of event {$this->name} has a wrong item. Valid: string");
        }

        // An empty string or an integer
        if (
            !isset($item->size) ||
            (!\is_int($item->size) && !\is_string($item->size)) ||
            (\is_string($item->size) && $item->size !== '')
        ) {
            throw new \BadMethodCallException("Property 'size' of argument 'item' of event {$this->name} has a wrong item. Valid: empty string or integer");
        }

        // A string
        if (!isset($item->mime_type) || !\is_string($item->mime_type)) {
            throw new \BadMethodCallException("Property 'mime_type' of argument 'item' of event {$this->name} has a wrong item. Valid: string");
        }

        // An integer
        if (!isset($item->width) || !\is_int($item->width)) {
            throw new \BadMethodCallException("Property 'width' of argument 'item' of event {$this->name} has a wrong item. Valid: integer");
        }

        // An integer
        if (!isset($item->height) || !\is_int($item->height)) {
            throw new \BadMethodCallException("Property 'height' of argument 'item' of event {$this->name} has a wrong item. Valid: integer");
        }

        // A string
        if (!isset($item->create_date) || !\is_string($item->create_date)) {
            throw new \BadMethodCallException("Property 'create_date' of argument 'item' of event {$this->name} has a wrong item. Valid: string");
        }

        // A string
        if (!isset($item->create_date_formatted) || !\is_string($item->create_date_formatted)) {
            throw new \BadMethodCallException("Property 'create_date_formatted' of argument 'item' of event {$this->name} has a wrong item. Valid: string");
        }

        // A string
        if (!isset($item->modified_date) || !\is_string($item->modified_date)) {
            throw new \BadMethodCallException("Property 'modified_date' of argument 'item' of event {$this->name} has a wrong item. Valid: string");
        }

        // A string
        if (!isset($item->modified_date_formatted) || !\is_string($item->modified_date_formatted)) {
            throw new \BadMethodCallException("Property 'modified_date_formatted' of argument 'item' of event {$this->name} has a wrong item. Valid: string");
        }
    }
}
