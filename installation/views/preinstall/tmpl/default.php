<?php
/**
 * @version		$Id: default.php 235 2009-05-26 06:19:45Z andrew.eddie $
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
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
		<h1><?php echo JText::_('Steps'); ?></h1>
		<div class="step-off">
			1 : <?php echo JText::_('Language'); ?>
		</div>
		<div class="step-on">
			2 : <?php echo JText::_('Pre-Installation check'); ?>
		</div>
		<div class="step-off">
			3 : <?php echo JText::_('License'); ?>
		</div>
		<div class="step-off">
			4 : <?php echo JText::_('Database'); ?>
		</div>
		<div class="step-off">
			5 : <?php echo JText::_('FTP Configuration'); ?>
		</div>
		<div class="step-off">
			6 : <?php echo JText::_('Configuration'); ?>
		</div>
		<div class="step-off">
			7 : <?php echo JText::_('Finish'); ?>
		</div>
		<div class="box"></div>
  	</div>
	<div class="b">
		<div class="b">
			<div class="b"></div>
		</div>
	</div>
</div>

<form action="index.php" method="post" name="adminForm">
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
					<div class="button1-left"><div class="refresh"><a href="index.php?view=preinstall" title="<?php echo JText::_('Check Again'); ?>"><?php echo JText::_('Check Again'); ?></a></div></div>
					<div class="button1-right"><div class="prev"><a href="index.php?view=language" title="<?php echo JText::_('Previous'); ?>"><?php echo JText::_('Previous'); ?></a></div></div>
					<div class="button1-left"><div class="next"><a href="index.php?view=license" title="<?php echo JText::_('Next'); ?>"><?php echo JText::_('Next'); ?></a></div></div>
<?php elseif ($this->document->direction == 'rtl') : ?>
					<div class="button1-right"><div class="prev"><a href="index.php?view=license" title="<?php echo JText::_('Next'); ?>"><?php echo JText::_('Next'); ?></a></div></div>
					<div class="button1-left"><div class="next"><a href="index.php?view=language" title="<?php echo JText::_('Previous'); ?>"><?php echo JText::_('Previous'); ?></a></div></div>
					<div class="button1-left"><div class="refresh"><a href="index.php?view=preinstall" title="<?php echo JText::_('Check Again'); ?>"><?php echo JText::_('Check Again'); ?></a></div></div>
<?php endif; ?>
				</div>
				<span class="step"><?php echo JText::_('Pre-Installation check'); ?></span>
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
				<h2><?php echo JText::_('Pre-installation check for'),' ',$this->version->getLongVersion(); ?>:</h2>
				<div class="install-text">
					<?php echo JText::_('If any of these items is not supported (marked as <strong><font color="#ff00">No</font></strong>)
					then please take actions to correct them. Failure to do so
					could lead to your Joomla! installation not functioning
					correctly.'); ?>
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
										<span class="<?php echo ($option->state) ? 'Yes' : 'No'; ?>">
											<?php echo ($option->state) ? 'Yes' : 'No'; ?>
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

				<h2><?php echo JText::_('Recommended settings'); ?>:</h2>
				<div class="install-text">
					<?php echo JText::_(
						'These settings are recommended for PHP in order to ensure full
						compatibility with Joomla.
						<br />
						However, Joomla! will still operate if your settings do not quite match the recommended.'
					); ?>
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
										<?php echo JText::_('Directive'); ?>
									</td>
									<td class="toggle">
										<?php echo JText::_('Recommended'); ?>
									</td>
									<td class="toggle">
										<?php echo JText::_('Actual'); ?>
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
										<span class="<?php echo ($setting->recommended) ? 'Yes' : 'No'; ?>">
										<?php echo ($setting->recommended) ? 'Yes' : 'No'; ?>
										</span>
									</td>
									<td>
										<span class="<?php echo ($setting->state) ? 'Yes' : 'No'; ?>">
										<?php echo ($setting->state) ? 'Yes' : 'No'; ?>
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
