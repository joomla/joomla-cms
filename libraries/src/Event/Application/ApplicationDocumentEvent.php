<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Application;

use Joomla\CMS\Document\Document;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Application's Document events
 *
 * @since  5.0.0
 */
abstract class ApplicationDocumentEvent extends ApplicationEvent
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
        if (!\array_key_exists('document', $arguments)) {
            throw new \BadMethodCallException("Argument 'document' of event {$name} is required but has not been provided");
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Setter for the document argument.
     *
     * @param   Document  $value  The value to set
     *
     * @return  Document
     *
     * @since  5.0.0
     */
    protected function onSetDocument(Document $value): Document
    {
        return $value;
    }

    /**
     * Get the event's document object
     *
     * @return  Document
     *
     * @since  5.0.0
     */
    public function getDocument(): Document
    {
        return $this->arguments['document'];
    }
}
