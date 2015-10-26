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
class plgSystemHikashopregistrationredirect extends JPlugin
{
	function plgSystemHikashopregistrationredirect(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('system', 'hikashopregistrationredirect');
			if(version_compare(JVERSION,'2.5','<')){
				jimport('joomla.html.parameter');
				$this->params = new JParameter(@$plugin->params);
			} else {
				$this->params = new JRegistry(@$plugin->params);
			}
		}
	}


	function onAfterRoute(){
		$app = JFactory::getApplication();
		if ($app->isAdmin()) return true;

		if((@$_REQUEST['option']=='com_user' && @$_REQUEST['view']=='register') || (@$_REQUEST['option']=='com_users' && @$_REQUEST['view']=='registration' && !in_array(@$_REQUEST['task'],array('remind.remind','reset.request','reset.confirm','reset.complete')))){

			$Itemid = $this->params->get('item_id');
			if(empty($Itemid)){
				global $Itemid;
				if(empty($Itemid)){
					$urlItemid = JRequest::getInt('Itemid');
					if($urlItemid){
						$Itemid = $urlItemid;
					}
				}
			}
			if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')) return true;
			$menuClass = hikashop_get('class.menus');
			if(!empty($Itemid)){
				$Itemid = $menuClass->loadAMenuItemId('','',$Itemid);
			}
			if(empty($Itemid)){
				$Itemid = $menuClass->loadAMenuItemId('','');
			}
			$url_itemid = '';
			if(!empty($Itemid)){
				$url_itemid.='&Itemid='.$Itemid;
			}

			$app->redirect(JRoute::_('index.php?option=com_hikashop&ctrl=user&task=form'.$url_itemid, false));
		}
		return true;
	}

}
