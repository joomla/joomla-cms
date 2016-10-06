<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$form     = $displayData->getForm();
$disabled = (int) ((int) $form->getValue('id') === 0 || JFactory::getApplication()->input->get('layout') === 'modal');
$options  = array(
	'formControl' => $form->getFormControl(),
	'disabled'    => $disabled,
	'hidden'      => (int) ($disabled || $form->getValue('language') === '*'),
);

JHtml::_('behavior.core');
JFactory::getDocument()->addScriptOptions('system.associations.edit', $options);
JHtml::script('system/associations-edit.js', false, true);

// JLayout for standard handling of associations fields in the administrator items edit screens.
echo $form->renderFieldset('item_associations');
