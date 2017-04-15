<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JFactory::getDocument()->addScriptDeclaration(
<<<JS
	jQuery(document).ready(function($) {
		$(':input[name="jform[activateMultilanguage]"]').each(function(el){
			$(this).click(function(){Install.toggle('installLocalisedContent', 'activateMultilanguage', 1);});
			$(this).click(function(){Install.toggle('activatePluginLanguageCode', 'activateMultilanguage', 1);});
		});
		Install.toggle('installLocalisedContent', 'activateMultilanguage', 1);
		Install.toggle('activatePluginLanguageCode', 'activateMultilanguage', 1);
	});
JS
);
?>
<?php echo JHtml::_('InstallationHtml.helper.stepbarlanguages'); ?>
<form action="index.php" method="post" id="adminForm" class="form-validate">
	<div class="btn-toolbar justify-content-end">
		<div class="btn-group">
			<a
				class="btn btn-secondary"
				href="#"
				onclick="return Install.goToPage('languages');"
				rel="prev"
				title="<?php echo JText::_('JPREVIOUS'); ?>">
				<span class="fa fa-arrow-left"></span>
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
					<span class="fa fa-arrow-right icon-white"></span>
					<?php echo JText::_('JNEXT'); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
	<h3><?php echo JText::_('INSTL_DEFAULTLANGUAGE_MULTILANGUAGE_TITLE'); ?></h3>
	<hr class="hr-condensed" />
	<p><?php echo JText::_('INSTL_DEFAULTLANGUAGE_MULTILANGUAGE_DESC'); ?></p>
	<div class="form-group">
		<?php echo $this->form->getLabel('activateMultilanguage'); ?>
		<?php echo $this->form->getInput('activateMultilanguage'); ?>
		<p class="form-text text-muted small"><?php echo JText::_('INSTL_DEFAULTLANGUAGE_ACTIVATE_MULTILANGUAGE_DESC'); ?></p>
	</div>
	<div id="multilanguageOptions">
		<div class="form-group" id="installLocalisedContent" style="display:none;">
			<?php echo $this->form->getLabel('installLocalisedContent'); ?>
			<?php echo $this->form->getInput('installLocalisedContent'); ?>
			<p class="form-text text-muted small"><?php echo JText::_('INSTL_DEFAULTLANGUAGE_INSTALL_LOCALISED_CONTENT_DESC'); ?></p>
		</div>
		<div class="form-group" id="activatePluginLanguageCode" style="display:none;">
			<?php echo $this->form->getLabel('activatePluginLanguageCode'); ?>
			<?php echo $this->form->getInput('activatePluginLanguageCode'); ?>
			<p class="form-text text-muted small"><?php echo JText::_('INSTL_DEFAULTLANGUAGE_ACTIVATE_LANGUAGE_CODE_PLUGIN_DESC'); ?></p>
		</div>
	</div>
	<h3><?php echo JText::_('INSTL_DEFAULTLANGUAGE_ADMINISTRATOR'); ?></h3>
	<hr class="hr-condensed" />
	<p><?php echo JText::_('INSTL_DEFAULTLANGUAGE_DESC'); ?></p>
	<table class="table table-striped table-sm">
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
		<?php foreach ($this->items->administrator as $i => $lang) : ?>
			<tr>
				<td>
					<input
						id="admin-language-cb<?php echo $i; ?>"
						type="radio"
						name="administratorlang"
						value="<?php echo $lang->language; ?>"
						<?php if ($lang->published) echo 'checked="checked"'; ?>
					>
				</td>
				<td align="text-center">
					<label for="admin-language-cb<?php echo $i; ?>">
						<?php echo $lang->name; ?>
					</label>
				</td>
				<td align="text-center">
					<?php echo $lang->language; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<h3><?php echo JText::_('INSTL_DEFAULTLANGUAGE_FRONTEND'); ?></h3>
	<hr class="hr-condensed" />
	<p><?php echo JText::_('INSTL_DEFAULTLANGUAGE_DESC_FRONTEND'); ?></p>
	<table class="table table-striped table-sm">
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
		<?php foreach ($this->items->frontend as $i => $lang) : ?>
			<tr>
				<td>
					<input
						id="site-language-cb<?php echo $i; ?>"
						type="radio"
						name="frontendlang"
						value="<?php echo $lang->language; ?>"
						<?php if ($lang->published) echo 'checked="checked"'; ?>
					>
				</td>
				<td align="text-center">
					<label for="site-language-cb<?php echo $i; ?>">
						<?php echo $lang->name; ?>
					</label>
				</td>
				<td align="text-center">
					<?php echo $lang->language; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<div class="btn-toolbar justify-content-end">
		<div class="btn-group">
			<a
				class="btn btn-secondary"
				href="#"
				onclick="return Install.goToPage('languages');"
				rel="prev"
				title="<?php echo JText::_('JPREVIOUS'); ?>">
				<span class="fa fa-arrow-left"></span>
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
					<span class="fa fa-arrow-right icon-white"></span>
					<?php echo JText::_('JNEXT'); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
	<input type="hidden" name="task" value="setdefaultlanguage">
	<?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/javascript">
	jQuery('input[name="jform[activateMultilanguage]"]').each(function(index, el) {
		jQuery(el).on('click', function() {
			Install.toggle('installLocalisedContent', 'activateMultilanguage', 1);
			Install.toggle('activatePluginLanguageCode', 'activateMultilanguage', 1);
		});
		Install.toggle('installLocalisedContent', 'activateMultilanguage', 1);
		Install.toggle('activatePluginLanguageCode', 'activateMultilanguage', 1);
	});
</script>
