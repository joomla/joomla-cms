<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('user'); ?>" method="post" name="adminForm" id="adminForm"  enctype="multipart/form-data">
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<div id="page-mail">
	<table style="width:100%">
		<tr>
			<td valign="top" width="30%">
<?php } else { ?>
<div id="page-files" class="row-fluid">
	<div class="span6">
<?php } ?>
				<fieldset class="adminform">
					<legend><?php echo JText::_('MAIN_INFORMATION'); ?></legend>
					<table class="admintable table">
						<tr>
							<td class="key"><?php
								echo JText::_( 'HIKA_USER_NAME' );
							?></td>
							<td>
								<?php echo $this->escape(@$this->user->name); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_( 'HIKA_USERNAME' ); ?>
							</td>
							<td>
								<?php echo $this->escape(@$this->user->username); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_( 'HIKA_EMAIL' ); ?>
							</td>
							<td>
								<input type="text" size="30" name="data[user][user_email]" class="inputbox" value="<?php echo $this->escape(@$this->user->user_email); ?>" />
							</td>
						</tr>
						<?php if(hikashop_level(2) && !empty($this->user->geolocation_ip)){ ?>
							<tr>
								<td class="key">
									<?php echo JText::_( 'IP' ); ?>
								</td>
								<td>
									<?php
									echo $this->user->geolocation_ip;
									if( $this->user->geolocation_country!='Reserved'){
										echo ' ( '.$this->user->geolocation_city.' '.$this->user->geolocation_state.' '.$this->user->geolocation_country.' )';
									}
									?>
								</td>
							</tr>
						<?php }
						if($this->affiliate_active){ ?>
							<tr>
								<td class="key">
										<?php echo JText::_( 'AFFILIATE_ACCOUNT_ACTIVE' ); ?>
								</td>
								<td>
									<?php echo JHTML::_('hikaselect.booleanlist', "data[user][user_partner_activated]",'',@$this->user->user_partner_activated); ?>
								</td>
							</tr>
							<tr>
								<td class="key">
										<?php echo JText::_( 'PAYMENT_EMAIL_ADDRESS' ); ?>
								</td>
								<td>
									<input type="text" size="30" name="data[user][user_partner_email]" class="inputbox" value="<?php echo $this->escape(@$this->user->user_partner_email); ?>" />
								</td>
							</tr>
						<?php
						}
						if(!empty($this->fields['user'])){
							foreach($this->fields['user'] as $fieldName => $oneExtraField) {
							?>
								<tr>
									<td class="key">
										<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
									</td>
									<td>
										<?php echo $this->fieldsClass->display($oneExtraField,$this->user->$fieldName,'data[user]['.$fieldName.']', false, '', false, $this->fields['user'],$this->user); ?>
									</td>
								</tr>
							<?php
							}
						}
						if(!empty($this->user->user_partner_activated)){
						?>
							<tr>
								<td class="key">
									<?php echo JText::_( 'PARTNER_CURRENCY' ); ?>
								</td>
								<td>
									<?php
									$config =& hikashop_config();
									if(!$config->get('allow_currency_selection',0) || empty($this->user->user_currency_id)){
										$this->user->user_currency_id =  $config->get('partner_currency',1);
									}
									if($config->get('allow_currency_selection',0)){
										echo $this->currencyType->display("data[user][user_currency_id]",$this->user->user_currency_id);
									}else{
										$currencyClass = hikashop_get('class.currency');
										$currency = $currencyClass->get($this->user->user_currency_id);
										echo $currency->currency_code;
									}
									?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<?php echo JText::_( 'CUSTOM_FEES' ); ?>
								</td>
								<td>
									<?php echo JHTML::_('hikaselect.booleanlist', "data[user][user_params][user_custom_fee]", 'onchange="updateCustomFeesPanel(this.value);return false;"',@$this->user->user_params->user_custom_fee);?>
								</td>
							</tr>
						</table>
						<div id="custom_fees_panel" <?php if(empty($this->user->user_params->user_custom_fee)) echo 'style="display:none"';?>>
							<table class="admintable table">
								<tr>
									<td class="key">
										<?php echo JText::_( 'PARTNER_FEES_CURRENCY' ); ?>
									</td>
									<td>
										<?php echo $this->currencyType->display("data[user][user_params][partner_fee_currency]",@$this->user->user_params->partner_fee_currency);?>
									</td>
								</tr>
								<tr>
									<td class="key">
										<?php echo JText::_( 'PARTNER_LEAD_FEE' ); ?>
									</td>
									<td>
										<input type="text" size="5" name="data[user][user_params][user_partner_lead_fee]" class="inputbox" value="<?php echo $this->escape(@$this->user->user_params->user_partner_lead_fee); ?>" />
									</td>
								</tr>
								<tr>
									<td class="key">
										<?php echo JText::_( 'PARTNER_ORDER_PERCENT_FEE' ); ?>
									</td>
									<td>
										<input type="text" size="5" name="data[user][user_params][user_partner_percent_fee]" class="inputbox" value="<?php echo $this->escape(@$this->user->user_params->user_partner_percent_fee); ?>" />%
									</td>
								</tr>
								<tr>
									<td class="key">
										<?php echo JText::_( 'PARTNER_ORDER_FLAT_FEE' ); ?>
									</td>
									<td>
										<input type="text" size="5" name="data[user][user_params][user_partner_flat_fee]" class="inputbox" value="<?php echo $this->escape(@$this->user->user_params->user_partner_flat_fee); ?>" />
									</td>
								</tr>
								<tr>
									<td class="key">
										<?php echo JText::_( 'PARTNER_CLICK_FEE' ); ?>
									</td>
									<td>
										<input type="text" size="5" name="data[user][user_params][user_partner_click_fee]" class="inputbox" value="<?php echo $this->escape(@$this->user->user_params->user_partner_click_fee); ?>" />
									</td>
								</tr>
							</table>
						</div>
							<?php
							}else{ echo '</table>'; }
							?>

						<?php if(!empty($this->user->user_partner_activated)){
							$config =& hikashop_config();
							$affiliate_payment_delay = $config->get('affiliate_payment_delay');
							 ?>
							<table class="admintable table">
								<thead>
									<tr>
										<th></th>
										<?php if(!empty($affiliate_payment_delay)){ ?><th><?php  $delayType = hikashop_get('type.delay'); echo hikashop_tooltip(JText::sprintf('AMOUNT_DELAY',$delayType->displayDelay($config->get('affiliate_payment_delay'))),JText::_('PAYABLE'),'',JText::_('PAYABLE'))?></th><?php } ?>
										<th><?php echo JText::_('HIKASHOP_TOTAL'); ?></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
											<?php echo JText::_( 'CLICKS_UNPAID_AMOUNT' );

												echo $this->popup->display(
													'<img src="'. HIKASHOP_IMAGES.'go.png" alt="'.JText::_('CLICKS_UNPAID_AMOUNT').'"/>',
													'CLICKS_UNPAID_AMOUNT',
													hikashop_completeLink('user&task=clicks&user_id='.$this->user->user_id.'',true),
													'clicks_link',
													760, 480, '', '', 'link'
												);
											?>
										</td>
										<?php if(!empty($affiliate_payment_delay)){ ?><td align="center">
											<?php echo $this->escape(@$this->user->accumulated['currentclicks']);?>
										</td><?php } ?>
										<td align="center">
											<?php echo $this->escape(@$this->user->accumulated['clicks']);?>
										</td>
									</tr>
									<tr>
										<td>
											<?php echo JText::_( 'LEADS_UNPAID_AMOUNT' );
												echo $this->popup->display(
													'<img src="'. HIKASHOP_IMAGES.'go.png" alt="'.JText::_('LEADS_UNPAID_AMOUNT').'"/>',
													'LEADS_UNPAID_AMOUNT',
													hikashop_completeLink('user&task=leads&user_id='.$this->user->user_id.'',true),
													'leads_link',
													760, 480, '', '', 'link'
												);?>
										</td>
										<?php if(!empty($affiliate_payment_delay)){ ?><td align="center">
											<?php echo $this->escape(@$this->user->accumulated['currentleads']);?>
										</td><?php } ?>
										<td align="center">
											<?php echo $this->escape(@$this->user->accumulated['leads']);?>
										</td>
									</tr>
									<tr>
										<td>
											<?php echo JText::_( 'SALES_UNPAID_AMOUNT' );
												echo $this->popup->display(
													'<img src="'. HIKASHOP_IMAGES.'go.png" alt="'.JText::_('SALES_UNPAID_AMOUNT').'"/>',
													'SALES_UNPAID_AMOUNT',
													hikashop_completeLink('user&task=sales&user_id='.$this->user->user_id.'',true),
													'sales_link',
													760, 480, '', '', 'link'
												); ?>
										</td>
										<?php if(!empty($affiliate_payment_delay)){ ?><td align="center">
											<?php echo $this->escape(@$this->user->accumulated['currentsales']); ?>
										</td><?php } ?>
										<td align="center">
											<?php echo $this->escape(@$this->user->accumulated['sales']); ?>
										</td>
									</tr>
									<tr>
										<td>
											<?php echo JText::_( 'TOTAL_UNPAID_AMOUNT' );
												echo $this->popup->display(
													'<img src="'. HIKASHOP_IMAGES.'pay.png" alt="'.JText::_('PAY_NOW').'"/>',
													'PAY_NOW',
													hikashop_completeLink('user&task=pay&user_id='.$this->user->user_id.'',true),
													'pay_link',
													760, 480, '', '', 'link'
												); ?>
										</td>
										<?php if(!empty($affiliate_payment_delay)){ ?><td align="center">
											<?php echo $this->escape(@$this->user->accumulated['currenttotal']); ?>
										</td><?php } ?>
										<td align="center">
											<?php echo $this->escape(@$this->user->accumulated['total']); ?>
										</td>
									</tr>
								</tbody>
							</table>
						<?php } ?>
				</fieldset>
				<fieldset class="adminform">
					<legend><?php echo JText::_('ADDRESSES');?></legend>
					<div class="toolbar" id="toolbar" style="float: right;">
						<?php
						echo $this->popup->display(
							'<img src="'. HIKASHOP_IMAGES.'add.png" alt="'.JText::_('ADD').'"/>',
							'ADD',
							hikashop_completeLink('user&task=editaddress&user_id='.$this->user->user_id.'',true),
							'add_address_link',
							760, 480, '', '', 'link'
						); ?>
					</div>
					<?php
						if(!empty($this->addresses)){
					?>
					<div style=" width: 300px ; margin-left: auto ; margin-right: auto ;">
					<table width"100%">
					<?php
							foreach($this->addresses as $address){
								$this->address =& $address;
						?>
						<tr>
							<td>
								<input type="radio" name="address_default" value="<?php echo $this->address->address_id;?>"<?php
									if($this->address->address_default == 1) {
										echo ' checked="checked"';
									}
								?> onclick="submitbutton('setdefault');"/>
							</td>
							<td>
								<span>
								<?php
									$addressClass = hikashop_get('class.address');
									echo $addressClass->displayAddress($this->fields['address'],$address,'order');
								?>
								</span>
							</td>
							<td>
								<a href="<?php echo hikashop_completeLink('user&task=deleteaddress&address_id='.$address->address_id.'&'.hikashop_getFormToken().'=1');?>"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png"/></a>
								<?php
								echo $this->popup->display(
									'<img src="'. HIKASHOP_IMAGES.'edit.png" alt="'.JText::_('HIKA_EDIT').'"/>',
									'HIKA_EDIT',
									hikashop_completeLink('user&task=editaddress&user_id='.$this->user->user_id.'&address_id='.$address->address_id,true),
									'aedit_address_'.$address->address_id.'_link',
									760, 480, '', '', 'link'
								); ?>
							</td>
						</tr>
						<?php
							}
					?>
					</table>
					</div>
					<?php
						}
					?>

				</fieldset>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
			<td valign="top" width="50%">
