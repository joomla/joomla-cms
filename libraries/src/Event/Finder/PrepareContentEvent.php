<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Finder;

use Joomla\Component\Finder\Administrator\Indexer\Result;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Finder events.
 * Example:
 *  new PrepareContentEvent('onEventName', ['subject' => $item]);
 *
 * @since  5.0.0
 */
class PrepareContentEvent extends AbstractFinderEvent
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
     * @param   Result  $value  The value to set
     *
     * @return  Result
     *
     * @since  5.0.0
     */
    protected function onSetSubject(Result $value): Result
    {
        return $value;
    }

    /**
     * Getter for the item.
     *
     * @return  Result
     *
     * @since  5.0.0
     */
    public function getItem(): Result
    {
        return $this->arguments['subject'];
    }
}
