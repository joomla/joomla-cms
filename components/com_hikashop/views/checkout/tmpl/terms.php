<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_checkout_terms" class="hikashop_checkout_terms">
	<input class="hikashop_checkout_terms_checkbox" id="hikashop_checkout_terms_checkbox" type="checkbox" name="hikashop_checkout_terms" value="1" <?php echo $this->terms_checked; ?> />
<?php
	$text = JText::_('PLEASE_ACCEPT_TERMS');
	$terms_article = $this->config->get('checkout_terms');
	$terms_width = $this->config->get('terms_and_conditions_width',450);
	$terms_height = $this->config->get('terms_and_conditions_height',480);
	if(!empty($terms_article)){
		$popupHelper = hikashop_get('helper.popup');
		$text = $popupHelper->display(
			$text,
			'HIKASHOP_CHECKOUT_TERMS',
			JRoute::_('index.php?option=com_content&view=article&id='.$terms_article.'&tmpl=component'),
			'shop_terms_and_cond',
			$terms_width, $terms_height, '', '', 'link'
		);
	}
?>
	<label for="hikashop_checkout_terms_checkbox"><?php echo $text; ?></label>
</div>
