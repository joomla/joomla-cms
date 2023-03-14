<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.User
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Form\Form;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Fields User Plugin
 *
 * @since  3.7.0
 */
class PlgFieldsUser extends \Joomla\Component\Fields\Administrator\Plugin\FieldsPlugin
{
    /**
     * Transforms the field into a DOM XML element and appends it as a child on the given parent.
     *
     * @param   stdClass    $field   The field.
     * @param   DOMElement  $parent  The field node parent.
     * @param   Form        $form    The form.
     *
     * @return  DOMElement
     *
     * @since   3.7.0
     */
    public function onCustomFieldsPrepareDom($field, DOMElement $parent, Form $form)
    {
        if ($this->app->isClient('site')) {
            // The user field is not working on the front end
            return;
        }

        return parent::onCustomFieldsPrepareDom($field, $parent, $form);
    }
}
