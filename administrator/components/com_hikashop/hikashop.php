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
if(!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);

include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php');

$taskGroup = JRequest::getCmd('ctrl','dashboard');
$config =& hikashop_config();
JHTML::_('behavior.tooltip');
if(!HIKASHOP_PHP5) {
	$bar = & JToolBar::getInstance('toolbar');
	$app =& JFactory::getApplication();
	$app->enqueueMessage('WARNING: PHP4 is not safe to use since 2008. Because of that we are discontinuing support for PHP 4 in newer versions of HikaShop. Please ask your hosting company to migrate your server to PHP 5.2 minimum.');
} else {
	$bar = JToolBar::getInstance('toolbar');
}
$bar->addButtonPath(HIKASHOP_BUTTON);

if($taskGroup != 'update' && !$config->get('installcomplete')){
	$url = hikashop_completeLink('update&task=install',false,true);
	echo "<script>document.location.href='".$url."';</script>\n";
	echo 'Install not finished... You will be redirected to the second part of the install screen<br/>';
	echo '<a href="'.$url.'">Please click here if you are not automatically redirected within 3 seconds</a>';
	return;
}

$className = ucfirst($taskGroup).'Controller';

$currentuser = JFactory::getUser();
if($taskGroup != 'update' && HIKASHOP_J16 && !$currentuser->authorise('core.manage', 'com_hikashop')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}
if($taskGroup == 'config' && HIKASHOP_J16 && !$currentuser->authorise('core.admin', 'com_hikashop')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

if(!class_exists($className) && (!file_exists(HIKASHOP_CONTROLLER.$taskGroup.'.php') || !include_once(HIKASHOP_CONTROLLER.$taskGroup.'.php'))) {
	if(!hikashop_getPluginController($taskGroup))
		return JError::raiseError(404, 'Page not found : '.$taskGroup);
}
ob_start();

$classGroup = new $className();
JRequest::setVar( 'view', $classGroup->getName() );
$classGroup->execute( JRequest::getCmd('task','listing'));
$classGroup->redirect();
if(JRequest::getString('tmpl') !== 'component'){
	echo hikashop_footer();
}
echo '<div id="hikashop_main_content">'.ob_get_clean().'</div>';

hikashop_cleanCart();
