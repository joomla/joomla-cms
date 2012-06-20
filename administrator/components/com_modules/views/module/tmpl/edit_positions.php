<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR.'/components/com_templates/helpers/templates.php';

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
$clientId       = $this->item->client_id;
$state          = $this->state->get('filter.state');
$templates      = array_keys(ModulesHelper::getTemplates($clientId, $state));
$templateGroups = array();

// Add an empty value to be able to deselect a module position
$option = new stdClass;
$option->value = '';
$option->text  = '';

$group = array();
$group['value'] = '';
$group['text']  = '';
$group['items'] = array($option);

$templateGroups[''] = $group;

// Add positions from templates
foreach ($templates as $template)
{
	$group = array();
	$group['value'] = $template;
	$group['text']  = $template;
	$group['items'] = array();

	$positions = TemplatesHelper::getPositions($clientId, $template);
	foreach ($positions as $position)
	{
		$option = new stdClass();
		$option->value = $position;
		$option->text = $position;

		$group['items'][] = $option;
	}

	$templateGroups[$template] = $group;
}

echo JHtml::_(
	'select.groupedlist', $templateGroups, 'jform[position]',
	array('id' => 'jform_position', 'list.select' => $this->item->position)
);
