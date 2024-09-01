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
 * Class for Joomla Update events
 *
 * @since  __DEPLOY_VERSION__
 */
class AfterJoomlaUpdateEvent extends AbstractJoomlaUpdateEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  __DEPLOY_VERSION__
     * @deprecated __DEPLOY_VERSION__ will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['oldVersion'];

    /**
     * Pre-setter for the oldVersion argument.
     *
     * @param   ?string  $value  The value to set
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function onSetOldVersion(?string $value): string
    {
        return $value ?? '';
    }

    /**
     * Getter for the oldVersion.
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getOldVersion(): string
    {
        return $this->arguments['oldVersion'] ?? '';
    }
}
