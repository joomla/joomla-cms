<?php defined('_JEXEC') or die; ?>

<?php
jimport('joomla.html.pane');
$pane = &JPane::getInstance('sliders');
echo $pane->startPane('content-pane');

foreach ($this->modules as $module) {
	echo $pane->startPanel($module->title, 'cpanel-panel-'.$module->name);
	echo JModuleHelper::renderModule($module);
	echo $pane->endPanel();
}

echo $pane->endPane();