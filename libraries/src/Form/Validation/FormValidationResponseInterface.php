<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Validation;

use Joomla\CMS\Form\Validation;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface to get the validation information about a form.
 *
 * @since  __DEPLOY_VERSION__
 */
interface FormValidationResponseInterface extends \Countable
{
    /**
     * Was the data submitted to the form valid or not.
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    public function isValid(): bool;

    /**
     * List of invalid fields. The names here are not human friendly names but the location of fields that can be
     * resolved by {@link Form::getField()} or through {@link static::getFieldValidation()}.
     *
     * @return  string[]
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getInvalidFields(): array;

    /**
     * Get the field validation result for a named field.
     *
     * @param   string  $name  The name of the field to get the result for
     *
     * @return  FieldValidationResponseInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getField(string $name): FieldValidationResponseInterface;
}
