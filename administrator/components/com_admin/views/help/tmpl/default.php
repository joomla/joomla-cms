<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.language.help');
?>
<form action="<?php echo JRoute::_('index.php?option=com_admin&amp;view=help'); ?>" method="post" name="adminForm">
<div class="width-40 fltlft">
	<fieldset class="adminform">
		<legend><?php echo JText::_('Admin_Search'); ?></legend>
		<input class="textarea" type="hidden" name="option" value="com_admin" />
		<input type="text" name="helpsearch" size="40"  value="<?php echo $this->help_search;?>" class="inputbox" />
		<input type="submit" value="<?php echo JText::_('Admin_Go'); ?>" class="button" />
		<input type="button" value="<?php echo JText::_('Admin_Clear_Results'); ?>" class="button" onclick="f=document.adminForm;f.helpsearch.value='';f.submit()" />
	</fieldset>
</div>
<div class="width-50 fltrt helplinks">
	<ul class="helpmenu">
		<?php if ($this->help_url):?>
			<li><?php echo JHtml::_('link', JHelp::createUrl('joomla.glossary'), JText::_('Admin_Glossary'), array('target' => 'helpFrame')) ?></li>
			<li><?php echo JHtml::_('link', JHelp::createUrl('joomla.credits'), JText::_('Admin_Credits'), array('target' => 'helpFrame')) ?></li>
			<li><?php echo JHtml::_('link', JHelp::createUrl('joomla.support'), JText::_('Admin_Support'), array('target' => 'helpFrame')) ?></li>
		<?php else:?>
			<li><?php echo JHtml::_('link', JURI::base() .'help/'.$this->lang_tag.'/joomla.glossary.html', JText::_('Admin_Glossary'), array('target' => 'helpFrame')) ?></li>
			<li><?php echo JHtml::_('link', JURI::base() .'help/'.$this->lang_tag.'/joomla.credits.html', JText::_('Admin_Credits'), array('target' => 'helpFrame')) ?></li>
			<li><?php echo JHtml::_('link', JURI::base() .'help/'.$this->lang_tag.'/joomla.support.html', JText::_('Admin_Support'), array('target' => 'helpFrame')) ?></li>
		<?php endif;?>
		<li><?php echo JHtml::_('link', 'http://www.gnu.org/licenses/gpl-2.0.html', JText::_('Admin_License'), array('target' => 'helpFrame')) ?></li>
		<li><?php echo JHtml::_('link', $this->latest_version_check, JText::_('Admin_Latest_Version_Check'), array('target' => 'helpFrame')) ?></li>
	</ul>
</div>
<div class="clr"> </div>
	<div id="treecellhelp" class="width-20 fltleft">
		<fieldset class="adminform whitebg" title="<?php echo JText::_('Admin_Alphabetical_Index'); ?>">
			<legend><?php echo JText::_('Admin_Alphabetical_Index'); ?></legend>

			<div class="helpIndex">
				<ul class="subext">
					<?php foreach ($this->toc as $k=>$v):?>
						<li>
							<?php if ($this->help_url):?>
								<?php echo JHtml::_('link', JHelp::createUrl($k), $v, array('target' => 'helpFrame'));?>
							<?php else:?>
								<?php echo JHtml::_('link', JURI::base() .'help/'.$this->lang_tag.'/'.$k, $v, array('target' => 'helpFrame'));?>
							<?php endif;?>
						</li>
					<?php endforeach;?>
				</ul>
			</div>
		</fieldset>
	</div>

	<div id="datacellhelp" class="width-80 fltrt">
		<fieldset title="<?php echo JText::_('Admin_View'); ?>">
			<legend>
				<?php echo JText::_('Admin_View'); ?>
			</legend>
			<?php if ($this->help_url && $this->page != 'joomla.whatsnew.html'):?>
				<iframe name="helpFrame" src="<?php echo $this->full_help_url .preg_replace('#\.xml$|\.html$#', '', $this->page);?>" class="helpFrame" frameborder="0"></iframe>
			<?php else:?>
				<iframe name="helpFrame" src="<?php echo JURI::base() .'help/' .$this->lang_tag. '/' . $this->page;?>" class="helpFrame" frameborder="0"></iframe>
			<?php endif;?>
		</fieldset>
	</div>
</form>

