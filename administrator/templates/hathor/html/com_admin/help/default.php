<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.language.help');
?>
<form action="<?php echo JRoute::_('index.php?option=com_admin&amp;view=help'); ?>" method="post" name="adminForm" id="adminForm">
<div class="width-50 fltrt helplinks">
	<ul class="helpmenu">
		<li><?php echo JHtml::_('link', JHelp::createUrl('JHELP_GLOSSARY'), JText::_('COM_ADMIN_GLOSSARY'), array('target' => 'helpFrame')) ?></li>
		<li><?php echo JHtml::_('link', 'http://www.gnu.org/licenses/gpl-2.0.html', JText::_('COM_ADMIN_LICENSE'), array('target' => 'helpFrame')) ?></li>
		<li><?php echo JHtml::_('link', $this->latest_version_check, JText::_('COM_ADMIN_LATEST_VERSION_CHECK'), array('target' => 'helpFrame')) ?></li>
		<li><?php echo JHtml::_('link', JHelp::createUrl('JHELP_START_HERE'), JText::_('COM_ADMIN_START_HERE'), array('target' => 'helpFrame')) ?></li>
	</ul>
</div>
<div class="clr"> </div>
	<div id="treecellhelp" class="width-20 fltleft">
		<fieldset class="adminform whitebg" title="<?php echo JText::_('COM_ADMIN_ALPHABETICAL_INDEX'); ?>">
			<legend><?php echo JText::_('COM_ADMIN_ALPHABETICAL_INDEX'); ?></legend>

			<div class="helpIndex">
				<ul class="subext">
					<?php foreach ($this->toc as $k => $v):?>
						<li>
						    <?php $url = JHelp::createUrl('JHELP_'.strtoupper($k)); ?>
							<?php echo JHtml::_('link', $url, $v, array('target' => 'helpFrame'));?>
						</li>
					<?php endforeach;?>
				</ul>
			</div>
		</fieldset>
	</div>

	<div id="datacellhelp" class="width-80 fltrt">
		<fieldset title="<?php echo JText::_('COM_ADMIN_VIEW'); ?>">
			<legend>
				<?php echo JText::_('COM_ADMIN_VIEW'); ?>
			</legend>
				<iframe name="helpFrame" src="<?php echo $this->page;?>" class="helpFrame"></iframe>
		</fieldset>
	</div>
</form>
