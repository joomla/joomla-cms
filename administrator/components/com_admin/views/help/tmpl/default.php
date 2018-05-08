<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
?>
<form action="<?php echo JRoute::_('index.php?option=com_admin&amp;view=help'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row-fluid">
		<div id="sidebar" class="span3">
			<div class="clearfix"></div>
			<div class="sidebar-nav">
				<ul class="nav nav-list">
					<li><?php echo JHtml::_('link', JHelp::createUrl('JHELP_START_HERE'), JText::_('COM_ADMIN_START_HERE'), array('target' => 'helpFrame')); ?></li>
					<li><?php echo JHtml::_('link', $this->latest_version_check, JText::_('COM_ADMIN_LATEST_VERSION_CHECK'), array('target' => 'helpFrame')); ?></li>
					<li><?php echo JHtml::_('link', 'https://www.gnu.org/licenses/gpl-2.0.html', JText::_('COM_ADMIN_LICENSE'), array('target' => 'helpFrame')); ?></li>
					<li><?php echo JHtml::_('link', JHelp::createUrl('JHELP_GLOSSARY'), JText::_('COM_ADMIN_GLOSSARY'), array('target' => 'helpFrame')); ?></li>
					<li class="divider"></li>
					<li class="nav-header"><?php echo JText::_('COM_ADMIN_ALPHABETICAL_INDEX'); ?></li>
					<?php foreach ($this->toc as $k => $v) : ?>
						<li>
							<?php $url = JHelp::createUrl('JHELP_' . strtoupper($k)); ?>
							<?php echo JHtml::_('link', $url, $v, array('target' => 'helpFrame')); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<div class="span9">
			<iframe name="helpFrame" title="helpFrame" height="2100px" src="<?php echo $this->page; ?>" class="helpFrame table table-bordered"></iframe>
		</div>
	</div>
	<input class="textarea" type="hidden" name="option" value="com_admin" />
</form>
