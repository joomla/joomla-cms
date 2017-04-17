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
if(hikashop_level(2)){
?>
<div class="hkc-xl-4 hikashop_menu_subblock hikashop_menu_edit_product_extra_part1">
	<div class="hikashop_menu_subblock_content">
		<div class="hikashop_menu_subblock_title hikashop_menu_edit_display_settings_div_title"><?php echo JText::_('HIKA_CAROUSEL_SETTINGS'); ?></div>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<?php echo JText::_('ENABLE_CAROUSEL');?>
			</dt>
			<dd class="hikashop_option_value" data-control="carousel">
				<?php echo JHTML::_('hikaselect.booleanlist', $this->name.'[enable_carousel]' , '',@$this->element['enable_carousel']); ?>
			</dd>
		</dl>
		<dl class="hika_options" data-part="carousel" id="<?php echo 'carousel_type_'.$this->type.'"'; ?>>
			<dt class="hikashop_option_name">
				<?php echo JText::_('HIKA_CAROUSEL_EFFECT');?>
			</dt>
			<dd class="hikashop_option_value" data-control="effect">
				<?php echo $this->effectType->display($this->name.'[carousel_effect]',@$this->element['carousel_effect'] , '');?>
			</dd>
		</dl>
		<dl class="hika_options" data-part="effect" id="<?php echo 'slide_direction_'.$this->type.'"'; ?>>
			<dt class="hikashop_option_name">
				<?php echo JText::_('SLIDE_DIRECTION');?>
			</dt>
			<dd class="hikashop_option_value">
				<?php echo $this->directionType->display($this->name.'[slide_direction]',@$this->element['slide_direction']);?>
			</dd>
		</dl>
		<dl class="hika_options" data-part="effect" id="<?php echo 'transition_effect_'.$this->type.'"'; ?>>
			<dt class="hikashop_option_name">
				<?php echo JText::_('TRANSITION_EFFECT');?>
			</dt>
			<dd class="hikashop_option_value">
				<?php echo $this->transition_effectType->display($this->name.'[transition_effect]',@$this->element['transition_effect']);?>
			</dd>
		</dl>
		<dl class="hika_options" data-part="carousel" id="<?php echo 'carousel_effect_duration_'.$this->type.'"'; ?>>
			<dt class="hikashop_option_name">
				<?php echo JText::_('HIKA_EFFECT_DURATION');?>
			</dt>
			<dd class="hikashop_option_value">
				<input size=12 name="<?php echo $this->name; ?>[carousel_effect_duration]" type="text" value="<?php echo @$this->element['carousel_effect_duration'];?>" /> ms
			</dd>
		</dl>
		<dl class="hika_options" data-part="effect" id="<?php echo 'product_by_slide_'.$this->type.'"'; ?>>
			<dt class="hikashop_option_name">
				<?php echo JText::_('PRODUCTS_BY_SLIDE');?>
			</dt>
			<dd class="hikashop_option_value">
				<input size=9 name="<?php echo $this->name; ?>[item_by_slide]" type="text" value="<?php echo @$this->element['item_by_slide'];?>" />
			</dd>
		</dl>
		<dl class="hika_options" data-part="effect" id="<?php echo 'slide_one_by_one_'.$this->type.'"'; ?>>
			<dt class="hikashop_option_name">
				<?php echo JText::_('SLIDE_ONE_BY_ONE');?>
			</dt>
			<dd class="hikashop_option_value">
				<?php echo JHTML::_('hikaselect.booleanlist', $this->name.'[one_by_one]' , '',@$this->element['one_by_one']); ?>
			</dd>
		</dl>
	</div>
</div>
<div class="hkc-xl-4 hikashop_menu_subblock hikashop_menu_edit_product_extra_part2">
	<div class="hikashop_menu_subblock_content">
		<div class="hikashop_menu_subblock_title hikashop_menu_edit_display_settings_div_title"><?php echo JText::_('HIKA_CAROUSEL_ADDITIONAL_SETTINGS'); ?></div>
		<dl class="hika_options" data-part="carousel" id="<?php echo 'auto_slide_'.$this->type.'"'; ?>>
			<dt class="hikashop_option_name">
				<?php echo JText::_('AUTO_SLIDE');?>
			</dt>
			<dd class="hikashop_option_value" data-control="autoslide">
				<?php echo JHTML::_('hikaselect.booleanlist', $this->name.'[auto_slide]' , '',@$this->element['auto_slide']); ?>
			</dd>
		</dl>
		<dl class="hika_options" data-part="autoslide" id="<?php echo 'auto_slide_duration_'.$this->type.'"'; ?>>
			<dt class="hikashop_option_name">
				<?php echo JText::_('HIKA_TRANSITION_DELAY');?>
			</dt>
			<dd class="hikashop_option_value">
				<input size=12 name="<?php echo $this->name; ?>[auto_slide_duration]" type="text" value="<?php echo @$this->element['auto_slide_duration'];?>" /> ms
			</dd>
		</dl>
		<dl class="hika_options" data-part="carousel" id="<?php echo 'slide_pagination_'.$this->type.'"'; ?>>
			<dt class="hikashop_option_name">
				<?php echo JText::_('SLIDE_PAGINATION_TYPE');?>
			</dt>
			<dd class="hikashop_option_value" data-control="pagination">
				<?php echo $this->slide_paginationType->display($this->name.'[pagination_type]',@$this->element['pagination_type'], '');?>
			</dd>
		</dl>
		<dl class="hika_options" data-part="paginationthumbnail" id="<?php echo 'pagination_width_'.$this->type.'"'; ?>>
			<dt class="hikashop_option_name">
				<?php echo JText::_('PAGINATION_IMAGE_WIDTH');?>
			</dt>
			<dd class="hikashop_option_value">
				<input size=12 name="<?php echo $this->name; ?>[pagination_image_width]" type="text" value="<?php echo @$this->element['pagination_image_width'];?>" /> px
			</dd>
		</dl>
		<dl class="hika_options" data-part="paginationthumbnail" id="<?php echo 'pagination_height_'.$this->type.'"'; ?>>
			<dt class="hikashop_option_name">
				<?php echo JText::_('PAGINATION_IMAGE_HEIGHT');?>
			</dt>
			<dd class="hikashop_option_value">
				<input size=12 name="<?php echo $this->name; ?>[pagination_image_height]" type="text" value="<?php echo @$this->element['pagination_image_height'];?>" /> px
			</dd>
		</dl>
		<dl class="hika_options" data-part="pagination" id="<?php echo 'pagination_position_'.$this->type.'"'; ?>>
			<dt class="hikashop_option_name">
				<?php echo JText::_('HIKA_PAGINATION');?>
			</dt>
			<dd class="hikashop_option_value">
				<?php echo $this->positionType->display($this->name.'[pagination_position]',@$this->element['pagination_position']);?>
			</dd>
		</dl>
		<dl class="hika_options" data-part="carousel" id="<?php echo 'display_button_'.$this->type.'"'; ?>>
			<dt class="hikashop_option_name">
				<?php echo JText::_('HIKA_SWITCH_BUTTONS');?>
			</dt>
			<dd class="hikashop_option_value">
				<?php echo JHTML::_('hikaselect.booleanlist', $this->name.'[display_button]' , '',@$this->element['display_button']); ?>
			</dd>
		</dl>
	</div>
</div>
<?php
	}else{ ?>
<div class="hkc-xl-4 hikashop_menu_subblock hikashop_menu_edit_product_extra_part1">
	<div class="hikashop_menu_subblock_content">
		<div class="hikashop_menu_subblock_title hikashop_menu_edit_display_settings_div_title"><?php echo JText::_('HIKA_CAROUSEL_SETTINGS'); ?></div>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<?php echo JText::_('ENABLE_CAROUSEL');?>
			</dt>
			<dd class="hikashop_option_value">
				<?php echo hikashop_getUpgradeLink('business'); ?>
			</dd>
		</dl>
	</div>
</div>
<?php } ?>
