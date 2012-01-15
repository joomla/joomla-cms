<?php
/**
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div id="step">
	<div class="far-right">
<?php if ($this->document->direction == 'ltr') : ?>
		<div class="button1-left"><div class="refresh"><a href="index.php?view=preinstall" onclick="return Install.goToPage('preinstall');" title="<?php echo JText::_('JCheck_Again'); ?>"><?php echo JText::_('JCheck_Again'); ?></a></div></div>
		<div class="button1-right"><div class="prev"><a href="index.php?view=language" onclick="return Install.goToPage('language');" rel="prev" title="<?php echo JText::_('JPrevious'); ?>"><?php echo JText::_('JPrevious'); ?></a></div></div>
	<?php if ($this->sufficient) : ?>
		<div class="button1-left"><div class="next"><a href="index.php?view=license" onclick="return Install.goToPage('license');" rel="next" title="<?php echo JText::_('JNext'); ?>"><?php echo JText::_('JNext'); ?></a></div></div>
	<?php endif; ?>
<?php elseif ($this->document->direction == 'rtl') : ?>
	<?php if ($this->sufficient) : ?>
		<div class="button1-right"><div class="prev"><a href="index.php?view=license" onclick="return Install.goToPage('license');" rel="next" title="<?php echo JText::_('JNext'); ?>"><?php echo JText::_('JNext'); ?></a></div></div>
	<?php endif; ?>
		<div class="button1-left"><div class="next"><a href="index.php?view=language" onclick="return Install.goToPage('language');" rel="prev" title="<?php echo JText::_('JPrevious'); ?>"><?php echo JText::_('JPrevious'); ?></a></div></div>
		<div class="button1-left"><div class="refresh"><a href="index.php?view=preinstall" onclick="return Install.goToPage('preinstall');" title="<?php echo JText::_('JCheck_Again'); ?>"><?php echo JText::_('JCheck_Again'); ?></a></div></div>
<?php endif; ?>
	</div>
	<h2><?php echo JText::_('INSTL_PRECHECK_TITLE'); ?></h2>
</div>
<form action="index.php" method="post" id="adminForm" class="form-validate">
	<div id="installer">
		<div class="m">
			<h3><?php echo JText::sprintf('INSTL_PRECHECK_FOR_VERSION', $this->version->getLongVersion()); ?></h3>
			<div class="install-text">
				<?php echo JText::_('INSTL_PRECHECK_DESC'); ?>
			</div>
			<div class="install-body">
				<div class="m">
					<fieldset>
						<table class="content">
							<tbody>
<?php foreach ($this->options as $option) : ?>
							<tr>
								<td class="item">
									<?php echo $option->label; ?>
								</td>
								<td>
									<span class="<?php echo ($option->state) ? 'green' : 'red'; ?>">
										<?php echo JText::_(($option->state) ? 'JYES' : 'JNO'); ?>
									</span>
									<span class="small">
										<?php echo $option->notice; ?>&#160;
									</span>
								</td>
							</tr>
<?php endforeach; ?>
							</tbody>
						</table>
					</fieldset>
				</div>
			</div>

			<div class="newsection"></div>

			<h3><?php echo JText::_('INSTL_PRECHECK_RECOMMENDED_SETTINGS_TITLE'); ?></h3>
			<div class="install-text">
				<?php echo JText::_('INSTL_PRECHECK_RECOMMENDED_SETTINGS_DESC'); ?>
			</div>
			<div class="install-body">
				<div class="m">
					<fieldset>
						<table class="content">
							<thead>
							<tr>
								<td class="toggle">
									<?php echo JText::_('INSTL_PRECHECK_DIRECTIVE'); ?>
								</td>
								<td class="toggle">
									<?php echo JText::_('INSTL_PRECHECK_RECOMMENDED'); ?>
								</td>
								<td class="toggle">
									<?php echo JText::_('INSTL_PRECHECK_ACTUAL'); ?>
								</td>
							</tr>
							</thead>
							<tbody>
<?php foreach ($this->settings as $setting) : ?>
								<tr>
									<td class="item">
										<?php echo $setting->label; ?>
									</td>
									<td class="toggle">
										<span>
										<?php echo JText::_(($setting->recommended) ? 'JON' : 'JOFF'); ?>
										</span>
									</td>
									<td>
										<span class="<?php echo ($setting->state === $setting->recommended) ? 'green' : 'red'; ?>">
										<?php echo JText::_(($setting->state) ? 'JON' : 'JOFF'); ?>
										</span>
									</td>
								</tr>
<?php endforeach; ?>
							</tbody>
						</table>
					</fieldset>
				</div>
			</div>
			<div class="clr"></div>
		</div>
	</div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
