<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$clientId         = $this->item->client_id;
$state            = 1;
$selectedPosition = $this->item->position;
$positions        = HTMLHelper::_('modules.positions', $clientId, $state, $selectedPosition);

// Add custom position to options
$customGroupText = Text::_('COM_MODULES_CUSTOM_POSITION');

// Build field
$attr = array(
	'id'          => 'jform_position',
	'list.select' => $this->item->position,
);

echo HTMLHelper::_('select.groupedlist', $positions, 'jform[position]', $attr);
