<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Module;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Module events
 *
 * @since  5.0.0
 */
abstract class ModuleListEvent extends ModuleEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.0.0
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['subject'];

    /**
     * Setter for the subject argument.
     *
     * @param   array|\ArrayAccess  $value  The value to set
     *
     * @return  array|\ArrayAccess
     *
     * @since  5.0.0
     */
    protected function setSubject(array|\ArrayAccess $value): array|\ArrayAccess
    {
        return $value;
    }

    /**
     * Getter for the subject argument.
     *
     * @return  array|\ArrayAccess
     *
     * @since  5.0.0
     */
    public function getModules(): array|\ArrayAccess
    {
        return $this->arguments['subject'];
    }
}
