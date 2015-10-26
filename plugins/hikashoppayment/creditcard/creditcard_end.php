<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_creditcard_end" id="hikashop_creditcard_end">
	<span class="hikashop_creditcard_end_message" id="hikashop_creditcard_end_message">
		<?php echo JText::_('ORDER_IS_COMPLETE').'<br/>'.
		$this->information.'<br/>'.
		JText::_('THANK_YOU_FOR_PURCHASE');?>
	</span>
</div>
<?php
if(!empty($this->payment_params->return_url)){
	$doc = JFactory::getDocument();
	$doc->addScriptDeclaration("window.hikashop.ready( function() {window.location='".$this->payment_params->return_url."'});");
}
