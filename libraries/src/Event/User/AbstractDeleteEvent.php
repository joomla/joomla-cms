<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\User;

\defined('_JEXEC') or die;

/**
 * Base class for User delete event
 *
 * @since  5.0.0
 */
abstract class AbstractDeleteEvent extends UserEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.0.0
     * @deprecated 5.0 will be removed in 7.0
     */
    protected $legacyArgumentsOrder = ['subject'];

    /**
     * Setter for the subject argument.
     *
     * @param   array  $value  The value to set
     *
     * @return  array
     *
     * @since  5.0.0
     */
    protected function onSetSubject(array $value): array
    {
        return $value;
    }

    /**
     * Getter for the user.
     *
     * @return  array
     *
     * @since  5.0.0
     */
    public function getUser(): array
    {
        return $this->arguments['subject'];
    }
}
