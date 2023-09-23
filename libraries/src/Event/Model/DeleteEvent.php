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
abstract class DeleteEvent extends ModelEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.0.0
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['context', 'subject'];

    /**
     * Setter for the subject argument.
     *
     * @param   object  $value  The value to set
     *
     * @return  object
     *
     * @since  5.0.0
     */
    protected function onSetSubject(object $value): object
    {
        return $value;
    }

    /**
     * Getter for the item.
     *
     * @return  object
     *
     * @since  5.0.0
     */
    public function getItem(): object
    {
        return $this->arguments['subject'];
    }
}
