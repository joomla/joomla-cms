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
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_eselectplus_end" id="hikashop_eselectplus_end">
	<span id="hikashop_eselectplus_end_message" class="hikashop_eselectplus_end_message">
		<?php echo JText::sprintf('PLEASE_WAIT_BEFORE_REDIRECTION_TO_X',$method->payment_name).'<br/>'. JText::_('CLICK_ON_BUTTON_IF_NOT_REDIRECTED');?>
	</span>
	<span id="hikashop_eselectplus_end_spinner" class="hikashop_eselectplus_end_spinner hikashop_checkout_end_spinner">
	</span>
	<br/>
	<form id="hikashop_eselectplus_form" name="hikashop_eselectplus_form" action="<?php echo $method->payment_params->url;?>" method="post">
		<div id="hikashop_eselectplus_end_image" class="hikashop_eselectplus_end_image">
			<input id="hikashop_eselectplus_button" type="submit" class="btn btn-primary" value="<?php echo JText::_('PAY_NOW');?>" name="" alt="<?php echo JText::_('PAY_NOW');?>" />
		</div>
		<?php
			foreach( $vars as $name => $value ) {
				echo '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars((string)$value).'" />';
			}
			JRequest::setVar('noform',1); ?>
	</form>
	<script type="text/javascript">
		<!--
		document.getElementById('hikashop_eselectplus_form').submit();
		//-->
	</script>
</div>
