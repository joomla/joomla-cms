<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php echo JText::sprintf('HI_CUSTOMER',@$data->name);?><br/>
<br/>
<?php echo JText::sprintf('WAITLIST_SUBSCRIBE_FOR_PRODUCT', $data->product_name);?><br/>
<?php
if($data->product_quantity < 0 ) { $data->product_quantity = JText::_('UNLIMITED'); }
echo JText::sprintf('THERE_IS_NOW_QTY_FOR_PRODUCT', $data->product_quantity);?><br/>
<?php 
	$url = HIKASHOP_LIVE . 'index.php?option=com_hikashop&ctrl=product&task=show&cid='. $data->product_id . '&Itemid='. $data->product_item_id;
	echo JText::sprintf('SEE_PRODUCT', $url);
?><br/>
<br/>
<?php echo JText::sprintf('BEST_REGARDS_CUSTOMER',$mail->from_name);?>
