<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$form     = $displayData->getForm();
$options  = array(
	'formControl' => $form->getFormControl(),
	'hidden'      => (int) ($form->getValue('language', null, '*') === '*'),
);

HTMLHelper::_('behavior.core');

// Load JavaScript message titles
Text::script('ERROR');
Text::script('WARNING');
Text::script('NOTICE');
Text::script('MESSAGE');
Text::script('JGLOBAL_ASSOC_NOT_POSSIBLE');
Text::script('JGLOBAL_ASSOCIATIONS_RESET_WARNING');

Factory::getDocument()->addScriptOptions('system.associations.edit', $options);
HTMLHelper::_('script', 'com_associations/associations-edit.min.js', array('version' => 'auto', 'relative' => true));

// JLayout for standard handling of associations fields in the administrator items edit screens.
echo $form->renderFieldset('item_associations');
