<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if($this->badge!='false'){ ?>
	<div id="result">
		<?php echo $this->rows[0]->discount_id.' '.$this->rows[0]->discount_code; ?>
		<input style="width: 50px; background-color:#e8f9db;" type="hidden" name="data[badge][badge_discount_id]" id="data[badge][badge_discount_id]" value="<?php echo $this->rows[0]->discount_id;?>"/>
	</div>
<?php }else{
?>
<table class="adminlist table table-striped table-hover" cellpadding="1" width="100%">
	<tbody id="result">
	<?php
	$k = 0;
		for($i = 0,$a = count($this->rows);$i<$a;$i++){
			$row =& $this->rows[$i];
			$id=rand();
			$html = '';
			?>
			<tr id="coupon_<?php echo $row->discount_id;?>">
				<td>
					<div id="coupon_<?php echo $row->discount_id; ?>_id">
					<a href="<?php echo hikashop_completeLink('discount&task=edit&cid='.$row->discount_id); ?>"><?php echo $row->discount_code; ?></a>
				</td>
				<td align="center">
					<a href="#" onclick="return deleteRow('coupon_div_<?php echo $row->discount_id;?>','coupon[<?php echo $row->discount_id;?>]','coupon_<?php echo $row->discount_id; ?>');">
						<img src="../media/com_hikashop/images/delete.png"/>
					</a>
				</td>
				<td width="1%" align="center">
					<?php echo $row->discount_id; ?>
					<div id="coupon_div_<?php echo $row->discount_id;?>">
						<input style="width: 50px; background-color:#e8f9db;" type	="hidden" name="coupon[<?php echo $row->discount_id;?>]" id="coupon[<?php echo $row->discount_id;?>]" value="<?php echo $row->discount_id;?>"/>
					</div>
				</td>
			</tr>
			<?php
			$k = 1-$k;
		}
	?>
	</tbody>
</table>
<?php } ?>
