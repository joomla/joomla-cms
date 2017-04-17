<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hkc-xl-4 hikashop_module_subblock hikashop_module_edit_general_part1">
	<div class="hikashop_module_subblock_content">
		<div class="hikashop_module_subblock_title hikashop_module_edit_display_settings_div_title"><?php echo JText::_('HIKA_DATA_DISPLAY'); ?></div>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<?php echo JText::_('TYPE_OF_CONTENT');?>
			</dt>
			<dd class="hikashop_option_value">
				<?php echo $this->contentType->display($this->name.'[content_type]',@$this->element['content_type'],$this->js,true,'_'.$this->id,true); ?>
			</dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_module__<?php echo $this->type; ?>_use_module_name"><?php echo JText::_( 'HIKA_MAIN_CATEGORY' ); ?></label>
			</dt>
			<dd class="hikashop_option_value">
				<?php
				if(@$this->element['selectparentlisting'] == '') $this->element['selectparentlisting'] = '2';
				echo $this->nameboxType->display(
					$this->name.'[selectparentlisting]',
					@$this->element['selectparentlisting'],
					hikashopNameboxType::NAMEBOX_SINGLE,
					'category',
					array(
						'delete' => true,
						'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
					)
				);
				?>
			</dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_module__<?php echo $this->type; ?>_product_order"><?php echo JText::_( 'ORDERING_FIELD' ); ?></label>
			</dt>
			<dd class="hikashop_option_value">
				<?php
				if(!isset($this->element['product_order'])) $this->element['product_order'] = 'inherit';
				echo $this->orderType->display($this->name.'[product_order]',$this->element['product_order'],'product');
				?>
			</dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_module__<?php echo $this->type; ?>_order_dir"><?php echo JText::_( 'ORDERING_DIRECTION' ); ?></label>
			</dt>
			<dd class="hikashop_option_value">
				<?php
				if(!isset($this->element['order_dir'])) $this->element['order_dir'] = 'inherit';
				echo $this->orderdirType->display($this->name.'[order_dir]',$this->element['order_dir']);
				?>
			</dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_module__<?php echo $this->type; ?>_random"><?php echo JText::_( 'RANDOM_ITEMS' ); ?></label>
			</dt>
			<dd class="hikashop_option_value">
				<?php
				if(!isset($this->element['random'])) $this->element['random'] = '-1';
				echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[random]', $this->element['random']);
				?>
			</dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name">
				<label for="data_module__<?php echo $this->type; ?>_filter_type"><?php echo JText::_( 'SUB_ELEMENTS_FILTER' ); ?></label>
			</dt>
			<dd class="hikashop_option_value">
				<?php
				if(!isset($this->element['filter_type'])) $this->element['filter_type'] = '0';
				echo $this->childdisplayType->display($this->name.'[filter_type]',$this->element['filter_type'], true, true, true);
				?>
			</dd>
		</dl>
		 <dl class="hika_options">
			<dt class="hikashop_option_name">
				<?php echo hikashop_tooltip(JText::_('SYNCHRO_WITH_ITEM'), '', '', JText::_('HIKA_SYNCHRONIZE'), '', 0);?>
			</dt>
			<dd class="hikashop_option_value">
				<?php echo JHTML::_('hikaselect.inheritRadiolist', $this->name.'[content_synchronize]', @$this->element['content_synchronize']); ?>
			</dd>
		</dl>
		<dl class="hika_options">
			<dt class="hikashop_option_name" >
				<?php echo JText::_('MENU'); ?>
			</dt>
			<dd class="hikashop_option_value">
				<?php echo JHTML::_('hikaselect.genericlist', $this->hikashop_menu, $this->name.'[itemid]' , 'size="1"', 'value', 'text', @$this->element['itemid']); ?>
			</dd>
		</dl>
	</div>
</div>
