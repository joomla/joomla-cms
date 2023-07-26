<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Content;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Content event
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class ContentChangeStateEvent extends ContentEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  __DEPLOY_VERSION__
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['context', 'subject', 'value'];

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
     * Setter for the value argument.
     *
     * @param   int  $value  The value to set
     *
     * @return  int
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function setValue($value): int
    {
        return (int) $value;
    }

    /**
     * Getter for the list of primary keys.
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getPks(): array
    {
        return $this->arguments['subject'];
    }

    /**
     * Getter for the value state.
     *
     * @return  integer
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getValue(): int
    {
        return $this->arguments['value'];
    }
}
