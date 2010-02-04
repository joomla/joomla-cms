<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the JavaScript behaviors.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>

<script language="JavaScript" type="text/javascript">
<!--
	function validateForm(frm, task) {
		Joomla.submitform(task);
	}
// -->
</script>

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

<form action="index.php" method="post" name="adminForm" class="form-validate">
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
					<div class="button1-right"><div class="prev"><a href="index.php?view=language" title="<?php echo JText::_('JPrevious'); ?>"><?php echo JText::_('JPrevious'); ?></a></div></div>
					<div class="button1-left"><div class="next"><a href="index.php?view=license" title="<?php echo JText::_('JNext'); ?>"><?php echo JText::_('JNext'); ?></a></div></div>
<?php elseif ($this->document->direction == 'rtl') : ?>
					<div class="button1-right"><div class="prev"><a href="index.php?view=license" title="<?php echo JText::_('JNext'); ?>"><?php echo JText::_('JNext'); ?></a></div></div>
					<div class="button1-left"><div class="next"><a href="index.php?view=language" title="<?php echo JText::_('JPrevious'); ?>"><?php echo JText::_('JPrevious'); ?></a></div></div>
					<div class="button1-left"><div class="refresh"><a href="index.php?view=preinstall" title="<?php echo JText::_('JCheck_Again'); ?>"><?php echo JText::_('JCheck_Again'); ?></a></div></div>
<?php endif; ?>
				</div>
				<span class="step"><?php echo JText::_('Instl_Precheck_Title'); ?></span>
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
				<h2><?php echo JText::sprintf('Instl_Precheck_for_version', $this->version->getLongVersion()); ?></h2>
				<div class="install-text">
					<?php echo JText::_('Instl_Precheck_Desc'); ?>
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
									<td class="item" valign="top">
										<?php echo $option->label; ?>
									</td>
									<td valign="top">
										<span class="<?php echo ($option->state) ? 'green' : 'red'; ?>">
											<?php echo JText::_(($option->state) ? 'JYes' : 'JNo'); ?>
										</span>
										<span class="small">
											<?php echo $option->notice; ?>&nbsp;
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

				<h2><?php echo JText::_('Instl_Precheck_Recommended_settings_Title'); ?></h2>
				<div class="install-text">
					<?php echo JText::_('Instl_Precheck_Recommended_settings_Desc'); ?>
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
										<?php echo JText::_('Instl_Precheck_Directive'); ?>
									</td>
									<td class="toggle">
										<?php echo JText::_('Instl_Precheck_Recommended'); ?>
									</td>
									<td class="toggle">
										<?php echo JText::_('Instl_Precheck_Actual'); ?>
									</td>
								</tr>
								</thead>
								<tbody>
<?php foreach ($this->settings as $setting) : ?>
								<tr>
									<td class="item">
										<?php echo $setting->label; ?>:
									</td>
									<td class="toggle">
										<span>
										<?php echo JText::_(($setting->recommended) ? 'JOn' : 'JOff'); ?>
										</span>
									</td>
									<td>
										<span class="<?php echo ($setting->state === $setting->recommended) ? 'green' : 'red'; ?>">
										<?php echo JText::_(($setting->state) ? 'JOn' : 'JOff'); ?>
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
</div>
<div class="clr"></div>

<input type="hidden" name="task" value="" />
<?php echo JHtml::_('form.token'); ?>
</form>
