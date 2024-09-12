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
 * @since  5.2.0
 */
class AfterJoomlaUpdateEvent extends AbstractJoomlaUpdateEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.2.0
     * @deprecated 5.2.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['oldVersion'];

    /**
     * Pre-setter for the oldVersion argument.
     *
     * @param   ?string  $value  The value to set
     *
     * @return  string
     *
     * @since  5.2.0
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
     * @since  5.2.0
     */
    public function getOldVersion(): string
    {
        return $this->arguments['oldVersion'] ?? '';
    }
}
