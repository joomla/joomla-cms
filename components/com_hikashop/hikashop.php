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
jimport('joomla.application.component.controller');
jimport('joomla.application.component.view');
JRequest::setVar('hikashop_front_end_main',1);

if(!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);
include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php');

if(defined('JDEBUG') && JDEBUG){
	error_reporting(E_ALL);
 	@ini_set("display_errors", 1);
}

$config =& hikashop_config();
if($config->get('store_offline')){
	$app = JFactory::getApplication();
	$app->enqueueMessage(JText::_('SHOP_IN_MAINTENANCE'));
	return;
}

global $Itemid;
if(empty($Itemid)){
	$urlItemid = JRequest::getInt('Itemid');
	if($urlItemid){
		$Itemid = $urlItemid;
	}
}

$view = JRequest::getCmd('view');
if(!empty($view) && !JRequest::getCmd('ctrl')){
	JRequest::setVar('ctrl',$view);
	$layout = JRequest::getCmd('layout');
	if(!empty($layout)){
		JRequest::setVar('task',$layout);
	}
}

if(HIKASHOP_J30) {
	$token = hikashop_getFormToken();
	$isToken = JRequest::getVar($token, '');
	if(!empty($isToken) && !JRequest::checkToken('request')) {
		$app = JFactory::getApplication();
		$app->input->request->set($token, 1);
	}
}

$session = JFactory::getSession();
if(is_null($session->get('registry'))){
	jimport('joomla.registry.registry');
	$session->set('registry', new JRegistry('session'));
}
$taskGroup = JRequest::getCmd('ctrl','category');
$className = ucfirst($taskGroup).'Controller';

if(!class_exists($className) && (!file_exists(HIKASHOP_CONTROLLER.$taskGroup.'.php') || !@include(HIKASHOP_CONTROLLER.$taskGroup.'.php'))){
	return JError::raiseError(404, 'Page not found : '.$taskGroup);
}
if($taskGroup != 'checkout'){
	$app = JFactory::getApplication();
	$app->setUserState('com_hikashop.ssl_redirect',0);
}

$classGroup = new $className();

JRequest::setVar('view', $classGroup->getName() );

$classGroup->execute(JRequest::getCmd('task'));

$classGroup->redirect();
if(JRequest::getString('tmpl') !== 'component'){
	echo hikashop_footer();
}

JRequest::setVar('hikashop_front_end_main',0);
