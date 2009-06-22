<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
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
		<h1><?php echo JText::_('Steps'); ?></h1>
		<div class="step-off">
			1 : <?php echo JText::_('Language'); ?>
		</div>
		<div class="step-off">
			2 : <?php echo JText::_('Pre-Installation check'); ?>
		</div>
		<div class="step-on">
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
					<div class="button1-right"><div class="prev"><a href="index.php?view=preinstall" title="<?php echo JText::_('Previous'); ?>"><?php echo JText::_('Previous'); ?></a></div></div>
					<div class="button1-left"><div class="next"><a href="index.php?view=database" title="<?php echo JText::_('Next'); ?>"><?php echo JText::_('Next'); ?></a></div></div>
<?php elseif ($this->document->direction == 'rtl') : ?>
					<div class="button1-right"><div class="prev"><a href="index.php?view=database" title="<?php echo JText::_('Next'); ?>"><?php echo JText::_('Next'); ?></a></div></div>
					<div class="button1-left"><div class="next"><a href="index.php?view=preinstall" title="<?php echo JText::_('Previous'); ?>"><?php echo JText::_('Previous'); ?></a></div></div>
<?php endif; ?>
				</div>
				<span class="step"><?php echo JText::_('License'); ?></span>
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
				<h2><?php echo JText::_('GNU/GPL License'); ?>:</h2>
				<iframe src="gpl.html" class="license" frameborder="0" marginwidth="25" scrolling="auto"></iframe>
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
