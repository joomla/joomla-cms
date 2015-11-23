<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

////////////////////////////////////////// CjLib Includes ///////////////////////////////////////////////
$cjlib = JPATH_ROOT.'/components/com_cjlib/framework.php';
if(file_exists($cjlib)){
	require_once $cjlib;
}else{
	die('CJLib (CoreJoomla API Library) component not found. Please download and install it to continue.');
}
CJLib::import('corejoomla.framework.core');
////////////////////////////////////////// CjLib Includes ///////////////////////////////////////////////

CJLib::behavior('bscore');
CJFunctions::load_jquery(array('libs'=>array('fontawesome')));

$user = JFactory::getUser();
?>
<script type="text/javascript">
<!--
function doMigrationSteps(currentStep)
{
	var features = new Array();
	jQuery('input[name="features[]"]:checked').each(function() {
		features.push(jQuery(this).val());
	});
	
	jQuery.ajax({
			url: '<?php echo JRoute::_('index.php?option=com_cjforum&task=migrate.execute&format=json', false);?>',
			dataType: 'json',
			data: {
				'step': currentStep,
				'extension': jQuery('select[name="source"]').val(),
				'features[]': features
			},
			beforeSend: function( xhr ) {
				jQuery('#migration-progress').show();
			}
		}).done(function(data){
			if(data.messages && data.messages.message)
			{
				jQuery.each(data.messages.message, function(index, message){
					jQuery('#migrationSteps').prepend(jQuery('<li>', {'class': 'list-group-item'}).html(message));
				});
			}
			if(data.success)
			{
				if(data.data == -1 || data.data == -2 || data.data == -3)
				{
					jQuery('#migrationSteps').prepend(jQuery('<li>', {'class': 'list-group-item'}).html(data.message));
					doMigrationSteps(data.data);
				}
				else
				{
					if(data.data)
					{
						jQuery('#migrationSteps').prepend(jQuery('<li>', {'class': 'list-group-item'}).html(data.data));
					}
					doMigrationSteps(currentStep + 1);
				}
			}
			else
			{
				jQuery('#btn-migrate').button('reset');
				alert(data.message);
			}
		})
		.fail(function(data){
			alert(data.message);
		});
}
//-->
</script>
<div id="cj-wrapper">
	<form action="<?php echo JRoute::_('index.php?option=com_cjforum&view=users');?>" method="post" name="adminForm" id="adminForm">
		<?php if (!empty( $this->sidebar)) : ?>
			<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
			</div>
		<?php endif;?>
		
		<div id="j-main-container"<?php echo !empty( $this->sidebar) ? ' class="span10"' : '';?>>
			<?php if($user->authorise('core.admin', 'com_cjforum')):?>
			<div class="panel panel-danger">
				<div class="panel-heading"><strong><?php echo JText::_('COM_CJFORUM_ALERT');?></strong></div>
				<div class="panel-body"><?php echo JText::_('COM_CJFORUM_MIGRATE_ALERT');?></div>
			</div>
		
			<div class="form-inline" style="margin-bottom: 20px;">
				<p><?php echo JText::_('COM_CJFORUM_LABEL_SELECT_COMPONENT');?>:</p>
				
				<select size="1" name="source">
					<option value="">-- Select Option --</option>
					<option value="kunena">Kunena Forum</option>
					<option value="cjblog">CjBlog</option>
				</select>
				<ul class="unstyled inline" style="margin-top: 10px;">
					<li><label><input type="checkbox" name="features[]" value="topics" checked="checked"> Topics</label></li>
					<li><label><input type="checkbox" name="features[]" value="avatar" checked="checked"> Avatars</label></li>
					<li><label><input type="checkbox" name="features[]" value="users" checked="checked"> Users</label></li>
					<li><label><input type="checkbox" name="features[]" value="points" checked="checked"> Points</label></li>
				</ul>
				
				<button id="btn-migrate" class="btn btn-danger" type="button" onclick="javascript: doMigrationSteps(0); jQuery(this).button('loading');">
					<?php echo JText::_('COM_CJFORUM_START_MIGRATION');?>
				</button>
			</div>
			
			<div class="panel panel-default" id="migration-progress" style="display: none; max-height: 250px; overflow: auto;">
				<div class="panel-heading"><?php echo JText::_('COM_CJFORUM_MIGRATION_IN_PROGRESS');?></div>
				<div class="panel-body"><i class="fa fa-cog fa-spin"></i> <?php echo JText::_('COM_CJFORUM_MIGRATION_IN_PROGRESS_DESC');?></div>
				<ul class="list-group" id="migrationSteps" style="margin-left: 0;">
				</ul>
			</div>
			<?php else :?>
			<div class="alert alert-info"><i class="fa fa-info-circle"></i> This feature avaialable only for the administrators.</div>
			<?php endif;?>
		</div>
		
		<input type="hidden" name="task" value="">
	</form>
</div>