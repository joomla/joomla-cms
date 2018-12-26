<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$data = $displayData;

// TODO include comprimed js
HTMLHelper::_('script', 'com_config/config-filter-options.min.js', ['relative' => true, 'version' => 'auto']);
HTMLHelper::_('stylesheet', 'com_config/filter-options.min.css', array('version' => 'auto', 'relative' => true));
?><div class="js-stools" role="search">
	<div class="js-stools-container-bar">
		<div class="btn-toolbar">
			<div class="btn-group mr-2">
				<div class="input-group">
					<label for="filter_search" class="sr-only">
						<?php echo Text::_('JSEARCH_TYPE_FILTER_TEXT'); ?>
					</label>
					<input type="text" name="filterOptionsInput" id="filterOptionsInput" value="" class="form-control" title="<?php echo Text::_('JSEARCH_TYPE_FILTER_TEXT'); ?>" placeholder="<?php echo Text::_('JSEARCH_TYPE_FILTER_TEXT'); ?>">
					<?php /*><span class="input-group-append">
						<button type="submit" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', 'JSEARCH_FILTER_SUBMIT'); ?>"  aria-label="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>">
							<span class="fa fa-search" aria-hidden="true"></span>
						</button>
					</span> */ ?>
				</div>
			</div>
			<button type="button" name="filterOptionsClear" id="filterOptionsClear" class="btn btn-primary hasTooltip js-stools-btn-clear mr-2" title="<?php echo HTMLHelper::_('tooltipText', 'JSEARCH_FILTER_CLEAR'); ?>">
				<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
			</button>
		</div>
	</div>
</div>