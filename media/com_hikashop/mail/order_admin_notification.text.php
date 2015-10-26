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
echo JText::sprintf('ORDER_STATUS_CHANGED',$data->mail_status)."\r\n\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$data->order_number,HIKASHOP_LIVE);
echo "\r\n\r\n".JText::_('HIKA_EMAIL').': '.$data->customer->user_email[0];
$currency = hikashop_get('class.currency');
echo "\r\n\r\n".JText::_('HIKASHOP_TOTAL').' : '.$currency->format($data->order_full_price,$data->order_currency_id);
echo "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));
if($data->order_payment_method=='creditcard' && !empty($data->credit_card_info->cc_number)){
	echo "\r\n".JText::_('CUSTOMER_PAID_WITH_CREDIT_CARD');
	if(!empty($data->credit_card_info->cc_owner)){
		echo "\r\n".JText::_('CREDIT_CARD_OWNER').' : '.$data->credit_card_info->cc_owner;
	}
	echo "\r\n".JText::_('END_OF_CREDIT_CARD_NUMBER').' : '.substr($data->credit_card_info->cc_number,8);
	if(!empty($data->credit_card_info->cc_CCV)){
		echo "\r\n".JText::_('CARD_VALIDATION_CODE').' : '.$data->credit_card_info->cc_CCV;
	}
	echo "\r\n".JText::_('CREDITCARD_WARNING');
}
$fieldsClass = hikashop_get('class.field');
$fields = $fieldsClass->getFields('frontcomp',$data,'order','');
foreach($fields as $fieldName => $oneExtraField) {
	$fieldData = trim(@$data->$fieldName);
	if(empty($fieldData)) continue;
	echo "\r\n".$fieldsClass->trans($oneExtraField->field_realname).' : '.$fieldsClass->show($oneExtraField,$data->$fieldName);
}
