<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_cardsave_end" id="hikashop_cardsave_end">
	<span id="hikashop_cardsave_end_message" class="hikashop_cardsave_end_message">
		<?php echo JText::sprintf('PLEASE_WAIT_BEFORE_REDIRECTION_TO_X',$this->payment_name).'<br/>'. JText::_('CLICK_ON_BUTTON_IF_NOT_REDIRECTED');?>
	</span>
	<span id="hikashop_cardsave_end_spinner" class="hikashop_cardsave_end_spinner">
		<img src="<?php echo HIKASHOP_IMAGES.'spinner.gif';?>" />
	</span>
	<br/>
	<form id="hikashop_cardsave_form" name="hikashop_cardsave_form" action="https://mms.cardsaveonlinepayments.com/Pages/PublicPages/PaymentForm.aspx" method="post">
		<div id="hikashop_cardsave_end_image" class="hikashop_cardsave_end_image">
			<input id="hikashop_cardsave_button" type="submit" class="btn btn-primary" value="<?php echo JText::_('PAY_NOW');?>" name="" alt="<?php echo JText::_('PAY_NOW');?>" />
		</div>
		<?php
			foreach( $this->vars as $name => $value ) {
				echo '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars((string)$value).'" />';
			}
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration("window.hikashop.ready( function() {document.getElementById('hikashop_cardsave_form').submit();});");
			JRequest::setVar('noform',1);
		?>
	</form>
</div>
