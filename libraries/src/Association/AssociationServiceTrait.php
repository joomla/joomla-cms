<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Association;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait to implement AssociationServiceInterface
 *
 * @since  4.0.0
 */
trait AssociationServiceTrait
{
    /**
     * The association extension.
     *
     * @var AssociationExtensionInterface
     *
     * @since  4.0.0
     */
    private $associationExtension = null;

    /**
     * Returns the associations extension helper class.
     *
     * @return  AssociationExtensionInterface
     *
     * @since  4.0.0
     */
    public function getAssociationsExtension(): AssociationExtensionInterface
    {
        return $this->associationExtension;
    }

    /**
     * The association extension.
     *
     * @param   AssociationExtensionInterface  $associationExtension  The extension
     *
     * @return  void
     *
     * @since  4.0.0
     */
    public function setAssociationExtension(AssociationExtensionInterface $associationExtension)
    {
        $this->associationExtension = $associationExtension;
    }
}
