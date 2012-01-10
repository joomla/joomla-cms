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

<form action="<?php echo JRoute::_('index.php?option=com_fieldsattach&view=fieldsattachimages'); ?>" method="post" name="adminForm" >
  <br /><br /><div class=" ">
		<div class="toolbar-list" id="toolbar">
                    <ul>
                    <li class="button" id="toolbar-new">
                    <a href="index.php?option=com_fieldsattach&view=fieldsattachimage&layout=edit&tmpl=component" class="toolbar">
                    <span class="icon-32-new">
                    </span>
                    New
                    </a>
                    </li>
                    <li class="button" id="toolbar-trash">
                    <a href="#" onclick="javascript:if (document.adminForm.boxchecked.value==0){alert('Please first make a selection from the list');}else{ Joomla.submitbutton('fieldsattachimages.delete')}" class="toolbar">
                    <span class="icon-32-trash">
                    </span>
                    Trash
                    </a>
                    </li>
                    </ul>
                </div> <div class="pagetitle icon-48-mediamanager"><h2>Image Manager</h2></div> </div>
 <br /><br />

        <table class="adminlist">
		<thead><?php echo $this->loadTemplate('head'); ?></thead>
		<tfoot><?php echo $this->loadTemplate('foot');?></tfoot>
		<tbody><?php echo $this->loadTemplate('body');?></tbody>
	</table>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
 
