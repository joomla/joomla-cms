<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/* Convert icomoon to fa */
$icon = $displayData['icon'];
if ( $icon === 'archive' )
{
	$icon = 'folder';
}

if ( $icon === 'publish' )
{
	$icon = 'check';
}

if ( $icon === 'unpublish' || $icon === 'cancel' || $icon === 'delete' || $icon === 'remove' )
{
	$icon = 'times';
}

if ( $icon === 'new' )
{
	$icon = 'plus';
}

if ( $icon === 'apply' )
{
	$icon = 'save';
}

if ( $icon === 'mail' )
{
	$icon = 'envelope';
}

if ( $icon === 'featured' || $icon === 'unfeatured' )
{
	$icon = 'star';
}

if ( $icon === 'checkedout' )
{
	$icon = 'lock';
}

if ( $icon === 'eye-close' )
{
	$icon = 'eye-slash';
}

if ( $icon === 'eye-open' )
{
	$icon = 'eye';
}

if ( $icon === 'refresh' )
{
	$icon = 'sync';
}
?>
fas fa-<?php echo $icon; ?>
