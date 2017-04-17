<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><form action="<?php echo hikashop_completeLink('update&task=wizard_save'); ?>" method="post" name="adminForm" id="adminForm">
<?php if(!HIKASHOP_J30) { ?>
	<fieldset>
<?php } ?>
	<div id="layout_menu_module_div" class="row-fluid wizard_main_divs <?php if(!HIKASHOP_J30)echo 'wizard_main_divs_j25'; ?>">
		<div style="text-align:left; margin-bottom: 10px;"><img src="<?php echo HIKASHOP_IMAGES; ?>wizard/step1.png" alt="1"/><?php echo JText::_('WIZARD_SHOP_ACCESS'); ?></div>
		<div class="span2 offsetdiv"></div>
		<div id="layout_products_menu" class="span2 wizard_thumbnail" onclick="selectLayout(this, 'layout_menu_module','products_menu');">
			<div class="wizard_thumbnail_img">
				<img src="<?php echo HIKASHOP_IMAGES; ?>wizard/menu_products.png" alt="layout_menu"/>
			</div>
			<div class="wizard_thumbnail_desc">
				<?php echo JText::_('PRODUCTS_LISTING_MENU'); ?>
				<br/><img src="<?php echo HIKASHOP_IMAGES; ?>wizard/separator.png" alt=""/><br/>
				<?php echo JText::_('PRODUCTS_LISTING_MENU_DESC'); ?>
			</div>
		</div>
		<div class="span1 offsetdiv"></div>
		<div id="layout_categories_menu" class="span2 wizard_thumbnail" onclick="selectLayout(this, 'layout_menu_module','categories_menu');">
			<div class="wizard_thumbnail_img">
				<img src="<?php echo HIKASHOP_IMAGES; ?>wizard/menu_categories.png" alt="layout_menu"/>
			</div>
			<div class="wizard_thumbnail_desc">
				<?php echo JText::_('CATEGORIES_LISTING_MENU'); ?>
				<br/><img src="<?php echo HIKASHOP_IMAGES; ?>wizard/separator.png" alt=""/><br/>
				<?php echo JText::_('CATEGORIES_LISTING_MENU_DESC'); ?>
			</div>
		</div>
		<div class="span1 offsetdiv"></div>
		<div id="layout_categories_module" class="span2 wizard_thumbnail" onclick="selectLayout(this, 'layout_menu_module','categories_module');">
			<div class="wizard_thumbnail_img">
				<img src="<?php echo HIKASHOP_IMAGES; ?>wizard/module.png" alt="layout_menu"/>
			</div>
			<div class="wizard_thumbnail_desc">
				<?php echo JText::_('CATEGORIES_LISTING_MODULE'); ?>
				<br/><img src="<?php echo HIKASHOP_IMAGES; ?>wizard/separator.png" alt=""/><br/>
				<?php echo JText::_('CATEGORIES_LISTING_MODULE_DESC'); ?>
			</div>
		</div>
		<div class="span2 offsetdiv"></div>
		<input type="hidden" value="1" id="products_menu" name="products_menu"/>
		<input type="hidden" value="1" id="categories_menu" name="categories_menu"/>
		<input type="hidden" value="1" id="categories_module" name="categories_module"/>
	</div>
	<div id="layout_type_div" class="row-fluid wizard_main_divs <?php if(!HIKASHOP_J30)echo 'wizard_main_divs_j25'; ?>">
		<div style="text-align:left; margin-bottom: 10px;"><img src="<?php echo HIKASHOP_IMAGES; ?>wizard/step2.png" alt="2"/><?php echo JText::_('WIZARD_LISTING_TYPE'); ?></div>
		<div class="span2 offsetdiv"></div>
		<div id="layout_listing_div" class="span2 wizard_thumbnail" onclick="selectLayout(this, 'layout_type','listing_div');">
			<div class="wizard_thumbnail_img">
				<img src="<?php echo HIKASHOP_IMAGES; ?>wizard/display_div.png" alt="layout_div"/>
			</div>
			<div class="wizard_thumbnail_desc">
				<?php echo JText::_('PRODUCTS_LISTING_DIV'); ?>
				<br/><img src="<?php echo HIKASHOP_IMAGES; ?>wizard/separator.png" alt=""/><br/>
				<?php echo JText::_('PRODUCTS_LISTING_DIV_DESC'); ?>
			</div>
		</div>
		<div class="span1 offsetdiv"></div>
		<div id="layout_listing_list" class="span2 wizard_thumbnail" onclick="selectLayout(this, 'layout_type','listing_list');">
			<div class="wizard_thumbnail_img">
				<img src="<?php echo HIKASHOP_IMAGES; ?>wizard/display_list.png" alt="layout_div"/>
			</div>
			<div class="wizard_thumbnail_desc">
				<?php echo JText::_('PRODUCTS_LISTING_LIST'); ?>
				<br/><img src="<?php echo HIKASHOP_IMAGES; ?>wizard/separator.png" alt=""/><br/>
				<?php echo JText::_('PRODUCTS_LISTING_LIST_DESC'); ?>
			</div>
		</div>
		<div class="span1 offsetdiv"></div>
		<div id="layout_listing_table" class="span2 wizard_thumbnail" onclick="selectLayout(this, 'layout_type','listing_table');">
			<div class="wizard_thumbnail_img">
				<img src="<?php echo HIKASHOP_IMAGES; ?>wizard/display_table.png" alt="layout_div"/>
			</div>
			<div class="wizard_thumbnail_desc">
				<?php echo JText::_('PRODUCTS_LISTING_TABLE'); ?>
				<br/><img src="<?php echo HIKASHOP_IMAGES; ?>wizard/separator.png" alt=""/><br/>
				<?php echo JText::_('PRODUCTS_LISTING_TABLE_DESC'); ?>
			</div>
		</div>
		<div class="span2 offsetdiv"></div>
		<input type="hidden" value="listing_div" id="layout_type" name="layout_type"/>
	</div>
	<div class="row-fluid wizard_main_divs <?php if(!HIKASHOP_J30)echo 'wizard_main_divs_j25'; ?>">
		<div style="text-align:left; margin-bottom: 10px;"><img src="<?php echo HIKASHOP_IMAGES; ?>wizard/step3.png" alt="3"/><?php echo JText::_('WIZARD_LOCATION'); ?></div>
		<div class="wizard_subdiv_left span3">
			<img src="<?php echo HIKASHOP_IMAGES; ?>wizard/location.png" alt=""/>
		</div>
		<div class="wizard_subdiv_right span8">
	<!-- ADDRESS -->
			<div class="wizard_field_address">
