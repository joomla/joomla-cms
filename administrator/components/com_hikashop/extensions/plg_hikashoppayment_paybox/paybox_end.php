<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_paybox_end" id="hikashop_paybox_end">
	<span id="hikashop_paybox_end_message" class="hikashop_paypal_end_message">
		<?php echo JText::sprintf('PLEASE_WAIT_BEFORE_REDIRECTION_TO_X', $this->payment_name).'<br/>'. JText::_('CLICK_ON_BUTTON_IF_NOT_REDIRECTED');?>
	</span>
	<span id="hikashop_paybox_end_spinner" class="hikashop_paybox_end_spinner hikashop_checkout_end_spinner">
	</span>
	<br/>
	<form id="hikashop_paybox_form" name="hikashop_paybox_form" action="<?php echo $this->url;?>" method="post">
<?php
foreach($this->vars as $key => $value) {
	echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />'."\r\n";
}
JRequest::setVar('noform',1);
?>
		<div id="hikashop_paybox_end_image" class="hikashop_paybox_end_image">
			<input id="hikashop_paybox_button" type="submit" class="btn btn-primary" value="<?php echo JText::_('PAY_NOW');?>" alt="<?php echo JText::_('PAY_NOW');?>" />
		</div>
	</form>
<script type="text/javascript">
<!--
document.getElementById('hikashop_paybox_form').submit();
//-->
</script>
</div>
