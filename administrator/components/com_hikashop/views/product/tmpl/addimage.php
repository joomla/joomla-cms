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
		<tr class="row<?php echo $k;?>" id="image_<?php echo $row->file_id.'_'.$id;?>">
			<td width="1%" align="center">
				<a rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("product&task=selectimage&cid=".$row->file_id.'&id='.$id, true);?>" onclick="SqueezeBox.fromElement(this,{parse: 'rel'});return false;">
					<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png"/>
				</a>
			</td>
			<td class="hikashop_product_image_thumbnail">
				<?php echo $this->image->display($row->file_path,true,"",'','', 100, 100); ?>
			</td>
			<td>
				<?php echo $row->file_name; ?>
			</td>
			<td class="order"><input type="text" size="5" value="<?php echo $row->file_ordering;?>" name="imageorder[<?php echo $row->file_id;?>]" class="text_area" style="text-align:center"/></td>
			<td width="1%" align="center">
				<a href="#" onclick="return deleteRow('image_div_<?php echo $row->file_id.'_'.$id;?>','image[<?php echo $row->file_id;?>][<?php echo $id;?>]','image_<?php echo $row->file_id.'_'.$id;?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/></a>
			</td>
			<td width="1%" align="center">
				<?php echo $row->file_id; ?>
				<div id="image_div_<?php echo $row->file_id.'_'.$id;?>">
					<input type="hidden" name="image[<?php echo $row->file_id;?>]" id="image[<?php echo $row->file_id;?>][<?php echo $id;?>]" value="<?php echo $row->file_id;?>"/>
				</div>
			</td>
		</tr>
<?php
	$k = 1-$k;
}
?>
	</tbody>
</table>
