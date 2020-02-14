<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

$class     = ' class="first"';

$item      = $displayData->item;
$items     = $displayData->get('items');
$params    = $displayData->params;
$extension = $displayData->get('extension');
$className = substr($extension, 4);

// This will work for the core components but not necessarily for other components
// that may have different pluralisation rules.
if (substr($className, -1) === 's')
{
	$className = rtrim($className, 's');
}
