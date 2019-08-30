<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_sampledata
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Session\Session;

JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');
JHtml::_('script', 'mod_sampledata/sampledata-process.js', false, true);

JText::script('MOD_SAMPLEDATA_CONFIRM_START');
JText::script('MOD_SAMPLEDATA_ITEM_ALREADY_PROCESSED');
JText::script('MOD_SAMPLEDATA_INVALID_RESPONSE');

JFactory::getDocument()->addScriptDeclaration('
	var modSampledataUrl = "index.php?option=com_ajax&format=json&group=sampledata&' . Session::getFormToken() . '=1",
		modSampledataIconProgress = "' . JUri::root(true) . '/media/jui/images/ajax-loader.gif";
');
?>
<div class="sampledata-container">
	<?php if ($items) : ?>
		<div class="row-striped">
			<?php foreach($items as $i => $item) : ?>
				<div class="row-fluid sampledata-<?php echo $item->name; ?>">
					<div class="span4">
						<a href="#" onclick="sampledataApply(this)" data-type="<?php echo $item->name; ?>" data-steps="<?php echo $item->steps; ?>">
							<strong class="row-title">
								<span class="icon-<?php echo $item->icon; ?>" aria-hidden="true"> </span>
								<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
							</strong>
						</a>
					</div>
					<div class="span6">
						<small>
							<?php echo $item->description; ?>
						</small>
					</div>
				</div>
				<!-- Progress bar -->
				<div class="row-fluid sampledata-progress-<?php echo $item->name; ?> hide">
					<progress class="span12"></progress>
				</div>
				<!-- Progress messages -->
				<div class="row-fluid sampledata-progress-<?php echo $item->name; ?> hide">
					<ul class="unstyled"></ul>
				</div>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<div class="alert"><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS');?></div>
	<?php endif; ?>
</div>
