<?php
/**
 * @package		 HikaShop for Joomla!
 * @subpackage Payment Plug-in for Worldpay Business Gateway.
 * @version		 0.0.1
 * @author		 brainforge.co.uk
 * @copyright	 (C) 2011 Brainforge derive from Paypal plug-in by HIKARI SOFTWARE. All rights reserved.
 * @license		 GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 * In order to configure and use this plug-in you must have a Worldpay Business Gateway account.
 * Worldpay Business Gateway is sometimes refered to as 'Select Junior'.
 */
defined('_JEXEC') or die('Restricted access');
?>
<div class="hikashop_rbsworldpay_end" id="hikashop_rbsworldpay_end">
	<span id="hikashop_rbsworldpay_end_message" class="hikashop_rbsworldpay_end_message">
		<?php echo JText::sprintf('PLEASE_WAIT_BEFORE_REDIRECTION_TO_X',$this->payment_name).'<br/>'. JText::_('CLICK_ON_BUTTON_IF_NOT_REDIRECTED');?>
	</span>
	<span id="hikashop_rbsworldpay_end_spinner" class="hikashop_rbsworldpay_end_spinner">
		<img src="<?php echo HIKASHOP_IMAGES.'spinner.gif';?>" />
	</span>
	<br/>
	<form id="hikashop_rbsworldpay_form" name="hikashop_rbsworldpay_form" action="<?php echo $this->payment_params->url;?>" method="post">
		<div id="hikashop_rbsworldpay_end_image" class="hikashop_rbsworldpay_end_image">
			<input id="hikashop_rbsworldpay_end_button" <?php echo $this->payment_params->redirect_button; ?> type="submit" class="btn btn-primary" value="" name="" alt="Click to pay with RBS Worldpay - it is fast, free and secure!" />
		</div>
		<?php
			foreach( $this->vars as $name => $value ) {
				echo '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars($value).'" />';
			}
			if (!empty($this->payment_params->showVars)) {
				echo '<pre>';
				print_r($this->vars);
				echo '</pre>';
			}
			else {
				$doc = JFactory::getDocument();
				$doc->addScriptDeclaration("window.hikashop.ready( function() {document.getElementById('hikashop_rbsworldpay_form').submit();});");
				JRequest::setVar('noform',1);
			}
		?>
	</form>
</div>