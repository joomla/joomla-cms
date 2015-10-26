<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div style="float:right">
	<?php
		echo $this->popup->display(
			'<img src="'.HIKASHOP_IMAGES.'add.png"/>'.JText::_('ADD'),
			'ADD',
			hikashop_completeLink("product&task=selectrelated&select_type=".$this->type,true ),
			$this->type.'_add_button',
			860, 480, '', '', 'button'
		);
	?>
</div>
<br/>
<table class="adminlist table table-striped table-hover" cellpadding="1">
	<thead>
		<tr>
			<th class="title">
				<?php echo JText::_('HIKA_NAME'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('PRODUCT_CODE'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('PRODUCT_PRICE'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('HIKA_ORDER'); ?>
			</th>
			<th class="title titletoggle">
				<?php echo JText::_('HIKA_DELETE'); ?>
			</th>
			<th class="title">
				<?php echo JText::_( 'ID' ); ?>
			</th>
		</tr>
	</thead>
	<tbody id="<?php echo $this->type;?>_listing">
		<?php
			$type=$this->type;
			if(!empty($this->element->$type)){
				$k = 0;

				for($i = 0,$a = count($this->element->$type);$i<$a;$i++){
					$row =& $this->element->{$type}[$i];
					$id = rand();
			?>
				<tr class="<?php echo "row$k"; ?>" id="<?php echo $type.'_'.$row->product_id.'_'.$id;?>">
					<td>
						<a href="<?php echo hikashop_completeLink('product&task=edit&cid='.$row->product_id); ?>"><?php echo $row->product_name; ?></a>
					</td>
					<td>
						<?php echo $row->product_code; ?>
					</td>
					<td>
						<?php echo $this->currencyHelper->displayPrices(@$row->prices); ?>
					</td>
					<td align="center" class="order">
						<div id="<?php echo $type;?>_ordering_div_<?php echo $row->product_id.'_'.$id;?>">
							<input type="text" size="3" name="<?php echo $type;?>_ordering[<?php echo $row->product_id;?>]" id="<?php echo $type;?>_ordering[<?php echo $row->product_id;?>][<?php echo $id?>]" value="<?php echo intval(@$row->product_related_ordering);?>"/>
						</div>
					</td>
					<td align="center">
							<a href="#" onclick="return deleteRow('<?php echo $type.'_div_'.$row->product_id.'_'.$id;?>','<?php echo $type;?>[<?php echo $row->product_id;?>][<?php echo $id;?>]','<?php echo $type.'_'.$row->product_id.'_'.$id;?>');"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/></a>
					</td>
					<td width="1%" align="center">
						<?php echo $row->product_id; ?>
						<div id="<?php echo $type.'_div_'.$row->product_id.'_'.$id;?>">
							<input type="hidden" name="<?php echo $type;?>[<?php echo $row->product_id;?>]" id="<?php echo $type;?>[<?php echo $row->product_id;?>][<?php echo $id;?>]" value="<?php echo $row->product_id;?>"/>
						</div>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			}
		?>
	</tbody>
</table>
