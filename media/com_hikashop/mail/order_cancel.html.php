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
$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$data->order_id;
echo JText::_('ORDER_CANCEL_BY_USER');?>
<br/><br/>
<?php echo JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE', $data->order_number, HIKASHOP_LIVE);?><br/>
<?php echo JText::sprintf('ACCESS_TO_THE_ORDER',$url);?>
<br/>
<?php echo JText::_('MAKE_A_REFUND_IF_POSSIBLE'); ?>
