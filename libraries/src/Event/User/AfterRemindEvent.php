<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\User;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for User event.
 * Example:
 *  new AfterRemindEvent('onEventName', ['subject' => $user]);
 *
 * @since  __DEPLOY_VERSION__
 */
class AfterRemindEvent extends UserEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  __DEPLOY_VERSION__
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['subject'];

    /**
     * Setter for the subject argument.
     *
     * @param   object  $value  The value to set
     *
     * @return  object
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setSubject(object $value): object
    {
        return $value;
    }

    /**
     * Getter for the user.
     *
     * @return  object
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getUser(): object
    {
        return $this->arguments['subject'];
    }
}
