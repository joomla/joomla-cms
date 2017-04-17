<?php
/**
 * @package		HikaShop for Joomla!
 * @version		0.0.0
 * @author		brainforge.co.uk
 * @copyright	(C) 2011 Brainforge derived from Paypal plug-in by HIKARI SOFTWARE. All rights reserved.
 * @license		GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * 
 * See: http://www.worldpay.com/support/kb/gg/submittingtransactionsredirect/rxml.html
 *
 * In order to configure and use this plug-in you must have a Worldpay Global Gateway account.
 * Worldpay Global Gateway is sometimes referred to as 'BiBit'.
 *
 * See: http://www.worldpay.com/support/kb/gg/submittingtransactionsredirect/rxml.html
 *
 * This file is a template used to generate the <orderContent> child element of the XML passed to Worldpay.
 * The plug-in contains a selection of example templates one of which, with the appropriate configuration tuning,
 * may be sufficient for your needs. However, you can always create your own if you require more control over the
 * output. Do not edit the templates distributed with the plug-in - your changes will get overwritten if you upgrade.
 * Always make a copy of one which looks closest to your objectives and give it a distinctive name. The list of
 * available templates, including your own, will appear on the Payment configuration page for this plug-in.
 * 
 * This template is derived from original RBS published XML examples.
 * 
 * Passed arguments from onAfterOrderConfirm()
 *  $params
 *  $order
 *  $user
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
	echo '<div class="rbs-wrapper">';
	echo '<table class="rbs-order">';
	echo '<tr class="rbs-order-header">';
	echo '<th class="rbs-product_code">Product Code</th>';
	echo '<th class="rbs-product_name">Product Name</th>';
	echo '<th class="rbs-product_quantity">Quantity</th>';
	echo '<th class="rbs-product_price">Total Price</th>';
	echo '</tr>';
	$row = 0;
	$tax_cart = 0;
	$config =& hikashop_config();
	$group = $config->get('group_options',0);
	foreach($order->cart->products as $product){
		if($group && $product->order_product_option_parent_id) continue;
		echo '<tr class="rbs-product-item rbs-product-row-' . $row . '">';
		echo '<td class="rbs-product_code">' . $product->order_product_code . '</td>';
		echo '<td class="rbs-product_name">' . $product->order_product_name . '</td>';
		echo '<td class="rbs-product_quantity">' . $product->order_product_quantity . '</td>';
		echo '<td class="rbs-product_price">' . rbsglobalgateway_helper::product_price($params, $product, $tax_cart) . '</td>';
		echo '</tr>';
		$row = ($row) ? 0 : 1;
	}
	if(!empty($order->order_shipping_price) || !empty($order->cart->shipping->shipping_name)){
		echo '<tr class="rbs-product-item rbs-product-row-' . $row . '">';
		echo '<td class="rbs-product_code">' . JText::_('HIKASHOP_SHIPPING') . '</td>';
		echo '<td class="rbs-product_name">' . rbsglobalgateway_helper::shipping_name($params, $order) . '</td>';
		echo '<td class="rbs-product_quantity">1</td>';
		echo '<td class="rbs-product_price">' . rbsglobalgateway_helper::shipping_price($params, $order, $tax_cart) . '</td>';
		echo '</tr>';
		$row = ($row) ? 0 : 1;
	}
	if(bccomp($tax_cart,0,5)) {
		echo '<tr class="rbs-product-item rbs-order-tax rbs-product-row-' . $row . '">';
		echo '<td class="rbs-product_code">&nbsp;</td>';
		echo '<td class="rbs-product_name">Tax</td>';
		echo '<td class="rbs-product_quantity">&nbsp;</td>';
		echo '<td class="rbs-product_price">' . rbsglobalgateway_helper::formatPrice($params, $tax_cart) . '</td>';
		echo '</tr>';
		$row = ($row) ? 0 : 1;
	}
	if(!empty($order->cart->coupon->discount_value)){
		echo '<tr class="rbs-product-item rbs-order-coupon rbs-product-row-' . $row . '">';
		echo '<td class="rbs-product_code">&nbsp;</td>';
		echo '<td class="rbs-product_name">Coupon</td>';
		echo '<td class="rbs-product_quantity">&nbsp;</td>';
		echo '<td class="rbs-product_price">' . rbsglobalgateway_helper::formatPrice($params, rbsglobalgateway_helper::roundPrice($params, $order->cart->coupon->discount_value)) . '</td>';
		echo '</tr>';
		 $row = ($row) ? 0 : 1;
	}
	echo '<tr class="rbs-product-item rbs-order-total rbs-product-row-' . $row . '">';
	echo '<td class="rbs-product_code">&nbsp;</td>';
	echo '<td class="rbs-product_name">Total Cost</td>';
	echo '<td class="rbs-product_quantity">&nbsp;</td>';
	echo '<td class="rbs-product_price">' . rbsglobalgateway_helper::formatPrice($params, rbsglobalgateway_helper::roundPrice($params, $order->order_full_price)) . '</td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';
?>