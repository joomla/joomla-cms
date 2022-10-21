<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\HTML\HTMLHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML utility class for creating a sortable table list
 *
 * @since  3.0
 * @deprecated  5.0  Sortable List will be deprecated in favour of a new dragula script in 4.0
 */
abstract class SortableList
{
    /**
     * Method to load the Sortable script and make table sortable
     *
     * @param   string   $tableId                 DOM id of the table
     * @param   string   $formId                  DOM id of the form
     * @param   string   $sortDir                 Sort direction
     * @param   string   $saveOrderingUrl         Save ordering url, ajax-load after an item dropped
     * @param   boolean  $proceedSaveOrderButton  Set whether a save order button is displayed
     * @param   boolean  $nestedList              Set whether the list is a nested list
     *
     * @return  void
     *
     * @since   3.0
     * @deprecated  5.0  In Joomla 4 call JHtml::_('draggablelist.draggable') and add a class of js-draggable to the tbody element of the table
     */
    public static function sortable($tableId, $formId, $sortDir = 'asc', $saveOrderingUrl = null, $proceedSaveOrderButton = true, $nestedList = false)
    {
        HTMLHelper::_('draggablelist.draggable', $tableId, $formId, $sortDir, $saveOrderingUrl, $proceedSaveOrderButton, $nestedList);
    }
}
