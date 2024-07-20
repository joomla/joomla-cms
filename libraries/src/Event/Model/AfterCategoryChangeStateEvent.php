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
 * Class for Model event.
 * Example:
 *  new AfterCategoryChangeStateEvent('onEventName', ['context' => $extension, 'subject' => $primaryKeys, 'value' => $newState]);
 *
 * @since  5.0.0
 */
class AfterCategoryChangeStateEvent extends ChangeStateEvent
{
    /**
     * Getter for the extension.
     *
     * @return  string
     *
     * @since  5.0.0
     */
    public function getExtension(): string
    {
        return $this->getContext();
    }
}
