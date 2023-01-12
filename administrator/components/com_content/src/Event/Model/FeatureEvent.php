<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Component\Content\Administrator\Event\Model;

use BadMethodCallException;
use Joomla\CMS\Event\AbstractImmutableEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event class for WebAsset events
 *
 * @since  4.0.0
 */
class FeatureEvent extends AbstractImmutableEvent
{
    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  BadMethodCallException
     *
     * @since   4.0.0
     */
    public function __construct($name, array $arguments = [])
    {
        if (!isset($arguments['extension'])) {
            throw new BadMethodCallException("Argument 'extension' of event $this->name is required but has not been provided");
        }

        if (!isset($arguments['extension']) || !is_string($arguments['extension'])) {
            throw new BadMethodCallException("Argument 'extension' of event $this->name is not of type 'string'");
        }

        if (strpos($arguments['extension'], '.') === false) {
            throw new BadMethodCallException("Argument 'extension' of event $this->name has wrong format. Valid format: 'component.section'");
        }

        if (!\array_key_exists('extensionName', $arguments) || !\array_key_exists('section', $arguments)) {
            $parts = explode('.', $arguments['extension']);

            $arguments['extensionName'] = $arguments['extensionName'] ?? $parts[0];
            $arguments['section']       = $arguments['section'] ?? $parts[1];
        }

        if (!isset($arguments['pks']) || !is_array($arguments['pks'])) {
            throw new BadMethodCallException("Argument 'pks' of event $this->name is not of type 'array'");
        }

        if (!isset($arguments['value']) || !is_numeric($arguments['value'])) {
            throw new BadMethodCallException("Argument 'value' of event $this->name is not of type 'numeric'");
        }

        $arguments['value'] = (int) $arguments['value'];

        if ($arguments['value'] !== 0 && $arguments['value'] !== 1) {
            throw new BadMethodCallException("Argument 'value' of event $this->name is not 0 or 1");
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Set used parameter to true
     *
     * @param   bool  $value  The value to set
     *
     * @return void
     *
     * @since   4.0.0
     */
    public function setAbort(string $reason)
    {
        $this->arguments['abort'] = true;
        $this->arguments['abortReason'] = $reason;
    }
}
