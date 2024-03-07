<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Fields;

use Joomla\CMS\Form\Form;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait for component fields service.
 *
 * @since  5.1.0
 */
trait FieldsServiceTrait
{
    /**
     * Prepares the fields form
     *
     * @param   Form          $form  The form to change
     * @param   array|object  $data  The form data
     *
     * @return void
     */
    public function prepareForm(Form $form, $data)
    {
    }
}
