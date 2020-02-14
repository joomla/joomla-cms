<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

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
JHtml::_('script', 'system/associations-edit.min.js', array('version' => 'auto', 'relative' => true));

// JLayout for standard handling of associations fields in the administrator items edit screens.
echo $form->renderFieldset('item_associations');
