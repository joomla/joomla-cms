<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div>
	<table class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title">
					<?php echo JText::_('PRODUCT_NAME'); ?>
				</th>
				<?php if($this->widget->widget_params->product_data=='orders'){ ?>
				<th class="title">
					<?php echo JText::_('ORDERS'); ?>
				</th>
				<?php }
				if($this->widget->widget_params->product_data=='orders' && $this->widget->widget_params->content=='categories'){}else{ ?>
				<th>
					<?php if($this->widget->widget_params->product_data=='clicks'){ echo JText::_('HIKASHOP_HITS'); }else{ echo JText::_('HIKASHOP_TOTAL');  } ?>
				</th>
				<?php } ?>
				<th>
					<?php echo JText::_('ID'); ?>
				</th>

			</tr>
		</thead>
		<tbody>
			<?php
				$k = 0;
				if(!empty($this->widget->elements)){
					foreach($this->widget->elements as $row){
					?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<a href="<?php echo hikashop_completeLink('product&task=edit&cid[]='.$row->product_id); ?>">
							<?php
								if($this->widget->widget_params->content=='products'){
									echo @$row->order_product_name;
								}else{
									echo @$row->category_name;
								}
							?>
						</a>
					</td>
					<?php if($this->widget->widget_params->product_data=='orders'){ ?>
					<td>
						<?php echo @$row->quantity; ?>
					</td>
					<?php }
					if($this->widget->widget_params->product_data=='orders' && $this->widget->widget_params->content=='categories'){}else{
						if($this->widget->widget_params->product_data=='clicks'){
							$value= $row->Total;
						}else{
							$currencyClass = hikashop_get('class.currency');
							$value=$currencyClass->format($row->Total, $row->order_currency_id);
						}?>
					<td>
						<?php echo $value; ?>
					</td>
					<?php } ?>
					<td>
						<?php echo @$row->order_product_id; ?>
					</td>

				</tr>
			<?php
				$k = 1-$k;
				}
			}
			?>
		</tbody>
	</table>
</div>
