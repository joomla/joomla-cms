<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><script type="text/javascript" src="https://ssl.ditonlinebetalingssystem.dk/integration/ewindow/paymentwindow.js" charset="UTF-8"></script>

<div class="hikashop_epay_end" id="hikashop_epay_end">
	<span id="hikashop_epay_end_message" class="hikashop_epay_end_message">
		<?php echo JText::sprintf('PLEASE_WAIT_BEFORE_REDIRECTION_TO_X',$this->payment_name).'<br/>'. JText::_('CLICK_ON_BUTTON_IF_NOT_REDIRECTED');?>
	</span>
	<span id="hikashop_epay_end_spinner" class="hikashop_epay_end_spinner">
		<img src="<?php echo HIKASHOP_IMAGES.'spinner.gif';?>" />
	</span>
	<br /><br />

	<script type="text/javascript">;
	paymentwindow = new PaymentWindow({
		<?php
		foreach ($this->vars as $name => $value)
		{
			if($name != "hash")
				echo '\''.$name.'\': "'.$value.'",';
			else
				echo '\''.$name.'\': "'.$value.'"';
		}

		?>
	});
	</script>

	<div id="hikashop_epay_end_image" class="hikashop_epay_end_image">
		<input id="hikashop_epay_button" type="button" onClick="paymentwindow.open();" value="<?php echo JText::_('PAY_NOW');?>" name="" alt="<?php echo JText::_('PAY_NOW');?>" />
	</div>
	<?php
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration("window.hikashop.ready( function() {paymentwindow.open();});");
		JRequest::setVar('noform',1);
	?>
</div>
