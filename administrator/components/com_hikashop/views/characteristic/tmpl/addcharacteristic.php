<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><table class="adminlist table table-striped table-hover" cellpadding="1" width="100%">
	<tbody id="result">
	<?php
		$row=$this->rows[0];
		$id=rand();
		?>
		<tr id="characteristic_<?php echo $row->characteristic_id.'_'.$id;?>">
			<td>
				<?php
					echo $this->popup->display(
						'<img src="'.HIKASHOP_IMAGES.'edit.png"/>',
						'HIKA_EDIT',
						hikashop_completeLink("characteristic&task=editpopup&cid=".$row->characteristic_id.'&characteristic_parent_id='.$row->characteristic_parent_id.'&id='.$id,true ),
						'charac_edit_button'.$row->characteristic_id,
						860, 480, '', '', 'link'
					);
				?>
			</td>
			<td>
				<?php echo $row->characteristic_value; ?>
			</td>
			<td class="order">
				0
			</td>
			<td align="center">
				<a href="#" onclick="return deleteRow('characteristic_div_<?php echo $row->characteristic_id.'_'.$id;?>','characteristic[<?php echo $row->characteristic_id;?>][<?php echo $id;?>]','characteristic_<?php echo $row->characteristic_id.'_'.$id;?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/></a>
			</td>
			<td width="1%" align="center">
				<?php echo $row->characteristic_id; ?>
				<div id="characteristic_div_<?php echo $row->characteristic_id.'_'.$id;?>">
					<input type="hidden" name="characteristic[<?php echo $row->characteristic_id;?>]" id="characteristic[<?php echo $row->characteristic_id;?>][<?php echo $id;?>]" value="<?php echo $row->characteristic_id;?>"/>
				</div>
			</td>
		</tr>
	</tbody>
</table>
