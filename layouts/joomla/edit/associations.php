<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$form     = $displayData->getForm();
$options  = [
    'formControl' => $form->getFormControl(),
    'hidden'      => (int) ($form->getValue('language', null, '*') === '*'),
];

// Load JavaScript message titles
Text::script('ERROR');
Text::script('WARNING');
Text::script('NOTICE');
Text::script('MESSAGE');
Text::script('JGLOBAL_ASSOC_NOT_POSSIBLE');
Text::script('JGLOBAL_ASSOCIATIONS_RESET_WARNING');

/** @var \Joomla\CMS\Document\HtmlDocument $doc */
$doc = Factory::getApplication()->getDocument();
$wa  = $doc->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_associations');
$wa->useScript('com_associations.associations-edit');
$doc->addScriptOptions('system.associations.edit', $options);

// JLayout for standard handling of associations fields in the administrator items edit screens.
echo $form->renderFieldset('item_associations');
