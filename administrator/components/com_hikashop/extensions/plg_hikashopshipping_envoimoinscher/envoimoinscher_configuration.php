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

try{
	include_once(dirname(__FILE__) . DS . 'lib' . DS . 'envoimoinscher.php');
}catch(Exception $e){
	hikashop_display($e->getMessage());
}
?><input type="hidden" name="lang_file_override" value="<?php echo @$this->element->shipping_params->lang_file_override;?>" />
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][emc_login]">
				<?php echo JText::_( 'HIKA_LOGIN' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][emc_login]" value="<?php echo @$this->element->shipping_params->emc_login; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][emc_password]">
				<?php echo JText::_( 'HIKA_PASSWORD' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][emc_password]" value="<?php echo @$this->element->shipping_params->emc_password; ?>" />
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
			<label for="data[shipping][shipping_params][sender_civility]">
				<?php echo JText::_( 'TITLE' ); ?>
			</label>
		</td>
		<td>
			<?php
			$options = array("Mr"=>JText::_('HIKA_TITLE_MR'), "Mrs"=>JText::_('HIKA_TITLE_MRS'), "Miss"=>JText::_('HIKA_TITLE_MISS'));
			$opts = array();
			foreach($options as $key=>$value){
				$opts[] = @JHTML::_('select.option',$key,$value);
			}

			echo JHTML::_('select.genericlist',$opts,"data[shipping][shipping_params][sender_civility]" , '', 'value', 'text', @$this->element->shipping_params->sender_civility); ?>
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][sender_lastname]">
				<?php echo JText::_( 'LASTNAME' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][sender_lastname]" value="<?php echo @$this->element->shipping_params->sender_lastname; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][sender_firstname]">
				<?php echo JText::_( 'FIRSTNAME' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][sender_firstname]" value="<?php echo @$this->element->shipping_params->sender_firstname; ?>" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][type]">
				<?php echo JText::_( 'HIKA_TYPE' ); ?>
			</label>
		</td>
		<td>
			<?php
			$options = array("entreprise"=>JText::_('COMPANY'), "particulier"=>JText::_('INDIVIDUAL'));
			$opts = array();
			foreach($options as $key => $value){
				$opts[] = @JHTML::_('select.option',$key,$value);
			}

			echo JHTML::_('select.genericlist',$opts,"data[shipping][shipping_params][type]" , '', 'value', 'text', @$this->element->shipping_params->type); ?>
		</td>
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
			<label for="data[shipping][shipping_params][sender_email]">
				<?php echo JText::_( 'HIKA_EMAIL' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][sender_email]" value="<?php echo @$this->element->shipping_params->sender_email; ?>" />
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
			<label for="data[shipping][shipping_params][sender_country]">
				<?php echo JText::_( 'COUNTRY' ); ?>
			</label>
		</td>
		<td><?php
			$nameboxType = hikashop_get('type.namebox');
			echo $nameboxType->display(
				'data[shipping][shipping_params][sender_country]',
				@$this->element->shipping_params->sender_country,
				hikashopNameboxType::NAMEBOX_SINGLE,
				'zone',
				array(
					'delete' => true,
					'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
					'zone_types' => array('country' => 'COUNTRY', 'tax' => 'TAXES'),
				)
			);
		?></td>
	</tr>
	<!-- List of all carriers -->
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][services]">
				<?php echo JText::_( 'SHIPPING_SERVICES' ); ?>
			</label>
		</td>
	<td id="shipping_services_list">
			<?php
				echo '<a style="cursor: pointer;" onclick="checkAllBox(\'shipping_services_list\',\'check\');">'.JText::_('SELECT_ALL').'</a> / <a style="cursor: pointer;" onclick="checkAllBox(\'shipping_services_list\',\'uncheck\');">'.JText::_('UNSELECT_ALL').'</a><br/>';
				$i = -1;
				foreach($this->data['envoimoinscher_methods'] as $method){
					$i++;
					$varName = $method['name'];
					$selMethods = unserialize(@$this->element->shipping_params->methodsList);
			?>
					<div>
					<input name="data[shipping_methods][<?php echo $varName;?>][name]" type="checkbox" value="<?php echo $varName;?>" <?php echo (!empty($selMethods[$varName])?'checked="checked"':''); ?>/><?php echo $method['name']; ?>
			<?php
					$opts = array();
					if(!empty($this->element->shipping_params->envoimoinscher_dropoff)){
						foreach(@$this->element->shipping_params->envoimoinscher_dropoff as $key => $value){
							if(!empty($value)){
								foreach($value as $k => $v){
									if($key == $method['code'] && !empty($v['name'])){
										$opts[] = @JHTML::_('select.option',$v['code'].'$'.$v['name'].' : '.$v['address'].', '.$v['zipcode'].', '.$v['city'],$v['name'].' : '.$v['address'].', '.$v['zipcode'].', '.$v['city']);
									}
								}
							}
						}
					}
					if(!empty($opts)){
						$name = $method["code"];
						echo '<span style = "margin-left:10px;">' . JHTML::_('select.genericlist',$opts,"data[shipping][shipping_params][$name]" , '', 'value', 'text', @$this->element->shipping_params->$name) . '</span>';
					}
				}
			 ?>
			 </div>
		</td>
	</tr>
	<!-- work environment , if value = test we send request to "test.envoimoinscher.com" -->
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][environment]">
				<?php echo JText::_( 'ENVIRONNEMENT' ); ?>
			</label>
		</td>
		<td>
			<?php
			$options = array("test"=>JText::_("HIKA_TEST"), "prod"=>JText::_("HIKA_PRODUCTION"));
			$opts = array();
			foreach($options as $key => $value){
				$opts[] = @JHTML::_('select.option',$key,$value);
			}

			echo JHTML::_('select.genericlist',$opts,"data[shipping][shipping_params][environment]" , '', 'value', 'text', @$this->element->shipping_params->environment); ?>
		</td>
	</tr>
	<!-- Important option!! if it's activated we can make directly the order, if it's disabled we can only get quotation -->
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][make_order]">
				<?php echo JText::_( 'Make order' ); ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', "data[shipping][shipping_params][make_order]" , '',@$this->element->shipping_params->make_order); ?>
		</td>
	</tr>
	<!-- To display opening hours of the relay points -->
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][schedule_display]">
				<?php echo JText::_( 'DISPLAY_OPENING_HOURS_PICKUP_POINT' ); ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', "data[shipping][shipping_params][schedule_display]" , '',@$this->element->shipping_params->schedule_display); ?>
		</td>
	</tr>
	<!-- Type of products sold with this method -->
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][sending_type]">
				<?php echo JText::_( 'SENDING_TYPE' ); ?>
			</label>
		</td>
		<td>
			<?php
			$options = array("colis"=>"Colis ou Paquet (poids < 70 kg)", "pli"=>"Pli ou courrier (enveloppe : poids < 3 kg)", "encombrant"=>"Encombrant (poids > 70 kg)");
			$opts = array();
			foreach($options as $key => $value){
				$opts[] = @JHTML::_('select.option',$key,$value);
			}

			echo JHTML::_('select.genericlist',$opts,"data[shipping][shipping_params][sending_type]" , '', 'value', 'text', @$this->element->shipping_params->sending_type); ?>
		</td>
	</tr>
	<!--  Type "pallet" doesn't work yet correctly with the library envoimoinscher
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][pallet_dimensions]">
				<?php echo JText::_( 'Pallet dimensions' ); ?>
			</label>
		</td>
		<td>
			<?php
			$palletDims = array(12080 => '120 x 80 cm EUR EPAL', 8060 => '80 x 60 cm demi palette', 120100 => '120 x 100 cm Standard Export',
							107107 => '107 x 107 cm', 110110 => '110 x 110 cm', 114114 => '114 x 114 cm', 11476 => '114 x 76 cm',
							120120 => '120 x 120 cm', 122102 => '122 x 102 cm',  130110 => '130 x 110 cm'
							);
			$opts = array();
			foreach($palletDims as $key => $value){
				$opts[] = @JHTML::_('select.option',$key,$value);
			}
			echo JHTML::_('select.genericlist',$opts,"data[shipping][shipping_params][pallet_dimensions]" , '', 'value', 'text', @$this->element->shipping_params->pallet_dimensions); ?>
		</td>
	</tr>
	-->
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
	<!-- Category of products sold with this method (necessary when we send request to envoimoinscher) -->
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][product_category]">
				<?php echo JText::_( 'PRODUCT_CATEGORY' ); ?>
			</label>
		</td>
		<td>
			<?php
			if(empty($this->element->shipping_params->emc_login) || empty($this->element->shipping_params->emc_password) || empty($this->element->shipping_params->api_key)){
				echo '<span style="color:red;">Please first fill in your login, password and API key</span>';
			}elseif(empty($this->element->shipping_params->contentCl['categories'])){
				echo '<span style="color:red;">Impossible de récupérer la liste des catégories auprès de Envoimoinscher. Veuillez vérifier les informations de connexion que vous avez entré.</span>';
			}else{
				$options = array();
				foreach($this->element->shipping_params->contentCl['categories'] as $key => $value){
					$options[$key] = $value['label'];
				}
				foreach($this->element->shipping_params->contentCl['contents'] as $key => $value){
					foreach($value as $k => $v){
						$options[$v['code']] = $v['label'];
					}
				}
				ksort($options);
				unset($options[100]);
				$opts = array();
				foreach($options as $key => $value){
					if($key%1000 != 0)
						$opts[] = @JHTML::_('select.option',$key,$value);
					else
						$opts[] = @JHTML::_('select.option',$key,$value, 'value', 'text',$disable=true);
				}

				echo JHTML::_('select.genericlist',$opts,"data[shipping][shipping_params][product_category]" , '', 'value', 'text', @$this->element->shipping_params->product_category);
			}
				?>
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
					JHTML::_('select.option', 'auto', JText::_('AUTO_DETERMINATION') ),
					JHTML::_('select.option', 'res', JText::_('RESIDENTIAL_ADDRESS') ),
					JHTML::_('select.option', 'com', JText::_('COMMERCIAL_ADDRESS') ),
				);
				echo JHTML::_('hikaselect.genericlist', $arr, "data[shipping][shipping_params][destination_type]", 'class="inputbox" size="1"', 'value', 'text', @$this->element->shipping_params->destination_type);
			?>
	</tr>
	<!-- Availability times for the collection of the package at home or in company (mandatory for some offers) -->
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][start_availability]">
				<?php echo JText::_( 'AVAILABILITY_COLLECTION' ); ?>
			</label>
		</td>
		<td>
			<input type="time" name="data[shipping][shipping_params][start_availability]" style="width:70px;" value="<?php echo @$this->element->shipping_params->start_availability; ?>" />
			<input type="time" name="data[shipping][shipping_params][end_availability]" style="width:70px;" value="<?php echo @$this->element->shipping_params->end_availability; ?>" />
		</td>
	</tr>
	<!-- To take into account the weight of the package. if it's empty default value = 0.01% -->
	<tr>
		<td class="key">
			<label for="data[shipping][shipping_params][package_weight]">
				<?php echo JText::_( 'PACKAGE_WEIGHT' ); ?>
			</label>
		</td>
		<td>
			<input type="text" name="data[shipping][shipping_params][package_weight]" value="<?php echo @$this->element->shipping_params->package_weight; ?>" /> %
		</td>
	</tr>
</fieldset>
