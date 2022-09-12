<?php defined('_JEXEC') or die; 
/**------------------------------------------------------------------------
 * field_cost - Fields for accounting and calculating the cost of goods
 * ------------------------------------------------------------------------
 * author    Sergei Borisovich Korenevskiy
 * Copyright (C) 2010 www./explorer-office.ru. All Rights Reserved.
 * @package  mod_multi_form
 * @license  GPL   GNU General Public License version 2 or later;  
 * Websites: //explorer-office.ru/download/joomla/category/view/1
 * Technical Support:  Forum - //fb.com/groups/multimodule
 * Technical Support:  Forum - //vk.com/multimodule
 */  
 

use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use Joomla\Registry\Registry as JRegistry;
use Joomla\CMS\Form\Field\ListField as JFormFieldList;
use \Joomla\CMS\Form\FormField as JFormField;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\HTML\HTMLHelper as JHtml; 
use Joomla\CMS\Factory as JFactory;
//use Joomla\CMS\Document\Document as JDocument;
//use Joomla\CMS\Form\FormHelper as JFormHelper;
use Joomla\CMS\Helper\ModuleHelper as JModuleHelper;
use Joomla\CMS\Layout\LayoutHelper as JLayoutHelper;
use Joomla\CMS\Layout\FileLayout as JLayoutFile;
use \Joomla\CMS\Version as JVersion;
use Joomla\CMS\Form\Form as JForm;
use Joomla\CMS\Language\Language as JLanguage;
//use Joomla\CMS\Layout\BaseLayout as JLayoutBase;
 

 

class JFormFieldTranslate extends JFormField  {//JFormField  //JFormFieldList   \Joomla\CMS\Form\FormField  Joomla\CMS\Form\Field; 
////JFormFieldTranslate TranslateField PerevodField  JFormFieldPerevod
	
	public function __construct($form = null) {
		parent::__construct($form);
		
		$this->file = '';
		
		$this->path = dirname(__DIR__.'/'); //dirname  basename
		
		$option1 = basename(dirname(dirname(dirname(__DIR__)))); //dirname  basename
		$option2 = basename(dirname(dirname(__DIR__))); //dirname  basename
		$option3 = basename(dirname(__DIR__)); //dirname  basename
		
		if($option2 == 'components')
			$this->file = $option3;
		if($option2 == 'modules')
			$this->file = $option3;
		if($option1 == 'plugins')
			$this->file = 'plg_'.$option2.'_'.$option3;
		
//		if($option1 == 'plugins')
//			$this->file = 'plg_'.$option2.'_'.$option3;
		
	}
	
	public function setup(\SimpleXMLElement $element, $value, $group = null) {
		
		$this->element = $element;
		
		if($element['path']){
			$this->path = (string)$element['path'];
				
			$option1 = basename(dirname(dirname(dirname($this->path)))); //dirname  basename
			$option2 = basename(dirname(dirname($this->path))); //dirname  basename
			$option3 = basename(dirname($this->path)); //dirname  basename
		
			if($option2 == 'components')
				$this->file = $option3;
			if($option2 == 'modules')
				$this->file = $option3;
			if($option1 == 'plugins')
				$this->file = 'plg_'.$option2.'_'.$option3;
		}
		
		if($element['file'])
			$this->file =  (string)$element['file'];
		
		if($element['option'])
			$this->file =  (string)$element['option'];
		
		if(str_ends_with($this->file, '.ini'))
			$this->file = substr($this->file, 0, -4);
		
		$this->path = rtrim($this->path,'/');
		
		if(str_ends_with($this->path, '/language'))
			$this->path = substr($this->path, 0, -9);
		
		
		$lang = JFactory::getApplication()->getLanguage();

		$lang->load($this->file, $this->path);
		$lang->load($this->file, $this->path.'/language');
		$lang->load($this->file, JPATH_SITE);
		$lang->load($this->file, JPATH_ADMINISTRATOR);
		
//		$paths		 = $lang->getPaths();
		$pathsOption = $lang->getPaths($this->file);
		
//		foreach ($pathsOption as $i => $f){
//			echo "<label>$i    - - -  ".( file_exists ($i)?' Yes ': ' No ')."</label><br>";
//		}
 
		
//		$lng = new JLanguage;
//		$lng->load($file, $this->path);
//		
//		\Joomla\CMS\Language\LanguageHelper::parseLanguageFiles($path);
		
//echo "<b>".print_r($this->file,true)." --- ".print_r($this->path,true)."</b>";
//echo "<pre>".print_r($pathsOption,true)."</pre>";
//echo "<pre>".print_r($paths,true)."</pre>";
//echo "<pre>".print_r($lang,true)."</pre>";
//		parent::setup($element, $value, $group);
		return true;
	}
	
	public $path = '';
	
	public $file = '';
	
	public function getInput() {
		return '';// (string)$this->path;
	}
	public function getLabel() {
		return '';// (string)$this->file;
	}
	public function getTitle() {  
		return '';
	}
	public function getId($fieldId, $fieldName) { 
		return '';
	}
}