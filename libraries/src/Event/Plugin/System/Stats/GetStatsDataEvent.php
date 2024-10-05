<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Plugin\System\Stats;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\ReshapeArgumentsAware;
use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeArrayAware;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Stats plugin.
 * Example:
 *   new GetStatsDataEvent('onEventName', ['context' => 'com_example.example']);
 *
 * @since  __DEPLOY_VERSION__
 */
class GetStatsDataEvent extends AbstractImmutableEvent implements ResultAwareInterface
{
    use ReshapeArgumentsAware;
    use ResultAware;
    use ResultTypeArrayAware;

    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  __DEPLOY_VERSION__
     * @deprecated __DEPLOY_VERSION__ will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['context'];

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
        if ($this->legacyArgumentsOrder) {
            $arguments = $this->reshapeArguments($arguments, $this->legacyArgumentsOrder);
        }

        parent::__construct($name, $arguments);

        if (!\array_key_exists('context', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'context' of event {$name} is required but has not been provided");
        }
    }

    /**
     * Setter for the context argument.
     *
     * @param   string  $value  The value to set
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function onSetContext(string $value): string
    {
        return $value;
    }

    /**
     * Getter for the context argument.
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getContext(): string
    {
        return $this->arguments['context'];
    }
}
