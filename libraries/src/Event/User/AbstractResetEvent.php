<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\User;

\defined('_JEXEC') or die;

/**
 * Class for User reset event.
 *
 * @since  5.2.0
 */
abstract class AbstractResetEvent extends UserEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.2.0
     * @deprecated 5.2.0 will be removed in 7.0
     */
    protected $legacyArgumentsOrder = ['subject'];

    /**
     * Setter for the subject argument.
     *
     * @param   object  $value  The value to set
     *
     * @return  object
     *
     * @since  5.2.0
     */
    protected function onSetSubject(object $value): object
    {
        return $value;
    }

    /**
     * Getter for the user.
     *
     * @return  object
     *
     * @since  5.2.0
     */
    public function getUser(): object
    {
        return $this->arguments['subject'];
    }
}
