<?php

/**
 * @package     acorn.Framework
 * @subpackage  Template-Layout Tab
 * @version     14-Nov-19
 * @copyright   Copyright (C) 2015 Troy T. Hall All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

// Variables
$theme                = $this->params->get('templateTheme');
$left                 = $this->params->get('sidebarLeftWidth', '');
$right                = $this->params->get('sidebarRightWidth', '');
$bodyclass            = $this->params->get('bodyclass', '');
$customCSS            = $this->params->get('customCSS');

/**
 * ==================================================
 *  Width & Sizing
 * ==================================================
 */
$fullWidth = ($task == "edit" || $layout == "form") ? 1 : 0;
// Width calculations
$cols = '';
$grid = 12;
if ($this->countModules('left') && $this->countModules('right'))
{
	$cols = ($grid - ($left + $right));
}
elseif ($this->countModules('left') && !$this->countModules('right'))
{
	$cols = ($grid - $left);
}
elseif (!$this->countModules('left') && $this->countModules('right'))
{
	$cols = ($grid - $right);
}
else
{
	$cols = 12;
}
