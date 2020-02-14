<?php
/**
 * @package     JCE.Plugin
 * @subpackage  Fields.Media_Jce
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @copyright   Copyright (C) 2019 Ryan Demmer. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::import('components.com_fields.libraries.fieldsplugin', JPATH_ADMINISTRATOR);

JForm::addFieldPath(JPATH_PLUGINS . '/system/jce/fields');

/**
 * Fields MediaJce Plugin
 *
 * @since  2.6.27
 */
class PlgFieldsMediaJce extends FieldsPlugin
{
    public function onCustomFieldsPrepareDom($field, DOMElement $parent, JForm $form)
    {
        $fieldNode = parent::onCustomFieldsPrepareDom($field, $parent, $form);
        return $fieldNode;
    }
}
