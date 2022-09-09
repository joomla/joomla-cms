<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Tag;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Access to component specific tagging information.
 *
 * @since  4.0.0
 */
interface TagServiceInterface
{
    /**
     * Adds Count Items for Tag Manager.
     *
     * @param   \stdClass[]  $items      The content objects
     * @param   string       $extension  The name of the active view.
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    public function countTagItems(array $items, string $extension);
}
