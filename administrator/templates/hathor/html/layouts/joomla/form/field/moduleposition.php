<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Layout variables
 * ---------------------
 *
 * @var  string   $id The module id number
 * @var  string   $clientId The Client id (site/admin)
 * @var  string   $inputTag The input field
 */
extract($displayData);

// Add the script to the document head.
JFactory::getDocument()->addScriptDeclaration(
	'
	function jSelectPosition_' . $id . '(name) {
		document.getElementById("' . $id . '").value = name;
		jQuery("#module' . $id . 'PositionModal").modal("hide");
	}
	'
);

$link = 'index.php?option=com_modules&view=positions&layout=modal&tmpl=component&function=jSelectPosition_'
	. $id . '&amp;client_id=' . $clientId;

echo JHtml::_(
	'bootstrap.renderModal',
	'module' . $id . 'PositionModal',
	array(
		'url' => $link,
		'title' => JText::_('COM_MODULES_CHANGE_POSITION_TITLE'),
		'height' => '300px',
		'width' => '800px'
	)
);
?>
<div class="input-append">
	<?php echo $inputTag; ?>
	<button onclick="jQuery('#module<?php echo $id; ?>PositionModal').modal('show')" class="btn" data-toggle="modal" title="<?php echo JText::_('COM_MODULES_CHANGE_POSITION_BUTTON'); ?>">
		<span><?php echo JText::_('COM_MODULES_CHANGE_POSITION_BUTTON'); ?></span>
	</button>
</div>