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
		<?php echo JHtml::_('installation.stepbar', 3); ?>
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
					<div class="button1-right"><div class="prev"><a href="index.php?view=preinstall" rel="prev" title="<?php echo JText::_('JPREVIOUS'); ?>"><?php echo JText::_('JPREVIOUS'); ?></a></div></div>
					<div class="button1-left"><div class="next"><a href="index.php?view=database" rel="next" title="<?php echo JText::_('JNEXT'); ?>"><?php echo JText::_('JNEXT'); ?></a></div></div>
<?php elseif ($this->document->direction == 'rtl') : ?>
					<div class="button1-right"><div class="prev"><a href="index.php?view=database" rel="next" title="<?php echo JText::_('JNEXT'); ?>"><?php echo JText::_('JNEXT'); ?></a></div></div>
					<div class="button1-left"><div class="next"><a href="index.php?view=preinstall" rel="prev" title="<?php echo JText::_('JPREVIOUS'); ?>"><?php echo JText::_('JPREVIOUS'); ?></a></div></div>
<?php endif; ?>
				</div>
				<span class="step"><?php echo JText::_('INSTL_LICENSE'); ?></span>
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
				<h2><?php echo JText::_('INSTL_GNU_GPL_LICENSE'); ?></h2>
				<iframe src="gpl.html" class="license" marginwidth="25" scrolling="auto"></iframe>
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
