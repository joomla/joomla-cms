<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_cpanel
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>

<?php
echo JHtml::_('sliders.start','panel-sliders',array('useCookie'=>'1'));

foreach ($this->modules as $module) {
	echo JHtml::_('sliders.panel', $module->title, 'cpanel-panel-'.$module->name);
	echo JModuleHelper::renderModule($module);
}

echo JHtml::_('sliders.end');
