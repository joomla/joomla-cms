<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Model event
 *
 * @since  5.0.0
 */
abstract class ChangeStateEvent extends ModelEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.0.0
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
     * @since  5.0.0
     */
    protected function onSetSubject(array $value): array
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
     * @since  5.0.0
     */
    protected function onSetValue($value): int
    {
        return (int) $value;
    }

    /**
     * Getter for the list of primary keys.
     *
     * @return  array
     *
     * @since  5.0.0
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
     * @since  5.0.0
     */
    public function getValue(): int
    {
        return $this->arguments['value'];
    }
}
