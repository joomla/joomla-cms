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
class fieldOpt_datepicker_options {
	public function show($value) {
		if(!empty($value)) {
			if(is_string($value))
				$value = unserialize($value);
		} else {
			$value = array();
		}

		$excludeFormats = array(
			JHTML::_('select.option', 'mdY', 'm/d/Y'),
			JHTML::_('select.option', 'dmY', 'd/m/Y')
		);

		$months = array();
		for($i = 1; $i <= 12; $i++) {
			$months[] = JHTML::_('select.option', $i, $i);
		}

		$ret = '
<table class="table admintable table-stripped">
	<tr>
		<td class="key">'.JText::_('DATE_PICKER_OPT_DEFAULT_TODAY').'</td>
		<td>'.
			JHTML::_('hikaselect.booleanlist', "field_options[datepicker_options][today]" , '', @$value['today']).
		'</td>
	</tr>
	<tr>
		<td class="key">'.JText::_('DATE_PICKER_OPT_INLINE_DISPLAY').'</td>
		<td>'.
			JHTML::_('hikaselect.booleanlist', "field_options[datepicker_options][inline]" , '', @$value['inline']).
		'</td>
	</tr>
	<tr>
		<td class="key">'.JText::_('DATE_PICKER_OPT_MONDAY_FIRST').'</td>
		<td>'.
			JHTML::_('hikaselect.booleanlist', "field_options[datepicker_options][monday_first]" , '', @$value['monday_first']).
		'</td>
	</tr>
	<tr>
		<td class="key">'.JText::_('DATE_PICKER_OPT_CHANGE_MONTH').'</td>
		<td>'.
			JHTML::_('hikaselect.booleanlist', "field_options[datepicker_options][change_month]" , '', @$value['change_month']).
		'</td>
	</tr>
	<tr>
		<td class="key">'.JText::_('DATE_PICKER_OPT_CHANGE_YEAR').'</td>
		<td>'.
			JHTML::_('hikaselect.booleanlist', "field_options[datepicker_options][change_year]" , '', @$value['change_year']).'<br/>'.
		JText::_('HIKA_START').'<input type="text" name="field_options[datepicker_options][year_range_start]" value="'.@$value['year_range_start'].'" /><br/>'.JText::_('HIKASHOP_CHECKOUT_END').'<input type="text" name="field_options[datepicker_options][year_range_end]" value="'.@$value['year_range_end'].'" /></td>
	</tr>
	<tr>
		<td class="key">'.JText::_('DATE_PICKER_OPT_SHOW_BTN_PANEL').'</td>
		<td>'.
			JHTML::_('hikaselect.booleanlist', "field_options[datepicker_options][show_btn_panel]" , '', @$value['show_btn_panel']).
		'</td>
	</tr>
	<tr>
		<td class="key">'.JText::_('DATE_PICKER_OPT_SHOW_MONTHS').'</td>
		<td>'.
			JHTML::_('select.genericlist', $months, "field_options[datepicker_options][show_months]", '', 'value', 'text', @$value['show_months']).
		'</td>
	</tr>
	<tr>
		<td class="key">'.JText::_('DATE_PICKER_OPT_OTHER_MONTH').'</td>
		<td>'.
			JHTML::_('hikaselect.booleanlist', "field_options[datepicker_options][other_month]" , '', @$value['other_month']).
		'</td>
	</tr>
	<tr>
		<td class="key">'.JText::_('DATE_PICKER_OPT_FORBIDDEN_DAYS').'</td>
		<td>
			<label><input type="checkbox" name="field_options[datepicker_options][forbidden_1]" value="1"'.(empty($value['forbidden_1'])?'':' checked="checked"').'/> '.JText::_('MONDAY').'</label><br/>
			<label><input type="checkbox" name="field_options[datepicker_options][forbidden_2]" value="1"'.(empty($value['forbidden_2'])?'':' checked="checked"').'/> '.JText::_('TUESDAY').'</label><br/>
			<label><input type="checkbox" name="field_options[datepicker_options][forbidden_3]" value="1"'.(empty($value['forbidden_3'])?'':' checked="checked"').'/> '.JText::_('WEDNESDAY').'</label><br/>
			<label><input type="checkbox" name="field_options[datepicker_options][forbidden_4]" value="1"'.(empty($value['forbidden_4'])?'':' checked="checked"').'/> '.JText::_('THURSDAY').'</label><br/>
			<label><input type="checkbox" name="field_options[datepicker_options][forbidden_5]" value="1"'.(empty($value['forbidden_5'])?'':' checked="checked"').'/> '.JText::_('FRIDAY').'</label><br/>
			<label><input type="checkbox" name="field_options[datepicker_options][forbidden_6]" value="1"'.(empty($value['forbidden_6'])?'':' checked="checked"').'/> '.JText::_('SATURDAY').'</label><br/>
			<label><input type="checkbox" name="field_options[datepicker_options][forbidden_0]" value="1"'.(empty($value['forbidden_0'])?'':' checked="checked"').'/> '.JText::_('SUNDAY').'</label>
		</td>
	</tr>
	<tr>
		<td class="key">'.JText::_('DATE_PICKER_OPT_EXCLUDES').'</td>
		<td>
			'.JHTML::_('select.genericlist', $excludeFormats, "field_options[datepicker_options][exclude_days_format]", '', 'value', 'text', @$value['exclude_days_format']).'<br/>
			<textarea name="field_options__datepicker_options__excludes">'.@$value['excludes'].'</textarea>
		</td>
	</tr>
	<tr>
		<td class="key">'.JText::_('DATE_PICKER_WAITING_DAYS').'</td>
		<td>
			<input type="text" name="field_options[datepicker_options][waiting]" value="'.@$value['waiting'].'" />
		</td>
	</tr>
	<tr>
		<td class="key">'.JText::_('DATE_PICKER_HOUR_EXTRA_DAY').'</td>
		<td>
			<input type="text" name="field_options[datepicker_options][hour_extra_day]" value="'.@$value['hour_extra_day'].'" />
		</td>
	</tr>
</table>';
		return $ret;
	}

