<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$user = JFactory::getUser();
$lang = JFactory::getLanguage();
?>
<div class="row-fluid">
	<div class="span3">
		<div class="sidebar-nav">
			<ul class="nav nav-list">
              <li class="nav-header">Submenu</li>
              <li class="active"><a href="<?php echo $this->baseurl; ?>">Dashboard</a></li>
              <li class="nav-header">System</li>
              <?php if($user->authorise('core.admin')):?>
              	<li><a href="<?php echo $this->baseurl; ?>/index.php?option=com_config">Global Configuration</a></li>
              	<li><a href="<?php echo $this->baseurl; ?>/index.php?option=com_admin&view=sysinfo">System Information</a></li>
              <?php endif;?>
              <?php if($user->authorise('core.manage', 'com_cache')):?>
             	 <li><a href="<?php echo $this->baseurl; ?>/index.php?option=com_cache">Clear Cache</a></li>
              <?php endif;?>
              <?php if($user->authorise('core.admin', 'com_checkin')):?>
              	<li><a href="<?php echo $this->baseurl; ?>/index.php?option=com_checkin">Global Check-in</a></li>
              <?php endif;?>
              <?php if($user->authorise('core.manage', 'com_installer')):?>
             	 <li><a href="<?php echo $this->baseurl; ?>/index.php?option=com_installer">Install Extensions</a></li>
              <?php endif;?>
            </ul>
        </div>
	</div>
	<div class="span9">
	<?php
	foreach ($this->modules as $module) {
		$output = JModuleHelper::renderModule($module, array('style' => 'well'));
		$params = new JRegistry;
		$params->loadString($module->params);
		echo $output;
	}
	?>
	</div>
</div>
