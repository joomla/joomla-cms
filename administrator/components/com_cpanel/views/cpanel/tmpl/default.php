<?php defined('_JEXEC') or die; ?>

<?php
echo JHtml::_('sliders.start','panel-sliders',array('useCookie'=>'1'));

foreach ($this->modules as $module) {
	echo JHtml::_('sliders.panel', $module->title, 'cpanel-panel-'.$module->name);
	echo JModuleHelper::renderModule($module);
}

echo JHtml::_('sliders.end');
