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
 * The fields service.
 *
 * @since  5.1.0
 *
 * @deprecated  5.1.0 will be removed in 7.0
 */
interface FieldsFormServiceInterface extends FieldsServiceInterface
{
    /**
     * Prepares the field form
     *
     * @param   Form          $form  The form to change
     * @param   array|object  $data  The form data
     *
     * @return  void
     *
     * @since   5.1.0
     *
     * @todo    7.0 Move to FieldsServiceInterface
     *
     * @deprecated  5.1.0 will be removed in 7.0
     *              Use the FieldServiceInterface instead
     *
     */
    public function prepareForm(Form $form, $data);
}
