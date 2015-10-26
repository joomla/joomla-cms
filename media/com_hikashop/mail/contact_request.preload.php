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
$url_itemid = '';
if(!empty($Itemid)) {
	$url_itemid = '&Itemid=' . $Itemid;
}

$texts = array(
	'MAIL_HEADER' => JText::_('HIKASHOP_MAIL_HEADER'),
	'CONTACT_TITLE' => JText::_('CONTACT_EMAIL_TITLE'),
	'CONTACT_BEGIN_MESSAGE' => JText::_('CONTACT_BEGIN_MESSAGE'),
	'USER_MESSAGE' => JText::_('CONTACT_USER_MESSAGE'),
	'USER' => JText::_('HIKA_USER'),
	'PRODUCT' => JText::_('PRODUCT'),
	'HI_USER' => JText::sprintf('HI_CUSTOMER', ''),
	'FOR_PRODUCT' => JText::sprintf('CONTACT_REQUEST_FOR_PRODUCT', $data->product->product_name),
);

$admin_product_url = JRoute::_('administrator/index.php?option=com_hikashop&ctrl=product&task=edit&cid[]='.$data->product->product_id, false, true);
$productClass = hikashop_get('class.product');
$productClass->addAlias($data->product);
$front_product_url = hikashop_frontendLink('index.php?option=com_hikashop&ctrl=product&task=show&cid='.$data->product->product_id.'&name='.$data->product->alias.$url_itemid);

$vars = array(
	'LIVE_SITE' => HIKASHOP_LIVE,
	'URL' => HIKASHOP_LIVE,
	'USER_DETAILS' => htmlentities($data->element->name.' ( '.$data->element->email . ' )', ENT_COMPAT, 'UTF-8'),
	'PRODUCT_DETAILS' => ('<a href="'.$admin_product_url.'">'.strip_tags($data->product->product_name.' ('.$data->product->product_code.')').'</a>'),
	'FRONT_PRODUCT_DETAILS' => ('<a href="'.$front_product_url.'">'.strip_tags($data->product->product_name.' ('.$data->product->product_code.')').'</a>'),
	'USER_MESSAGE' => str_replace(array("\r\n","\r","\n"), '<br/>', $data->element->altbody),
);

if(hikashop_level(1)) {
	$null = null;
	$fieldsClass = hikashop_get('class.field');
	$contactFields = $fieldsClass->getFields('frontcomp',$null,'contact');
	if(!empty($contactFields)){
		foreach($contactFields as $field){
			$namekey = $field->field_namekey;
			if(empty($data->element->$namekey) && !strlen($data->element->$namekey)) continue;
			$vars['PRODUCT_DETAILS'] .= '<p>'.$fieldsClass->getFieldName($field).': '.$fieldsClass->show($field,$data->element->$namekey,'user_email').'</p>';
		}
	}
}
