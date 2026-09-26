<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML utility class for creating a sortable table list
 *
 * @since  4.0.0
 */
abstract class DraggableList
{
    /**
     * Array containing information for loaded files
     *
     * @var    array
     * @since  4.0.0
     */
    protected static $loaded = [];

    /**
     * Method to load the Dragula script and make table sortable
     *
     * @param   string   $tableId          DOM id of the table
     * @param   string   $formId           DOM id of the form
     * @param   string   $sortDir          Sort direction
     * @param   string   $saveOrderingUrl  Save ordering url, ajax-load after an item dropped
     * @param   string   $redundant        Not used
     * @param   boolean  $nestedList       Set whether the list is a nested list
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public static function draggable(
        string $tableId = '',
        string $formId = '',
        string $sortDir = 'asc',
        string $saveOrderingUrl = '',
        $redundant = null,
        bool $nestedList = false
    ) {
        // Only load once
        if (isset(static::$loaded[__METHOD__])) {
            return;
        }

        $doc = Factory::getDocument();

        // Please consider using data attributes instead of passing arguments here!
        if (!empty($tableId) && !empty($saveOrderingUrl) && !empty($formId) && !empty($sortDir)) {
            $doc->addScriptOptions(
                'draggable-list',
                [
                    'id'        => '#' . $tableId . ' tbody',
                    'formId'    => $formId,
                    'direction' => $sortDir,
                    'url'       => $saveOrderingUrl . '&' . Session::getFormToken() . '=1',
                    'nested'    => $nestedList,
                ]
            );
        }

        $doc->getWebAssetManager()
            ->usePreset('dragula')
            ->useScript('joomla.draggable');

        // Set static array
        static::$loaded[__METHOD__] = true;
    }
}
