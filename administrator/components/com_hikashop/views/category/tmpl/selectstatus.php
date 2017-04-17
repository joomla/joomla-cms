<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><script language="javascript" type="text/javascript">
<!--
	var selectedContents = new Array();
	var allElements = <?php echo count($this->rows);?>;
	<?php
		foreach($this->rows as $oneRow){
			if(!empty($oneRow->selected)){
				echo "selectedContents['".$oneRow->category_name."'] = 'content';";
			}
		}
	?>
	function applyContent(contentid,rowClass){
		if(selectedContents[contentid]){
			window.document.getElementById('content'+contentid).className = rowClass;
			delete selectedContents[contentid];
		}else{
			window.document.getElementById('content'+contentid).className = 'selectedrow';
			selectedContents[contentid] = 'content';
		}
	}
	function insertTag(){
		var tag = '';
		for(var i in selectedContents){
			if(selectedContents[i] == 'content'){
				allElements--;
				if(tag != '') tag += ',';
				tag = tag + i;
			}
		}
		window.parent.document.getElementById('<?php echo $this->controlName; ?>').value = tag;
		window.parent.hikashop.closeBox();
	}
//-->
</script>
<style type="text/css">
	table.adminlist tr.selectedrow td{
		background-color:#FDE2BA;
	}
</style>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>" method="post"  name="adminForm" id="adminForm">
	<div style="float:right;margin-bottom : 10px">
		<button class="btn" id='insertButton' onclick="insertTag(); return false;"><?php echo JText::_('HIKA_APPLY'); ?></button>
	</div>
	<div style="clear:both"></div>
	<table class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title">
					<?php echo JText::_('HIKA_NAME'); ?>
				</th>
				<?php if($this->translated){?>
				<th class="title">
					<?php echo JText::_('NAME_TRANSLATED'); ?>
				</th>
				<?php }?>
				<th class="title titleid">
					<?php echo JText::_('ID'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$k = 0;
				foreach($this->rows as $row){
			?>
				<tr class="<?php echo empty($row->selected) ? "row$k" : 'selectedrow'; ?>" id="content<?php echo $row->category_name; ?>" onclick="applyContent('<?php echo $row->category_name."','row$k'"?>);" style="cursor:pointer;">
					<td>
					<?php echo $row->category_name; ?>
					</td>
					<?php if($this->translated){?>
					<td>
					<?php echo @$row->translation; ?>
					</td>
					<?php }?>
					<td align="center">
						<?php echo $row->category_id; ?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>
</form>
