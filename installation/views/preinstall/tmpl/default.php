<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the JavaScript behaviors.
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('script', 'installation/template/js/installation.js', true, false, false, false);
?>

<div id="stepbar">
	<div class="t">
		<div class="t">
			<div class="t"></div>
		</div>
	</div>
	<div class="m">
		<?php echo JHtml::_('installation.stepbar', 2); ?>
		<div class="box"></div>
	</div>
	<div class="b">
		<div class="b">
			<div class="b"></div>
		</div>
	</div>
</div>

<form action="index.php" method="post" id="adminForm" class="form-validate">
<div id="right">
	<div id="rightpad">
		<div id="step">
			<div class="t">
				<div class="t">
					<div class="t"></div>
				</div>
			</div>
			<div class="m">
				<div class="far-right">
<?php if ($this->document->direction == 'ltr') : ?>
					<div class="button1-left"><div class="refresh"><a href="index.php?view=preinstall" title="<?php echo JText::_('JCheck_Again'); ?>"><?php echo JText::_('JCheck_Again'); ?></a></div></div>
					<div class="button1-right"><div class="prev"><a href="index.php?view=language" rel="prev" title="<?php echo JText::_('JPrevious'); ?>"><?php echo JText::_('JPrevious'); ?></a></div></div>
	<?php if ($this->sufficient) : ?>
					<div class="button1-left"><div class="next"><a href="index.php?view=license" rel="next" title="<?php echo JText::_('JNext'); ?>"><?php echo JText::_('JNext'); ?></a></div></div>
	<?php endif; ?>
<?php elseif ($this->document->direction == 'rtl') : ?>
	<?php if ($this->sufficient) : ?>
					<div class="button1-right"><div class="prev"><a href="index.php?view=license" rel="next" title="<?php echo JText::_('JNext'); ?>"><?php echo JText::_('JNext'); ?></a></div></div>
	<?php endif; ?>
					<div class="button1-left"><div class="next"><a href="index.php?view=language" rel="prev" title="<?php echo JText::_('JPrevious'); ?>"><?php echo JText::_('JPrevious'); ?></a></div></div>
					<div class="button1-left"><div class="refresh"><a href="index.php?view=preinstall" title="<?php echo JText::_('JCheck_Again'); ?>"><?php echo JText::_('JCheck_Again'); ?></a></div></div>
<?php endif; ?>
				</div>
				<span class="step"><?php echo JText::_('INSTL_PRECHECK_TITLE'); ?></span>
			</div>
			<div class="b">
				<div class="b">
					<div class="b"></div>
				</div>
			</div>
		</div>
		<div id="installer">
			<div class="t">
				<div class="t">
					<div class="t"></div>
				</div>
			</div>
			<div class="m">
				<h2><?php echo JText::sprintf('INSTL_PRECHECK_FOR_VERSION', $this->version->getLongVersion()); ?></h2>
				<div class="install-text">
					<?php echo JText::_('INSTL_PRECHECK_DESC'); ?>
				</div>
				<div class="install-body">
					<div class="t">
						<div class="t">
							<div class="t"></div>
						</div>
					</div>
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
					<div class="b">
						<div class="b">
							<div class="b"></div>
						</div>
					</div>

					<div class="clr"></div>
				</div>

				<div class="newsection"></div>

				<h2><?php echo JText::_('INSTL_PRECHECK_RECOMMENDED_SETTINGS_TITLE'); ?></h2>
				<div class="install-text">
					<?php echo JText::_('INSTL_PRECHECK_RECOMMENDED_SETTINGS_DESC'); ?>
				</div>
				<div class="install-body">
					<div class="t">
						<div class="t">
							<div class="t"></div>
						</div>
					</div>
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
					<div class="b">
						<div class="b">
							<div class="b"></div>
						</div>
					</div>
					<div class="clr"></div>
				</div>
				<div class="clr"></div>
			</div>
			<div class="b">
				<div class="b">
					<div class="b"></div>
				</div>
			</div>
		</div>
	</div>
	<input type="hidden" name="task" value="" />
<?php echo JHtml::_('form.token'); ?>
</div>
<div class="clr"></div>
</form>
