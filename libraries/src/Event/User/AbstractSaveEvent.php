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
 * Base class for User save event
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class AbstractSaveEvent extends UserEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  __DEPLOY_VERSION__
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['subject', 'isNew'];

    /**
     * Setter for the subject argument.
     *
     * @param   array  $value  The value to set
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setSubject(array $value): array
    {
        return $value;
    }

    /**
     * Setter for the isNew argument.
     *
     * @param   bool  $value  The value to set
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setIsNew($value): bool
    {
        return (bool) $value;
    }

    /**
     * Getter for the user.
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getUser(): array
    {
        return $this->arguments['subject'];
    }

    /**
     * Getter for the isNew state.
     *
     * @return  boolean
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getIsNew(): bool
    {
        return $this->arguments['isNew'];
    }
}