<?php
	if(isset($this->extraFields['address']['address_country'])) {
		$oneExtraField = $this->extraFields['address']['address_country'];
		echo '<span class="wizard_label"><label for="address_country">'.JText::_('COUNTRY').': '.'</label></span>';
		echo $this->fieldsClass->display($oneExtraField,@$this->address->address_country,'address_country',false,'').'<br/>';
	}
	if(isset($this->extraFields['address']['address_state'])) {
		$oneExtraField = $this->extraFields['address']['address_state'];
		echo '<span class="wizard_label"><label for="address_state">'.JText::_('STATE').': '.'</label></span>';
		echo $this->fieldsClass->display($oneExtraField,@$this->address->address_state,'address_state',false,'').'<br/>';
	}
?>
			</div>
			<div class="wizard_shop_address">
				<span class="wizard_label"><label for="shop_address"><?php echo JText::_('ADDRESS').': '; ?></label></span>
				<textarea class="wizard_shop_address" style="min-width:300px; min-height: 80px;" id="shop_address" name="shop_address"></textarea>*
			</div>
		</div>
	</div>
	<div class="row-fluid wizard_main_divs <?php if(!HIKASHOP_J30)echo 'wizard_main_divs_j25'; ?>">
		<div style="text-align:left; margin-bottom: 10px;">
			<img src="<?php echo HIKASHOP_IMAGES; ?>wizard/step4.png" alt="4"/><?php echo JText::_('WIZARD_MAIN_PARAMETERS'); ?>
		</div>
