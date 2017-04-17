<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
	$currencyHelper = hikashop_get('class.currency');
?>

<div>
	<table class="adminlist table table-striped" cellpadding="1">
		<thead>
			<tr>
				<th class="title">
					<?php echo JText::_('DISCOUNT_CODE'); ?>
				</th>
				<th>
					<?php echo JText::_('ORDERS'); ?>
				</th>
				<th>
					<?php echo JText::_('VALUE'); ?>
				</th>
				<th>
					<?php echo JText::_('ID'); ?>
				</th>

			</tr>
		</thead>
		<tbody>
			<?php
				$k=0;
				if(!empty($this->widget->elements)){
					foreach($this->widget->elements as $row){
					?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<a href="<?php echo hikashop_completeLink('discount&task=edit&cid[]='.$row->discount_id); ?>">
							<?php
								echo @$row->order_discount_code;
							?>
						</a>
					</td>
					<td>
						<?php echo @$row->Total; ?>
					</td>
					<td>
						<?php
							if(isset($row->discount_flat_amount) && $row->discount_flat_amount > 0){
								echo $currencyHelper->displayPrices(array($row),'discount_flat_amount','discount_currency_id');
							}
							elseif(isset($row->discount_percent_amount) && $row->discount_percent_amount > 0){
								echo $row->discount_percent_amount. '%';
							}
						?>
					</td>
					<td>
						<?php echo @$row->discount_id; ?>
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
