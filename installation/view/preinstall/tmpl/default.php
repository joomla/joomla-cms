<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/* @var InstallationViewPreinstallHtml $this */
?>
<form action="index.php" method="post" id="languageForm" class="form-horizontal">
	<div class="btn-toolbar">
		<div class="btn-group pull-right">
			<a href="#" class="btn btn-primary" onclick="Install.submitform();" title="<?php echo JText::_('JCHECK_AGAIN'); ?>"><span class="icon-refresh icon-white"></span> <?php echo JText::_('JCHECK_AGAIN'); ?></a>
		</div>
	</div>
	<div class="control-group">
		<label for="jform_language" class="control-label"><?php echo JText::_('INSTL_SELECT_LANGUAGE_TITLE'); ?></label>
		<div class="controls">
			<?php echo $this->form->getInput('language'); ?>
		</div>
	</div>
	<input type="hidden" name="view" value="preinstall" />
	<input type="hidden" name="task" value="setlanguage" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<form action="index.php" method="post" id="adminForm" class="form-horizontal">
	<div class="row-fluid">
		<div class="span6">
			<h3><?php echo JText::_('INSTL_PRECHECK_TITLE'); ?></h3>
			<hr class="hr-condensed" />
			<p class="install-text">
				<?php echo JText::_('INSTL_PRECHECK_DESC'); ?>
			</p>
			<table class="table table-striped table-condensed">
				<tbody>
					<?php foreach ($this->options as $option) : ?>
					<tr>
						<td class="item">
							<?php echo $option->label; ?>
						</td>
						<td>
							<span class="label label-<?php echo $option->state ? 'success' : 'important'; ?>">
								<?php echo JText::_($option->state ? 'JYES' : 'JNO'); ?>
								<?php if ($option->notice) : ?>
									<span class="icon-info-sign icon-white hasTooltip" title="<?php echo $option->notice; ?>"></span>
								<?php endif;?>
							</span>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="2"></td>
					</tr>
				</tfoot>
			</table>
		</div>
		<div class="span6">
			<h3><?php echo JText::_('INSTL_PRECHECK_RECOMMENDED_SETTINGS_TITLE'); ?></h3>
			<hr class="hr-condensed" />
			<p class="install-text"><?php echo JText::_('INSTL_PRECHECK_RECOMMENDED_SETTINGS_DESC'); ?></p>
			<table class="table table-striped table-condensed">
				<thead>
					<tr>
						<th>
							<?php echo JText::_('INSTL_PRECHECK_DIRECTIVE'); ?>
						</th>
						<th>
							<?php echo JText::_('INSTL_PRECHECK_RECOMMENDED'); ?>
						</th>
						<th>
							<?php echo JText::_('INSTL_PRECHECK_ACTUAL'); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($this->settings as $setting) : ?>
					<tr>
						<td>
							<?php echo $setting->label; ?>
						</td>
						<td>
							<span class="label label-success disabled">
								<?php echo JText::_($setting->recommended ? 'JON' : 'JOFF'); ?>
							</span>
						</td>
						<td>
							<span class="label label-<?php echo ($setting->state === $setting->recommended) ? 'success' : 'warning'; ?>">
								<?php echo JText::_($setting->state ? 'JON' : 'JOFF'); ?>
							</span>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="3"></td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
	<div class="row-fluid">
		<div class="btn-toolbar">
			<div class="btn-group pull-right">
				<a href="#" class="btn btn-primary" onclick="Install.submitform();" title="<?php echo JText::_('JCHECK_AGAIN'); ?>"><span class="icon-refresh icon-white"></span> <?php echo JText::_('JCHECK_AGAIN'); ?></a>
			</div>
		</div>
	</div>
	<input type="hidden" name="task" value="preinstall" />
	<?php echo JHtml::_('form.token'); ?>
</form>
