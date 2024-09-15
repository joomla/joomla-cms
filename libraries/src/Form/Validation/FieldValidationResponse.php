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
 * Base class for implementing a ConstraintValidationInterface
 *
 * @since  __DEPLOY_VERSION__
 */
class FieldValidationResponse implements FieldValidationResponseInterface
{
    /**
     * The name of the field
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private string $name = '';

    /**
     * The group of the field
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private string $group = '';

    /**
     * Is the field valid?
     *
     * @var    bool
     * @since  __DEPLOY_VERSION__
     */
    private bool $valid = true;

    /**
     * List of constraints tested
     *
     * @var    ConstraintInterface[]
     * @since  __DEPLOY_VERSION__
     */
    private array $constraints = [];

    /**
     * List of invalid constraints
     *
     * @var    string[]
     * @since  __DEPLOY_VERSION__
     */
    private array $invalidConstraints = [];

    /**
     * Method to instantiate the object.
     *
     * @param   string  $name   The name of the field.
     * @param   string  $group  The group of the field.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(string $name, string $group)
    {
        $this->name  = $name;
        $this->group = $group;
    }

    /**
     * Was the field data valid or not. If there are no constraints on the field this should return true.
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * List of constraints that returned a negative result.
     *
     * @return  string[]
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getInvalidConstraints(): array
    {
        return $this->invalidConstraints;
    }

    /**
     * Method to get a constraint result for the field.
     *
     * @param   string  $name  The name of the property
     *
     * @return  ConstraintInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getConstraint(string $name): ConstraintInterface
    {
        return $this->constraints[$name];
    }

    /**
     * Adds the result of a constraint to the field validation result.
     *
     * @param   ConstraintInterface  $constraint  The constraint to add
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function addConstraint(ConstraintInterface $constraint): void
    {
        if (!$constraint->isValid()) {
            $this->valid                = false;
            $this->invalidConstraints[] = $constraint->getName();
        }

        $this->constraints[$constraint->getName()] = $constraint;
    }

    /**
     * Count the number of constraints checked on the field
     *
     * @return  int<0,max>  The custom count as an integer.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function count(): int
    {
        return \count($this->constraints);
    }

    /**
     * Name of the field.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Group of the field.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getGroup(): string
    {
        return $this->group;
    }
}
