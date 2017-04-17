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
class hikashopTranslationHelper {
	var $languages = array();
	var $falang = false;

	function hikashopTranslationHelper(){
		$this->database = JFactory::getDBO();
		$app = JFactory::getApplication();
		if(version_compare(JVERSION,'1.6','<')){
			jimport('joomla.filesystem.folder');
			if(JFolder::exists(HIKASHOP_ROOT.'components/com_joomfish/images/flags/')){
				$this->flagPath = 'components/com_joomfish/images/flags/';
			}else{
				$this->flagPath = 'media/com_joomfish/default/flags/';
			}
		}else{
			$this->flagPath = 'media/mod_languages/images/';
		}
		if($app->isAdmin()){
			$this->flagPath = '../'.$this->flagPath;
		} else {
			$this->flagPath = rtrim(JURI::base(true),'/') . '/' . $this->flagPath;
		}
	}

	function isMulti($inConfig=false,$level=true){
		static $multi=array();
		static $falang = false;
		$this->falang =& $falang;
		$key = (int)$inConfig.'_'.(int)$level;
		if(!isset($multi[$key])){
			$multi[$key] = false;
			$config =& hikashop_config();
			if((hikashop_level(1) || !$level) && ($config->get('multi_language_edit',1) || $inConfig)){
				$oldQuery = $this->database->getQuery(false);
				$query = 'SHOW TABLES LIKE '.$this->database->Quote($this->database->getPrefix().substr(hikashop_table('falang_content',false),3));
				$this->database->setQuery($query);
				$table = $this->database->loadResult();
				if(!empty($table)){
					$falang = true;
					$multi[$key] = true;
				}else{
					$query='SHOW TABLES LIKE '.$this->database->Quote($this->database->getPrefix().substr(hikashop_table('jf_content',false),3));
					$this->database->setQuery($query);
					$table = $this->database->loadResult();
					if(!empty($table)) $multi[$key] = true;
				}
				$this->database->setQuery($oldQuery);
			}
		}
		return $multi[$key];
	}

	function getFlag($id=0){
		$this->loadLanguages();
		if(isset($this->languages[$id])){
			return '<span style="background: url('.$this->flagPath.$this->languages[$id]->shortcode.'.gif) no-repeat;padding-left:20px">'.$this->languages[$id]->code.'</span>';
		}
		return $this->languages[$id]->code;
	}

	function loadLanguages($active = true){
		if(empty($this->languages)){
			if(version_compare(JVERSION,'1.6','<')){
				$query = 'SELECT * FROM '.hikashop_table('languages',false);
				$this->database->setQuery($query);
				$languages = $this->database->loadObjectList();
				if(!empty($languages)){
					foreach($languages as $key => $language){
						if(!$active ||(isset($language->active)&&$language->active)||(isset($language->published)&&$language->published)){
							if(!isset($language->id)){
								$language->id=$language->lang_id;
								$language->code=$language->lang_code;
								$language->shortcode=$language->image;
								$language->active=$language->published;
							}
							$this->languages[$language->id]=$language;
						}
					}
				}
			}else{
				$query = 'SELECT lang_id as id, lang_code as code, image as shortcode, published as active FROM '.hikashop_table('languages',false).($active?' WHERE published=1':'');
				$this->database->setQuery($query);
				$this->languages = $this->database->loadObjectList('id');
			}
		}
		foreach($this->languages as $key => $language){
			if(empty($language->shortcode)){
				$this->languages[$key]->shortcode = substr($language->code,0,2);
			}
		}
		return $this->languages;
	}
	function loadLanguage($id){
		if(version_compare(JVERSION,'1.6','<')){
			$this->languages = $this->loadLanguages(false);
			if(isset($this->languages[$id])){
				return $this->languages[$id];
			}
		}

		$query = 'SELECT lang_id as id, lang_code as code, image as shortcode, published as active FROM '.hikashop_table('languages',false).' WHERE lang_id='.(int)$id;
		$this->database->setQuery($query);
		return $this->database->loadObject();
	}

