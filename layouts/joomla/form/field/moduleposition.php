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

// Load the modal behavior script.
JHtml::_('behavior.modal', 'a.modal');

// Add the script to the document head.
JFactory::getDocument()->addScriptDeclaration(
	'
	function jSelectPosition_' . $id . '(name) {
		document.getElementById("' . $id . '").value = name;
		jModalClose();
	}
	'
);

$link = 'index.php?option=com_modules&view=positions&layout=modal&tmpl=component&function=jSelectPosition_'
	. $id . '&amp;client_id=' . $clientId;
?>
<div class="input-append">
	<?php echo $inputTag; ?>
	<a class="btn modal" title="<?php echo JText::_('COM_MODULES_CHANGE_POSITION_TITLE'); ?>"  href="<?php echo $link; ?>" rel="{handler: 'iframe', size: {x: 800, y: 450}}">
		<?php echo JText::_('COM_MODULES_CHANGE_POSITION_BUTTON'); ?></a>
</div>