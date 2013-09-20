<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$user = JFactory::getUser();
?>
<div class="row-fluid">
	<div class="span3 cpanel-links">
		<?php
		// Display the submenu position modules
		$this->iconmodules = JModuleHelper::getModules('icon');
		foreach ($this->iconmodules as $iconmodule)
		{
			$output = JModuleHelper::renderModule($iconmodule, array('style' => ''));
			$params = new JRegistry;
			$params->loadString($iconmodule->params);
			echo $output;
		}
		?>
	</div>
	<div class="span9">
		<?php
		foreach ($this->modules as $module)
		{
			$output = JModuleHelper::renderModule($module, array('style' => 'well'));
			$params = new JRegistry;
			$params->loadString($module->params);
			echo $output;
		}
		?>
	</div>
</div>