<?php if(!HIKASHOP_J30){ ?>
		<div style="text-align:left; margin-bottom: 10px;">
<?php } ?>
			<div>
			<div class="wizard_subdiv_left span3">
				<img src="<?php echo HIKASHOP_IMAGES; ?>wizard/tax.png" alt=""/>
			</div>
			<div class="wizard_subdiv_right span8">
				<div class="wizard_money"><?php echo JText::_('WIZARD_MONEY'); ?></div>
		<!-- CURRENCY -->
				<div class="wizard_shop_currency">
					<span class="wizard_label"><label for="wizard_currency"><?php echo JText::_('MAIN_CURRENCY').': '; ?></label></span>
					<select name="currency" id="wizard_currency"><?php
						foreach($this->currencies as $currency){
							$selected = '';
							if($currency->currency_code == 'EUR') $selected = 'selected="selected"';
							echo '<option value="'.$currency->currency_id.'" '.$selected.'>'.$currency->currency_code.' '.$currency->currency_symbol.'</option>';
						}
					?></select>
				</div>
		<!-- TAX -->
				<div class="wizard_shop_tax">
					<dl>
						<dt>
							<label for="wizard_tax_name"><?php echo JText::_('TAX_NAME').': '; ?></label>
						</dt>
						<dd>
							<input type="text" name="tax_name" id="wizard_tax_name" value=""/>
						</dd>
						<dt>
							<label for="wizard_tax_rate"><?php echo JText::_('RATE').': '; ?></label>
						</dt>
						<dd>
							<input style="width:50px;" type="text" name="tax_rate" id="wizard_tax_rate" value=""/>%
						</dd>
					</dl>
				</div>
			</div>
			</div>
<?php
	if(!empty($this->languageNames)){
?>
			<div style="clear:both;"></div>
			<div>
			<div class="wizard_subdiv_left span3">
				<img src="<?php echo HIKASHOP_IMAGES; ?>wizard/language.png" alt=""/>
			</div>
			<div class="wizard_subdiv_right span8">
				<img src="<?php echo HIKASHOP_IMAGES; ?>wizard/separator2.png" alt=""/>
		<!-- LANGUAGES -->
				<div class="wizard_shop_language">
				<?php
					echo '<div class="wizard_language">'.JText::_('WIZARD_LANGUAGE').'</div>';
					echo JText::sprintf('USE_JOOMLA_LANGUAGES',$this->languageNames); ?><br/>
					<?php
					$values = array(
						JHTML::_('select.option', $this->languageCodes, JText::_('HIKASHOP_YES')),
						JHTML::_('select.option', 0,JText::_('HIKASHOP_NO'))
					);
					echo JHTML::_('hikaselect.radiolist',  $values, 'import_language', '', 'value', 'text', 0 );
					?>
				</div>
			</div>
			</div>
<?php
	}
?>
			<div style="clear:both;"></div>
			<div>
				<div class="wizard_subdiv_left span3">
					<br/><br/>
					<img src="<?php echo HIKASHOP_IMAGES; ?>wizard/paypal.png" alt=""/>
				</div>
				<div class="wizard_subdiv_right span8">
					<img src="<?php echo HIKASHOP_IMAGES; ?>wizard/separator2.png" alt=""/>
		<!-- PAYPAL -->
					<div class="wizard_shop_paypal">
						<div class="wizard_language"><?php echo JText::_('WIZARD_PAYPAL'); ?></div>
						<label for="wizard_paypal_email"><?php echo JText::_('HIKA_PAYPAL_EMAIL_OPTIONAL').': '; ?></label>
						<input type="text" name="paypal_email" id="wizard_paypal_email" value=""/>
					</div>
				</div>
			</div>
			<div style="clear:both;"></div>
			<div>
				<div class="wizard_subdiv_left span3">
					<br/><br/>
					<img src="<?php echo HIKASHOP_IMAGES; ?>wizard/product_type.png" alt=""/>
				</div>
				<div class="wizard_subdiv_right span8">
					<img src="<?php echo HIKASHOP_IMAGES; ?>wizard/separator2.png" alt=""/>
					<!-- PAYPAL -->
					<div class="wizard_shop_virtual">
						<br/>
						<div class="wizard_product_type"><b><?php echo JText::_('WIZARD_PRODUCT_TYPE'); ?></b></div>
							<label for="wizard_virtual_product"><?php echo JText::_('WIZARD_PRODUCT_TYPE_SOLD').': '; ?></label>
							<select name="product_type" id="wizard_virtual_product">
								<option value="virtual"><?php echo JText::_('WIZARD_VIRTUAL'); ?></option>
								<option value="real"><?php echo JText::_('WIZARD_REAL'); ?></option>
								<option value="both"><?php echo JText::_('WIZARD_BOTH'); ?></option>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div style="clear:both;"></div>
			<!-- SAMPLE DATA -->
			<?php if(HIKASHOP_J25){ ?>
			<div>
				<div class="wizard_subdiv_left span3">
					<br/><br/>
					<img src="<?php echo HIKASHOP_IMAGES; ?>wizard/sampledata.png" alt=""/>
				</div>
				<div class="wizard_subdiv_right span8">
					<img src="<?php echo HIKASHOP_IMAGES; ?>wizard/separator2.png" alt=""/>
					<div class="wizard_shop_sample">
						<br/>
						<div class="wizard_data_sample"><b><?php echo JText::_('WIZARD_SAMPLE_DATA'); ?></b></div><br/>
							<label for="wizard_virtual_product"><?php echo JText::_('WIZARD_ACTIVATE_DATA').' : '; ?></label>
							<?php
							$values = array(
								JHTML::_('select.option', 1, JText::_('HIKASHOP_YES')),
								JHTML::_('select.option', 0,JText::_('HIKASHOP_NO'))
							);
							echo JHTML::_('hikaselect.radiolist',  $values, 'data_sample', '', 'value', 'text', 0 );
							?>
						</div>
					</div>
				</div>
			</div>
			<?php } ?>
			<!--</div>-->
