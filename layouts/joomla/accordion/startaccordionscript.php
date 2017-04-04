<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$options  = empty($displayData['options']) ? '' : $displayData['options'];
$selector = empty($displayData['selector']) ? '' : $displayData['selector'];
$onShow   = empty($displayData['onShow']) ? '' : $displayData['onShow'];
$onShown  = empty($displayData['onShown']) ? '' : $displayData['onShown'];
$onHide   = empty($displayData['onHide']) ? '' : $displayData['onHide'];
$onHidden = empty($displayData['onHidden']) ? '' : $displayData['onHidden'];

$script = [];
$script[] = "jQuery(function($){";
$script[] = "\t$('#" . $selector . "').collapse(" . $options . ")";

if ($onShow)
{
	$script[] = "\t.on('show', " . $onShow . ")";
}

if ($onShown)
{
	$script[] = "\t.on('shown', " . $onShown . ")";
}

if ($onHide)
{
	$script[] = "\t.on('hideme', " . $onHide . ")";
}

if ($onHidden)
{
	$script[] = "\t.on('hidden', " . $onHidden . ")";
}

$script[] = "});";


echo implode("\n", $script);
