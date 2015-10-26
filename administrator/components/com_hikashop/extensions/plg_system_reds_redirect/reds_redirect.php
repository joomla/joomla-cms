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

class plgSystemReds_redirect extends JPlugin {

	function plgSystemReds_redirect(&$subject, $config) {
		parent::__construct($subject, $config);
	}

	function onAfterRoute()
	{
		$app = JFactory::getApplication();
		if( JRequest::getString('option') != 'com_redshop' || $app->isAdmin() )
			return true;

		$redsProdId = JRequest::getInt('pid');
		$redsCatId = JRequest::getInt('cid');
		$redsOrderId= JRequest::getInt('oid');

		$url = null; //HIKASHOP_LIVE;
		$db = JFactory::getDBO();
		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php'))
			return true;

		$query='SHOW TABLES LIKE '.$db->Quote($db->getPrefix().substr(hikashop_table('reds_prod'),3));

		$db->setQuery($query);
		$table = $db->loadResult();
		if(empty($table))
			return true;

		if( !empty($redsProdId) && $redsProdId > 0 ) {
			$query = "SELECT a.hk_id, b.product_name as 'name' FROM `#__hikashop_reds_prod` a INNER JOIN `#__hikashop_product` b ON a.hk_id = b.product_id WHERE a.reds_id = " . $redsProdId . ";";
			$baseUrl = 'product&task=show';
		} else if( !empty($redsCatId)  && $redsCatId > 0 ) {
			$id = 'reds-fallback';
			$alias = 'hikashop-menu-for-module-'.$id;
			$db->setQuery('SELECT id FROM '.hikashop_table('menu',false).' WHERE alias=\''.$alias.'\''); //Set ?
			$itemId = $db->loadResult();
			if(empty($itemId)) {
				$options = new stdClass();
				$config =& hikashop_config();
				$options->hikashop_params = $config->get('default_params',null);
				$classMenu = hikashop_get('class.menus');
				$classMenu->loadParams($options);
				$options->hikashop_params['content_type'] = 'category';
				$options->hikashop_params['layout_type']='div';
				$options->hikashop_params['content_synchronize']='1';
				if($options->hikashop_params['columns']==1){
					$options->hikashop_params['columns']=3;
				}
				$classMenu->createMenu($options->hikashop_params, $id);
				$itemId = $options->hikashop_params['itemid'];
			}
			$query = "SELECT a.hk_id, b.category_name as 'name' FROM `#__hikashop_reds_cat` a INNER JOIN `#__hikashop_category` b ON a.hk_id = b.category_id WHERE a.reds_id = " . $redsCatId . " and a.category_type = 'category';";
			$baseUrl = 'category&task=listing&Itemid='.$itemId;
		}elseif(!empty($redsOrderId)){
			$db->setQuery('SELECT order_id FROM '.hikashop_table('order').' WHERE order_reds_id='.$redsOrderId);
			$hikaOrderId = $db->loadResult();
			if(!empty($hikaOrderId)){
				$url = hikashop_completeLink('order&task=show&cid='.$hikaOrderId, false, true);
				$app->redirect($url);
				return true;
			}
		}

		if( !empty($query) && !empty($baseUrl) ) {
			$db->setQuery($query);
			$link = $db->loadObject();
			if( $link ) {
				if(method_exists($app,'stringURLSafe')) {
					$name = $app->stringURLSafe(strip_tags($link->name));
				} else {
					$name = JFilterOutput::stringURLSafe(strip_tags($link->name));
				}
				$url = hikashop_completeLink($baseUrl.'&cid='.$link->hk_id.'&name='.$name, false, true);
			}
		}

		if( $url )
			$app->redirect($url,'','message',true);
	}
}
