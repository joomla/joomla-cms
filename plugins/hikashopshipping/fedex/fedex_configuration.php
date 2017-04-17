<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><input type="hidden" name="lang_file_override" value="<?php echo @$this->element->shipping_params->lang_file_override;?>" />
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][origination_postcode]">
				<?php echo JText::_( 'FEDEX_ORIGINATION_POSTCODE' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][origination_postcode]" value="<?php echo @$this->element->shipping_params->origination_postcode; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][account_number]">
				<?php echo JText::_( 'FEDEX_ACCOUNT_NUMBER' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][account_number]" value="<?php echo @$this->element->shipping_params->account_number; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][meter_id]">
				<?php echo JText::_( 'FEDEX_METER_ID' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][meter_id]" value="<?php echo @$this->element->shipping_params->meter_id; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][api_key]">
				<?php echo JText::_( 'FEDEX_API_KEY' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][api_key]" value="<?php echo @$this->element->shipping_params->api_key; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][api_password]">
				<?php echo JText::_( 'HIKA_PASSWORD' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][api_password]" value="<?php echo @$this->element->shipping_params->api_password; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][rate_types]">
				<?php echo 'Rates'; ?>
			</label>
		</td>
		<td>
			<?php
			$options = array("LIST"=>"Public rates", "ACCOUNT"=>"Discounted rates of your FedEx account");
			$opts = array();
			foreach($options as $key=>$value){
				$opts[] = @JHTML::_('select.option',$key,$value);
			}

			echo JHTML::_('select.genericlist',$opts,"data[shipping][shipping_params][rate_types]" , '', 'value', 'text', @$this->element->shipping_params->rate_types); ?>
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][destination_type]">
				<?php echo JText::_( 'DESTINATION_TYPE' ); ?>
			</label>
		</td>
		<td>
			<?php
				$arr = array(
					JHTML::_('select.option', 'auto', JText::_('Auto-determination') ),
					JHTML::_('select.option', 'res', JText::_('Residential Address') ),
					JHTML::_('select.option', 'com', JText::_('Commercial Address') ),
				);
				echo JHTML::_('hikaselect.genericlist', $arr, "data[shipping][shipping_params][destination_type]", 'class="inputbox" size="1"', 'value', 'text', @$this->element->shipping_params->destination_type);
			?>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][sender_company]">
				<?php echo JText::_( 'COMPANY' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][sender_company]" value="<?php echo @$this->element->shipping_params->sender_company; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][sender_phone]">
				<?php echo JText::_( 'TELEPHONE' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][sender_phone]" value="<?php echo @$this->element->shipping_params->sender_phone; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][sender_address]">
				<?php echo JText::_( 'ADDRESS' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][sender_address]" value="<?php echo @$this->element->shipping_params->sender_address; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][sender_city]">
				<?php echo JText::_( 'CITY' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][sender_city]" value="<?php echo @$this->element->shipping_params->sender_city; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][sender_state]">
				<?php echo JText::_( 'STATE' ); ?>
			</label>
		</td>
		<td>
			<?php
				echo $this->data['nameboxType']->display(
					'data[shipping][shipping_params][sender_state]',
					@$this->element->shipping_params->sender_state,
					hikashopNameboxType::NAMEBOX_SINGLE,
					'zone',
					array(
						'delete' => true,
						'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
						'zone_types' => array('state' => 'STATE'),
					)
				);
			?>
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][sender_country]">
				<?php echo JText::_( 'COUNTRY' ); ?>
			</label>
		</td>
		<td>
			<?php
				echo $this->data['nameboxType']->display(
					'data[shipping][shipping_params][sender_country]',
					@$this->element->shipping_params->sender_country,
					hikashopNameboxType::NAMEBOX_SINGLE,
					'zone',
					array(
						'delete' => true,
						'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
						'zone_types' => array('country' => 'COUNTRY'),
					)
				);
			?>
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][sender_postcode]">
				<?php echo JText::_( 'POST_CODE' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][sender_postcode]" value="<?php echo @$this->element->shipping_params->sender_postcode; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][show_notes]">
				<?php echo JText::_( 'FEDEX_SHOW_NOTES' ); ?>
			</label>
		</td>
		<td>
			<input class="inputbox" type="checkbox" name="data[shipping][shipping_params][show_notes]" <?php
				if (@$this->element->shipping_params->show_notes=="1") {
					echo 'checked="checked"';
				}
				?> value="1" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][show_eta]">
				<?php echo JText::_( 'FEDEX_SHOW_ETA' ); ?>
			</label>
		</td>
		<td>
			<input class="inputbox" type="checkbox" name="data[shipping][shipping_params][show_eta]" <?php
				if (@$this->element->shipping_params->show_eta=="1") {
					echo 'checked="checked"';
				}
				?> value="1" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][show_eta_delay]">
				<?php echo JText::_( 'FEDEX_SHOW_ETA_DELAY' ); ?>
			</label>
		</td>
		<td>
			<input class="inputbox" type="checkbox" name="data[shipping][shipping_params][show_eta_delay]" <?php
				if (@$this->element->shipping_params->show_eta_delay=="1") {
					echo 'checked="checked"';
				}
				?> value="1" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][show_eta_format]">
				ETA format
			</label>
		</td>
		<td>
			<?php
				$arr = array(
					JHTML::_('select.option', '12', '12 Hour' ),
					JHTML::_('select.option', '24', '24 Hour' ),
				);
				echo JHTML::_('hikaselect.genericlist', $arr, "data[shipping][shipping_params][show_eta_format]", 'class="inputbox" size="1"', 'value', 'text', @$this->element->shipping_params->show_eta_format);
			?>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][services]">
				<?php echo JText::_( 'SHIPPING_SERVICES' ); ?>
			</label>
		</td>
		<td id="shipping_services_list">
			<?php
					echo '<a style="cursor: pointer;" onclick="checkAllBox(\'shipping_services_list\',\'check\');">'.JText::_('SELECT_ALL').'</a> / <a style="cursor: pointer;" onclick="checkAllBox(\'shipping_services_list\',\'uncheck\');">'.JText::_('UNSELECT_ALL').'</a><br/>';
					$i=-1; foreach($this->data['fedex_methods'] as $method){
					$i++;
					$varName=strtolower($method['name']);
					$varName=str_replace(' ','_', $varName);
					$selMethods = unserialize(@$this->element->shipping_params->methodsList);

				?>
				<input name="data[shipping_methods][<?php echo $varName;?>][name]" type="checkbox" value="<?php echo $varName;?>" <?php echo (!empty($selMethods[$varName])?'checked="checked"':''); ?>/><?php echo $method['name'].' ('.@$method['countries'].')'; ?><br/>
			<?php	} ?>
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][packaging_type]">
				<?php echo JText::_( 'SHIPPING_PACKAGING_TYPE' ); ?>
			</label>
		</td>
		<td>
			<?php
			$options = array("FEDEX_BOX"=>"FedEx Box", "FEDEX_PAK"=>"FedEx Pak", "FEDEX_TUBE"=>"FedEx Tube", "YOUR_PACKAGING"=>JText::_( 'SHIPPING_YOUR_PACKAGING'));
			$opts = array();
			foreach($options as $key=>$value){
				$opts[] = @JHTML::_('select.option',$key,$value);
			}

			echo JHTML::_('select.genericlist',$opts,"data[shipping][shipping_params][packaging_type]" , '', 'value', 'text', @$this->element->shipping_params->packaging_type); ?>
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][include_price]">
				<?php echo JText::_( 'INCLUDE_PRICE' ); ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', "data[shipping][shipping_params][include_price]" , '',@$this->element->shipping_params->include_price	); ?>
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][weight_approximation]">
				<?php echo JText::_( 'UPS_WEIGHT_APPROXIMATION' ); ?>
			</label>
		</td>
		<td>
			<input size="5" type="text" name="data[shipping][shipping_params][weight_approximation]" value="<?php echo @$this->element->shipping_params->weight_approximation; ?>" />%
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][dim_approximation_l]">
				<?php echo JText::_( 'SHIPPING_BOX_DIMENSIONS' ); ?>
			</label>
		</td>
		<td>
			<label for="data[shipping][shipping_params][dim_approximation_l]"><?php echo JText::_( 'PRODUCT_LENGTH' ); ?></label> <input size="5" type="text" name="data[shipping][shipping_params][dim_approximation_l]" value="<?php echo @$this->element->shipping_params->dim_approximation_l; ?>" /> x <label for="data[shipping][shipping_params][dim_approximation_w]"><?php echo JText::_( 'PRODUCT_WIDTH' ); ?></label> <input size="5" type="text" name="data[shipping][shipping_params][dim_approximation_w]" value="<?php echo @$this->element->shipping_params->dim_approximation_w; ?>" /> x <label for="data[shipping][shipping_params][dim_approximation_h]"><?php echo JText::_( 'PRODUCT_HEIGHT' ); ?></label> <input size="5" type="text" name="data[shipping][shipping_params][dim_approximation_h]" value="<?php echo @$this->element->shipping_params->dim_approximation_h; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][use_dimensions]">
					<?php echo JText::_( 'FEDEX_USE_BOX_DIMENSION' ); ?>
			</label>
		</td>
		<td>
			<input class="inputbox" type="checkbox" name="data[shipping][shipping_params][use_dimensions]" <?php
				if (@$this->element->shipping_params->use_dimensions=="1") {
					echo 'checked="checked"';
				}
				?> value="1" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][group_package]">
				<?php echo JText::_( 'GROUP_PACKAGE' ); ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', "data[shipping][shipping_params][group_package]" , '',@$this->element->shipping_params->group_package	); ?>
		</td>
	</tr>
	<tr>
	<td class="key">
		<label for="data[shipping][shipping_params][debug]"><?php
			echo JText::_('DEBUG');
		?></label>
	</td>
	<td><?php
		echo JHTML::_('hikaselect.booleanlist', "data[shipping][shipping_params][debug]" , '', @$this->element->shipping_params->debug);
	?></td>
</tr>
</fieldset>
