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

if(!isset($data)){
	$data = array('type' => '');
}elseif(!is_array($data)){
	$data = array($data);
}
if(!isset($data['type'])){
	$data['type'] = '';
}

switch($data['type']){
	case 'csv_export':
		$texts = array(
			'EMAIL_BODY' => JText::sprintf('MASS_CSV_EMAIL_BODY',hikashop_getDate(time()))
		);
		break;
	case 'product_notification':
		if($data['action']['bodyData'] == 'product_listing'){
			$productClass = hikashop_get('class.product');
			$texts = array(
				'EMAIL_BODY' => JText::_('MASS_CUSTOM_EMAIL_BODY')
			);
			$cids = array();
			foreach($data['elements'] as $item) {
				$cids = $item->product_id;
			}
			$productClass->getProducts($cids);
			$cartProducts = array();
			foreach($productClass->products as $item) {
				$cartProducts[] = array(
					'PRODUCT_NAME' => $item->product_name,
					'PRODUCT_PRICE' => $item->prices[0]->price_value,
					'PRODUCT_QUANTITY' => $item->product_quantity
				);
			}
			$templates = array();
			$templates['PRODUCT_LINE'] = $cartProducts;
			if(!empty($cids)){
				$vars = array(
					'product_listing' => 1
				);
			}else{
				$vars = array(
					'product_listing' => 0
				);
			}
		}elseif(!empty($data['action']['bodyData'])){
			$texts = array(
				'EMAIL_BODY' => JText::_($data['action']['bodyData'])
			);
		}else{
			$texts = array(
				'EMAIL_BODY' => JText::_('MASS_NOTIFICATION_PRODUCT_EMAIL_BODY')
			);
		}
		break;
	case 'address_notification':
			$texts = array(
				'EMAIL_BODY' => JText::_('MASS_NOTIFICATION_ADDRESS_EMAIL_BODY')
			);
		break;
	case 'category_notification':
			$texts = array(
				'EMAIL_BODY' => JText::_('MASS_NOTIFICATION_CATEGORY_EMAIL_BODY')
			);
		break;
	case 'order_notification':
			$texts = array(
				'EMAIL_BODY' => JText::_('MASS_NOTIFICATION_ORDER_EMAIL_BODY')
			);
		break;
	case 'user_notification':
			$texts = array(
				'EMAIL_BODY' => JText::_('MASS_NOTIFICATION_USER_EMAIL_BODY')
			);
		break;
	default :
		$texts = array(
			'EMAIL_BODY' => JText::sprintf('PLEASE_CHECK_MASS_SETTINGS',hikashop_getDate(time()))
		);
		break;
}
