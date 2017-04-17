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
$i = $this->params->get('i');
$min_quantity = $this->params->get('min_quantity');
$max_quantity = $this->params->get('max_quantity');
$html = $this->params->get('html');

$qLayout = JRequest::getVar('quantitylayout','show_default');
switch($qLayout){
	case 'show_regrouped':
?>
		<div class="input-append hikashop_product_quantity_div hikashop_product_quantity_input_div_regrouped">
			<input id="hikashop_product_quantity_field_<?php echo $i; ?>" type="text" value="<?php echo JRequest::getInt('quantity',$min_quantity); ?>" class="hikashop_product_quantity_field" name="quantity" onchange="hikashopCheckQuantityChange('hikashop_product_quantity_field_<?php echo $i; ?>',<?php echo $max_quantity; ?>,<?php echo $min_quantity; ?>);" />
			<div class="add-on hikashop_product_quantity_div hikashop_product_quantity_change_div_regrouped">
				<div class="hikashop_product_quantity_change_div_plus_regrouped">
					<a id="hikashop_product_quantity_field_change_plus_<?php echo $i; ?>" class="hikashop_product_quantity_field_change_plus hikashop_product_quantity_field_change" href="#" onclick="return hikashopQuantityChange('hikashop_product_quantity_field_<?php echo $i; ?>',1,<?php echo $max_quantity; ?>,<?php echo $min_quantity; ?>);">+</a>
				</div>
				<div class="hikashop_product_quantity_change_div_plus_regrouped">
					<a id="hikashop_product_quantity_field_change_minus_<?php echo $i; ?>" class="hikashop_product_quantity_field_change_minus hikashop_product_quantity_field_change" href="#" onclick="return hikashopQuantityChange('hikashop_product_quantity_field_<?php echo $i; ?>',0,<?php echo $max_quantity; ?>,<?php echo $min_quantity; ?>);">&ndash;</a>
				</div>
			</div>
		</div>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_regrouped">
			<?php echo $html; ?>
		</div>
<?php
		break;

	case 'show_select':
		if($min_quantity == 0)
			$min_quantity = 1;
		if($max_quantity == 0)
			$max_quantity = (int)$min_quantity * 15;
?>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_input_div_select">
			<select id="hikashop_product_quantity_select_<?php echo $i; ?>" onchange="var id = this.id.replace('select','field'); document.getElementById(id).value = this.value;">
				<?php
				for($j = $min_quantity; $j <= $max_quantity; $j += $min_quantity){
					echo '<option value="'.$j.'">'.$j.'</option>';
				}
				?>
			</select>
			<input id="hikashop_product_quantity_field_<?php echo $i; ?>" type="hidden" value="<?php echo JRequest::getInt('quantity',$min_quantity); ?>" class="hikashop_product_quantity_field" name="quantity" onchange="hikashopCheckQuantityChange('hikashop_product_quantity_field_<?php echo $i; ?>',<?php echo $max_quantity; ?>,<?php echo $min_quantity; ?>);" />
		</div>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_select">
			<?php echo $html; ?>
		</div>
<?php
		break;

	case 'show_simple':
?>
		<input id="hikashop_product_quantity_field_<?php echo $i; ?>" type="hidden" value="<?php echo JRequest::getInt('quantity',$min_quantity); ?>" class="hikashop_product_quantity_field" name="quantity" />
		<div class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_simple">
			<?php echo $html; ?>
		</div>
<?php
		break;

	case 'show_leftright':
?>
		<div class="input-prepend input-append hikashop_product_quantity_div hikashop_product_quantity_change_div_leftright">
			<span class="add-on">
				<a id="hikashop_product_quantity_field_change_plus_<?php echo $i; ?>" class="hikashop_product_quantity_field_change_plus hikashop_product_quantity_field_change" href="#" onclick="return hikashopQuantityChange('hikashop_product_quantity_field_<?php echo $i; ?>',1,<?php echo $max_quantity; ?>,<?php echo $min_quantity; ?>);">+</a>
			</span>
			<input id="hikashop_product_quantity_field_<?php echo $i; ?>" type="text" value="<?php echo JRequest::getInt('quantity',$min_quantity); ?>" class="hikashop_product_quantity_field" name="quantity" onchange="hikashopCheckQuantityChange('hikashop_product_quantity_field_<?php echo $i; ?>',<?php echo $max_quantity; ?>,<?php echo $min_quantity; ?>);" />
			<span class="add-on">
				<a id="hikashop_product_quantity_field_change_minus_<?php echo $i; ?>" class="hikashop_product_quantity_field_change_minus hikashop_product_quantity_field_change" href="#" onclick="return hikashopQuantityChange('hikashop_product_quantity_field_<?php echo $i; ?>',0,<?php echo $max_quantity; ?>,<?php echo $min_quantity; ?>);">&ndash;</a>
			</span>
		</div>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_leftright">
			<?php echo $html; ?>
		</div>
<?php
		break;

	case 'show_simplified':
?>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_input_div_simplified">
			<input id="hikashop_product_quantity_field_<?php echo $i; ?>" type="text" value="<?php echo JRequest::getInt('quantity',$min_quantity); ?>" class="hikashop_product_quantity_field" name="quantity" onchange="hikashopCheckQuantityChange('hikashop_product_quantity_field_<?php echo $i; ?>',<?php echo $max_quantity; ?>,<?php echo $min_quantity; ?>);" />
		</div>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_simplified">
			<?php echo $html; ?>
		</div>
<?php
		break;

	case 'show_default_div':
?>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_input_div_default">
			<input id="hikashop_product_quantity_field_<?php echo $i; ?>" type="text" value="<?php echo JRequest::getInt('quantity',$min_quantity); ?>" class="hikashop_product_quantity_field" name="quantity" onchange="hikashopCheckQuantityChange('hikashop_product_quantity_field_<?php echo $i; ?>',<?php echo $max_quantity; ?>,<?php echo $min_quantity; ?>);" />
		</div>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_change_div_default">
			<div class="hikashop_product_quantity_change_div_plus_default">
				<a id="hikashop_product_quantity_field_change_plus_<?php echo $i; ?>" class="hikashop_product_quantity_field_change_plus hikashop_product_quantity_field_change" href="#" onclick="return hikashopQuantityChange('hikashop_product_quantity_field_<?php echo $i; ?>',1,<?php echo $max_quantity; ?>,<?php echo $min_quantity; ?>);">+</a>
			</div>
			<div class="hikashop_product_quantity_change_div_minus_default">
				<a id="hikashop_product_quantity_field_change_minus_<?php echo $i; ?>" class="hikashop_product_quantity_field_change_minus hikashop_product_quantity_field_change" href="#" onclick="return hikashopQuantityChange('hikashop_product_quantity_field_<?php echo $i; ?>',0,<?php echo $max_quantity; ?>,<?php echo $min_quantity; ?>);">&ndash;</a>
			</div>
		</div>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_default">
			<?php echo $html; ?>
		</div>
<?php
		break;

	case 'show_default':
?>
		<table>
			<tr>
				<td rowspan="2">
					<input id="hikashop_product_quantity_field_<?php echo $i; ?>" type="text" value="<?php echo JRequest::getInt('quantity',$min_quantity); ?>" class="hikashop_product_quantity_field" name="quantity" onchange="hikashopCheckQuantityChange('hikashop_product_quantity_field_<?php echo $i; ?>',<?php echo $max_quantity; ?>,<?php echo $min_quantity; ?>);" />
				</td>
				<td>
					<a id="hikashop_product_quantity_field_change_plus_<?php echo $i; ?>" class="hikashop_product_quantity_field_change_plus hikashop_product_quantity_field_change" href="#" onclick="return hikashopQuantityChange('hikashop_product_quantity_field_<?php echo $i; ?>',1,<?php echo $max_quantity; ?>,<?php echo $min_quantity; ?>);">+</a>
				</td>
				<td rowspan="2">
					<?php echo $html; ?>
				</td>
			</tr>
			<tr>
				<td>
					<a id="hikashop_product_quantity_field_change_minus_<?php echo $i; ?>" class="hikashop_product_quantity_field_change_minus hikashop_product_quantity_field_change" href="#" onclick="return hikashopQuantityChange('hikashop_product_quantity_field_<?php echo $i; ?>',0,<?php echo $max_quantity; ?>,<?php echo $min_quantity; ?>);">&ndash;</a>
				</td>
			</tr>
		</table>
<?php
		break;

	default:
		if(substr($qLayout,0,14) == 'show_quantity_'){
			$doc = JFactory::getDocument();
			$app = JFactory::getApplication();
			$quantityDisplayType = hikashop_get('type.quantitydisplay');
			if($quantityDisplayType->check( $qLayout, $app->getTemplate())){
				$controller = new hikashopBridgeController(array('name'=>'product'));
				$viewType	= $doc->getType();
				if(!HIKASHOP_PHP5) {
					$view = & $controller->getView( '', $viewType, '');
				} else {
					$view = $controller->getView( '', $viewType, '');
				}
				$view->setLayout($qLayout);
				echo $view->loadTemplate();
				break;
			}
		}
?>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_input_div_default">
			<input id="hikashop_product_quantity_field_<?php echo $i; ?>" type="text" value="<?php echo JRequest::getInt('quantity',$min_quantity); ?>" class="hikashop_product_quantity_field" name="quantity" onchange="hikashopCheckQuantityChange('hikashop_product_quantity_field_<?php echo $i; ?>',<?php echo $max_quantity; ?>,<?php echo $min_quantity; ?>);" />
		</div>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_change_div_default">
			<div class="hikashop_product_quantity_change_div_plus_default">
				<a id="hikashop_product_quantity_field_change_plus_<?php echo $i; ?>" class="hikashop_product_quantity_field_change_plus hikashop_product_quantity_field_change" href="#" onclick="return hikashopQuantityChange('hikashop_product_quantity_field_<?php echo $i; ?>',1,<?php echo $max_quantity; ?>,<?php echo $min_quantity; ?>);">+</a>
			</div>
			<div class="hikashop_product_quantity_change_div_minus_default">
				<a id="hikashop_product_quantity_field_change_minus_<?php echo $i; ?>" class="hikashop_product_quantity_field_change_minus hikashop_product_quantity_field_change" href="#" onclick="return hikashopQuantityChange('hikashop_product_quantity_field_<?php echo $i; ?>',0,<?php echo $max_quantity; ?>,<?php echo $min_quantity; ?>);">&ndash;</a>
			</div>
		</div>
		<div class="hikashop_product_quantity_div hikashop_product_quantity_add_to_cart_div hikashop_product_quantity_add_to_cart_div_default">
			<?php echo $html; ?>
		</div>
<?php
		break;
}
