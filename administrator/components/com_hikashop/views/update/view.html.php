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

class UpdateViewUpdate extends hikashopView{
	var $ctrl= 'update';
	var $nameListing = 'UPDATE';
	var $nameForm = 'UPDATE';
	var $icon = 'update';

	function display($tpl = null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}

	function wizard(){
		$db = JFactory::getDBO();

		hikashop_setTitle(JText::_('HIKA_WIZARD'),'config','update&task=wizard');

		if (!HIKASHOP_PHP5) {
			$bar =& JToolBar::getInstance('toolbar');
		}else{
			$bar = JToolBar::getInstance('toolbar');
		}
		$bar->appendButton( 'Link', 'hikashop', JText::_('HIKA_SKIP'), hikashop_completeLink('update&task=post_install&fromversion=&update=0') );

		$languagesCodes = array();
		$languagesNames = array();
		if(HIKASHOP_J25){
			$db->setQuery('SELECT * FROM '.hikashop_table('languages',false).' WHERE `published` = 1');
			$languages = $db->loadObjectList();
			foreach($languages as $language){
				$path = JLanguage::getLanguagePath(JPATH_ROOT).DS.$language->lang_code.DS.$language->lang_code.'.com_hikashop.ini';
				if(!JFile::exists($path)){
					$languagesCodes[] = $language->lang_code;
					$languagesNames[] = $language->title;
				}
			}
		}
		if(!empty($languagesCodes))
			$languageCodes = implode('_',$languagesCodes);
		if(!empty($languagesNames))
			$languagesNames = implode(', ',$languagesNames);
		$this->assignRef('languageCodes', $languageCodes);
		$this->assignRef('languageNames', $languagesNames);

		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass', $fieldsClass);

		static $Itemid;
		if(isset($Itemid) && !empty($Itemid))
			$url_itemid = '&item_id='.$Itemid;
		else
			$url_itemid = '';
		$address = new stdClass();
		$extraFields=array();
		$extraFields['address'] = $fieldsClass->getFields('frontcomp',$address,'address','update&task=state'.$url_itemid);
		$this->assignRef('extraFields',$extraFields);
		$this->assignRef('address',$address);

		$db->setQuery('SELECT * FROM '.hikashop_table('currency').' WHERE 1 ORDER BY `currency_code`');
		$currencies = $db->loadObjectList();
		$this->assignRef('currencies', $currencies);

		hikashop_loadJslib('jquery');
		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::_('WELCOME_WIZARD', 'success'));
	}
	function state(){
		$namekey = JRequest::getCmd('namekey','');
		if(!headers_sent()){
			header('Content-Type:text/html; charset=utf-8');
		}
		if(!empty($namekey)){
			$field_namekey = JRequest::getCmd('field_namekey', '');
			if(empty($field_namekey))
				$field_namekey = 'address_state';

			$field_id = JRequest::getCmd('field_id', '');
			if(empty($field_id))
				$field_id = 'address_state';

			$field_type = JRequest::getCmd('field_type', '');
			if(empty($field_type))
				$field_type = 'address';

			$class = hikashop_get('type.country');
			echo $class->displayStateDropDown($namekey, $field_id, $field_namekey, $field_type);
		}
		exit;
	}
}
