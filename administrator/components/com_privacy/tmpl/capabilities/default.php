<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var PrivacyViewCapabilities $this */

?>
<div id="j-main-container">
	<div class="alert alert-info">
		<h4 class="alert-heading"><?php echo Text::_('COM_PRIVACY_MSG_CAPABILITIES_ABOUT_THIS_INFORMATION'); ?></h4>
		<?php echo Text::_('COM_PRIVACY_MSG_CAPABILITIES_INTRODUCTION'); ?>
	</div>
	<?php if (empty($this->capabilities)) : ?>
		<div class="alert alert-info">
			<span class="fa fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
			<?php echo Text::_('COM_PRIVACY_MSG_CAPABILITIES_NO_CAPABILITIES'); ?>
		</div>
	<?php else : ?>
		<?php $i = 0; ?>
		<?php echo HTMLHelper::_('bootstrap.startAccordion', 'slide-capabilities', array('active' => 'slide-0')); ?>

		<?php foreach ($this->capabilities as $extension => $capabilities) : ?>
			<?php echo HTMLHelper::_('bootstrap.addSlide', 'slide-capabilities', $extension, 'slide-' . $i); ?>
				<?php if (empty($capabilities)) : ?>
					<div class="alert alert-info">
						<span class="fa fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('COM_PRIVACY_MSG_EXTENSION_NO_CAPABILITIES'); ?>
					</div>
				<?php else : ?>
					<ul>
						<?php foreach ($capabilities as $capability) : ?>
							<li><?php echo $capability; ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			<?php echo HTMLHelper::_('bootstrap.endSlide'); ?>
			<?php $i++; ?>
		<?php endforeach; ?>

		<?php echo HTMLHelper::_('bootstrap.endAccordion'); ?>
	<?php endif; ?>
</div>
