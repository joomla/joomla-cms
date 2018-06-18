<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;

$id       = null;
$name     = '';
$allowed  = [];
$urls     = [];
$text     = [];
$title    = '';
$value    = '';
$attribs  = [];
$required = '0';

extract($displayData, EXTR_IF_EXISTS & EXTR_REFS);

// Add the modal field script to the document head.
HTMLHelper::_('jquery.framework');
HTMLHelper::_('webcomponent', 'system/webcomponents/joomla-field-modal.min.js', ['version' => 'auto', 'detectDebug' => true]);

$attribs = array_merge($attribs, [
	'id' => $id,
	'value' => $value,
	'name' => $name,
	'required' => $required,
]);

foreach ($allowed as $key => $value)
{
	$attribs['allow-' . $key] = $value ? 'true' : 'false';
}

foreach ($urls as $key => $value)
{
	$attribs['url-' . $key] = $value;
}

foreach ($text as $key => $value)
{
	$attribs['text-' . $key] = $value;
}

?>
<joomla-field-modal <?php echo ArrayHelper::toString($attribs); ?>><?php echo $title; ?></joomla-field-modal>
