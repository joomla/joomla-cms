<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Validation;

// phpcs:disable PSR1.Files.SideEffects
use Joomla\CMS\Form\Constraint\ConstraintInterface;

\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface to get the validation information about a form field
 *
 * @since  __DEPLOY_VERSION__
 */
interface FieldValidationResponseInterface extends \Countable
{
    /**
     * Was the field data valid or not. If there are no constraints on the field this should return true.
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    public function isValid(): bool;

    /**
     * List of constraints that returned a negative result.
     *
     * @return  string[]
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getInvalidConstraints(): array;

    /**
     * Method to get a constraint result for the field.
     *
     * @param   string  $name  The name of the property
     *
     * @return  ConstraintInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getConstraint(string $name): ConstraintInterface;

    /**
     * Name of the field.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getName(): string;

    /**
     * Group of the field.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getGroup(): string;
}
