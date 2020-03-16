<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var PrivacyViewCapabilities $this */

?>
<?php if (!empty($this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif; ?>
	<div class="alert alert-info">
		<h4 class="alert-heading"><?php echo JText::_('COM_PRIVACY_MSG_CAPABILITIES_ABOUT_THIS_INFORMATION'); ?></h4>
		<?php echo JText::_('COM_PRIVACY_MSG_CAPABILITIES_INTRODUCTION'); ?>
	</div>
	<?php if (empty($this->capabilities)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('COM_PRIVACY_MSG_CAPABILITIES_NO_CAPABILITIES'); ?>
		</div>
	<?php else : ?>
		<?php $i = 0; ?>
		<?php echo JHtml::_('bootstrap.startAccordion', 'slide-capabilities', array('active' => 'slide-0')); ?>

		<?php foreach ($this->capabilities as $extension => $capabilities) : ?>
			<?php echo JHtml::_('bootstrap.addSlide', 'slide-capabilities', $extension, 'slide-' . $i); ?>
				<?php if (empty($capabilities)) : ?>
					<div class="alert alert-no-items">
						<?php echo JText::_('COM_PRIVACY_MSG_EXTENSION_NO_CAPABILITIES'); ?>
					</div>
				<?php else : ?>
					<ul>
						<?php foreach ($capabilities as $capability) : ?>
							<li><?php echo $capability; ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			<?php echo JHtml::_('bootstrap.endSlide'); ?>
			<?php $i++; ?>
		<?php endforeach; ?>

		<?php echo JHtml::_('bootstrap.endAccordion'); ?>
	<?php endif; ?>
</div>
