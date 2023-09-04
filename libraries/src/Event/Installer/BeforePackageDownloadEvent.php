<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Installer;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\ReshapeArgumentsAware;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Installer events
 *
 * @since  __DEPLOY_VERSION__
 */
class BeforePackageDownloadEvent extends AbstractImmutableEvent
{
    use ReshapeArgumentsAware;

    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  __DEPLOY_VERSION__
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['url', 'headers'];

    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct($name, array $arguments = [])
    {
        // Reshape the arguments array to preserve b/c with legacy listeners
        // Do not override existing $arguments in place, or it will break references!
        if ($this->legacyArgumentsOrder) {
            parent::__construct($name, $this->reshapeArguments($arguments, $this->legacyArgumentsOrder));
        } else {
            parent::__construct($name, $arguments);
        }

        if (!\array_key_exists('url', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'url' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('headers', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'headers' of event {$name} is required but has not been provided");
        }

        // For backward compatibility make sure the value is referenced
        // TODO: Remove in Joomla 6
        // @deprecated: Passing argument by reference is deprecated, and will not work in Joomla 6
        if (key($arguments) === 0) {
            $this->arguments['url'] = &$arguments[0];
        } elseif (\array_key_exists('url', $arguments)) {
            $this->arguments['url'] = &$arguments['url'];
        }
    }

    /**
     * Setter for the url argument.
     *
     * @param   string  $value  The value to set
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setUrl(string $value): string
    {
        return $value;
    }

    /**
     * Setter for the headers argument.
     *
     * @param   array|\ArrayAccess  $value  The value to set
     *
     * @return  array|\ArrayAccess
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setHeaders(array|\ArrayAccess $value): array|\ArrayAccess
    {
        return $value;
    }

    /**
     * Getter for the url.
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getUrl(): string
    {
        return $this->arguments['url'];
    }

    /**
     * Getter for the headers.
     *
     * @return  array|\ArrayAccess
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getHeaders(): array|\ArrayAccess
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
     * @since  __DEPLOY_VERSION__
     */
    public function updateUrl(string $value): static
    {
        $this->arguments['url'] = $value;

        return $this;
    }
}
