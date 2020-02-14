<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('WF_EDITOR') or die('RESTRICTED');

$tabs = WFTabs::getInstance();

?>
<form action="#" class="uk-form uk-form-horizontal">
	<!-- Render Tabs -->
	<?php $tabs->render(); ?>
	<!-- Token -->	
	<input type="hidden" id="token" name="<?php echo JSession::getFormToken(); ?>" value="1" />
</form>
<div class="actionPanel">
	<button class="button" id="insert"><?php echo JText::_('WF_LABEL_INSERT')?></button>
	<button class="button" id="help"><?php echo JText::_('WF_LABEL_HELP')?></button>
	<button class="button" id="cancel"><?php echo JText::_('WF_LABEL_CANCEL')?></button>
</div>