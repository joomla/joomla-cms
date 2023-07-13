<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\WebAsset;

use BadMethodCallException;
use Joomla\CMS\WebAsset\WebAssetItemInterface;
use Joomla\CMS\WebAsset\WebAssetRegistryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event class for WebAssetRegistry "asset changed" events
 *
 * @since  4.0.0
 */
class WebAssetRegistryAssetChanged extends AbstractEvent
{
    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  BadMethodCallException
     *
     * @since  4.0.0
     */
    public function __construct($name, array $arguments = [])
    {
        parent::__construct($name, $arguments);

        // Check for required arguments
        if (!\array_key_exists('asset', $arguments) || !($arguments['asset'] instanceof WebAssetItemInterface)) {
            throw new BadMethodCallException("Argument 'asset' of event $name is not of the expected type");
        }

        if (!\array_key_exists('assetType', $arguments) || !is_string($arguments['assetType'])) {
            throw new BadMethodCallException("Argument 'assetType' of event $name is not of the expected type");
        }

        if (!\array_key_exists('change', $arguments) || !is_string($arguments['change'])) {
            throw new BadMethodCallException("Argument 'change' of event $name is not of the expected type");
        }
    }

    /**
     * Setter for the subject argument
     *
     * @param   WebAssetRegistryInterface  $value  The value to set
     *
     * @return  WebAssetRegistryInterface
     *
     * @throws  BadMethodCallException  if the argument is not of the expected type
     *
     * @since  4.0.0
     */
    protected function setSubject($value)
    {
        if (!$value || !($value instanceof WebAssetRegistryInterface)) {
            throw new BadMethodCallException("Argument 'subject' of event {$this->name} is not of the expected type");
        }

        return $value;
    }

    /**
     * Return modified asset
     *
     * @return  WebAssetItemInterface
     *
     * @since  4.0.0
     */
    public function getAsset(): WebAssetItemInterface
    {
        return $this->arguments['asset'];
    }

    /**
     * Return a type of modified asset
     *
     * @return  string
     *
     * @since  4.0.0
     */
    public function getAssetType(): string
    {
        return $this->arguments['assetType'];
    }

    /**
     * Return a type of changes: new, remove, override
     *
     * @return  string
     *
     * @since  4.0.0
     */
    public function getChange(): string
    {
        return $this->arguments['change'];
    }
}
