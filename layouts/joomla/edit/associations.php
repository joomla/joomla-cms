<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$form     = $displayData->getForm();
$options  = array(
	'formControl' => $form->getFormControl(),
	'hidden'      => (int) ($form->getValue('language', null, '*') === '*'),
);

JHtml::_('behavior.core');
JHtml::_('jquery.framework');
JText::script('JGLOBAL_ASSOC_NOT_POSSIBLE');
JText::script('JGLOBAL_ASSOCIATIONS_RESET_WARNING');
JFactory::getDocument()->addScriptOptions('system.associations.edit', $options);
JHtml::_('script', 'system/associations-edit.js', array('version' => 'auto', 'relative' => true));

// JLayout for standard handling of associations fields in the administrator items edit screens.
echo $form->renderFieldset('item_associations');
