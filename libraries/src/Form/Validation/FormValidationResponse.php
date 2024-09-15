<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Validation;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base class for implementing a ValidationResponseInterface
 *
 * @since  __DEPLOY_VERSION__
 */
class FormValidationResponse implements FormValidationResponseInterface
{
    /**
     * Is the form valid?
     *
     * @var    bool
     * @since  __DEPLOY_VERSION__
     */
    private bool $valid = true;

    /**
     * List of fields
     *
     * @var    FieldValidationResponseInterface[]
     * @since  __DEPLOY_VERSION__
     */
    private array $fields = [];

    /**
     * List of invalid fields
     *
     * @var    string[]
     * @since  __DEPLOY_VERSION__
     */
    private array $invalidFields = [];

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
     * List of validation properties tested that returned a negative result
     *
     * @return  string[][]  An array of fields that failed validation. Each field contains a name and group
     * *                    key that can be used in the getField method.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getInvalidFields(): array
    {
        return $this->invalidFields;
    }

    /**
     * Method to get a constraint result for the field.
     *
     * @param   string  $name   The name of the field to get the result for
     * @param   string  $group  The group of the field to get the result for
     *
     * @return  FieldValidationResponseInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getField(string $name, string $group): FieldValidationResponseInterface
    {
        return $this->fields[$group . '.' . $name];
    }

    /**
     * Adds the result of a constraint to the field validation result.
     *
     * @param   FieldValidationResponseInterface  $field  The field to add
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function addField(FieldValidationResponseInterface $field): void
    {
        if (!$field->isValid()) {
            $this->valid           = false;
            $this->invalidFields[] = ['name' => $field->getName(), 'group' => $field->getGroup()];
        }

        $this->fields[$field->getGroup() . '.' . $field->getName()] = $field;
    }

    /**
     * Count the number of fields validated
     *
     * @return  int<0,max>  The custom count as an integer.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function count(): int
    {
        return \count($this->fields);
    }
}
