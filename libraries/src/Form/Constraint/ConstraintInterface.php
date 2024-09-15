<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Constraint;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface defining a constraint applied against a form field
 *
 * @since  __DEPLOY_VERSION__
 */
interface ConstraintInterface
{
    /**
     * Was the data validated in the constraint.
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    public function isValid(): bool;

    /**
     * Name of the constraint - note this is for machine access and should be unique for a form field.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getName(): string;

    /**
     * Get an error message that can be displayed to a user about how to remediate the constraint.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \BadMethodCallException  When the constraint is valid
     */
    public function getErrorMessage(): string;
}
