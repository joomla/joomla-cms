<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;

$tabs = WFTabs::getInstance();
?>
<form onsubmit="return false;" action="#">
	<div id="<?php echo $this->plugin->getElementName(); ?>">
  	<?php $tabs->render(); ?>
  	</div>
	<div class="mceActionPanel">
		<button class="uk-button" type="submit" id="insert" onclick="XHTMLXtrasDialog.insert();"><?php echo JText::_('WF_LABEL_UPDATE'); ?></button>
		<button class="uk-button" type="button" id="remove" onclick="XHTMLXtrasDialog.remove();"><?php echo JText::_('WF_XHTMLXTRAS_REMOVE'); ?></button>
		<button class="uk-button" type="button" id="cancel"><?php echo JText::_('WF_LABEL_CANCEL'); ?></button>
	</div>
</form>