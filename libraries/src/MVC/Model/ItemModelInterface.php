<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface for an item model.
 *
 * @since  4.0.0
 */
interface ItemModelInterface
{
    /**
     * Method to get an item.
     *
     * @param   integer  $pk  The id of the item
     *
     * @return  object
     *
     * @since 4.0.0
     * @throws \Exception
     */
    public function getItem($pk = null);
}
