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
 * @since  __DEPLOY_VERSION__
 */
class BeforeUninstallEvent extends AbstractExtensionEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  __DEPLOY_VERSION__
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['eid'];

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
        parent::__construct($name, $arguments);

        if (!\array_key_exists('eid', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'eid' of event {$name} is required but has not been provided");
        }
    }

    /**
     * Setter for the eid argument.
     *
     * @param   integer  $value  The value to set
     *
     * @return  integer
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setEid(int $value): int
    {
        return $value;
    }

    /**
     * Getter for the eid.
     *
     * @return  integer
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getEid(): int
    {
        return $this->arguments['eid'];
    }
}
