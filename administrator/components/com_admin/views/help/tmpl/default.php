<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="<?php echo JRoute::_('index.php?option=com_admin&amp;task=help'); ?>" method="post" name="adminForm">

<table class="adminform" border="1">
<tr>
	<td colspan="2">
		<table width="100%">
			<tr>
				<td>
					<strong><?php echo JText::_('Search'); ?>:</strong>
					<input class="text_area" type="hidden" name="option" value="com_admin" />
					<input type="text" name="helpsearch" value="<?php echo $this->helpsearch;?>" class="inputbox" />
					<input type="submit" value="<?php echo JText::_('Go'); ?>" class="button" />
					<input type="button" value="<?php echo JText::_('Clear Results'); ?>" class="button" onclick="f=document.adminForm;f.helpsearch.value='';f.submit()" />
				</td>
				<td class="helpMenu">
					<?php
					if ($helpurl) {
					?>
					<?php echo JHTML::_('link', JHelp::createUrl('joomla.glossary'), JText::_('Glossary'), array('target' => 'helpFrame')) ?>
					|
					<?php echo JHTML::_('link', JHelp::createUrl('joomla.credits'), JText::_('Credits'), array('target' => 'helpFrame')) ?>
					|
					<?php echo JHTML::_('link', JHelp::createUrl('joomla.support'), JText::_('Support'), array('target' => 'helpFrame')) ?>
					<?php
					} else {
					?>
					<?php echo JHTML::_('link', JURI::base() .'help/'.$this->langTag.'/joomla.glossary.html', JText::_('Glossary'), array('target' => 'helpFrame')) ?>
					|
					<?php echo JHTML::_('link', JURI::base() .'help/'.$this->langTag.'/joomla.credits.html', JText::_('Credits'), array('target' => 'helpFrame')) ?>
					|
					<?php echo JHTML::_('link', JURI::base() .'help/'.$this->langTag.'/joomla.support.html', JText::_('Support'), array('target' => 'helpFrame')) ?>
					<?php
					}
					?>
					|
					<?php echo JHTML::_('link', 'http://www.gnu.org/licenses/gpl-2.0.html', JText::_('License'), array('target' => 'helpFrame')) ?>
					|
					<?php echo JHTML::_('link', 'http://help.joomla.org', 'help.joomla.org', array('target' => 'helpFrame')) ?>
					|
					<?php echo JHTML::_('link', 'index.php?option=com_admin&amp;task=changelog&amp;tmpl=component', JText::_('Changelog'), array('target' => 'helpFrame')) ?>
					|
					<?php echo JHTML::_('link', 'http://www.joomla.org/content/blogcategory/57/111/', JText::_('Latest Version Check'), array('target' => 'helpFrame')) ?>
				</td>
			</tr>
		</table>
	</td>
</tr>
</table>

<div id="treecellhelp">
	<fieldset title="<?php echo JText::_('Alphabetical Index'); ?>">
		<legend>
			<?php echo JText::_('Alphabetical Index'); ?>
		</legend>

		<div class="helpIndex">
			<ul class="subext">
				<?php
				$helpurl = $mainframe->getCfg('helpurl');
				foreach ($this->toc as $k=>$v) {
					if ($helpurl) {
						echo '<li>';
						echo JHTML::_('link', JHelp::createUrl($k), $v, array('target' => 'helpFrame'));
						echo '</li>';
					} else {
						echo '<li>';
						echo JHTML::_('link', JURI::base() .'help/'.$this->langTag.'/'.$k, $v, array('target' => 'helpFrame'));
						echo '</li>';
					}
				}
				?>
			</ul>
		</div>
	</fieldset>
</div>

<div id="datacellhelp">
	<fieldset title="<?php echo JText::_('View'); ?>">
		<legend>
			<?php echo JText::_('View'); ?>
		</legend>
		<?php
		if ($helpurl && $this->page != 'joomla.whatsnew15.html') {
			?>
			<iframe name="helpFrame" src="<?php echo $this->fullhelpurl .preg_replace('#\.xml$|\.html$#', '', $this->page);?>" class="helpFrame" frameborder="0"></iframe>
			<?php
		} else {
			?>
			<iframe name="helpFrame" src="<?php echo JURI::base() .'/help/' .$this->lang->getTag(). '/' . $this->page;?>" class="helpFrame" frameborder="0"></iframe>
			<?php
		}
		?>
	</fieldset>
</div>

<input type="hidden" name="task" value="help" />
</form>