<?php /** $Id$ */ defined('_JEXEC') or die('Restricted access'); ?>

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
					<?php if ($this->direction == 'ltr') : ?>
						<div class="button1-right"><div class="prev"><a onclick="submitForm(adminForm, 'preinstall');" title="<?php echo JText::_('Previous', true) ?>"><?php echo JText::_('Previous') ?></a></div></div>
						<div class="button1-left"><div class="next"><a onclick="submitForm(adminForm, 'dbconfig');" title="<?php echo JText::_('Next' ,true) ?>"><?php echo JText::_('Next') ?></a></div></div>
					<?php else: ?>
						<div class="button1-right"><div class="prev"><a onclick="submitForm(adminForm, 'dbconfig');" title="<?php echo JText::_('Next' ,true) ?>"><?php echo JText::_('Next') ?></a></div></div>
						<div class="button1-left"><div class="next"><a onclick="submitForm(adminForm, 'preinstall');" title="<?php echo JText::_('Previous' ,true) ?>"><?php echo JText::_('Previous') ?></a></div></div>
					<?php endif; ?>
				</div>
				<span class="step"><?php echo JText::_('License') ?></span>
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

				<h2><?php echo JText::_('GNU/GPL License') ?>:</h2>
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
</form>
