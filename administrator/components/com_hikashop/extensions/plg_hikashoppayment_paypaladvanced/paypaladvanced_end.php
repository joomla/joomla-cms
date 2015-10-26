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

$mode = 'LIVE';
if($this->payment_params->test_mode == '1')
	$mode = 'TEST';

if($this->payment_params->type == 'iframe'){
	echo '<iframe src="https://payflowlink.paypal.com?MODE='.$mode.'&SECURETOKEN='.$this->vars['SECURETOKEN'].'&SECURETOKENID='.$this->vars['SECURETOKENID'].'" name="paypal_iframe" scrolling="no" width="'.(int)$this->payment_params->width.'px" height="'.(int)$this->payment_params->height.'px"></iframe>';
}else{ ?>
	<form id="hikashop_paypaladvanced_form" name="hikashop_paypaladvanced_form" action="https://payflowlink.paypal.com" method="post">
		<?php

			echo '<input type="hidden" name="MODE" value="'.$mode.'" />';
			echo '<input type="hidden" name="SECURETOKEN" value="'.$this->vars['SECURETOKEN'].'" />';
			echo '<input type="hidden" name="SECURETOKENID" value="'.$this->vars['SECURETOKENID'].'" />';

			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration("window.hikashop.ready( function() {document.getElementById('hikashop_paypaladvanced_form').submit();});");
			JRequest::setVar('noform',1);
		?>
		<input id="hikashop_paypaladvanced_button" type="submit" class="btn btn-primary" value="<?php echo JText::_('PAY_NOW');?>" name="" alt="<?php echo JText::_('PAY_NOW');?>" />
	</form>
<?php } ?>
