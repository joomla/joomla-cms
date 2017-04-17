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
					<?php echo JText::_('HIKA_USER_NAME'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('HIKA_USERNAME'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('HIKA_EMAIL'); ?>
				</th>
				<?php
				$best_customers=false;
				if(isset($this->widget->widget_params->customers)){ if($this->widget->widget_params->customers=='best_customers'){ $best_customers=true; } }
				if(($this->widget->widget_params->content=='customers' && $best_customers) || ($this->widget->widget_params->content=='partners' && $this->widget->widget_params->partners=='best_customers')){ ?>
				<th>
					<?php echo JText::_('TOTAL'); ?>
				</th>
				<th>
					<?php echo JText::_('ORDERS'); ?>
				</th>
				<?php }

				if($this->widget->filter_partner==1){?>
				<th class="title">
					<?php echo JText::_('UNPAID_TOTAL_AMOUNT'); ?>
				</th>
				<?php }?>
			</tr>
		</thead>
		<tbody>
			<?php
				$k = 0;
				if(!empty($this->widget->elements)){
					for($i = 0,$a = count($this->widget->elements);$i<$a;$i++){
						$row =& $this->widget->elements[$i];
			?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo @$row->name; ?>
					</td>
					<td>
						<?php echo @$row->username; ?>
					</td>
					<td>
						<?php echo $row->user_email; ?>
						<a href="<?php echo hikashop_completeLink('user&task=edit&cid[]='.$row->user_id); ?>">
							<img src="<?php echo HIKASHOP_IMAGES;?>edit.png" alt="edit"/>
						</a>
					</td>
					<?php
					$best_customers=false;
					if(isset($this->widget->widget_params->customers)){ if($this->widget->widget_params->customers=='best_customers'){ $best_customers=true; } }
					if(($this->widget->widget_params->content=='customers' && $best_customers) || ($this->widget->widget_params->content=='partners' && $this->widget->widget_params->partners=='best_customers')){
						$currencyClass = hikashop_get('class.currency');
						$value=$currencyClass->format($row->Total, $row->order_currency_id); ?>
					<td>
						<?php echo $value; ?>
					</td>
					<td>
						<?php echo $row->order_number; ?>
					</td>
					<?php }

					if($this->widget->filter_partner==1){?>
					<td align="center">
						<?php
						if(bccomp($row->user_unpaid_amount,0,5)){
							echo $this->currencyHelper->format($row->user_unpaid_amount,$row->user_currency_id);
						}
						?>
					</td>
					<?php }?>
				</tr>
			<?php
					$k = 1-$k;
				}
			}
			?>
		</tbody>
	</table>
</div>
