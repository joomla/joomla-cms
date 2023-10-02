<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Extension;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Extension events
 *
 * @since  5.0.0
 */
class BeforeUpdateEvent extends AbstractExtensionEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.0.0
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['type', 'manifest'];

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

        if (!\array_key_exists('type', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'type' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('manifest', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'manifest' of event {$name} is required but has not been provided");
        }
    }

    /**
     * Setter for the type argument.
     *
     * @param   string  $value  The value to set
     *
     * @return  string
     *
     * @since  5.0.0
     */
    protected function onSetType(string $value): string
    {
        return $value;
    }

    /**
     * Setter for the manifest argument.
     *
     * @param   ?\SimpleXMLElement  $value  The value to set
     *
     * @return  ?\SimpleXMLElement
     *
     * @since  5.0.0
     */
    protected function onSetManifest(?\SimpleXMLElement $value): ?\SimpleXMLElement
    {
        return $value;
    }

    /**
     * Getter for the type.
     *
     * @return  string
     *
     * @since  5.0.0
     */
    public function getType(): string
    {
        return $this->arguments['type'];
    }

    /**
     * Getter for the manifest.
     *
     * @return  ?\SimpleXMLElement
     *
     * @since  5.0.0
     */
    public function getManifest(): ?\SimpleXMLElement
    {
        return $this->arguments['manifest'];
    }
}