<?php } else { ?>
	</div>
	<div class="span6">
<?php } ?>
				<fieldset class="adminform">
					<legend><?php echo JText::_('ORDERS');?></legend>
					<table id="hikashop_user_order_listing" class="adminlist table table-striped table-hover" cellpadding="1">
						<thead>
							<tr>
								<th class="title titlenum">
									<?php echo JText::_( 'HIKA_NUM' );?>
								</th>
								<th class="title">
									<?php echo JText::_('ORDER_NUMBER'); ?>
								</th>
								<th class="title">
									<?php echo JText::_('PAYMENT_METHOD'); ?>
								</th>
								<th class="title">
									<?php echo JText::_('DATE'); ?>
								</th>
								<th class="title">
									<?php echo JText::_('HIKA_LAST_MODIFIED'); ?>
								</th>
								<th class="title">
									<?php echo JText::_('ORDER_STATUS'); ?>
								</th>
								<th class="title">
									<?php echo JText::_('HIKASHOP_TOTAL'); ?>
								</th>
								<th class="title">
									<?php echo JText::_( 'ID' ); ?>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$k = 0;
								$i = 0;
								foreach($this->rows as $row){
									$i++;
							?>
								<tr class="row<?php echo $k; ?>">
									<td align="center">
										<?php echo $i; ?>
									</td>
									<td align="center">
										<a href="<?php echo hikashop_completeLink('order&task=edit&cid[]='.$row->order_id.'&user_id='.$this->user->user_id); ?>">
											<?php echo $row->order_number; ?>
										</a>
									</td>
									<td align="center">
										<?php
										if(!empty($row->order_payment_method)){
											if(!empty($this->payments[$row->order_payment_id])){
												echo $this->payments[$row->order_payment_id]->payment_name;
											}else{
												echo $row->order_payment_method;
											}
										} ?>
									</td>
									<td align="center">
										<?php echo hikashop_getDate($row->order_created,'%Y-%m-%d %H:%M');?>
									</td>
									<td align="center">
										<?php echo hikashop_getDate($row->order_modified,'%Y-%m-%d %H:%M');?>
									</td>
									<td align="center">
										<?php echo hikashop_orderStatus($row->order_status); ?>
									</td>
									<td align="center">
										<?php echo $this->currencyHelper->format($row->order_full_price,$row->order_currency_id);?>
									</td>
									<td width="1%" align="center">
										<?php echo $row->order_id; ?>
									</td>
								</tr>
							<?php
									$k = 1-$k;
								}
							?>
						</tbody>
					</table>
				</fieldset>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
		</tr>
	</table>
</div>
<?php } else { ?>
	</div>
</div>
<?php } ?>
	<input type="hidden" name="cancel_redirect" value="<?php echo base64_encode(JRequest::getString('cancel_redirect'));?>" />
	<input type="hidden" name="cid[]" value="<?php echo @$this->user->user_id; ?>" />
	<input type="hidden" name="order_id" value="<?php echo JRequest::getInt('order_id',0); ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="user" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
