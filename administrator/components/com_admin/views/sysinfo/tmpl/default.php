<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Add specific helper files for html generation
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
?>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row-fluid">
		<!-- Begin Content -->
		<div class="span10">
			<ul class="nav nav-tabs">
				<li class="active"><a href="#site" data-toggle="tab"><?php echo JText::_('COM_ADMIN_SYSTEM_INFORMATION');?></a></li>
				<li><a href="#phpsettings" data-toggle="tab"><?php echo JText::_('COM_ADMIN_PHP_SETTINGS');?></a></li>
				<li><a href="#config" data-toggle="tab"><?php echo JText::_('COM_ADMIN_CONFIGURATION_FILE');?></a></li>
				<li><a href="#directory" data-toggle="tab"><?php echo JText::_('COM_ADMIN_DIRECTORY_PERMISSIONS');?></a></li>
				<li><a href="#phpinfo" data-toggle="tab"><?php echo JText::_('COM_ADMIN_PHP_INFORMATION');?></a></li>
			</ul>
			<div class="tab-content">
				<!-- Begin Tabs -->
				<div class="tab-pane active" id="site">
					<?php echo $this->loadTemplate('system'); ?>
				</div>
				<div class="tab-pane" id="phpsettings">
					<?php echo $this->loadTemplate('phpsettings'); ?>
				</div>
				<div class="tab-pane" id="config">
					<?php echo $this->loadTemplate('config'); ?>
				</div>
				<div class="tab-pane" id="directory">
					<?php echo $this->loadTemplate('directory'); ?>
				</div>
				<div class="tab-pane" id="phpinfo">
					<?php echo $this->loadTemplate('phpinfo'); ?>
				</div>
				<!-- End Tabs -->
			</div>
		</div>
		<!-- End Content -->
	</div>
</form>
