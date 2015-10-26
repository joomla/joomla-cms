<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div style="background-color: #ffffff; font-family: Verdana, Arial, Helvetica, sans-serif;font-size:12px; color: #000000; width: 100%;">
	<table style="margin: auto;font-family: Verdana, Arial, Helvetica, sans-serif;font-size:12px;" border="0" cellspacing="0" cellpadding="0">
		<tbody>
			<tr>
				<td height="10">
				</td>
			</tr>
<?php
	$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=product&task=edit&cid=';
	foreach($data->products as $product){
?>
			<tr>
				<td>
<?php echo JText::sprintf('THE_PRODUCT_IS_OUT_OF_STOCK',$url.$product->product_id,$product->product_name,$product->product_quantity); ?>
				</td>
			</tr>
<?php } ?>
			<tr>
				<td height="10">
				</td>
			</tr>
		</tbody>
	</table>
</div>
