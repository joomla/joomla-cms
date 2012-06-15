<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_cpanel
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>

<?php
echo JHtml::_('sliders.start', 'panel-sliders', array('useCookie'=>'1'));

foreach ($this->modules as $module) {
	$output = JModuleHelper::renderModule($module);
	$params = new JRegistry;
	$params->loadString($module->params);
	if ($params->get('automatic_title', '0')=='0') {
		echo JHtml::_('sliders.panel', $module->title, 'cpanel-panel-'.$module->name);
	}
	elseif (method_exists('mod'.$module->name.'Helper', 'getTitle')) {
		echo JHtml::_('sliders.panel', call_user_func_array(array('mod'.$module->name.'Helper', 'getTitle'), array($params)), 'cpanel-panel-'.$module->name);
	}
	else {
		echo JHtml::_('sliders.panel', JText::_('MOD_'.$module->name.'_TITLE'), 'cpanel-panel-'.$module->name);
	}
	echo $output;
}

echo JHtml::_('sliders.end');
