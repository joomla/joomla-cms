<?php
/**
 * @version		$Id: default.php 01 2013-07-29 11:37:09Z maverick $
 * @package		CoreJoomla.cjlib
 * @subpackage	Components
 * @copyright	Copyright (C) 2009 - 2011 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */
defined('_JEXEC') or die;
$languages = array(
	'af-ZA'=>'Afrikaans', 'sq-ZL'=>'Albanian', 'ar-AA'=>'Arabic Unitag', 'hy-AM'=>'Armenian', 'az-AZ'=>'Azeri', 'eu_ES'=>'Basque',
	'bn-BD'=>'Bengali (Bangladesh)', 'be-BY'=>'Belarusian', 'bs-BA'=>'Bosnian', 'bg-BG'=>'Bulgarian', 'ca-ES'=>'Catalan',
	'ckb-IQ'=>'Central Kurdish', 'zh-CN'=>'Chinese Simplified', 'zh-TW'=>'Chinese Traditional', 'hr-HR'=>'Croatian', 'cs-CZ'=>'Czech',
	'da-DK'=>'Danish', 'nl-NL'=>'Dutch', 'en-AU'=>'English (Australia)', 'en-US'=>'English (USA)', 'eo-XX'=>'Esperanto', 'et-EE'=>'Estonian',
	'fi-FI'=>'Finnish', 'nl-BE'=>'Flemish', 'fr-FR'=>'French', 'gl-ES'=>'Galician', 'ka-GE'=>'Georgian', 'de-DE'=>'German', 'el-GR'=>'Greek',
	'he-IL'=>'Hebrew', 'hi-IN'=>'Hindi', 'hu-HU'=>'Hungarian', 'id-ID'=>'Indonesian', 'it-IT'=>'Italian', 'ja-JP'=>'Japanese', 'km-KH'=>'Khmer',
	'ko-KR'=>'Korean', 'lo-LA'=>'Laotian', 'lv-LV'=>'Latvian', 'lt-LT'=>'Lithuanian', 'mk-MK'=>'Macedonian', 'ml-IN'=>'Malayalam',
	'mn-MN'=>'Mongolian', 'nb-NO'=>'Norwegian (Bokmål)', 'nn-NO'=>'Norwegian (Nynorsk)', 'fa-IR'=>'Persian', 'pl-PL'=>'Polish',
	'pt-BR'=>'Portuguese (Brazil)', 'pt-PT'=>'Portuguese (Portugal)', 'ro-RO'=>'Romanian', 'ru-RU'=>'Russian', 'gd-GB'=>'Scottish Gaelic',
	'sr-RS'=>'Serbian (Cyrillic)', 'sr-YU'=>'Serbian (Latin)', 'sk-SK'=>'Slovak', 'es-ES'=>'Spanish', 'sw-KE'=>'Swahili', 'sv-SE'=>'Swedish',
	'sy-IQ'=>'Syriac (East)', 'ta-IN'=>'Tamil (India)', 'te-IN'=>'Telugu (India)', 'th-TH'=>'Thai', 'tr-TR'=>'Turkish', 'uk-UA'=>'Ukrainian',
	'ur-PK'=>'Urdu', 'ug-CN'=>'Uyghur', 'vi-VN'=>'Vietnamese', 'cy-GB'=>'Welsh');
?>
<div id="cj-wrapper">
	<form action="<?php echo JRoute::_('index.php?option=com_cjlib&task=countries');?>" method="post" name="adminForm" id="adminForm">
	
		<div class="clearfix form-inline margin-bottom-10">
			<div class="pull-right">
				
				<span class="badge badge-important tooltip-hover" title="<?php echo JText::_('COM_CJLIB_ADD_LANGUAGE_HELP');?>">?</span>
				
				<select name="filter_language" size="1" onchange="document.adminForm.submit();">
					<option value=""><?php echo JText::_('COM_CJLIB_FILTER_LANGUAGE');?></option>
					<?php foreach ($languages as $code=>$language):?>
					<option value="<?php echo $code;?>"<?php echo $code == $this->state->get('filter.language') ? ' selected="selected"' : '';?>>
						<?php echo $this->escape($language);?>
					</option>
					<?php endforeach;?>
				</select>
				
				<?php if(strlen($this->state->get('filter.language')) > 1 && count($this->items) == 0):?>
				<button type="button" class="btn btn-danger" onclick="document.adminForm.task.value='add_language';document.adminForm.submit();">
					<?php echo JText::_('COM_CJLIB_ADD_LANGUAGE');?>
				</button>
				<?php endif;?>
			</div>
			
			<input type="text" name="filter_search" id="filter_search"
				value="<?php echo $this->state->get('filter.search');?>" placeholder="<?php echo JText::_('COM_CJLIB_SEARCH')?>"/>
			<input type="submit" value="<?php echo JText::_('COM_CJLIB_SEARCH');?>" class="btn btn-primary">
			<input type="button" value="<?php echo JText::_('COM_CJLIB_RESET');?>" class="btn" 
				onclick="document.adminForm.filter_search.value=''; document.adminForm.submit();">
		</div>

		<table class="adminlist table table-bordered table-striped">
			<thead><?php echo $this->loadTemplate('head');?></thead>
			<tfoot><?php echo $this->loadTemplate('foot');?></tfoot>
			<tbody><?php echo $this->loadTemplate('body');?></tbody>
		</table>
		<div style="display: none;">
			<input type="hidden" name="task" id="task" value="countries" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering'); ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction'); ?>" />
			<input type="hidden" name="cjlib_page_id" id="cjlib_page_id" value="countries">
			<img id="progress-confirm" alt="..." src="components/com_cjlib/assets/images/ui-anim_basic_16x16.gif"/>
			<span id="url-save-country-name"><?php echo JRoute::_('index.php?option=com_cjlib&task=save_country_name');?></span>
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>