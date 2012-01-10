<?php
/**
 * @version		$Id: default.php 15 2011-09-02 18:37:15Z cristian $
 * @package		fieldsattach
 * @subpackage		Components
 * @copyright		Copyright (C) 2011 - 2020 Open Source Cristian Grañó, Inc. All rights reserved.
 * @author		Cristian Grañó
 * @link		http://joomlacode.org/gf/project/fieldsattach_1_6/
 * @license		License GNU General Public License version 2 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
// load tooltip behavior
JHtml::_('behavior.tooltip');
?>
 
<form action="<?php echo JRoute::_('index.php?option=com_fieldsattach&view=fieldsattachunidades'); ?>" method="post" name="adminForm" >
        <fieldset id="filter-bar">
		<div class="filter-select fltrt"> 
                        <?php //echo $this->create_filter(); ?>
                     <select name="filter_group_id" class="inputbox" onchange="this.form.submit()">
				<option value="-1"><?php echo JText::_('- Choose group -');?></option>
				<?php echo JHtml::_('select.options', fieldsattachHelper::getGroups(), 'value', 'text', $this->state->get('filter.group_id'));?>
                    </select>
                    <select name="filter_language" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language'));?>
		    </select>
		</div>


	</fieldset> 

        <table class="adminlist">
		<thead><?php echo $this->loadTemplate('head');?></thead>
		<tfoot><?php echo $this->loadTemplate('foot');?></tfoot>
		<tbody><?php echo $this->loadTemplate('body');?></tbody>
	</table>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
