<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php echo JHtml::_('installation.stepbar'); ?>
<form action="index.php" method="post" id="adminForm" class="form-validate form-horizontal">
	<div class="btn-toolbar">
		<div class="btn-group">
			<a class="btn" href="index.php?view=preinstall" onclick="return Install.goToPage('preinstall');" title="<?php echo JText::_('JCheck_Again'); ?>"><i class="icon-refresh"></i> <?php echo JText::_('JCheck_Again'); ?></a>
		</div>
		<div class="btn-group pull-right">
			<a class="btn" href="index.php?view=language" onclick="return Install.goToPage('language');" rel="prev" title="<?php echo JText::_('JPrevious'); ?>"><i class="icon-arrow-left"></i> <?php echo JText::_('JPrevious'); ?></a>
			<a  class="btn btn-primary" href="index.php?view=license" onclick="return Install.goToPage('database');" rel="next" title="<?php echo JText::_('JNext'); ?>"><i class="icon-arrow-right icon-white"></i> <?php echo JText::_('JNext'); ?></a>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span6">
			<h3>
				<?php echo JText::_('INSTL_PRECHECK_TITLE'); ?>
			</h3>
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
							<span class="label label-<?php echo ($option->state) ? 'success' : 'important'; ?>">
								<?php echo JText::_(($option->state) ? 'JYES' : 'JNO'); ?>
								<?php if ($option->notice):?>
									<i class="icon-info-sign icon-white" rel="tooltip" title="<?php echo $option->notice; ?>"></i>
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
			<h3>
				<?php echo JText::_('INSTL_PRECHECK_RECOMMENDED_SETTINGS_TITLE'); ?>
			</h3>
			<p class="install-text">
				<?php echo JText::_('INSTL_PRECHECK_RECOMMENDED_SETTINGS_DESC'); ?>
			</p>
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
							<span class="label label-<?php echo ($setting->state === $setting->recommended) ? 'success' : 'important'; ?> disabled">
								<?php echo JText::_(($setting->recommended) ? 'JON' : 'JOFF'); ?>
							</span>
						</td>
						<td>
							<span class="label label-<?php echo ($setting->state === $setting->recommended) ? 'success' : 'important'; ?>">
								<?php echo JText::_(($setting->state) ? 'JON' : 'JOFF'); ?>
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
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
