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
		global $Itemid;
		$url_Itemid = '';
		if(isset($Itemid)){
			$url_Itemid = "&Itemid=".$Itemid;
		}
?>
<?php echo JText::sprintf('HI_CUSTOMER',@$data->vars['first_name']." ".$data->vars['last_name']);?>
<br/><br/>
<?php echo JText::sprintf('OUT_OF_DATE_SUBSCRIPTION',$data->order->order_number); ?>
<br/><br/>
<?php echo "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$data->order->order_number,HIKASHOP_LIVE); ?>
<br/><br/>
<?php echo "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK_FRONTEND',HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=show&cid='.$data->order->order_id.$url_Itemid)); ?>
<br/><br/>
<?php echo JText::sprintf('BEST_REGARDS_CUSTOMER',HIKASHOP_LIVE);?>
