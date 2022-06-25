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

/**
 * Event object to set an url.
 *
 * @since  4.1.0
 */
final class FetchMediaItemUrlEvent extends AbstractImmutableEvent
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
        // Check for required arguments
        if (!\array_key_exists('adapter', $arguments) || !is_string($arguments['adapter'])) {
            throw new \BadMethodCallException("Argument 'adapter' of event $name is not of the expected type");
        }

        $this->arguments[$arguments['adapter']] = $arguments['adapter'];
        unset($arguments['adapter']);

        // Check for required arguments
        if (!\array_key_exists('path', $arguments) || !is_string($arguments['path'])) {
            throw new \BadMethodCallException("Argument 'path' of event $name is not of the expected type");
        }

        $this->arguments[$arguments['path']] = $arguments['path'];
        unset($arguments['path']);

        // Check for required arguments
        if (!\array_key_exists('url', $arguments) || !is_string($arguments['url'])) {
                throw new \BadMethodCallException("Argument 'url' of event $name is not of the expected type");
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Validate $value to be a string
     *
     * @param   string  $value  The value to set
     *
     * @return string
     *
     * @since   4.1.0
     */
    protected function setUrl(string $value): string
    {
        return $value;
    }

    /**
     * Forbid setting $path
     *
     * @param   string  $value  The value to set
     *
     * @since   4.1.0
     *
     * @throws \BadMethodCallException
     */
    protected function setPath(string $value): string
    {
        throw new \BadMethodCallException('Cannot set the argument "path" of the immutable event ' . $this->name . '.');
    }

    /**
     * Forbid setting $path
     *
     * @param   string  $value  The value to set
     *
     * @since   4.1.0
     *
     * @throws \BadMethodCallException
     */
    protected function setAdapter(string $value): string
    {
        throw new \BadMethodCallException('Cannot set the argument "adapter" of the immutable event ' . $this->name . '.');
    }
}
