<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if (($this->payment_params->iframe == 1)) {
?><iframe  frameborder="0" id="hss_iframe" name="hss_iframe" width="570px" height="540px"></iframe>

<div class="hikashop_paypalintegralevolution_end" id="hikashop_paypalintegralevolution_end">
	<form id="hikashop_paypalintegralevolution_form" target="hss_iframe" name="hikashop_paypalintegralevolution_form" action="<?php echo $this->payment_params->url;?>" method="post">
		<?php
			foreach($this->vars as $name => $value ) {
				echo '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars((string)$value).'" />';
			}

			JRequest::setVar('noform',1); ?>
		<input type="hidden" name="template" value="templateD">
	</form>
	<script type="text/javascript">
	window.hikashop.ready(function(){
		 document.hikashop_paypalintegralevolution_form.submit();
	});
	</script>
</div>
<?php
}else{ ?>
<div class="hikashop_paypalintegralevolution_end" id="hikashop_paypalintegralevolution_end">
	<span id="hikashop_paypalintegralevolution_end_message" class="hikashop_paypalintegralevolution_end_message">
		<?php echo JText::sprintf('PLEASE_WAIT_BEFORE_REDIRECTION_TO_X', $this->payment_name).'<br/><span id="hikashop_paypalintegralevolution_button_message">'. JText::_('CLICK_ON_BUTTON_IF_NOT_REDIRECTED').'</span>';?>
	</span>
	<span id="hikashop_paypalintegralevolution_end_spinner" class="hikashop_paypalintegralevolution_end_spinner hikashop_checkout_end_spinner">
	</span>
	<br/>
	<form id="hikashop_paypalintegralevolution_form" name="hikashop_paypalintegralevolution_form" action="<?php echo $this->payment_params->url;?>" method="post">
		<div id="hikashop_paypalintegralevolution_end_image" class="hikashop_paypalintegralevolution_end_image">
			<input id="hikashop_paypalintegralevolution_button" type="submit" class="btn btn-primary" value="<?php echo JText::_('PAY_NOW');?>" name="" alt="<?php echo JText::_('PAY_NOW');?>" />
		</div>
		<?php
			foreach($this->vars as $name => $value ) {
				echo '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars((string)$value).'" />';
			}
			JRequest::setVar('noform',1); ?>
	</form>
	<script type="text/javascript">
	window.hikashop.ready(function(){
		 document.hikashop_paypalintegralevolution_form.submit();
	});
	</script>
	</div>
<?php
}
