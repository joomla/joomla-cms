<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

// Set the tooltips
JText::script('JTOGGLE_HIDE_SIDEBAR');
JText::script('JTOGGLE_SHOW_SIDEBAR');
?>
<div
	id="j-toggle-sidebar-button"
	class="j-toggle-sidebar-button hidden-phone hasTooltip"
	title="<?php echo JHtml::tooltipText('JTOGGLE_HIDE_SIDEBAR'); ?>"
	type="button"
	onclick="toggleSidebar(false); return false;"
	>
	<span id="j-toggle-sidebar-icon" class="icon-arrow-left-2"></span>
</div>
