<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Association;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Association Extension Interface for the helper classes
 *
 * @since  3.7.0
 */
interface AssociationExtensionInterface
{
    /**
     * Checks if the extension supports associations
     *
     * @return  boolean  Supports the extension associations
     *
     * @since   3.7.0
     */
    public function hasAssociationsSupport();

    /**
     * Method to get the associations for a given item.
     *
     * @param   integer  $id    Id of the item
     * @param   string   $view  Name of the view
     *
     * @return  array   Array of associations for the item
     *
     * @since  4.0.0
     */
    public function getAssociationsForItem($id = 0, $view = null);
}
