<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Association;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The association service.
 *
 * @since  4.0.0
 */
interface AssociationServiceInterface
{
    /**
     * Returns the associations extension helper class.
     *
     * @return  AssociationExtensionInterface
     *
     * @since  4.0.0
     */
    public function getAssociationsExtension(): AssociationExtensionInterface;
}
