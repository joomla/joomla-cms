<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Association;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
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
    public function getAssociationExtension(): AssociationExtensionInterface
    {
        return $this->associationExtension;
    }

    /**
     * Returns the associations extension helper class.
     *
     * @return  AssociationExtensionInterface
     *
     * @since  4.0.0
     * @deprecated __DEPLOY_VERSION__ will be removed in 7.0
     *             Use ->getAssociationExtension() instead
     */
    public function getAssociationsExtension(): AssociationExtensionInterface
    {
        return $this->getAssociationExtension();
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
