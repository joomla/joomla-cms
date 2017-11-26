<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_sampledata
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('jquery.framework');
JHtml::_('script', 'mod_sampledata/sampledata-process.js', false, true);

JText::script('MOD_SAMPLEDATA_CONFIRM_START');
JText::script('MOD_SAMPLEDATA_ITEM_ALREADY_PROCESSED');
JText::script('MOD_SAMPLEDATA_INVALID_RESPONSE');

JFactory::getDocument()->addScriptDeclaration('
	var modSampledataUrl = "index.php?option=com_ajax&format=json&group=sampledata",
		modSampledataIconProgress = "' . JUri::root(true) . '/media/system/images/ajax-loader.gif";
');
?>
<?php if ($items) : ?>
	<ul class="list-group list-group-flush">
		<?php foreach($items as $i => $item) : ?>
			<li class="list-group-item sampledata-<?php echo $item->name; ?>">
				<div class="d-flex justify-content-between align-items-center">
					<div class="mr-2">
						<span class="icon-<?php echo $item->icon; ?>" aria-hidden="true"></span>
						<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
					</div>
					<a href="#" class="btn btn-primary btn-sm" onclick="sampledataApply(this)" data-type="<?php echo $item->name; ?>" data-steps="<?php echo $item->steps; ?>">
					<?php echo JText::_('JLIB_INSTALLER_INSTALL'); ?></a>
				</div>
				<p class="small mt-1"><?php echo $item->description; ?></p>
			</li>
			<?php // Progress bar ?>
			<li class="list-group-item sampledata-progress-<?php echo $item->name; ?> d-none">
				<div class="progress">
					<div class="progress-bar progress-bar-striped progress-bar-animated w-100" role="progressbar"></div>
				</div>
			</li>
			<?php // Progress messages ?>
			<li class="list-group-item sampledata-progress-<?php echo $item->name; ?> d-none">
				<ul class="unstyled"></ul>
			</li>
		<?php endforeach; ?>
	</ul>
<?php else : ?>
	<joomla-alert type="warning"><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
<?php endif; ?>
