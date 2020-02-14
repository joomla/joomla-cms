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

?>
<div class="uk-form-row uk-margin-small-bottom">
<label for="popup_list" class="uk-form-label uk-width-1-5 hastip" title="<?php echo JText::_('WF_POPUP_TYPE_DESC'); ?>"><?php echo JText::_('WF_POPUP_TYPE'); ?></label>
	<div class="uk-form-controls uk-width-2-5">
		<?php echo $this->popups->getPopupList(); ?>
	</div>
</div>

<div class="uk-form-row uk-margin-small-bottom" style="display:<?php echo ($this->popups->get('text') === false) ? 'none' : ''?>;">
	<label for="popup_text" class="hastip uk-form-label uk-width-1-5"
			title="<?php echo JText::_('WF_POPUP_TEXT_DESC'); ?>"><?php echo JText::_('WF_POPUP_TEXT'); ?></label>
		<div class="uk-form-controls uk-width-4-5">
			<input id="popup_text" type="text" value="" />
		</div>
</div>
<?php echo $this->popups->getPopupTemplates(); ?>
