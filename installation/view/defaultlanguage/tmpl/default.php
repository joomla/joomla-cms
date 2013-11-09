<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<?php echo JHtml::_('installation.stepbarlanguages'); ?>
<form action="index.php" method="post" id="adminForm" class="form-validate form-horizontal">
	<div class="btn-toolbar">
		<div class="btn-group pull-right">
			<a
				class="btn"
				href="#"
				onclick="return Install.goToPage('languages');"
				rel="prev"
				title="<?php echo JText::_('JPREVIOUS'); ?>">
				<i class="icon-arrow-left"></i>
				<?php echo JText::_('JPREVIOUS'); ?>
			</a>
			<?php // Check if there are languages in the list, if not you cannot move forward ?>
			<?php if ($this->items->administrator) : ?>
				<a
					class="btn btn-primary"
					href="#"
					onclick="Install.submitform();"
					rel="next"
					title="<?php echo JText::_('JNEXT'); ?>">
					<i class="icon-arrow-right icon-white"></i>
					<?php echo JText::_('JNEXT'); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>

	<h3><?php echo JText::_('INSTL_DEFAULTLANGUAGE_MULTILANGUAGE_TITLE'); ?></h3>
	<hr class="hr-condensed" />
	<p><?php echo JText::_('INSTL_DEFAULTLANGUAGE_MULTILANGUAGE_DESC'); ?></p>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('activateMultilanguage'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('activateMultilanguage'); ?>
			<p class="help-block">
				<?php echo JText::_('INSTL_DEFAULTLANGUAGE_ACTIVATE_MULTILANGUAGE_DESC'); ?>
			</p>
		</div>
	</div>
	<div id="multilanguageOptions">
		<div class="control-group" id="installLocalisedContent" style="display:auto;">
			<div class="control-label">
				<?php echo $this->form->getLabel('installLocalisedContent'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('installLocalisedContent'); ?>
				<p class="help-block">
					<?php echo JText::_('INSTL_DEFAULTLANGUAGE_INSTALL_LOCALISED_CONTENT_DESC'); ?>
				</p>
			</div>
		</div>
		<div class="control-group" id="activatePluginLanguageCode" style="display:auto;">
			<div class="control-label">
				<?php echo $this->form->getLabel('activatePluginLanguageCode'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('activatePluginLanguageCode'); ?>
				<p class="help-block">
					<?php echo JText::_('INSTL_DEFAULTLANGUAGE_ACTIVATE_LANGUAGE_CODE_PLUGIN_DESC'); ?>
				</p>
			</div>
		</div>
	</div>


	<h3><?php echo JText::_('INSTL_DEFAULTLANGUAGE_ADMINISTRATOR'); ?></h3>
	<hr class="hr-condensed" />
	<p><?php echo JText::_('INSTL_DEFAULTLANGUAGE_DESC'); ?></p>
	<table class="table table-striped table-condensed">
		<thead>
		<tr>
			<th>
				<?php echo JText::_('INSTL_DEFAULTLANGUAGE_COLUMN_HEADER_SELECT'); ?>
			</th>
			<th>
				<?php echo JText::_('INSTL_DEFAULTLANGUAGE_COLUMN_HEADER_LANGUAGE'); ?>
			</th>
			<th>
				<?php echo JText::_('INSTL_DEFAULTLANGUAGE_COLUMN_HEADER_TAG'); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($this->items->administrator as $lang) : ?>
			<tr>
				<td>
					<input
						type="radio"
						name="administratorlang"
						value="<?php echo $lang->language; ?>"
						<?php if ($lang->published) echo 'checked="checked"'; ?>
						/>
				</td>
				<td align="center">
					<?php echo $lang->name; ?>
				</td>
				<td align="center">
					<?php echo $lang->language; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<h3><?php echo JText::_('INSTL_DEFAULTLANGUAGE_FRONTEND'); ?></h3>
	<hr class="hr-condensed" />
	<p><?php echo JText::_('INSTL_DEFAULTLANGUAGE_DESC_FRONTEND'); ?></p>
	<table class="table table-striped table-condensed">
		<thead>
		<tr>
			<th>
				<?php echo JText::_('INSTL_DEFAULTLANGUAGE_COLUMN_HEADER_SELECT'); ?>
			</th>
			<th>
				<?php echo JText::_('INSTL_DEFAULTLANGUAGE_COLUMN_HEADER_LANGUAGE'); ?>
			</th>
			<th>
				<?php echo JText::_('INSTL_DEFAULTLANGUAGE_COLUMN_HEADER_TAG'); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($this->items->frontend as $lang) : ?>
			<tr>
				<td>
					<input
						type="radio"
						name="frontendlang"
						value="<?php echo $lang->language; ?>"
						<?php if ($lang->published) echo 'checked="checked"'; ?>
						/>
				</td>
				<td align="center">
					<?php echo $lang->name; ?>
				</td>
				<td align="center">
					<?php echo $lang->language; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<input type="hidden" name="task" value="setdefaultlanguage" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/javascript">
	window.addEvent('domready', function() {
		document.getElements('input[name=jform[activateMultilanguage]]').each(function(el){
			el.addEvent('click', function(){Install.toggle('installLocalisedContent', 'activateMultilanguage', 1);});
			el.addEvent('click', function(){Install.toggle('activatePluginLanguageCode', 'activateMultilanguage', 1);});
		});
		Install.toggle('installLocalisedContent', 'activateMultilanguage', 1);
		Install.toggle('activatePluginLanguageCode', 'activateMultilanguage', 1);
	});
</script>
