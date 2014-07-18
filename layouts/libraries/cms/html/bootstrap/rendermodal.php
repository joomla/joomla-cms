<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

$selector = empty($displayData['selector']) ? '' : $displayData['selector'];
$params = empty($displayData['params']) ? array() : $displayData['params'];
$footer = empty($displayData['footer']) ? '' : $displayData['footer'];

$title = empty($params['title']) ? '' : $params['title'];
$url = empty($params['url']) ? '' : $params['url'];
$height = empty($params['height']) ? '' : $params['height'];
$width = empty($params['width']) ? '' : $params['width'];

?>
<div class="modal hide fade" id="<?php echo $selector; ?>">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3><?php echo $title; ?></h3>
	</div>
	<div id="<?php echo $selector; ?>-container">
	</div>
</div>
<script>
	jQuery(function ($) {
		$(<?php echo json_encode('#' . $selector); ?>).on('show', function () {
			document.getElementById(<?php echo json_encode($selector . '-container'); ?>)
				.innerHTML = '<div class="modal-body"><iframe class="iframe"' +
					' src="' + <?php echo json_encode($url); ?> + '"' +
					' height="' . <?php echo json_encode($height); ?> + '"' +
					' width="' . <?php echo json_encode($width); ?> + '"></iframe></div>' +
					 <?php echo json_encode($footer); ?>;
		});
	});
</script>
