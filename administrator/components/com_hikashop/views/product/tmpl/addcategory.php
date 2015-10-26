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
	$class = hikashop_get('class.category');
	for($i = 0,$a = count($this->rows);$i<$a;$i++){
		$row =& $this->rows[$i];
		$id=rand();
		$html = array();
		$parents = $class->getParents($row->category_id);
?>
		<tr id="category_<?php echo $row->category_id.'_'.$id;?>">
			<td><?php
				if(!empty($parents)){
					foreach($parents as $parent){
						if($parent->category_type!='product') continue;
						$html[] = '<a href="'. hikashop_completeLink('category&task=edit&cid='.$parent->category_id).'">'.$parent->category_name.'</a>';
					}
				}
				echo implode(' / ',$html);
			?></td>
			<td align="center">
				<?php if(JRequest::getCmd('control')=='plugin'){ ?>
					<input style="width: 50px; background-color:#e8f9db;" type="text" name="category_points[<?php echo $row->category_id;?>]" id="category_points[<?php echo $row->category_id;?>]" value="0" />
				<?php }else{ ?>
				<a href="#" onclick="return deleteRow('category_div_<?php echo $row->category_id.'_'.$id;?>','category[<?php echo $row->category_id;?>][<?php echo $id;?>]','category_<?php echo $row->category_id.'_'.$id;?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/></a>
				<?php } ?>
			</td>
			<td width="1%" align="center">
				<?php echo $row->category_id; ?>
				<div id="category_div_<?php echo $row->category_id.'_'.$id;?>">
					<input type="hidden" name="category[<?php echo $row->category_id;?>]" id="category[<?php echo $row->category_id;?>][<?php echo $id;?>]" value="<?php echo $row->category_id;?>"/>
				</div>
			</td>
		</tr>
<?php
		$k = 1-$k;
	}
?>
	</tbody>
</table>
