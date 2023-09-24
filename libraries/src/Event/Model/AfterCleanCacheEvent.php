<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Model;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\ReshapeArgumentsAware;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Clean cache events
 *
 * @since  5.0.0
 */
class AfterCleanCacheEvent extends AbstractImmutableEvent
{
    use ReshapeArgumentsAware;

    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.0.0
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['defaultgroup', 'cachebase', 'result'];

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
        // Reshape the arguments array to preserve b/c with legacy listeners
        if ($this->legacyArgumentsOrder) {
            $arguments = $this->reshapeArguments($arguments, $this->legacyArgumentsOrder);
        }

        parent::__construct($name, $arguments);

        if (!\array_key_exists('defaultgroup', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'defaultgroup' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('cachebase', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'cachebase' of event {$name} is required but has not been provided");
        }

        if (!\array_key_exists('result', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'result' of event {$name} is required but has not been provided");
        }
    }

    /**
     * Setter for the defaultgroup argument.
     *
     * @param   string  $value  The value to set
     *
     * @return  string
     *
     * @since  5.0.0
     */
    protected function onSetDefaultgroup(string $value): string
    {
        return $value;
    }

    /**
     * Setter for the cachebase argument.
     *
     * @param   string  $value  The value to set
     *
     * @return  string
     *
     * @since  5.0.0
     */
    protected function onSetCachebase(string $value): string
    {
        return $value;
    }

    /**
     * Setter for the result argument.
     *
     * @param   bool  $value  The value to set
     *
     * @return  bool
     *
     * @since  5.0.0
     */
    protected function onSetResult(bool $value): bool
    {
        return $value;
    }

    /**
     * Getter for the defaultgroup.
     *
     * @return  string
     *
     * @since  5.0.0
     */
    public function getDefaultGroup(): string
    {
        return $this->arguments['defaultgroup'];
    }

    /**
     * Getter for the cachebase.
     *
     * @return  string
     *
     * @since  5.0.0
     */
    public function getCacheBase(): string
    {
        return $this->arguments['cachebase'];
    }

    /**
     * Getter for the result.
     *
     * @return  bool
     *
     * @since  5.0.0
     */
    public function getResult(): bool
    {
        return $this->arguments['result'];
    }
}
