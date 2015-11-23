<?php
/**
 * @version		$Id: default.php 01 2013-07-29 11:37:09Z maverick $
 * @package		CoreJoomla.cjlib
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2011 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */
defined('_JEXEC') or die;
?>
<div id="cj-wrapper">
	<form action="<?php echo JRoute::_('index.php?option=com_cjlib&task=queue');?>" method="post" name="adminForm" id="adminForm">
		<table class="adminlist table table-bordered table-striped">
			<thead><?php echo $this->loadTemplate('head');?></thead>
			<tfoot><?php echo $this->loadTemplate('foot');?></tfoot>
			<tbody><?php echo $this->loadTemplate('body');?></tbody>
		</table>
		<div style="display: none;">
			<input type="hidden" name="task" value="queue" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering'); ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction'); ?>" />
			<input type="hidden" name="cjlib_page_id" id="cjlib_page_id" value="queue">
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>