<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hkc-xl-12 hikashop_menu_edit_display_settings_table" data-type="<?php echo $this->type; ?>_layout" data-layout="<?php echo $this->type; ?>_table">
	<div class="hkc-xl-4 hikashop_menu_subblock hikashop_menu_edit_display_settings_subdiv">
		<div class="hikashop_menu_subblock_content">
			<div class="hikashop_menu_subblock_title hikashop_menu_edit_display_settings_div_title"><?php echo JText::_('HIKA_ITEMS'); ?></div>
			<dl class="hika_options">
				<dt class="hikashop_option_name">
					<label class="field_rows" for="data___<?php echo $this->type; ?>_limit"><?php echo JText::_( 'FIELD_ROWS' ); ?></label>
				</dt>
				<dd class="hikashop_option_value">
					<div class="listing_item_quantity_selector" data-name="<?php echo $this->name; ?>">
<?php
					$colsNb = @$this->element['columns'];
					$rowsNb = 0;
					if(@$this->element['columns'] != 0)
						$rowsNb = round($this->element['limit'] / $this->element['columns']);
					$i = 0;
					for($j = 0; $j < 12; $j++){
						$class = ' listing_table ';
						if($i < $colsNb && $j < $rowsNb)
							$class .= ' selected';
						echo '<div class="col'.$i.' row'.$j.$class.'"></div>';
						echo '<br/>';
					}
?>
					</div>
					<div class="listing_item_quantity_fields" data-list-type="table">
						<input type="text" name="<?php echo $this->name; ?>[rows]" value="<?php echo $rowsNb; ?>">
					</div>
				</dd>
			</dl>
			<dl class="hika_options" style="display: none;">
				<dt class="hikashop_option_name">
					<label for="data_menu__<?php echo $this->type; ?>_limit"><?php echo JText::_( 'NUMBER_OF_ITEMS' ); ?></label>
				</dt>
				<dd class="hikashop_option_value">
					<?php if(!isset($this->element['limit'])) $this->element['limit'] = '20'; ?>
					<input type="text" id="data_menu__<?php echo $this->type; ?>_limit" name="<?php echo $this->name; ?>[limit]" value="<?php echo $this->element['limit']; ?>">
				</dd>
			</dl>
		</div>
	</div>
</div>