	public function save(&$options) {
		if(!empty($options['datepicker_options']))
			$options['datepicker_options']['excludes'] = JRequest::getVar('field_options__datepicker_options__excludes','','','string',JREQUEST_ALLOWRAW);
	}
}

class hikashopDatepickerfield {

	public $prefix = null;
	public $suffix = null;
	public $excludeValue = null;
	public $report = null;
	public $parent = null;
	protected $params = null;

	public function __construct(&$obj) {
		$this->prefix = $obj->prefix;
		$this->suffix = $obj->suffix;
		$this->excludeValue =& $obj->excludeValue;
		$this->report = @$obj->report;
		$this->parent =& $obj;

		$timeoffset = 0;
		$jconfig = JFactory::getConfig();
		if(!HIKASHOP_J30){
			$timeoffset = $jconfig->getValue('config.offset');
		} else {
			$timeoffset = $jconfig->get('offset');
		}
		if(HIKASHOP_J16){
			$dateC = JFactory::getDate(time(),$timeoffset);
			$timeoffset = $dateC->getOffsetFromGMT(true);
		}
		$this->timeoffset = $timeoffset *60*60 + date('Z');
	}

	private function init() {
		static $init = null;
		if($init !== null)
			return $init;

		hikashop_loadJsLib('jquery');
		$doc = JFactory::getDocument();
		$lang = JFactory::getLanguage();
		$tag = $lang->getTag();
		$conversionTable = array(
			'af-ZA' => 'af',
			'ar-AR' => 'ar',
			'eu-ES' => 'eu',
			'bg-BG' => 'bg',
			'ca-ES' => 'ca',
			'zh-CN' => 'zh-CN',
			'zh-TW' => 'zh-TW',
			'bs-BA' => 'bs',
			'cs-CZ' => 'cs',
			'da-DK' => 'da',
			'nl-NL' => 'nl',
			'en-AU' => 'en-AU',
			'en-NZ' => 'en-NZ',
			'fi-FI' => 'fi',
			'fr-FR' => 'fr',
			'fr-CA' => 'fr',
			'fr-CH' => 'fr-CH',
			'gl-ES' => 'gl',
			'de-DE' => 'de',
			'el-GR' => 'el',
			'he-IL' => 'he',
			'hu-HU' => 'hu',
			'it-IT' => 'it',
			'ja-JP' => 'ja',
			'ko-KR' => 'ko',
			'lv-LV' => 'lv',
			'lt-LT' => 'lt',
			'mk-MK' => 'mk',
			'nb-NO' => 'no',
			'fa-IR' => 'fa',
			'pl-PL' => 'pl',
			'pt-BR' => 'pt-BR',
			'pt-PT' => 'pt',
			'ro-RO' => 'ro',
			'ru-RU' => 'ru',
			'sr-RS' => 'sr',
			'es-ES' => 'es',
			'sk-SK' => 'sk',
			'sl-SL' => 'sl',
			'sv-SE' => 'sv',
			'th-TH' => 'th',
			'tr-TR' => 'tr',
			'uk-UA' => 'uk',
			'vi-VN' => 'vi',
		);
		if(isset($conversionTable[$tag])){
			$tag = $conversionTable[$tag];
		}else{
			$tag = 'en-GB';
		}
		$doc->addScript('//jquery-ui.googlecode.com/svn/tags/latest/ui/minified/i18n/jquery-ui-i18n.min.js');

		$js = '
hkjQuery(function() {
	var excludeWDays = function(date, w, d, dt, rg) {
		var day = date.getDay(),
			md = (date.getMonth()+1) * 100 + date.getDate(),
			fd = date.getFullYear() * 10000 + md,
			r = true;
		if(w) { for(var i = w.length - 1; r && i >= 0; i--) { r = (day != w[i]); }}
		if(d) { for(var i = d.length - 1; r && i >= 0; i--) { r = (md != d[i]); }}
		if(dt) { for(var i = dt.length - 1; r && i >= 0; i--) { r = (fd != dt[i]); }}
		if(rg) { for(var i = rg.length - 1; r && i >= 0; i--) {
			if(rg[i][2] == 2)
				r = (md < rg[i][0] || md > rg[i][1]);
			else
				r = (fd < rg[i][0] || fd > rg[i][1]);
		}}
		return [r, \'\'];
	};
	hkjQuery(".hikashop_datepicker").each(function(){
		var t = hkjQuery(this), options = {};
		if(t.attr("data-options")) {
			options = Oby.evalJSON( t.attr("data-options") );
		}
		if(options["exclude"] || options["excludeDays"] || options["excludeDates"] || options["excludeRanges"]) {
			options["beforeShowDay"] = function(date){ return excludeWDays(date, options["exclude"], options["excludeDays"], options["excludeDates"], options["excludeRanges"]); };
		}
		options["altField"] = "#"+t.attr("data-picker");
		options["altFormat"] = "yy/mm/dd";
		hkjQuery.datepicker.setDefaults(hkjQuery.datepicker.regional[\''.$tag.'\']);
		t.datepicker(options);

		t.change(function(){
			var e = hkjQuery(this), format = e.datepicker("option", "dateFormat");
			if(e.val() == "") {
				hkjQuery("#"+e.attr("data-picker")).val("");
			} else {
				try{
					hkjQuery.datepicker.parseDate(format, e.val());
				}catch(ex) {
					hkjQuery("#"+e.attr("data-picker")).val("");
				}
			}
		});
	});
});';

		$doc->addScriptDeclaration($js);
		$doc->addStyleSheet('//code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css');

		$init = true;
		return $init;
	}

	public function getFieldName($field) {
		return '<label for="' . $this->prefix . $field->field_namekey . $this->suffix.'">' . $this->trans($field->field_realname) . '</label>';
	}

	public function trans($name) {
		$val = preg_replace('#[^a-z0-9]#i', '_', strtoupper($name));
		$trans = JText::_($val);
		if($val == $trans)
			return $name;
		return $trans;
	}

	public function show(&$field, $value) {
		if(!$this->init())
			return '';

		if($value === '')
			return '';

		if(!empty($field->field_value) && !is_array($field->field_value)) {
			$field->field_value = $this->parent->explodeValues($field->field_value);
		}
		if(isset($field->field_value[$value])) {
			$value = $field->field_value[$value]->value;
		}

		if(is_string($field->field_options)) {
			$field->field_options = unserialize($field->field_options);
		}
		$format = @$field->field_options['format'];
		if(strpos($format, '%') !== false) {
			$format = str_replace(array('%A','%d','%B','%m','%Y','%y','%H','%M','%S','%a'),array('l','d','F','m','Y','y','H','i','s','D'),$format);
		}

		$ret = $value;
		$date = $this->getDate($value);
		$timestamp = $this->getTimestamp($date);

		$joomlaFormat = str_replace(array('l','d','F','m','Y','y','H','i','s','D'),array('%A','%d','%B','%m','%Y','%y','%H','%M','%S','%a'),$format);
		if(!empty($joomlaFormat))
			$ret = hikashop_getDate($timestamp, $joomlaFormat);
		else
			$ret = hikashop_getDate($timestamp);

		return $ret;
	}

	public function display($field, $value, $map, $inside, $options = '', $test = false, $allFields = null, $allValues = null) {
		if(!$this->init())
			return '';

		$app = JFactory::getApplication();
		$ret = '';
		$timestamp = null;
		$id = $this->prefix . @$field->field_namekey . $this->suffix;

		$default_value = $field->field_default;
		if(!empty($value) && !empty($default_value) && !empty($datepicker_options['today']) && ((int)$value == (int)$default_value)) {
			$value = null;
		}

		if(!empty($value)) {
			$value = $this->getDate($value);
			$timestamp = $this->getTimestamp($value);
		}

		$datepicker_options = @$field->field_options['datepicker_options'];
		if(!empty($datepicker_options)) {
			if(is_string($datepicker_options))
				$datepicker_options = unserialize($datepicker_options);
		} else {
			$datepicker_options = array();
		}

		$dateOptions = array();

		if(!empty($datepicker_options['hour_extra_day'])) {
			$parts = explode(':',$datepicker_options['hour_extra_day']);
			$hour = (int)array_shift($parts);
			$minute = 0;
			if(count($parts))
				$minute = (int)array_shift($parts);
			$date_today = getdate();
			$current_hour = (int)$date_today['hours'];
			$current_minute = (int)$date_today['minutes'];
			if($current_hour > $hour || ($current_hour == $hour && $current_minute >= $minute))
				$datepicker_options['waiting'] = (int)$datepicker_options['waiting'] + 1;
		}

		if(@$field->field_options['allow'] == 'future') {
			if(!empty($datepicker_options['waiting']))
				$dateOptions[] = 'minDate:'.(int)$datepicker_options['waiting'];
			else
				$dateOptions[] = 'minDate:0';
		} else if(@$field->field_options['allow'] == 'past') {
			if(!empty($datepicker_options['waiting']))
				$dateOptions[] = 'maxDate:'.(0 - (int)($datepicker_options['waiting']));
			else
				$dateOptions[] = 'maxDate:0';
		}

		$format = @$field->field_options['format'];
		if(strpos($format,'%') !== false) {
			$format = str_replace(array('%A','%d','%B','%m','%Y','%y','%H','%M','%S','%a'),array('l','d','F','m','Y','y','H','i','s','D'),$format);
		}
		if(!empty($format)) {
			$dateOptions[] = 'dateFormat:\''.str_replace(
					array('j','d', 'z','D','l', 'n','m', 'M','F', 'y','Y'),
					array('d','dd','o','D','DD','m','mm','M','MM','y','yy'),
					$format
				).'\'';
		}

		$joomlaFormat = str_replace(array('l','d','F','m','Y','y','H','i','s','D'),array('%A','%d','%B','%m','%Y','%y','%H','%M','%S','%a'),$format);
		if(!empty($value) && !empty($value['y'])) {
			if(!empty($joomlaFormat))
				$txtValue = hikashop_getDate($timestamp, $joomlaFormat);
			else
				$txtValue = hikashop_getDate($timestamp);
		} else {
			$timestamp = 0;
			$txtValue = '';
		}

		if(!empty($datepicker_options['today']) && empty($timestamp)) {
			$timestamp = time();

			if(empty($field->field_options['allow']) || $field->field_options['allow'] == 'future') {
				if(!empty($datepicker_options['waiting']))
					$timestamp += 86400 * (int)$datepicker_options['waiting'];

				do {
					$inc = $this->checkFuturRules($timestamp, $datepicker_options);
					if(is_int($inc) && (int)$inc > 0)
						$timestamp += 86400 * (int)$inc;
				} while(is_int($inc) && $inc > 0);
			}

			if(!empty($joomlaFormat))
				$txtValue = hikashop_getDate($timestamp, $joomlaFormat);
			else
				$txtValue = hikashop_getDate($timestamp);
		}
		if(empty($value) && !empty($timestamp))
			$value = $this->getDate($timestamp);

		if(!empty($txtValue))
			$dateOptions[] = 'defaultDate:\''.$txtValue.'\'';

		if(!empty($datepicker_options['monday_first']))
			$dateOptions[] = 'firstDay:1';

		if(!empty($datepicker_options['change_month']))
			$dateOptions[] = 'changeMonth:true';
		if(!empty($datepicker_options['change_year'])){
			$dateOptions[] = 'changeYear:true';
			if(!empty($datepicker_options['year_range_start']) || !empty($datepicker_options['year_range_end'])){
				if(empty($datepicker_options['year_range_start'])){
					$datepicker_options['year_range_start']='c-10';
				}
				if(empty($datepicker_options['year_range_end'])){
					$datepicker_options['year_range_end']='c+10';
				}
				$dateOptions[] = 'yearRange: \''.$datepicker_options['year_range_start'].':'.$datepicker_options['year_range_end'].'\'';
			}
		}
		if(!empty($datepicker_options['show_btn_panel']))
			$dateOptions[] = 'showButtonPanel:true';
		if(!empty($datepicker_options['show_months']) && (int)$datepicker_options['show_months'] > 1 && (int)$datepicker_options['show_months'] <= 12)
			$dateOptions[] = 'numberOfMonths:'.(int)$datepicker_options['show_months'];

		if(!empty($datepicker_options['other_month'])) {
			$dateOptions[] = 'showOtherMonths:true';
			$dateOptions[] = 'selectOtherMonths:true';
		}

		$spe_day_format = 'm/d/Y';
		if(!empty($datepicker_options['exclude_days_format'])) {
			$spe_day_format = $datepicker_options['exclude_days_format'];
		}

		$excludeDays = array();
		for($i = 0; $i <= 6; $i++) { if(!empty($datepicker_options['forbidden_'.$i])) { $excludeDays[] = $i; } }
		if(!empty($excludeDays)) $dateOptions[] = 'exclude:['.implode(',',$excludeDays).']';

		$excludeDays = explode('|', str_replace(array("\r\n","\n","\r",' '),array('|','|','|','|'), @$datepicker_options['excludes']));
		$date_today = getdate();
		$disabled_dates = array();
		$disabled_days = array();
		$disabled_ranges = array();
		foreach($excludeDays as $day){
			if(strpos($day, '-') === false) {
				$day = explode('/', trim($day));
				$ret = $this->convertDay($day, $date_today, $spe_day_format);
				if(!empty($ret)) {
					if(count($day) == 3)
						$disabled_dates[] = $ret;
					if(count($day) == 2)
						$disabled_days[] = $ret;
				}
			} else {
				$days = explode('-', trim($day));
				$day1 = explode('/', trim($days[0]));
				$ret1 = $this->convertDay($day1, $date_today, $spe_day_format);
				$day2 = explode('/', trim($days[1]));
				$ret2 = $this->convertDay($day2, $date_today, $spe_day_format);

				if(!empty($ret1) && !empty($ret2) && count($day1) == count($day2)) {
					$disabled_ranges[] = '['.$ret1.','.$ret2.','.count($day1).']';
				}
			}
		}
		if(!empty($disabled_days))
			$dateOptions[] = 'excludeDays:['.implode(',',$disabled_days).']';
		if(!empty($disabled_dates))
			$dateOptions[] = 'excludeDates:['.implode(',',$disabled_dates).']';
		if(!empty($disabled_ranges))
			$dateOptions[] = 'excludeRanges:['.implode(',',$disabled_ranges).']';

		if(!empty($dateOptions)) {
			$dateOptions = '{' . implode(',', $dateOptions) . '}';
		} else {
			$dateOptions = '';
		}

		if(empty($datepicker_options['inline'])) {
			if(($app->isAdmin() && HIKASHOP_BACK_RESPONSIVE) || (!$app->isAdmin() && HIKASHOP_RESPONSIVE)) {
				$ret = '<div class="input-append">'.
					'<input type="text" id="'.$id.'_input" data-picker="'.$id.'" data-options="'.$dateOptions.'" class="hikashop_datepicker" value="'.$txtValue.'"/>'.
					'<button class="btn" onclick="document.getElementById(\''.$id.'_input\').focus();return false;"><i class="icon-calendar"></i></button>'.
					'</div>';
			} else {
				$ret = '<input type="text" data-picker="'.$id.'" data-options="'.$dateOptions.'" class="hikashop_datepicker" value="'.$txtValue.'"/>';
			}
		} else {
			$ret = '<div data-picker="'.$id.'" data-options="'.$dateOptions.'" class="hikashop_datepicker" value="'.$txtValue.'"></div>';
		}

		$ret .= '<input type="hidden" value="'.$this->serializeDate($value).'" name="'.$map.'" id="'.$id.'"/>';

		return $ret;
	}

	private function convertDay($day, $today, $spe_day_format) {
		if(count($day) == 3) {
			$y = (int)$day[2];
			if($y < 100) $y += 2000;
			if($spe_day_format == 'dmY') {
				$d = (int)$day[0]; $m = (int)$day[1];
			} else {
				$d = (int)$day[1]; $m = (int)$day[0];
			}

			if( empty($today) || $y >= $today['year'] || $m >= $today['mon'] || $d >= $today['mday'] ) {
				return $y.(($m<10)?'0':'').$m.(($d<10)?'0':'').$d;
			}
			return '';
		}

		if(count($day) == 2) {
			if($spe_day_format == 'dmY') {
				$d = (int)$day[0]; $m = (int)$day[1];
			} else {
				$d = (int)$day[1]; $m = (int)$day[0];
			}
			return $m.(($d<10)?'0':'').$d;
		}
		return '';
	}

	private function getDate($value, $format = 'm/d/Y') {
		$ret = array(
			'y' => 0, 'm' => 0, 'd' => 0,
			'h' => 0, 'i' => 0, 's' => 0
		);

		if(empty($value))
			return $ret;

		$dateValue = $value;
		if(preg_match('#^([0-9]+)$#', $value)) {
			if(strlen($value) == 14) {
				$dateValue = substr($value,0,4) . '/' . substr($value,4,2) . '/' . substr($value,6,2);
			} else {
				$dateValue = hikashop_getDate($value, '%Y/%m/%d');
			}
			list($y,$m,$d) = explode('/', $dateValue, 3);
		} else {
			$y = 0; $m = 0; $d = 0;
			$timestamp = strtotime(str_replace('/', '-', $value));
			if($timestamp !== false && $timestamp !== -1 && $timestamp > 0) {
				$dateValue = date('Y/m/d', $timestamp);
				list($y,$m,$d) = explode('/', $dateValue, 3);
			} else {
				$v = explode('/', $value, 3);
				if(count($v) == 3)
					list($y,$m,$d) = $v;
			}
		}

		$ret['y'] = (int)$y;
		$ret['m'] = (int)$m;
		$ret['d'] = (int)$d;

		return $ret;
	}

	private function getTimestamp($value) {
		if(is_array($value)) {
			$value = $value['y'] . '/' . $value['m'] . '/' . $value['d'];
			if(empty($this->params)) {
				$plugin = JPluginHelper::getPlugin('hikashop', 'datepickerfield');
				if(version_compare(JVERSION,'2.5','<')) {
					jimport('joomla.html.parameter');
					$this->params = new JParameter(@$plugin->params);
				} else {
					$this->params = new JRegistry(@$plugin->params);
				}
			}
			if($this->params->get('time_shift', 0))
				$value .= ' 12:00:00';
		}
		$ret = hikashop_getTime($value);

		return $ret;
	}

	private function serializeDate($value) {
		if(empty($value))
			return '';

		$ret = $value['y'];

		$keys = array('m' => 12, 'd' => 31, 'h' => 24, 'i' => 60, 's' => 60);
		foreach($keys as $k => $v) {
			$t = (int)$value[$k];
			if($t > $v) $t = $v;
			if($t < 0) $t = 0;
			if($t < 10) $ret .= '0';
			$ret .= $t;
		}

		return $ret;
	}

	public function JSCheck(&$oneField, &$requiredFields, &$validMessages, &$values) {
		if(empty($oneField->field_required))
			return;

		$requiredFields[] = $oneField->field_namekey;
		if(!empty($oneField->field_options['errormessage'])) {
			$validMessages[] = addslashes($this->trans($oneField->field_options['errormessage']));
		}else{
			$validMessages[] = addslashes(JText::sprintf('FIELD_VALID', $this->trans($oneField->field_realname)));
		}
	}

	protected function checkFuturRules($timestamp, &$datepicker_options) {
		$phpDate = getdate($timestamp);
		$wday = $phpDate['wday'];
		$wday_cursor = $wday;

		$ret = 0;

		for($i = $wday; $i <= 6; $i++) {
			if(!empty($datepicker_options['forbidden_'.$i]) && $i == $wday_cursor) {
				$ret++;
				$wday_cursor = (($wday_cursor+1) % 7);
			}
		}
		for($i = 0; $i < $wday; $i++) {
			if(!empty($datepicker_options['forbidden_'.$i]) && $i == $wday_cursor) {
				$ret++;
				$wday_cursor = (($wday_cursor+1) % 7);
			}
		}

		if($ret == 7)
			return 0;

		if(empty($datepicker_options['excludes']))
			return $ret;

		$spe_day_format = 'm/d/Y';
		if(!empty($datepicker_options['exclude_days_format'])) {
			$spe_day_format = $datepicker_options['exclude_days_format'];
		}

		$dateValue = $this->getDate($timestamp + (86400*$ret));
		$fullDayCode = $dateValue['y'] * 10000 + $dateValue['m'] * 100 + $dateValue['d'];
		$dayCode = $dateValue['m'] * 100 + $dateValue['d'];

		$excludeDays = explode('|', str_replace(array("\r\n","\n","\r",' '),array('|','|','|','|'), $datepicker_options['excludes']));
		foreach($excludeDays as $day) {
			if(strpos($day, '-') === false) {
				$day = explode('/', trim($day));
				$exc_day = (int)$this->convertDay($day, null, $spe_day_format);
				if(empty($exc_day))
					continue;

				if((count($day) == 3 && $fullDayCode == $exc_day) || (count($day) == 2 && $dayCode == $exc_day)){
					$ret++;
					$dateValue = $this->getDate($timestamp + (86400*$ret));
					$fullDayCode = $dateValue['y'] * 10000 + $dateValue['m'] * 100 + $dateValue['d'];
					$dayCode = $dateValue['m'] * 100 + $dateValue['d'];
				}
			} else {
				$days = explode('-', trim($day));
				$day1 = explode('/', trim($days[0]));
				$ret1 = (int)$this->convertDay($day1, null, $spe_day_format);
				$day2 = explode('/', trim($days[1]));
				$ret2 = (int)$this->convertDay($day2, null, $spe_day_format);

				if(!empty($ret1) && !empty($ret2) && count($day1) == count($day2) && $ret1 < $ret2) {
					$final_date = 0;
					if(count($day1) == 3 && $fullDayCode >= $ret1 && $fullDayCode <= $ret2) {
						$final_date = floor($ret2 / 10000) . '/' . floor(($ret2 % 10000) / 100) . '/' . ($ret2 % 100);
					} else if(count($day1) == 2 && $dayCode >= $ret1 && $dayCode <= $ret2) {
						$final_date = $dateValue['y'] . '/' . floor($ret2 / 100) . '/' . ($ret2%100);
					}
					if(!empty($final_date)) {
						$t1 = hikashop_getTime($final_date);
						$t2 = hikashop_getTime($dateValue['y'].'/'.$dateValue['m'].'/'.$dateValue['d']);

						$ret += 1 + (int)(($t1 - $t2) / 86400);
						$dateValue = $this->getDate($timestamp + (86400*$ret));
						$fullDayCode = $dateValue['y'] * 10000 + $dateValue['m'] * 100 + $dateValue['d'];
						$dayCode = $dateValue['m'] * 100 + $dateValue['d'];
					}
				}
			}
		}

		return $ret;
	}

	public function check(&$field, &$value, $oldvalue) {
		$app = JFactory::getApplication();

		$fieldClass = hikashop_get('class.field');
		$fullField = $fieldClass->get($field->field_id);

		$datepicker_options = @$fullField->field_options['datepicker_options'];
		if(!empty($datepicker_options)) {
			if(is_string($datepicker_options))
				$datepicker_options = unserialize($datepicker_options);
		} else {
			$datepicker_options = array();
		}

		if(!empty($value)) {
			$dateValue = $this->getDate($value);
			$value = $this->serializeDate($dateValue);
		} else {
			$value = '';
			$dateValue = array();
		}

		if(!empty($value) && !empty($dateValue['y'])) {
			$fullDayCode = $dateValue['y'] * 10000 + $dateValue['m'] * 100 + $dateValue['d'];
			$dayCode = $dateValue['m'] * 100 + $dateValue['d'];

			$today = getdate();
			$today_year = (int)$today['year'];
			$today_month = (int)$today['mon'];
			$today_day = (int)$today['mday'];

			$fullTodayCode = $today_year * 10000 + $today_month * 100 + $today_day;
			$todayCode = $today_month * 100 + $today_day;

			if(!empty($datepicker_options['hour_extra_day'])) {
				$hour = (int)$datepicker_options['hour_extra_day'];
				$date_today = getdate();
				$current_hour = (int)$date_today['hours'];
				if($current_hour >= $hour)
					$datepicker_options['waiting'] = (int)$datepicker_options['waiting'] + 1;
			}

			if(!empty($fullField->field_options['allow'])) {

				if($fullField->field_options['allow'] == 'future') {
					$fullTodayCode += (int)@$datepicker_options['waiting'];
					$todayCode += (int)@$datepicker_options['waiting'];
				}
				if($fullField->field_options['allow'] == 'past') {
					$fullTodayCode -= (int)@$datepicker_options['waiting'];
					$todayCode -= (int)@$datepicker_options['waiting'];
				}

				if($fullField->field_options['allow'] == 'future' && $fullDayCode < $fullTodayCode) {
					$app->enqueueMessage(JText::sprintf('PLEASE_FILL_THE_FIELD', $this->trans($field->field_realname)));
					return false;
				}

				if($fullField->field_options['allow'] == 'past' && $fullDayCode > $fullTodayCode) {
					$app->enqueueMessage(JText::sprintf('PLEASE_FILL_THE_FIELD', $this->trans($field->field_realname)));
					return false;
				}
			}

			$timestamp = $this->getTimestamp($dateValue);
			$phpDate = getdate($timestamp);

			if($phpDate['hours'] != 0) {
				$timestamp -= $this->timeoffset;
				$phpDate = getdate($timestamp);
			}

			$wday = $phpDate['wday'];

			$excludeDays = array();
			for($i = 0; $i <= 6; $i++) {
				if(!empty($datepicker_options['forbidden_'.$i]) && $i == $wday) {
					$app->enqueueMessage(JText::sprintf('DATE_PICKER_INCORRECT_DATE_FOR', $this->trans($field->field_realname)));
					return false;
				}
			}

			if(!empty($datepicker_options['excludes'])) {
				$spe_day_format = 'm/d/Y';
				if(!empty($datepicker_options['exclude_days_format'])) {
					$spe_day_format = $datepicker_options['exclude_days_format'];
				}

				$excludeDays = explode('|', str_replace(array("\r\n","\n","\r",' '),array('|','|','|','|'), $datepicker_options['excludes']));
				foreach($excludeDays as $day){
					if(strpos($day, '-') === false) {
						$day = explode('/', trim($day));
						$ret = (int)$this->convertDay($day, null, $spe_day_format);
						if(!empty($ret)) {
							if(count($day) == 3 && $fullDayCode == $ret) {
								$app->enqueueMessage(JText::sprintf('DATE_PICKER_INCORRECT_DATE_FOR', $this->trans($field->field_realname)));
								return false;
							}
							if(count($day) == 2 && $dayCode == $ret) {
								$app->enqueueMessage(JText::sprintf('DATE_PICKER_INCORRECT_DATE_FOR', $this->trans($field->field_realname)));
								return false;
							}
						}
					} else {
						$days = explode('-', trim($day));
						$day1 = explode('/', trim($days[0]));
						$ret1 = (int)$this->convertDay($day1, null, $spe_day_format);
						$day2 = explode('/', trim($days[1]));
						$ret2 = (int)$this->convertDay($day2, null, $spe_day_format);

						if(!empty($ret1) && !empty($ret2) && count($day1) == count($day2) && $ret1 < $ret2) {
							if(count($day1) == 3 && $fullDayCode >= $ret1 && $fullDayCode <= $ret2) {
								$app->enqueueMessage(JText::sprintf('DATE_PICKER_INCORRECT_DATE_FOR', $this->trans($field->field_realname)));
								return false;
							} else if(count($day1) == 2 && $dayCode >= $ret1 && $dayCode <= $ret2) {
								$app->enqueueMessage(JText::sprintf('DATE_PICKER_INCORRECT_DATE_FOR', $this->trans($field->field_realname)));
								return false;
							}
						}
					}
				}
			}
		}

		if(!$field->field_required || strlen($value) || strlen($oldvalue))
			return true;

		if($this->report)
			$app->enqueueMessage(JText::sprintf('PLEASE_FILL_THE_FIELD', $this->trans($field->field_realname)));
		return false;
	}
}
