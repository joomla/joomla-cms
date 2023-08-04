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
 * Class for User delete event.
 * Example:
 *  new AfterDeleteEvent('onEventName', ['subject' => $userArray, 'deletingResult' => $result, 'errorMessage' => $errorStr]);
 *
 * @since  __DEPLOY_VERSION__
 */
class AfterDeleteEvent extends AbstractDeleteEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  __DEPLOY_VERSION__
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['subject', 'deletingResult', 'errorMessage'];

    /**
     * Setter for the deletingResult argument.
     *
     * @param   bool  $value  The value to set
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setDeletingResult(bool $value): bool
    {
        return $value;
    }

    /**
     * Setter for the errorMessage argument.
     *
     * @param   string  $value  The value to set
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setErrorMessage(string $value): string
    {
        return $value;
    }

    /**
     * Getter for the deleting result.
     *
     * @return  bool
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getDeletingResult(): bool
    {
        return $this->arguments['deletingResult'];
    }

    /**
     * Getter for the error message.
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getErrorMessage(): string
    {
        return $this->arguments['errorMessage'];
    }
}