	function getId($code){
		$this->loadLanguages();
		if(empty($this->languages) || !is_array($this->languages)) return 0;
		foreach($this->languages as $lg){
			if($lg->code==$code) return $lg->id;
		}
		return 0;
	}

	function load($table,$id,&$element,$language_id=0){
		$where="";
		if(empty($language_id)){
			$this->loadLanguages();
			$languages =& $this->languages;
		}else{
			$where=' AND language_id='.(int)$language_id;
			$languages=array((int)$language_id=>$this->loadLanguage($language_id));
		}
		$trans_table = 'jf_content';
		if($this->falang){
			$trans_table = 'falang_content';
		}
		$query = 'SELECT * FROM '.hikashop_table($trans_table,false).' WHERE reference_id='.(int)$id.' AND reference_table='.$this->database->Quote($table).$where;
		$this->database->setQuery($query);
		$data = $this->database->loadObjectList();

		$element->translations=array();

		if(!empty($data)){
			foreach($data as $entry){
				$field = $entry->reference_field;
				$lg = (int)$entry->language_id;
				if(!isset($element->translations[$lg])){
					$obj = new stdClass();
					$obj->$field = $entry;
					$element->translations[$lg] = $obj;
				}else{
					$element->translations[$lg]->$field=$entry;
				}
			}

		}
		if(!empty($languages)){
			foreach($languages as $lg){
				$lgid = (int)$lg->id;
				if(!isset($element->translations[$lgid])){
					$element->translations[$lgid] = array();
				}
			}
		}
		ksort($element->translations);
	}

	function getTranslations(&$element) {
		$transArray = JRequest::getVar('translation',array(),'','array',JREQUEST_ALLOWRAW);
		foreach($transArray as $field => $trans){
			foreach($trans as $lg => $value){
				if(!empty($value)){
					$obj = new stdClass();
					$obj->reference_field = $field;
					$obj->language_id=(int)$lg;
					$obj->value = $value;
					if(!isset($element->translations)){
						$element->translations = array();
					}
					if(!isset($element->translations[(int)$lg])){
						$element->translations[(int)$lg] = new stdClass();
					}
					$element->translations[(int)$lg]->$field = $obj;
				}
			}
		}
		foreach($_POST as $name => $value) {
			if(preg_match('#^translation_([a-z_]+)_([0-9]+)$#i', $name, $match)) {
				$html_element = JRequest::getVar($name, '', '', 'string', JREQUEST_ALLOWRAW);
				if(!empty($html_element)) {
					$obj = new stdClass();
					$type = $match[1];
					$obj->reference_field = $type;
					$obj->language_id = $match[2];
					$obj->value = $html_element;
					$element->translations[$match[2]]->$type = $obj;
				}
			}
		}
	}

