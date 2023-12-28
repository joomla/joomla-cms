<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Rule;

/**
 * Helps rules to implement {@link \Joomla\CMS\Form\Constraint\ConstraintInterface}
 *
 * @since   __DEPLOY_VERSION__
 */
trait RuleConstraintTrait
{
    /**
     * The regular expression to use in testing a form field value.
     *
     * @var    bool
     * @since  __DEPLOY_VERSION__
     */
    protected bool $isValid = false;

    /**
     * An error message to display to the user.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected string $errorMessage;

    /**
     * Has the rule been run?
     *
     * @var    bool
     * @since  __DEPLOY_VERSION__
     */
    protected bool $ruleRun = false;

    /**
     * Was the data validated in the constraint.
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * Get an error message that can be displayed to a user about how to remediate the constraint.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \BadMethodCallException  When the constraint is valid
     */
    public function getErrorMessage(): string
    {
        if (!$this->ruleRun) {
            throw new \BadMethodCallException(sprintf('The %s::test() method must be run', static::class));
        }

        if ($this->isValid) {
            throw new \BadMethodCallException('The rule was valid');
        }

        return $this->errorMessage;
    }
}
