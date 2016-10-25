<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.codemirror
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

$params   = $displayData->params;
$options  = $displayData->options;
$basePath = $params->get('basePath', 'media/editors/codemirror/');
$modePath = $params->get('modePath', 'media/editors/codemirror/mode/%N/%N');
$extJS    = JDEBUG ? '.js' : '.min.js';
$extCSS   = JDEBUG ? '.css' : '.min.css';

JHtml::_('script', $basePath . 'lib/codemirror' . $extJS, array('version' => 'auto'));
JHtml::_('script', $basePath . 'lib/addons' . $extJS, array('version' => 'auto'));
JHtml::_('stylesheet', $basePath . 'lib/codemirror' . $extCSS, array('version' => 'auto'));
JHtml::_('stylesheet', $basePath . 'lib/addons' . $extCSS, array('version' => 'auto'));

$fskeys          = $params->get('fullScreenMod', array());
$fskeys[]        = $params->get('fullScreen', 'F10');
$fullScreenCombo = implode('-', $fskeys);

JFactory::getDocument()->addScriptOptions(
	'js-editors-cm',
	array(
		'fsCombo' => $fullScreenCombo,
		'modPath' => JUri::root(true) . '/' . $modePath . $extJS,
		'options' => $options,
	));

JHtml::_('behavior.core');
JHtml::_('script', 'system/editor-codemirror.js', false, true);
