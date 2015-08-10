<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_sampledata
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');

$url = 'index.php?option=com_ajax&format=json&group=sampledata';
$js  = '
function applySampledataAjax(type, steps, step) {
	if (step <= steps) {
		jQuery("div.sampledata-progress-" + type + " ul").append("<li class=\"sampledata-steps-" + type + "-" + step + " center\"><img src=\"' . JUri::root() . '/media/jui/img/ajax-loader.gif\" width=\"30\" height=\"30\" ></li>");
		jQuery.get(
			"' . $url . '&type=" + type + "&plugin=SampledataApplyStep" + step,
			function(response) {
				var success = false;
				if (response.data.length > 0) {
					jQuery.each(
						response.data,
						function(index, value) {
							var successClass = "error";
							if (value.success) {
								success = true;
								successClass = "success";
								jQuery(".sampledata-progress-" + type + " progress").val(step/steps);
							}
							jQuery("li.sampledata-steps-" + type + "-" + step).removeClass("center");
							jQuery("li.sampledata-steps-" + type + "-" + step).addClass("alert alert-" + successClass);
							jQuery("li.sampledata-steps-" + type + "-" + step).html(value.message);
						}
					);
					if (success) {
						step++;
						applySampledataAjax(type, steps, step);
					}
				} else {
					jQuery(".sampledata-progress-" + type + " progress").val(0);
					jQuery("li.sampledata-steps-" + type + "-" + step).addClass("alert alert-error");
					jQuery("li.sampledata-steps-" + type + "-" + step).html("' . JText::_('MOD_SAMPLEDATA_INVALID_RESPONSE') . '");
				}
			}
		);
	}
}
function applySampledata(type, steps) {
	var step = 1;
	jQuery(".sampledata-" + type).after("<div class=\"row-fluid sampledata-progress-" + type + "\"><ul class=\"span12 unstyled\"></ul></div>");
	jQuery(".sampledata-" + type).after("<div class=\"row-fluid sampledata-progress-" + type + "\"><progress class=\"span12\"></progress></div>");
	applySampledataAjax(type, steps, step);
}';

JFactory::getDocument()->addScriptDeclaration($js);
?>
<div class="sampledata-container">
	<?php if ($items) : ?>
		<div class="row-striped">
			<?php foreach($items as $i => $item) : ?>
				<div class="row-fluid sampledata-<?php echo $item->name; ?>">
					<div class="span4">
						<a href="#" onclick="applySampledata('<?php echo $item->name; ?>', '<?php echo $item->steps; ?>');">
							<strong class="row-title">
								<span class="icon-<?php echo $item->icon; ?>"> </span>
								<?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
							</strong>
						</a>
					</div>
					<div class="span6">
						<small>
							<?php echo htmlspecialchars($item->description); ?>
						</small>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<div class="alert"><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS');?></div>
	<?php endif; ?>
</div>