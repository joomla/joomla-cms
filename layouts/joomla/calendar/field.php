<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load the calendar behavior
JHtml::_('behavior.calendar');

if ($displayData['done'])
{
	$document 	= JFactory::getDocument();
	$js 		= array();

	$js[] = 'jQuery(document).ready(function($) {Calendar.setup({';
	// Id of the input field
	$js[] = 'inputField: "' . $displayData['id'] . '",';
	// Format of the input field
	$js[] = 'ifFormat: "' . $displayData['format'] . '",';
	// Trigger for the calendar (button ID)
	$js[] = 'button: "' . $displayData['id'] . '_img",';
	// Alignment (defaults to "Bl")
	$js[] = 'align: "Tl",';
	$js[] = 'singleClick: true,';
	$js[] = 'firstDay: ' . JFactory::getLanguage()->getFirstDay();
	$js[] = '});});';
	$document->addScriptDeclaration(implode($js));
}

$title = ($displayData['inputvalue'] ? JHtml::_('date', $displayData['value'], null, null) : '');
$value = htmlspecialchars($displayData['inputvalue'], ENT_COMPAT, 'UTF-8');

?>
<div<?php echo $displayData['div_class']; ?>>
	<input type="text" title="<?php echo $title; ?>" name="<?php echo $displayData['name']; ?>" id="<?php echo $displayData['id']; ?>" value="<?php echo $value; ?>" <?php echo $displayData['attribs']; ?> />
	<button type="button" class="btn" id="<?php echo $displayData['id']; ?>_img"<?php echo $displayData['btn_style']; ?>>
		<i class="icon-calendar"></i>
	</button>
</div>