<?php if(!HIKASHOP_J30){ ?>
		</div>
<?php } ?>
	</div>
	<div style="text-align:center;	margin: 40px auto;">
		<input class="wizard_button" type="submit" value="<?php echo JText::_('SAVE_AND_CREATE_FIRST_PRODUCT'); ?>"/>
		<span class="wizard_submit_arrow<?php if(HIKASHOP_J30)echo '_j30'; ?>">
			<img onclick="document.adminForm.submit();" src="<?php echo HIKASHOP_IMAGES; ?>wizard/arrow.png" alt=""/>
		</span>
	</div>
<?php if(!HIKASHOP_J30) { ?>
	</fieldset>
<?php } ?>
</form>
<script type="text/javascript">
	if(!window.hkjQuery)
		window.hkjQuery = window.jQuery;

	function selectLayout(el, type, value){
		if(type == 'layout_menu_module') {
			var selected = hkjQuery('#'+value).val();
			if(selected == 0) {
				selected = 0;
				hkjQuery(el).addClass('selected');
			} else {
				selected = 1;
				hkjQuery(el).removeClass('selected');
			}
			hkjQuery('#'+value).val( (1 - selected) );
		} else {
			var currentValue = hkjQuery('#'+type).val();
			hkjQuery('#layout_'+currentValue).removeClass('selected');
			hkjQuery(el).addClass('selected');
			hkjQuery('#'+type).val(value);
		}
	}

	hkjQuery(document).ready(function () {
		fillShopAddress(1000);
		setTimeout(function(){
			var currentValue = hkjQuery('#layout_type').val();
			hkjQuery('#layout_'+currentValue).addClass('selected');

			hkjQuery.each(['products_menu', 'categories_menu', 'categories_module'], function(index, value) {
				var e = hkjQuery('#'+value);
				if(e.val() == 1) {
					hkjQuery('#layout_' + value).addClass('selected');
				}
			});

		}, 500);
	});
	hkjQuery("#address_state").on("change", function(event) {
		fillShopAddress();
	});
	hkjQuery("#address_country").on("change", function(event) {
		fillShopAddress();
	});
	window.Oby.registerAjax('hikashop.stateupdated', function(params) {
		hkjQuery(params.elem).on("change", function(event) {
			fillShopAddress();
		});
		fillShopAddress();
	});
	function fillShopAddress(wait){
		if(wait === undefined)
			wait = 10;
		setTimeout(function () {
			var country = hkjQuery("#address_country option:selected").text();
			if(hkjQuery("#address_state").length != 0){
				var state = hkjQuery("#address_state option:selected").text()+'\r\n';
			}else{
				var state = '';
			}
			var content = hkjQuery("#shop_address").html();
			if(hkjQuery("#shop_address").html().match('<?php echo JText::_('YOUR_ADDRESS'); ?>') || content == ''){
				hkjQuery("#shop_address").html('<?php echo JText::_('YOUR_ADDRESS'); ?>'+'\r\n\r\n'+state+country);
			}
		}, wait);
	}
</script>