	function handleTranslations($table, $id, &$element, $table_prefix = 'hikashop_', $data = null) {
		if(!empty($table_prefix))
			$table = $table_prefix . $table;
		else
			$table = 'hikashop_' . $table;

		if(empty($data) || $data === null)
			$transArray = JRequest::getVar('translation', array(), '', 'array', JREQUEST_ALLOWRAW);
		else
			$transArray = $data;

		$arrayToSearch = array();
		$conditions = array();
		foreach($transArray as $field => $trans) {
			foreach($trans as $lg => $value) {
				if(empty($value))
					continue;

				$lg = (int)$lg;
				$field = hikashop_secureField($field);
				$arrayToSearch[] = array(
					'value' => $value,
					'language_id' => $lg,
					'reference_field' => $field
				);
				$conditions[] = ' language_id = '.(int)$lg.' AND reference_field = '.$this->database->Quote($field).' AND reference_table = '.$this->database->Quote($table).' AND reference_id='.(int)$id;
			}
		}

		if(empty($data) || $data === null) {
			foreach($_POST as $name => $value){
				if(!preg_match('#^translation_([a-z_]+)_([0-9]+)$#i', $name, $match))
					continue;

				$html_element = JRequest::getVar($name, '', '', 'string', JREQUEST_ALLOWRAW);
				if(empty($html_element))
					continue;

				$lg = (int)$match[2];
				$field = hikashop_secureField($match[1]);
				$value = $html_element;
				$arrayToSearch[] = array(
					'value' => $value,
					'language_id' => $lg,
					'reference_field' => $field
				);
				$conditions[] = ' language_id = '.(int)$lg.' AND reference_field = '.$this->database->Quote($field).' AND reference_table = '.$this->database->Quote($table).' AND reference_id='.(int)$id;
			}
		}

		if(empty($arrayToSearch))
			return;

		$this->isMulti();
		$trans_table = 'jf_content';
		if($this->falang)
			$trans_table = 'falang_content';

		$query = 'SELECT * FROM '.hikashop_table($trans_table,false).' WHERE ('.implode(') OR (',$conditions).');';
		$this->database->setQuery($query);
		$entries = $this->database->loadObjectList('id');

		$user = JFactory::getUser();
		$userId = $user->get( 'id' );
		$toInsert = array();
		foreach($arrayToSearch as $item) {
			$already = false;
			if(!empty($entries)) {
				foreach($entries as $entry_id => $entry){
					if($item['language_id'] == $entry->language_id && $item['reference_field'] == $entry->reference_field) {
						$query = 'UPDATE '.hikashop_table($trans_table, false) .
							' SET value='.$this->database->Quote($item['value']).', modified_by=' . (int)$userId.', modified=NOW()'.
							' WHERE id = ' . (int)$entry_id . ';';
						$this->database->setQuery($query);
						$this->database->query();
						$already = true;
						break;
					}
				}
			}
			if(!$already) {
				$toInsert[] = $item;
			}
		}

		if(empty($toInsert))
			return;

		$conf =& hikashop_config();
		$default_translation_publish = (int)$conf->get('default_translation_publish', 1);
		$rows = array();
		foreach($toInsert as $item) {
			$field = $item['reference_field'];
			$rows[] = (int)$id.','.(int)$item['language_id'].','.$this->database->Quote($table).','.$this->database->Quote($item['value']).','.$this->database->Quote($field).','.$this->database->Quote(md5($element->$field)).','.(int)$default_translation_publish.','.(int)$userId.',\'\',NOW()';
		}
		$query = 'INSERT IGNORE INTO '.hikashop_table($trans_table, false).' (reference_id,language_id,reference_table,value,reference_field,original_value,published,modified_by,original_text,modified) VALUES ('.implode('),(',$rows).');';
		$this->database->setQuery($query);
		$this->database->query();
	}

	function deleteTranslations($table,$ids){
		if($this->isMulti()){
			if(!is_array($ids))$ids = array($ids);
			$trans_table = 'jf_content';
			if($this->falang){
				$trans_table = 'falang_content';
			}
			$query = 'DELETE FROM '.hikashop_table($trans_table,false).' WHERE reference_table = '.$this->database->Quote('hikashop_'.$table).' AND reference_id IN ('.implode(',',$ids).')';
			$this->database->setQuery($query);
			$this->database->query();
		}
	}

