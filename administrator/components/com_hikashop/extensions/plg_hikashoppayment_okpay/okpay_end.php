<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_okpay_end" id="hikashop_okpay_end">
	<form id="hikashop_okpay_form" name="hikashop_okpay_form" action="<?php echo $this->payment_params->url;?>" method="post">
		<div id="hikashop_okpay_end_image" class="hikashop_okpay_end_image">
			<input id="hikashop_okpay_button" type="submit" class="btn btn-primary" value="<?php echo JText::_('PAY_NOW');?>" name="" alt="<?php echo JText::_('PAY_NOW');?>" />
		</div>
		<?php
			foreach( $this->vars as $name => $value ) {
				echo '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars((string)$value).'" />';
			}
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration("window.hikashop.ready( function() {document.getElementById('hikashop_okpay_form').submit();});");
			JRequest::setVar('noform',1); ?>
		</form>
</div>
