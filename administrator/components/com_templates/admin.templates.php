<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Templates
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
 * Make sure the user is authorized to view this page
 */
$user = & JFactory::getUser();
if (!$user->authorize('com_templates', 'manage')) {
	$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

// Import file dependencies
require_once (JPATH_COMPONENT.'/helper.php');
require_once (JPATH_COMPONENT.'/admin.templates.html.php');
require_once (JPATH_COMPONENT.'/tables/template_positions.php');
require_once (JPATH_COMPONENT.'/controller.php');

$task = JRequest::getVar('task');
switch ($task)
{
	case 'edit' :
		JTemplatesController::editTemplate();
		break;

	case 'save'  :
	case 'apply' :
		JTemplatesController::saveTemplate();
		break;

	case 'edit_source' :
		JTemplatesController::editTemplateSource();
		break;

	case 'save_source'  :
	case 'apply_source' :
		JTemplatesController::saveTemplateSource();
		break;

	case 'choose_css' :
		JTemplatesController::chooseTemplateCSS();
		break;

	case 'edit_css' :
		JTemplatesController::editTemplateCSS();
		break;

	case 'save_css'  :
	case 'apply_css' :
		JTemplatesController::saveTemplateCSS();
		break;

	case 'publish' :
	case 'default' :
		JTemplatesController::publishTemplate();
		break;

	case 'cancel' :
		JTemplatesController::cancelTemplate();
		break;

	case 'positions' :
		JTemplatesController::editPositions();
		break;

	case 'save_positions' :
		JTemplatesController::savePositions();
		break;

	case 'preview' :
		JTemplatesController::previewTemplate();
		break;

	default :
		JTemplatesController::viewTemplates();
		break;
}
?>