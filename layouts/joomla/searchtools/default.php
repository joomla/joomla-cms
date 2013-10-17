<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$data = $displayData;
?>
<div class="stools js-stools clearfix">
	<div id="filter-bar" class="hidden-phone row-fluid clearfix">
		<div class="span6">
			<?php echo JLayoutHelper::render('joomla.searchtools.default.bar', $data); ?>
		</div>
		<div class="span6 hidden-phone stools-list js-stools-container-order">
			<?php echo JLayoutHelper::render('joomla.searchtools.default.list', $data); ?>
		</div>
	</div>
	<!-- Filters div -->
	<div class="js-stools-container">
		<div class="js-stools-container-filter stools-filters hidden-phone">
			<?php echo JLayoutHelper::render('joomla.searchtools.default.filters', $data); ?>
		</div>
	</div>
</div>