	function getStatusTrans(){
		$config = JFactory::getConfig();
		if(HIKASHOP_J30){
			$locale = $config->get('language');
		} else {
			$locale = $config->getValue('config.language');
		}
		$user = JFactory::getUser();
		$current_locale = $user->getParam('language');
		if(empty($current_locale)){
			$current_locale=$locale;
		}
		$database = JFactory::getDBO();

		$query = 'SELECT a.category_name,a.category_id FROM '.hikashop_table('category'). ' AS a WHERE a.category_type=\'status\'';
		$database->setQuery($query);
		if(class_exists('JFDatabase')){
			$statuses = $database->loadObjectList('category_id',false);
		}else{
			$statuses = $database->loadObjectList('category_id');
		}
		if($this->isMulti(true, false)){
			$lgid = $this->getId($current_locale);
			$trans_table = 'jf_content';
			if($this->falang){
				$trans_table = 'falang_content';
			}
			$query = 'SELECT value,reference_id FROM '.hikashop_table($trans_table,false).' WHERE reference_table=\'hikashop_category\' AND reference_field=\'category_name\' AND published=1 AND language_id='.$lgid.' AND reference_id IN('.implode(',',array_keys($statuses)).')';
			$database->setQuery($query);
			$trans = $database->loadObjectList('reference_id');
			foreach($statuses as $k => $stat){
				if(isset($trans[$k])){
					$statuses[$k]->status = $trans[$k]->value;
				}else{
					$val = str_replace(' ','_',strtoupper($statuses[$k]->category_name));
					$new = JText::_($val);
					if($val!=$new){
						$statuses[$k]->status=$new;
					}else{
						$statuses[$k]->status=$statuses[$k]->category_name;
					}
				}
			}
		}else{
			foreach($statuses as $k => $stat){
				$val = str_replace(' ','_',strtoupper($statuses[$k]->category_name));
				$new = JText::_($val);
				if($val!=$new){
					$statuses[$k]->status=$new;
				}else{
					$statuses[$k]->status=$statuses[$k]->category_name;
				}
			}
		}
		$cleaned_statuses = array();
		foreach($statuses as $status){
			$cleaned_statuses[$status->category_name]=$status->status;
		}
		return $cleaned_statuses;
	}


	function getAllLanguages()
	{
		jimport('joomla.filesystem.folder');
		$path = JLanguage::getLanguagePath(JPATH_ROOT);
		$dirs = JFolder::folders( $path );
		$edit_image = HIKASHOP_IMAGES.'icons/icon-16-edit.png';
		$new_image = HIKASHOP_IMAGES.'icons/icon-16-new.png';
		$popup = hikashop_get('helper.popup');

		foreach ($dirs as $dir){
			$xmlFiles = JFolder::files( $path.DS.$dir, '^([-_A-Za-z]*)\.xml$' );
			$xmlFile = array_pop($xmlFiles);
			if($xmlFile=='install.xml') $xmlFile = array_pop($xmlFiles);
			if(empty($xmlFile)) continue;
			$data = JApplicationHelper::parseXMLLangMetaFile($path.DS.$dir.DS.$xmlFile);
			$oneLanguage = new stdClass();
			$oneLanguage->language 	= $dir;
			$oneLanguage->name = $data['name'];
			$languageFiles = JFolder::files( $path.DS.$dir, '^(.*)\.com_hikashop\.ini$' );
			$languageFile = reset($languageFiles);

			if(!empty($languageFile)){
				$oneLanguage->edit = $popup->display(
					'<img id="image'.$oneLanguage->language.'" src="'. $edit_image.'" alt="'.JText::_('EDIT_LANGUAGE_FILE').'"/>',
					'EDIT_LANGUAGE_FILE',
					'index.php?option=com_hikashop&amp;tmpl=component&amp;ctrl=config&amp;task=language&amp;code='.$oneLanguage->language,
					'edit_language_'.$oneLanguage->language,
					760, 480, '', '', 'link'
				);
			}else{
				$oneLanguage->edit = $popup->display(
					'<img id="image'.$oneLanguage->language.'" src="'. $new_image.'" alt="'.JText::_('ADD_LANGUAGE_FILE').'"/>',
					'ADD_LANGUAGE_FILE',
					'index.php?option=com_hikashop&amp;tmpl=component&amp;ctrl=config&amp;task=language&amp;code='.$oneLanguage->language,
					'edit_language_'.$oneLanguage->language,
					760, 480, '', '', 'link'
				);
			}

			$languages[] = $oneLanguage;
		}
		return $languages;
	}

}
