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
class userController extends hikashopController{
	var $delete = array();
	var $modify = array();
	var $modify_views = array();
	var $add = array();
	function __construct($config = array(),$skip=false){
		parent::__construct($config,$skip);
		if(!$skip){
			$this->registerDefaultTask('cpanel');
		}
		$this->display[]='cpanel';
		$this->display[]='form';
		$this->display[]='register';
		$this->display[]='downloads';
	}

	function register(){
		if(empty($_REQUEST['data'])){
			return $this->form();
		}
		$class = hikashop_get('class.user');
		$status = $class->register($this,'user');
		if($status){
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('THANK_YOU_FOR_REGISTERING',HIKASHOP_LIVE));
			JRequest::setVar( 'layout', 'after_register'  );
			return parent::display();
		}
		$this->form();
	}

	function cpanel(){
		if(!$this->_checkLogin()) return true;
		JRequest::setVar( 'layout', 'cpanel'  );
		return parent::display();
	}

	function form(){
		$user = JFactory::getUser();
		if ($user->guest) {
			JRequest::setVar( 'layout', 'form'  );
			return $this->display();
		}else{
			$app=JFactory::getApplication();
			$app->redirect(hikashop_completeLink('user&task=cpanel',false,true));
			return false;
		}
	}

	function downloads(){
		if(!$this->_checkLogin()) return true;
		JRequest::setVar( 'layout', 'downloads'  );
		return parent::display();
	}

	function _checkLogin(){
		$user = JFactory::getUser();
		if ($user->guest) {
			$app=JFactory::getApplication();
			$app->enqueueMessage(JText::_('PLEASE_LOGIN_FIRST'));
			global $Itemid;
			$url = '';
			if(!empty($Itemid)){
				$url='&Itemid='.$Itemid;
			}
			if(!HIKASHOP_J16){
				$url = 'index.php?option=com_user&view=login'.$url;
			}else{
				$url = 'index.php?option=com_users&view=login'.$url;
			}
			$app->redirect(JRoute::_($url.'&return='.urlencode(base64_encode(hikashop_currentUrl('',false))),false));
			return false;
		}
		return true;
	}

}
