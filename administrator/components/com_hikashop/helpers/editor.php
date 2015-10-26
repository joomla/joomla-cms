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
class hikashopEditorHelper {
	var $width = '100%';
	var $height = '500';
	var $cols = 100;
	var $rows = 20;
	var $editor = null;
	var $name = '';
	var $content = '';
	var $id = 'jform_articletext';
	static $cpt = 0;
	static $initialized = array();

	function __construct() {
		$this->setEditor();
		$this->options = array('pagebreak');
		$config =& hikashop_config();
		$readmore = $config->get('readmore',0);
		if(!$readmore)
			$this->options[] = 'readmore';
	}

	function setDescription() {
		$this->width = 700;
		$this->height = 200;
		$this->cols = 80;
		$this->rows = 10;
	}

	function setContent($var) {
		$name = $this->myEditor->get('_name');
		$function = 'try{'.$this->myEditor->setContent($this->name, $var).' }catch(err){alert(\'Error using the setContent function of the wysiwyg editor\')}';
		switch($name) {
			case 'jce':
				return ' try{JContentEditor.setContent(\''.$this->name.'\', '.$var.'); }catch(err){try{WFEditor.setContent(\''.$this->name.'\', '.$var.')}catch(err){'.$function.'} }';
			case 'fckeditor':
				return ' try{FCKeditorAPI.GetInstance(\''.$this->name.'\').SetHTML('.$var.'); }catch(err){'.$function.'} ';
			case 'jckeditor':
				return ' try{oEditor.setData('.$var.');}catch(err){(!oEditor) ? CKEDITOR.instances.'.$this->name.'.setData('.$var.') : oEditor.insertHtml = '.$var.'}';
			case 'ckeditor':
				return ' try{CKEDITOR.instances.'.$this->name.'.setData('.$var.'); }catch(err){'.$function.'} ';
			case 'artofeditor':
				return ' try{CKEDITOR.instances.'.$this->name.'.setData('.$var.'); }catch(err){'.$function.'} ';
		}
		return $function;
	}

	function getContent() {
		return $this->myEditor->getContent($this->name);
	}

	function display() {
		if(version_compare(JVERSION,'1.6','<'))
			return $this->myEditor->display($this->name, $this->content, $this->width, $this->height, $this->cols, $this->rows, $this->options);

		$id = $this->id;
		if(self::$cpt >= 1 && $this->id == 'jform_articletext') {
			$id = $this->id . '_' . self::$cpt;
		}
		self::$cpt++;
		return $this->myEditor->display($this->name, $this->content, $this->width, $this->height, $this->cols, $this->rows, $this->options, $id);
	}

	function jsCode() {
		return 'try{ ' .
				$this->myEditor->save($this->name) .
			' }catch(e){ try{ ' .
				$this->myEditor->save($this->id) .
			' } catch(f){}}';
	}

	function jsUnloadCode() {
		$name = $this->myEditor->get('_name');
		switch($name) {
			case 'tinymce':
				return 'try{ var n = ["'.str_replace(array('"','\\'),'', $this->name).'", "'.str_replace(array('"','\\'),'', $this->id).'"]; '.
						' if(document.getElementById(n[0])) { tinymce.remove("#"+n[0]); } '.
						' if(document.getElementById(n[1])) { tinymce.remove("#"+n[1]); } ' .
					' } catch(f){}';
		}
	}

	function displayCode($name, $content) {
		if($this->hasCodeMirror())
			$this->setEditor('codemirror');
		else
			$this->setEditor('none');

		$this->myEditor->setContent($name,$content);

		if(version_compare(JVERSION,'1.6','<'))
			return $this->myEditor->display( $name,  $content ,$this->width, $this->height, $this->cols, $this->rows,false);

		$id = $this->id;
		if(self::$cpt >= 1 && $this->id == 'jform_articletext') {
			$id = $this->id . '_' . self::$cpt;
		}
		self::$cpt++;
		return $this->myEditor->display( $name,  $content ,$this->width, $this->height, $this->cols, $this->rows,false,$id) ;
	}

	function setEditor($editor = '') {
		if(empty($editor)) {
			$config =& hikashop_config();
			$this->editor = $config->get('editor',null);
			if(empty($this->editor))
				$this->editor = null;
		} else {
			$this->editor = $editor;
		}

		if(!HIKASHOP_PHP5)
			$this->myEditor =& JFactory::getEditor($this->editor);
		else
			$this->myEditor = JFactory::getEditor($this->editor);

		if(!HIKASHOP_J30 || empty(self::$initialized[$this->editor]))
			$this->myEditor->initialise();
		self::$initialized[$this->editor] = true;
	}

	function hasCodeMirror() {
		static $has = null;
		if($has !== null)
			return $has;

		if(version_compare(JVERSION,'1.6','<'))
			$query = 'SELECT element FROM '.hikashop_table('plugins',false).' WHERE element=\'codemirror\' AND folder=\'editors\' AND published=1';
		else
			$query = 'SELECT element FROM '.hikashop_table('extensions',false).' WHERE element=\'codemirror\' AND folder=\'editors\' AND enabled=1 AND type=\'plugin\'';

		$db = JFactory::getDBO();
		$db->setQuery($query);
		$editor = $db->loadResult();
		$has = !empty($editor);
		return $has;
	}
}
