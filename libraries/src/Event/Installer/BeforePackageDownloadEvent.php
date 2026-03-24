<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Installer;

use Joomla\CMS\Event\AbstractImmutableEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Installer events
 *
 * @since  5.0.0
 */
class BeforePackageDownloadEvent extends AbstractImmutableEvent
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

        if (!\array_key_exists('url', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'url' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('headers', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'headers' of event {$name} is required but has not been provided");
        }

        if (key($arguments) === 0) {
            $this->arguments['url']     = $arguments[0];
            $this->arguments['headers'] = $arguments[1];
        } elseif (\array_key_exists('url', $arguments)) {
            $this->arguments['url']     = $arguments['url'];
            $this->arguments['headers'] = $arguments['headers'];
        }
    }

    /**
     * Setter for the url argument.
     *
     * @param   string  $value  The value to set
     *
     * @return  string
     *
     * @since  5.0.0
     */
    protected function onSetUrl(string $value): string
    {
        return $value;
    }

    /**
     * Setter for the headers argument.
     *
     * @param   array  $value  The value to set
     *
     * @return  array
     *
     * @since  5.0.0
     */
    protected function onSetHeaders(array $value): array
    {
        return $value;
    }

    /**
     * Getter for the url.
     *
     * @return  string
     *
     * @since  5.0.0
     */
    public function getUrl(): string
    {
        return $this->arguments['url'];
    }

    /**
     * Getter for the headers.
     *
     * @return  array
     *
     * @since  5.0.0
     */
    public function getHeaders(): array
    {
        return $this->arguments['headers'];
    }

    /**
     * Update the url.
     *
     * @param   string  $value  The value to set
     *
     * @return  static
     *
     * @since  5.0.0
     */
    public function updateUrl(string $value): static
    {
        $this->arguments['url'] = $this->onSetUrl($value);

        return $this;
    }

    /**
     * Update the headers.
     *
     * @param   array  $value  The value to set
     *
     * @return  static
     *
     * @since  5.0.0
     */
    public function updateHeaders(array $value): static
    {
        $this->arguments['headers'] = $this->onSetHeaders($value);

        return $this;
    }
}
