<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Menuitem
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Form\Form;

/**
 * Fields Menuitem Plugin
 *
 * @since  4.2.0
 */
class PlgFieldsMenuitem extends \Joomla\Component\Fields\Administrator\Plugin\FieldsPlugin
{

    /**
     * Prepare the form for display.
     *
     * @param   Form  $form The form being prepared for display
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function onCustomFieldsPreprocessForm(Form $form): void
    {
        $form->removeField('default_value');
    }
}
