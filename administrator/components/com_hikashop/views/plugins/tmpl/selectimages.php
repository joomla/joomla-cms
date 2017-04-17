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
				echo "selectedContents['".$oneRow->id."'] = 'content';";
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
		if(allElements == 0) tag = 'All';
		if(allElements == <?php echo count($this->rows);?>) tag = 'None';
		window.top.document.getElementById('plugin_images').value = tag;
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
<div style="clear:both"/>
	<table class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title">
					<?php echo JText::_('HIKA_IMAGE'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('HIKA_NAME'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$k = 0;

				for($i = 0,$a = count($this->rows);$i<$a;$i++){
					$row =& $this->rows[$i];
			?>
				<tr class="<?php echo empty($row->selected) ? "row$k" : 'selectedrow'; ?>" id="content<?php echo $row->id?>" onclick="applyContent('<?php echo $this->escape($row->id)."','row$k'"?>);" style="cursor:pointer;">
					<td>
						<img src="<?php echo $row->full;?>" />
					</td>
					<td>
						<?php echo $row->name;?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>
</div>
</form>
