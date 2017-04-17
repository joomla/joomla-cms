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
				echo "selectedContents['".$oneRow->filter_namekey."'] = 'content';";
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
		window.top.document.getElementById('<?php echo $this->controlName; ?>filters').value = tag;
		window.top.document.getElementById('link<?php echo $this->controlName; ?>filters').href = 'index.php?option=com_hikashop&tmpl=component&ctrl=choose&task=filters&control=<?php echo $this->controlName; ?>&values='+tag;
		hikashop.closeBox();
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
	<table class="adminlist table table-striped" cellpadding="1">
		<thead>
			<tr>
				<th class="title">
					<?php echo 'Filter'; ?>
				</th>
				<th class="title titleid">
					<?php echo JText::_('ID'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$k = 0;
				foreach($this->rows as $i => $row){
			?>
				<tr class="<?php echo empty($row->selected) ? "row$k" : 'selectedrow'; ?>" id="content<?php echo $row->filter_namekey; ?>" onclick="applyContent('<?php echo $row->filter_namekey."','row$k'"?>);" style="cursor:pointer;">
					<td>
					<?php echo $row->filter_namekey; ?>
					</td>
					<td align="center">
						<?php echo $i; ?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>
</form>
