<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><table class="adminlist table table-striped" cellpadding="1" width="100%">
	<tbody id="result">
<?php
$k = 0;
for($i = 0,$a = count($this->rows);$i<$a;$i++){
	$row =& $this->rows[$i];
	$id = rand();
?>
		<tr class="row<?php echo $k;?>" id="file_<?php echo $row->file_id.'_'.$id;?>">
			<td width="1%" align="center">
				<a rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("product&task=selectfile&cid=".$row->file_id.'&id='.$id,true ); ?>" onclick="SqueezeBox.fromElement(this,{parse: 'rel'});return false;">
					<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png"/>
				</a>
			</td>
			<td><?php
				echo $row->file_path;
			?></td>
			<td><?php
				echo $row->file_name;
			?></td>
			<td width="1%" align="right">
				? / <?php
				if(!isset($row->file_limit) || $row->file_limit == 0) {
					echo '<em>'.$this->config->get('download_number_limit').'</em>';
				} else {
					if((int)$row->file_limit > 0)
						echo $row->file_limit;
					else
						echo JText::_('UNLIMITED');
				}
				?>
			</td>
			<td width="1%" align="center">
				<input type="checkbox" disabled="disabled" <?php echo !empty($row->file_free_download) ? 'checked="checked"' : ''; ?> />
			</td>
			<td width="1%" align="center">
				<a href="#" onclick="return deleteRow('file_div_<?php echo $row->file_id.'_'.$id;?>','file[<?php echo $row->file_id;?>][<?php echo $id;?>]','file_<?php echo $row->file_id.'_'.$id;?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/></a>
			</td>
			<td width="1%" align="center">
				<?php echo $row->file_id; ?>
				<div id="file_div_<?php echo $row->file_id.'_'.$id;?>">
					<input type="hidden" name="file[<?php echo $row->file_id;?>]" id="file[<?php echo $row->file_id;?>][<?php echo $id;?>]" value="<?php echo $row->file_id;?>"/>
				</div>
			</td>
		</tr>
<?php
	$k = 1-$k;
}
?>
	</tbody>
</table>
