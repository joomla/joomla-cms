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
if(isset($this->element->main)){
	if($this->element->product_weight==0 && isset($this->element->main->product_weight)){
		$this->element->product_weight = $this->element->main->product_weight;
	}
	if($this->element->product_width==0 && isset($this->element->main->product_width)){
		$this->element->product_width = $this->element->main->product_width;
	}
	if($this->element->product_height==0 && isset($this->element->main->product_height)){
		$this->element->product_height = $this->element->main->product_height;
	}
	if($this->element->product_length==0 && isset($this->element->main->product_length)){
		$this->element->product_length = $this->element->main->product_length;
	}
}
if ($this->config->get('weight_display', 0)) {
	if(isset($this->element->product_weight) && bccomp($this->element->product_weight,0,3)){ ?>
		<span id="hikashop_product_weight_main" class="hikashop_product_weight_main">
			<?php echo JText::_('PRODUCT_WEIGHT').': '.rtrim(rtrim($this->element->product_weight,'0'),',.').' '.JText::_($this->element->product_weight_unit); ?><br />
		</span>
	<?php
	}
}

if ($this->config->get('dimensions_display', 0) && bccomp($this->element->product_width, 0, 3)) {
?>
	<span id="hikashop_product_width_main" class="hikashop_product_width_main">
		<?php echo JText::_('PRODUCT_WIDTH').': '.rtrim(rtrim($this->element->product_width,'0'),',.').' '.JText::_($this->element->product_dimension_unit); ?><br />
	</span>
<?php
}
if ($this->config->get('dimensions_display', 0) && bccomp($this->element->product_length, 0, 3)) {
?>
	<span id="hikashop_product_length_main" class="hikashop_product_length_main">
		<?php echo JText::_('PRODUCT_LENGTH').': '.rtrim(rtrim($this->element->product_length,'0'),',.').' '.JText::_($this->element->product_dimension_unit); ?><br />
	</span>
<?php
}
if ($this->config->get('dimensions_display', 0) && bccomp($this->element->product_height, 0, 3)) {
?>
	<span id="hikashop_product_height_main" class="hikashop_product_height_main">
		<?php echo JText::_('PRODUCT_HEIGHT').': '.rtrim(rtrim($this->element->product_height,'0'),',.').' '.JText::_($this->element->product_dimension_unit); ?><br />
	</span>
<?php
}
if($this->config->get('manufacturer_display', 0) && !empty($this->element->product_manufacturer_id)){
	$class = hikashop_get('class.category');
	$manufacturer = $class->get($this->element->product_manufacturer_id);
	$menuClass = hikashop_get('class.menus');
	$Itemid = $menuClass->loadAMenuItemId('manufacturer','listing');
	if(empty($Itemid)){
		$Itemid = $menuClass->loadAMenuItemId('','');
	}
	$categoryClass = hikashop_get('class.category');
	$categoryClass->addAlias($manufacturer);
	echo JText::_('MANUFACTURER').': '.'<a href="'.hikashop_contentLink('category&task=listing&cid='.$manufacturer->category_id.'&name='.$manufacturer->alias.'&Itemid='.$Itemid,$manufacturer).'">'.$manufacturer->category_name.'</a>';
}
