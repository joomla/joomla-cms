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

define('MAX_IMPORT_ID', 13);

class hikashopImportHelper
{
	var $template = null;
	var $totalInserted = 0;
	var $totalTry = 0;
	var $totalValid = 0;
	var $listSeparators = array(';',',','|',"\t");
	var $perBatch = 50;
	var $codes = array();
	var $characteristics = array();
	var $characteristicsConversionTable = array();
	var $characteristicColumns = array();
	var $countVariant = true;
	var $overwrite = false;
	var $products_already_in_db = array();
	var $new_variants_in_db = array();
	var $columnNamesConversionTable = array();
	var $createCategories = false;
	var $header_errors = true;
	var $force_published = true;
	var $tax_category=0;
	var $default_file = '';

	var $importName;
	var $db;
	var $options;
	var $refreshPage;
	var $token = '';
	var $linkstyle;
	var $titlestyle;
	var $bullstyle;
	var $pmarginstyle;
	var $titlefont;
	var $copywarning;
	var $copyImgDir;
	var $copyCatImgDir;
	var $copycDownloadDir;
	var $copyManufDir;


	function __construct()
	{
		$this->db = JFactory::getDBO();
		$this->options = null;
		$this->refreshPage = false;
		$this->linkstyle = ' style="color:#297F93;text-decoration:none;" onmouseover="this.style.color=\'#3AABC6\';this.style.textDecoration=\'underline\';" onmouseout="this.style.color=\'#297F93\';this.style.textDecoration=\'none\';" ';
		$this->titlestyle = ' style="color:#297F93; text-decoration:underline;" ';
		$this->bullstyle = ' style="color:#E69700;" ';
		$this->pmarginstyle = 'style="margin-left:15px;"';
		$this->titlefont = ' style="font-size:1.2em;" ';
		$this->copywarning = ' style="color:grey;font-size:0.8em" ';

		$this->fields = array('product_weight','product_description','product_meta_description','product_tax_id','product_vendor_id','product_manufacturer_id','product_url','product_keywords','product_weight_unit','product_dimension_unit','product_width','product_length','product_height','product_max_per_order','product_min_per_order');
		$fieldClass = hikashop_get('class.field');
		$userFields = $fieldClass->getData('','product');

		if(!empty($userFields)){
			foreach($userFields as $k => $v){
				if($v->field_type!='customtext'){
					$this->fields[]=$k;
				}
			}
		}

		$this->all_fields = array_merge($this->fields,array('product_name','product_published','product_code','product_created','product_modified','product_sale_start','product_sale_end','product_type','product_quantity'));
		$this->db = JFactory::getDBO();

		if(version_compare(JVERSION,'3.0','<')) {
			$columnsProductTable = $this->db->getTableFields(hikashop_table('product'));
			$this->columnsProductTable = array_keys($columnsProductTable[hikashop_table('product')]);
		} else {
			$this->columnsProductTable = array_keys($this->db->getTableColumns(hikashop_table('product')));
		}

		$characteristic = hikashop_get('class.characteristic');
		$characteristic->loadConversionTables($this);

		$this->volumeHelper = hikashop_get('helper.volume');
		$this->weightHelper = hikashop_get('helper.weight');
		$class = hikashop_get('class.category');
		$this->mainProductCategory = 'product';
		$class->getMainElement($this->mainProductCategory);
		$this->mainManufacturerCategory = 'manufacturer';
		$class->getMainElement($this->mainManufacturerCategory);
		$this->mainTaxCategory = 'tax';
		$class->getMainElement($this->mainTaxCategory);
		$this->db->setQuery('SELECT category_id FROM '. hikashop_table('category'). ' WHERE category_type=\'tax\' && category_parent_id='.(int)$this->mainTaxCategory.' ORDER BY category_ordering DESC');
		$this->tax_category = $this->db->loadResult();

		$config =& hikashop_config();
		$uploadFolder = ltrim(JPath::clean(html_entity_decode($config->get('uploadfolder'))),DS);
		$uploadFolder = rtrim($uploadFolder,DS).DS;
		$this->uploadFolder = JPATH_ROOT.DS.$uploadFolder;
		$this->uploadFolder_url = str_replace(DS,'/',$uploadFolder);
		$this->uploadFolder_url = HIKASHOP_LIVE.$this->uploadFolder_url;
		jimport('joomla.filesystem.file');
	}

	function addTemplate($template_product_id){
		if($template_product_id){
			$productClass = hikashop_get('class.product');
			if($productClass->getProducts($template_product_id,'import') && !empty($productClass->products)){
				$key = key($productClass->products);
				$this->template = $productClass->products[$key];

			}
		}
	}

	function importFromFolder($type,$delete,$uploadFolder){
		$config =& hikashop_config();
		if($type=='both'){
			$allowed = explode(',',strtolower($config->get('allowedimages')));
		}else{
			$allowed = explode(',',strtolower($config->get('allowed'.$type)));
		}

		$uploadFolder = rtrim(JPath::clean(html_entity_decode($uploadFolder)),DS.' ').DS;
		if(!preg_match('#^([A-Z]:)?/.*#',$uploadFolder)){
			if(!$uploadFolder[0]=='/' || !is_dir($uploadFolder)){
				$uploadFolder = JPath::clean(HIKASHOP_ROOT.DS.trim($uploadFolder,DS.' ').DS);
			}
		}
		$fileClass = hikashop_get('class.file');
		if($delete && !$fileClass->checkFolder($uploadFolder)){
			return false;
		}
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$app =& JFactory::getApplication();
		$files = JFolder::files($uploadFolder);
		if(!empty($files)){
			$imageHelper = hikashop_get('helper.image');
			if(!empty($this->template->variants)){
				$this->countVariant = false;
			}
			$oldType = $type;
			foreach($files as $file){
				if(in_array($file,array('index.html','.htaccess'))) continue;

				$extension = strtolower(substr($file,strrpos($file,'.')+1));
				if(!in_array($extension,$allowed)){
					$app->enqueueMessage(JText::sprintf('FILE_SKIPPED',$file));
					continue;
				}
				$type = $oldType;
				$newProduct = new stdClass();
				if($type=='both'){
					$newProduct->files = $file;
					$newProduct->images = $file;
				}else{
					$newProduct->$type = $file;
				}
				$this->_checkData($newProduct);
				$this->totalTry++;
				if(!empty($newProduct->product_code)){
					$this->totalValid++;
					$products = array($newProduct);
					if(!empty($this->template->variants)){
						foreach($this->template->variants as $variant){
							$copy = (!HIKASHOP_PHP5) ? $variant : clone($variant);
							unset($copy->product_id);
							$copy->product_parent_id = $newProduct->product_code;
							$copy->product_code = $newProduct->product_code.'_'.$copy->product_code;
							$products[]=$copy;
						}
					}

					$this->_insertProducts($products);
					$folder = 'image';
					if($type!='images'){
						$folder = 'file';
					}
					$uploadPath = $fileClass->getPath($folder);
					if($delete){
						JFile::move($uploadFolder.$file,$uploadPath.$file);
					}else{
						JFile::copy($uploadFolder.$file,$uploadPath.$file);
					}
					if($type=='both'){
						$type='images';
						$uploadPath2 = $fileClass->getPath('image');
						JFile::copy($uploadPath.$file,$uploadPath2.$file);
					}
					if($type!='files'){
						$imageHelper->resizeImage($file);
					}
				}
			}
			$this->_deleteUnecessaryVariants();
		}

		$app->enqueueMessage(JText::sprintf('IMPORT_REPORT',$this->totalTry,$this->totalInserted,$this->totalTry - $this->totalValid,$this->totalValid - $this->totalInserted));
	}

	function copyProduct($product_id){
		$this->addTemplate($product_id);
		$newProduct = new stdClass();
		$newProduct->product_code = $this->template->product_code.'_copy'.rand();
		$this->_checkData($newProduct);

		if(!empty($newProduct->product_code)){
			$products = array($newProduct);
			if(!empty($this->template->variants)){
				foreach($this->template->variants as $variant){
					$copy = (!HIKASHOP_PHP5) ? $variant : clone($variant);
					$copy->product_parent_id = $newProduct->product_code;
					$copy->product_code = str_replace($this->template->product_code,$newProduct->product_code,$copy->product_code);
					unset($copy->product_id);
					$products[]=$copy;
				}
			}
			JPluginHelper::importPlugin( 'hikashop' );
			$dispatcher = JDispatcher::getInstance();
			$do = true;
			$dispatcher->trigger( 'onBeforeProductCopy', array( & $this->template, & $products[0], & $do) );
			if(!$do){
				return false;
			}

			$this->_insertProducts($products);

			$dispatcher->trigger( 'onAfterProductCopy', array( & $this->template, & $products[0]) );
		}
		return true;
	}

	function importFromFile(&$importFile, $process = true){
		$app = JFactory::getApplication();
		if(empty($importFile['name'])){
			$app->enqueueMessage(JText::_('BROWSE_FILE'),'notice');
			return false;
		}
		$this->charsetConvert = JRequest::getString('charsetconvert','');
		jimport('joomla.filesystem.file');
		$config =& hikashop_config();
		$allowedFiles = explode(',',strtolower($config->get('allowedfiles')));
		$uploadFolder = JPath::clean(html_entity_decode($config->get('uploadfolder')));
		$uploadFolder = trim($uploadFolder,DS.' ').DS;
		$uploadPath = JPath::clean(HIKASHOP_ROOT.$uploadFolder);
		if(!is_dir($uploadPath)){
			jimport('joomla.filesystem.folder');
			JFolder::create($uploadPath);
			JFile::write($uploadPath.'index.html','<html><body bgcolor="#FFFFFF"></body></html>');
		}
		if(!is_writable($uploadPath)){
			@chmod($uploadPath,'0755');
			if(!is_writable($uploadPath)){
				$app->enqueueMessage(JText::sprintf( 'WRITABLE_FOLDER',$uploadPath), 'notice');
			}
		}
		$attachment = new stdClass();
		$attachment->filename = strtolower(JFile::makeSafe($importFile['name']));
		$attachment->size = $importFile['size'];
		if(!preg_match('#\.('.str_replace(array(',','.'),array('|','\.'),$config->get('allowedfiles')).')$#Ui',$attachment->filename,$extension) || preg_match('#\.(php.?|.?htm.?|pl|py|jsp|asp|sh|cgi)$#Ui',$attachment->filename)){
			$app->enqueueMessage(JText::sprintf( 'ACCEPTED_TYPE',substr($attachment->filename,strrpos($attachment->filename,'.')+1),$config->get('allowedfiles')), 'notice');
			return false;
		}
		$attachment->filename = str_replace(array('.',' '),'_',substr($attachment->filename,0,strpos($attachment->filename,$extension[0]))).$extension[0];
		if ( !move_uploaded_file($importFile['tmp_name'], $uploadPath . $attachment->filename)) {
			if(!JFile::upload($importFile['tmp_name'], $uploadPath . $attachment->filename)){
				$app->enqueueMessage(JText::sprintf( 'FAIL_UPLOAD',$importFile['tmp_name'],$uploadPath . $attachment->filename), 'error');
			}
		}
		hikashop_increasePerf();
		$contentFile = file_get_contents($uploadPath . $attachment->filename);
		if(!$contentFile){
			$app->enqueueMessage(JText::sprintf( 'FAIL_OPEN',$uploadPath . $attachment->filename), 'error');
			return false;
		};

		if($process){
			unlink($uploadPath . $attachment->filename);
			$toTest = array();
			if(empty($this->charsetConvert)){
				$encodingHelper = hikashop_get('helper.encoding');
				$this->charsetConvert = $encodingHelper->detectEncoding($contentFile);
			}
			return $this->handleContent($contentFile);
		}else{
			$filePath = $uploadPath . $attachment->filename;
			return $filePath;
		}
	}

	function handleContent(&$contentFile){
		$app = JFactory::getApplication();
		$contentFile = str_replace(array("\r\n","\r"),"\n",$contentFile);
		$this->importLines = explode("\n", $contentFile);
		$this->i = 0;
		while(empty($this->header)){
			$this->header = trim($this->importLines[$this->i]);
			$this->i++;
		}
		if(!$this->_autoDetectHeader()){
			return false;
		}

		$this->numberColumns = count($this->columns);
		$importProducts = array();
		$encodingHelper = hikashop_get('helper.encoding');
		$errorcount = 0;
		while ($data = $this->_getProduct()) {
			$this->totalTry++;

			$newProduct = new stdClass();
			foreach($data as $num => $value){
				if(!empty($this->columns[$num])){
					$field = $this->columns[$num];
					if( strpos('|',$field) !== false ) { $field = str_replace('|','__tr__',$field); }
					$newProduct->$field = preg_replace('#^[\'" ]{1}(.*)[\'" ]{1}$#','$1',$value);
					if(!empty($this->charsetConvert)){
						$newProduct->$field = $encodingHelper->change($newProduct->$field,$this->charsetConvert,'UTF-8');
					}
				}
			}

			$this->_checkData($newProduct,true);

			if(!empty($newProduct->product_code)){
				$importProducts[] = $newProduct;
				if(count($this->currentProductVariants)){
					foreach($this->currentProductVariants as $variant){
						$importProducts[] = $variant;
					}
				}
				$this->totalValid++;
			}else{
				$errorcount++;
				if($errorcount<20){
					if(isset($this->importLines[$this->i-1]))$app->enqueueMessage(JText::sprintf('IMPORT_ERRORLINE',$this->importLines[$this->i-1]).' '.JText::_('PRODUCT_NOT_FOUND'),'notice');
				}elseif($errorcount == 20){
					$app->enqueueMessage('...','notice');
				}
			}

			if( $this->totalValid%$this->perBatch == 0){
				$this->_insertProducts($importProducts);
				$importProducts = array();
			}

		}
		if(!empty($importProducts)){
			$this->_insertProducts($importProducts);
		}

		$this->_deleteUnecessaryVariants();

		$app->enqueueMessage(JText::sprintf('IMPORT_REPORT',$this->totalTry,$this->totalInserted,$this->totalTry - $this->totalValid,$this->totalValid - $this->totalInserted));

		return true;
	}

	function _deleteUnecessaryVariants(){
		if(!empty($this->products_already_in_db)){
			$this->db->setQuery('SELECT product_id FROM '.hikashop_table('product').' WHERE product_parent_id IN ('.implode(',',$this->products_already_in_db).') AND product_id NOT IN ('.implode(',',$this->new_variants_in_db).') AND product_type=\'variant\'');
			if(!HIKASHOP_J25){
				$variants_to_be_deleted = $this->db->loadResultArray();
			} else {
				$variants_to_be_deleted = $this->db->loadColumn();
			}
			if(!empty($variants_to_be_deleted)){
				$productClass = hikashop_get('class.product');
				$productClass->delete($variants_to_be_deleted);
			}
		}
	}

	function &_getProduct(){
		$false = false;
		if(!isset($this->importLines[$this->i])){
			return $false;
		}
		if(empty($this->importLines[$this->i])){
			$this->i++;
			return $this->_getProduct();
		}

		$quoted = false;
		$dataPointer=0;
		$data = array('');

		while($data!==false && isset($this->importLines[$this->i]) && (count($data) < $this->numberColumns||$quoted)){
			$k = 0;
			$total = strlen($this->importLines[$this->i]);
			while($k < $total){
				switch($this->importLines[$this->i][$k]){
					case '"':

						if($quoted && isset($this->importLines[$this->i][$k+1]) && $this->importLines[$this->i][$k+1]=='"'){
							$data[$dataPointer].='"';
							$k++;
						}elseif($quoted){
							$quoted = false;
						}elseif(empty($data[$dataPointer])){
							$quoted = true;
						}else{
							$data[$dataPointer].='"';
						}
						break;
					case $this->separator:
						if(!$quoted){
							$data[]='';
							$dataPointer++;
							break;
						}
					default:
						$data[$dataPointer].=$this->importLines[$this->i][$k];
						break;
				}
				$k++;
			}

			$this->_checkLineData($data);

			if(count($data) < $this->numberColumns||$quoted){
				$data[$dataPointer].="\r\n";
			}

			$this->i++;
		}

		if($data!=false) $this->_checkLineData($data,false);
		return $data;
	}

	function _checkLineData(&$data,$type=true){
		if($type){
			$not_ok = count($data) > $this->numberColumns;
		}else{
			$not_ok = count($data) != $this->numberColumns;
		}

		if($not_ok){
			static $errorcount = 0;
			if(empty($errorcount)){
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::sprintf('IMPORT_ARGUMENTS',$this->numberColumns),'error');
			}
			$errorcount++;
			if($errorcount<20){
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::sprintf('IMPORT_ERRORLINE',$this->importLines[$this->i]),'notice');
				$data = $this->_getProduct();
			}elseif($errorcount == 20){
				$app = JFactory::getApplication();
				$app->enqueueMessage('...','notice');
			}
		}
	}

	function _checkData(&$product,$main=false){
		$this->currentProductVariants = array();
		if(empty($product->product_created)){
			$product->product_created = time();
		}elseif(!is_numeric($product->product_created)){
			$product->product_created = strtotime($product->product_created);
		}
		if(empty($product->product_modified)){
			$product->product_modified = time();
		}elseif(!is_numeric($product->product_modified)){
			$product->product_modified = strtotime($product->product_modified);
		}
		if(empty($product->product_sale_start)){
			if(!empty($this->template->product_sale_start)){
				$product->product_sale_start = $this->template->product_sale_start;
			}
		}elseif(!is_numeric($product->product_sale_start)){
			$product->product_sale_start = strtotime($product->product_sale_start);
		}
		if(empty($product->product_sale_end)){
			if(!empty($this->template->product_sale_end)){
				$product->product_sale_end = $this->template->product_sale_end;
			}
		}elseif(!is_numeric($product->product_sale_end)){
			$product->product_sale_end = strtotime($product->product_sale_end);
		}

		if(!empty($product->product_weight)){
			$product->product_weight = hikashop_toFloat($product->product_weight);
		}
		if(!empty($product->product_width)){
			$product->product_width = hikashop_toFloat($product->product_width);
		}
		if(!empty($product->product_height)){
			$product->product_height = hikashop_toFloat($product->product_height);
		}
		if(!empty($product->product_length)){
			$product->product_length = hikashop_toFloat($product->product_length);
		}

		if(empty($product->product_type)){
			if(empty($product->product_parent_id)){
				$product->product_type='main';
			}else{

				if(!empty($product->product_parent_id) && !empty($product->product_code) && $product->product_parent_id == $product->product_code){
					$app = JFactory::getApplication();
					$app->enqueueMessage('The product '.$product->product_code.' has the same value in the product_parent_id and product_code fields which is not possible ( a main product cannot be a variant at the same time ). This product has been considered as a main product by HikaShop and has been imported as such.');
					$product->product_type='main';
					$product->product_parent_id=0;
				}else{
					$product->product_type='variant';
				}
			}
		}else{
			if(!in_array($product->product_type,array('main','variant'))){
				$product->product_type = 'main';
			}
		}
		if($product->product_type=='main'){
			if(!empty($product->product_parent_id)){
				$app = JFactory::getApplication();
				$app->enqueueMessage('The product '.@$product->product_code.' should have an empty value instead of the value '.$product->product_parent_id.' in the field product_parent_id as it is a main product (not a variant) and thus doesn\'t have any parent.','error');
			}
		}else{
			$product->product_tax_id = 0;
		}

		if(!isset($product->product_tax_id) || strlen($product->product_tax_id)<1){
			$product->product_tax_id = $this->tax_category;
		}else{
			if(!is_numeric($product->product_tax_id)){
				$id = $this->_getCategory($product->product_tax_id,0,!$this->createCategories,'tax');
				if(empty($id) && $this->createCategories){
					$id = $this->_createCategory($product->product_tax_id,0,'tax');
				}
				$product->product_tax_id = $id;
			}
		}
		if(!empty($product->product_manufacturer_id) && !is_numeric($product->product_manufacturer_id)){
			$id = $this->_getCategory($product->product_manufacturer_id,0,!$this->createCategories,'manufacturer');
			if(empty($id) && $this->createCategories){
				$id = $this->_createCategory($product->product_manufacturer_id,0,'manufacturer');
			}
			$product->product_manufacturer_id = $id;
		}
		if(!isset($product->product_quantity) || strlen($product->product_quantity)<1){
			if(!empty($this->template->product_quantity)){
				$product->product_quantity=$this->template->product_quantity;
			}
		}
		if(isset($product->product_quantity) && !is_numeric($product->product_quantity)){
			$product->product_quantity=-1;
		}

		foreach($this->fields as $field){
			if(empty($product->$field)&&!empty($this->template->$field)){
				$product->$field=$this->template->$field;
			}
		}

		if(empty($product->product_dimension_unit)){
			$product->product_dimension_unit=$this->volumeHelper->getSymbol();
		}else{
			$product->product_dimension_unit= strtolower($product->product_dimension_unit);
		}
		if(empty($product->product_weight_unit)){
			$product->product_weight_unit=$this->weightHelper->getSymbol();
		}else{
			$product->product_weight_unit= strtolower($product->product_weight_unit);
		}

		if(!empty($product->product_published)){
			$product->product_published=1;
		}
		if(!isset($product->product_published)){
			if(!empty($this->template)){
				$product->product_published = $this->template->product_published;
			}
		}

		if(!empty($product->price_value_with_tax)){
			$currencyHelper = hikashop_get('class.currency');
			if(empty($product->product_tax_id)){
				$product->product_tax_id = $currencyHelper->getTaxCategory();
			}

			if($product->product_tax_id){
				if(strpos($product->price_value_with_tax,'|')===false){
					$product->price_value = $currencyHelper->getUntaxedPrice(hikashop_toFloat($product->price_value_with_tax),hikashop_getZone(),$product->product_tax_id);
				}else{
					$price_value = explode('|',$product->price_value_with_tax);
					foreach($price_value as $k => $price_value_one){
						$price_value[$k] = $currencyHelper->getUntaxedPrice($price_value_one,hikashop_getZone(),$product->product_tax_id);
					}
					$product->price_value = implode('|',$price_value);
				}
			}
		}
		if(!empty($product->price_value)){
			$product->prices = array();
			if(strpos($product->price_value,'|')===false){
				$price = new stdClass();
				$price->price_value = hikashop_toFloat($product->price_value);
				if(!empty($this->price_fee)){
					$price->price_value += $price->price_value*hikashop_toFloat($this->price_fee)/100;
				}
				$price->price_min_quantity = (int)@$product->price_min_quantity;
				if($price->price_min_quantity==1){
					$price->price_min_quantity=0;
				}
				if(empty($product->price_access)){
					$price->price_access = 'all';
				}else{
					$price->price_access = $product->price_access;
				}
				if(!empty($product->price_currency_id)){
					if(!is_numeric($product->price_currency_id)){
						$product->price_currency_id = $this->_getCurrency($product->price_currency_id);
					}
					$price->price_currency_id = $product->price_currency_id;
				}else{
					$config =& hikashop_config();
					$price->price_currency_id = $config->get('main_currency',1);
				}
				$product->prices[]=$price;
			}else{
				$price_value = explode('|',$product->price_value);
				if(!empty($product->price_min_quantity)){
					$price_min_quantity = explode('|',$product->price_min_quantity);
				}
				if(!empty($product->price_access)){
					$price_access = explode('|',$product->price_access);
				}
				if(!empty($product->price_currency_id)){
					$price_currency_id = explode('|',$product->price_currency_id);
				}
				foreach($price_value as $k => $price_value_one){
					$price = new stdClass();
					$price->price_value = hikashop_toFloat($price_value_one);
					if(!empty($this->price_fee)){
						$price->price_value += $price->price_value*hikashop_toFloat($this->price_fee)/100;
					}
					$price->price_min_quantity = (int)@$price_min_quantity[$k];
					if($price->price_min_quantity==1){
						$price->price_min_quantity=0;
					}
					if(empty($price_access[$k])){
						$price->price_access = 'all';
					}else{
						$price->price_access = $price_access[$k];
					}
					if(!empty($price_currency_id[$k])){
						if(!is_numeric($price_currency_id[$k])){
							$price_currency_id[$k] = $this->_getCurrency($price_currency_id[$k]);
						}
						$price->price_currency_id = $price_currency_id[$k];
					}else{
						$config =& hikashop_config();
						$price->price_currency_id = $config->get('main_currency',1);
					}
					$product->prices[]=$price;
				}
			}

		}
		if(!empty($product->files) && !is_array($product->files)){
			$this->_separate($product->files);
			$unset = array();
			foreach($product->files as $k => $file){
				$product->files[$k] = $file = trim($file);
				if(substr($file,0,7)=='http://'||substr($file,0,8)=='https://'){
					$parts = explode('/',$file);
					$name = array_pop($parts);
					$name = explode('?',$name);
					$name = array_shift($name);
					$name = urldecode($name);
					if(!file_exists($this->uploadFolder.$name)){
						$data = @file_get_contents($file);
						if(empty($data) && !empty($this->default_file)){
							$name = $this->default_file;
						}else{
							JFile::write($this->uploadFolder.$name,$data);
						}
					}else{
						$size = $this->getSizeFile($file);
						if($size!=filesize($this->uploadFolder.$name)){
							$name=$size.'_'.$name;
							if(!file_exists($this->uploadFolder.$name)){
								JFile::write($this->uploadFolder.$name,file_get_contents($file));
							}
						}
					}
					if(file_exists($this->uploadFolder.$name) && (filesize($this->uploadFolder.$name) > 0 || filesize($this->uploadFolder.$name) === false)){
						$product->files[$k] = $name;
					}else{
						$unset[]=$k;
					}
				}
			}
			if(!empty($unset)){
				foreach($unset as $k){
					unset($product->files[$k]);
				}
			}
			$this->_filesToObject($product->files,'files');
			$this->_getFilesExtraData($product->files, $product,'files');
		}
		if(!empty($product->images) && !is_array($product->images)){
			$this->_separate($product->images);
			$unset = array();
			foreach($product->images as $k => $image){
				$product->images[$k] = $image = trim($image);
				if(substr($image,0,7)=='http://'||substr($image,0,8)=='https://'){
					$parts = explode('/',$image);
					$name = array_pop($parts);
					$name = explode('?',$name);
					$name = array_shift($name);
					$name = urldecode($name);
					if(!file_exists($this->uploadFolder.$name)){
						$content = file_get_contents($image);
						JFile::write($this->uploadFolder.$name,$content);
					}else{
						$size = $this->getSizeFile($image);
						if($size!=filesize($this->uploadFolder.$name)){
							$name=$size.'_'.$name;
							if(!file_exists($this->uploadFolder.$name)){
								$content = file_get_contents($image);
								JFile::write($this->uploadFolder.$name,$content);
							}
						}
					}
					if(file_exists($this->uploadFolder.$name) && (filesize($this->uploadFolder.$name) > 0 || filesize($this->uploadFolder.$name) === false)){
						$product->images[$k] = $name;
					}else{
						$unset[]=$k;
					}
				}
			}
			if(!empty($unset)){
				foreach($unset as $k){
					unset($product->images[$k]);
				}
			}
			$this->_filesToObject($product->images,'images');
			$this->_getFilesExtraData($product->images, $product,'images');
		}

		if(empty($product->product_name)){
			if(!empty($product->files)){
				if(!is_array($product->files)){
					$this->_separate($product->files);
				}
				if(is_object($product->files[0])){
					$name = $product->files[0]->file_name;
				}else{
					$name = substr($product->files[0],0,strrpos($product->files[0],'.'));
				}
				$product->product_name=$name;
			}elseif(!empty($product->images)){
				if(!is_array($product->images)){
					$this->_separate($product->images);
				}
				if(is_object($product->images[0])){
					$name = $product->images[0]->file_name;
				}else{
					$name = substr($product->images[0],0,strrpos($product->images[0],'.'));
				}
				$product->product_name=$name;
			}
		}

		if(!empty($product->related) && !is_array($product->related)){
			$this->_separate($product->related);
		}
		if(!empty($product->options) && !is_array($product->options)){
			$this->_separate($product->options);
		}

		if($product->product_type=='variant'){
			$product->categories = null;
		}else{
			if(!empty($product->categories)){
				if(!is_array($product->categories)){
					$this->_separate($product->categories);
				}
				$parent_id=0;
				if($this->createCategories && !empty($product->parent_category)){
					$this->_separate($product->parent_category);
					$parent_id = array();
					foreach($product->parent_category as $k => $parent_category){
						if(is_numeric($parent_category)){
							$parent_id[$k] = $parent_category;
						}else{
							$parent_id[$k] = $this->_getCategory($parent_category,0,false,'product');

							if(empty($parent_id[$k])){
								$parent_id[$k] = $this->_createCategory($parent_category);
							}
						}
					}
				}
				if($this->createCategories && !empty($product->categories_image)){
					$unset = array();
					$this->_separate($product->categories_image);
					foreach($product->categories_image as $k => $image){
						if(substr($image,0,7)=='http://'||substr($image,0,8)=='https://'){
							$parts = explode('/',$image);
							$name = array_pop($parts);
							if(!file_exists($this->uploadFolder.$name)){
								JFile::write($this->uploadFolder.$name,file_get_contents($image));
							}else{
								$size = $this->getSizeFile($image);
								if($size!=filesize($this->uploadFolder.$name)){
									$name=$size.'_'.$name;
									if(!file_exists($this->uploadFolder.$name)){
										JFile::write($this->uploadFolder.$name,file_get_contents($image));
									}
								}
							}
							if(filesize($this->uploadFolder.$name)){
								$product->categories_image[$k] = $name;
							}else{
								$unset[]=$k;
							}
						}
					}
					if(!empty($unset)){
						foreach($unset as $k){
							unset($product->categories_image[$k]);
						}
					}
				}
				if($this->createCategories && !empty($product->categories_namekey)){
					$this->_separate($product->categories_namekey);
				}

				foreach($product->categories as $k => $v){
					if(!is_numeric($v) || !empty($product->categories_namekey)){
						$pid = 0;
						if(is_array($parent_id)){
							if(!empty($parent_id[$k])){
								$pid = $parent_id[$k];
							}elseif(!empty($parent_id[0])){
								$pid = $parent_id[0];
							}
						}
						$id = $this->_getCategory($v,0,!$this->createCategories,'product',$pid,@$product->categories_image[$k]);
						if(empty($id)){
							if($this->createCategories){
								$id = $this->_createCategory($v,$pid,'product',@$product->categories_image[$k],@$product->categories_namekey[$k]);
							}else{
								$app = JFactory::getApplication();
								$app->enqueueMessage('The product '.@$product->product_code.' has the category name "'.$v.'" in your CSV but that category doesn\'t exist on your website and you turned off the automatic creation of categories for the import.','error');
							}
						}
						$product->categories[$k] = (int)$id;
					}
				}

			}
		}
		if(!empty($product->categories_ordering)){
			$this->_separate($product->categories_ordering);
		}

		if(empty($product->product_access)){
			if(!empty($this->template)){
				$product->product_access = @$this->template->product_access;
			}else{
				$product->product_access = 'all';
			}
		}

		if(!isset($product->product_contact) && !empty($this->template)){
			$product->product_contact = @$this->template->product_contact;
		}
		if(!isset($product->product_group_after_purchase) && !empty($this->template)){
			$product->product_group_after_purchase = @$this->template->product_group_after_purchase;
		}

		if(hikashop_level(2) && !empty($product->product_access)){
			if(!is_array($product->product_access)){
				if(!in_array($product->product_access,array('none','all'))){
					if(!is_array($product->product_access)){
						$this->_separate($product->product_access);
					}
				}
			}
			if(is_array($product->product_access)){
				$accesses = array();
				foreach($product->product_access as $access){
					if(empty($access))continue;
					if(!is_numeric($access)){
						$access = $this->_getAccess($access);
						if(empty($access))continue;
					}
					$accesses[] = $access;
				}
				$product->product_access = ','.implode(',',$accesses).',';
			}
		}

		if(!empty($this->characteristicColumns)){
			foreach($this->characteristicColumns as $column){
				if(isset($product->$column) && strlen($product->$column)>0){
					if($product->product_type=='main' && !empty($this->characteristicsConversionTable[$column])){
						if(!isset($product->variant_links)){
							$product->variant_links=array();
						}
						$product->variant_links[]=$this->characteristicsConversionTable[$column];
					}
					if(function_exists('mb_strtolower')){
						$key = mb_strtolower(trim($product->$column,'" '));
					}else{
						$key = strtolower(trim($product->$column,'" '));
					}

					if(!empty($this->characteristicsConversionTable[$column.'_'.$key])){
						$key = $column.'_'.$key;
					}
					if(!empty($this->characteristicsConversionTable[$key])){
						if(!isset($product->variant_links)){
							$product->variant_links=array();
						}
						$product->variant_links[]=$this->characteristicsConversionTable[$key];
					}
				}
			}
		}

		if (!empty($product->product_id) && empty($product->product_code)){
			$query = 'SELECT `product_code` FROM '.hikashop_table('product') .
			' WHERE product_id='.(int)$product->product_id;
			$this->db->setQuery($query);
			$product->product_code = $this->db->loadResult();
		}
		else
		if(empty($product->product_code)&&!empty($product->product_name)){
			$test=preg_replace('#[^a-z0-9_-]#i','',$product->product_name);
			if(empty($test)){
				static $last_pid = null;
				if($last_pid===null){
					$query = 'SELECT MAX(`product_id`) FROM '.hikashop_table('product');
					$this->db->setQuery($query);
					$last_pid = (int)$this->db->loadResult();
				}
				$last_pid++;
				$product->product_code = 'product_'.$last_pid;
			}else{
				$product->product_code = preg_replace('#[^a-z0-9_-]#i','_',$product->product_name);
			}
		}

		if(empty($product->product_name)&&!empty($this->template->product_name)){
			$product->product_name = $this->template->product_name;
		}

		if( !empty($this->translateColumns) ) {
			foreach($this->translateColumns as $k => $v) {
				if( !empty($product->$v) ) {
					list($name,$lng) = explode('__tr__',$v);
					if( $lng == $this->locale ) {
						$product->$name =& $product->$v;
					} else {
						if( isset($this->translateLanguages[$lng]) ) {
							if( !isset($product->translations) ) {
								$product->translations = array();
							}

							$obj = new stdClass();
							$obj->language_id = $this->translateLanguages[$lng];
							$obj->reference_table = 'hikashop_product';
							$obj->reference_field = $name;
							$obj->value =& $product->$v;
							$obj->modified_by = 0; //TODO
							$obj->published = 1;
							$product->translations[] = $obj;
						}
					}
				}
			}
		}

		$unset = array();
		foreach(get_object_vars($product) as $column=>$value){
			if(!empty($this->columnNamesConversionTable[$column]) && is_array($this->columnNamesConversionTable[$column])){
				if(!empty($this->columnNamesConversionTable[$column]['append'])){
					$new_column = $this->columnNamesConversionTable[$column]['append'];
					if(in_array($column,array('files','images'))){
						if(is_array($value)){
							$tmp=array();
							foreach($value as $v){
								$tmp[]='<a href="'.$this->uploadFolder_url.$v.'">'.$v.'</a>';
							}
							$value = implode(',',$tmp);
						}else{
							$value='<a href="'.$this->uploadFolder_url.$value.'">'.$value.'</a>';
						}
					}
					$trans_string = 'HIKASHOP_FEED_'.strtoupper($column);
					$trans = JText::_($trans_string);
					if($trans_string==$trans){
						$trans=$column;
					}
					$product->$new_column.='<div id="hikashop_product_'.$column.'">'.$trans.':'.$value.'</div>';
					$unset[]=$column;
				}
				if(!empty($this->columnNamesConversionTable[$column]['copy'])){
					$new_column = $this->columnNamesConversionTable[$column]['copy'];
					$product->$new_column=$value;
				}
			}
		}

		if($product->product_type=='main' && $main && !isset($product->product_parent_id)){
			if(!empty($this->template->variants)){
				foreach($this->template->variants as $variant){
					$copy = (!HIKASHOP_PHP5) ? $variant : clone($variant);
					unset($copy->product_id);
					$copy->product_parent_id = $product->product_code;
					$copy->product_code = $product->product_code.'_'.$copy->product_code;
					$this->currentProductVariants[]=$copy;
				}
			}
		}

		if(!empty($unset)){
			foreach($unset as $u){
				unset($product->$u);
			}
		}
	}

	function _filesToObject(&$files,$type='files'){
		foreach($files as $k => $name){
			$file = new stdClass();
			$file->file_path = $name;
			$file->file_name = str_replace('_',' ',substr($name,0,strrpos($name,'.')));
			$file->file_description = '';
			$file->file_ordering = 0;
			$file->file_limit = 0;
			$file->file_free_download = 0;
			$files[$k] = $file;
		}
	}

	function _getFilesExtraData(&$files, &$product,$type='files'){
		$variables = array('name','description','ordering','limit','free_download');
		foreach($variables as $var){
			$name = $type.'_'.$var;
			if(empty($product->$name)) continue;
			$this->_separate($product->$name);
			if(count($product->$name) != count($files)) continue;
			$key = 'file_'.$var;
			foreach($files as $k => $file){
				$files[$k]->$key = $product->{$name}[$k];
			}
		}
	}

	function getSizeFile($url) {
		if (substr($url,0,4) == 'http') {
			$x = array_change_key_case(get_headers($url, 1),CASE_LOWER);
			if ( strcasecmp($x[0], 'HTTP/1.1 200 OK') != 0 ) { $x = $x['content-length'][1]; }
			else { $x = $x['content-length']; }
		}
		else { $x = @filesize($url); }

		return $x;
	}

	function _createCategory($category,$parent_id=0,$type='product',$img='',$namekey=''){
		$obj=new stdClass();
		$obj->category_name = $category;
		$obj->category_namekey = $namekey;
		$obj->category_type = $type;
		if(empty($parent_id)){
			$name = 'main'.ucfirst($type).'Category';
			$parent_id = @$this->$name;
		}
		$obj->category_parent_id = $parent_id;
		$class = hikashop_get('class.category');
		$new_id = $class->save($obj,false);
		$this->_getCategory($obj->category_namekey,$new_id,true,$type,$parent_id);
		$this->_getCategory($obj->category_name,$new_id,true,$type,$parent_id);
		if($new_id && !empty($img)){
			$db = JFactory::getDBO();
			$base = substr($img,0,strrpos($img,'.'));
			$db->setQuery('INSERT IGNORE INTO '.hikashop_table('file').' (`file_name`,`file_description`,`file_path`,`file_type`,`file_ref_id`) VALUES ('.$db->Quote($base).',\'\','.$db->Quote($img).',\'category\','.(int)$new_id.');');
			$db->query();
		}
		return $new_id;
	}

	function _getCategory($code,$newId=0,$error=true,$type='product',$parent_id=0,$image=''){
		static $data=array();
		$namekey = $code;
		$parent_condition = '';
		if(!empty($parent_id)){
			$namekey.='__'.$parent_id;
			$parent_condition = ' AND category_parent_id='.$parent_id;
		}
		if(!empty($newId)){
			$data[$code] = $newId;
			$data[$namekey] = $newId;
		}
		if(!isset($data[$namekey])){
			$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE category_namekey='.$this->db->Quote($code).' AND category_type='.$this->db->Quote($type).$parent_condition;
			$this->db->setQuery($query);
			$data[$namekey] = $this->db->loadResult();
			if(empty($data[$namekey])){
				$query = 'SELECT category_id FROM '.hikashop_table('category').' WHERE category_name='.$this->db->Quote($code).' AND category_type='.$this->db->Quote($type).$parent_condition;
				$this->db->setQuery($query);
				$data[$namekey] = $this->db->loadResult();
				if(empty($data[$namekey])){
					if($error){
						$app =& JFactory::getApplication();
						$app->enqueueMessage('The '.$type.' category "'.$code.'" could not be found in the database. Products imported and using this '.$type.' category will be linked to the main '.$type.' category.');
						$name = 'main'.ucfirst($type).'Category';
						$data[$namekey] = @$this->$name;
					}else{
						$data[$namekey] = 0;
					}

				}
			}
		}
		if($data[$namekey] && !empty($image)){
			$base = substr($image,0,strrpos($image,'.'));
			$this->db->setQuery('DELETE FROM '.hikashop_table('file').' WHERE file_type = \'category\' AND file_ref_id='.(int)$data[$namekey].';');
			$this->db->query();
			$this->db->setQuery('INSERT IGNORE INTO '.hikashop_table('file').' (`file_name`,`file_description`,`file_path`,`file_type`,`file_ref_id`) VALUES ('.$this->db->Quote($base).',\'\','.$this->db->Quote($image).',\'category\','.(int)$data[$namekey].');');
			$this->db->query();
		}
		return $data[$namekey];
	}

	function _getRelated($code){
		static $data=array();
		if(!isset($data[$code])){
			$query = 'SELECT product_id FROM '.hikashop_table('product').' WHERE product_code='.$this->db->Quote($code);
			$this->db->setQuery($query);
			$id = $this->db->loadResult();
			if(empty($id)){
				$query = 'SELECT product_id FROM '.hikashop_table('product').' WHERE product_id='.$this->db->Quote($code);
				$this->db->setQuery($query);
				$id = $this->db->loadResult();
				if(empty($id)){
					return $code;
				}else{
					$data[$code] = $id;
				}
			}else{
				$data[$code] = $id;
			}
		}
		return $data[$code];
	}

	function _getAccess($access){
		static $data=array();
		if(!isset($data[$access])){
			if(version_compare(JVERSION,'1.6','<')){
				$query = 'SELECT id FROM '.hikashop_table('core_acl_aro_groups',false).' WHERE name='.$this->db->Quote($access);
			}else{
				$query = 'SELECT id FROM '.hikashop_table('usergroups',false).' WHERE title='.$this->db->Quote($access);
			}
			$this->db->setQuery($query);
			$data[$access] = (int)$this->db->loadResult();
		}
		return $data[$access];
	}

	function _getCurrency($code){
		static $data=array();
		if(!isset($data[$code])){
			$query = 'SELECT currency_id FROM '.hikashop_table('currency').' WHERE currency_code='.$this->db->Quote(strtoupper($code));
			$this->db->setQuery($query);
			$data[$code] = $this->db->loadResult();
		}
		return $data[$code];
	}

	function _insertPrices(&$products){
		$values = array();
		$totalValid=0;
		$insert = 'INSERT IGNORE INTO '.hikashop_table('price').' (`price_value`,`price_currency_id`,`price_min_quantity`,`price_product_id`,`price_access`) VALUES (';
		$ids = array();
		foreach($products as $product){
			if(empty($product->prices) && empty($product->hikashop_update)){
				if(@$product->product_type!='variant' && !empty($this->template->prices)){
					foreach($this->template->prices as $price){
						$value = array($this->db->Quote($price->price_value),(int)$price->price_currency_id,(int)$price->price_min_quantity,(int)$product->product_id,$this->db->Quote(@$price->price_access));
						$values[] = implode(',',$value);
						$totalValid++;
						if( $totalValid%$this->perBatch == 0){
							$this->db->setQuery($insert.implode('),(',$values).')');
							$this->db->query();
							$totalValid=0;
							$values=array();
						}
					}
				}
			}elseif(!empty($product->prices)){
				$ids[]=(int)$product->product_id;

				foreach($product->prices as $price){
					$value = array($this->db->Quote($price->price_value),(int)$price->price_currency_id,(int)$price->price_min_quantity,(int)$product->product_id,$this->db->Quote(@$price->price_access));
					$values[] = implode(',',$value);
					$totalValid++;
					if( $totalValid%$this->perBatch == 0){
						if(!empty($ids)){
							$this->db->setQuery('DELETE FROM '.hikashop_table('price').' WHERE price_product_id IN ('.implode(',',$ids).')');
							$this->db->query();
							$ids=array();
						}
						$this->db->setQuery($insert.implode('),(',$values).')');
						$this->db->query();
						$totalValid=0;
						$values=array();
					}
				}
			}
		}
		if(!empty($values)){
			if(!empty($ids)){
				$this->db->setQuery('DELETE FROM '.hikashop_table('price').' WHERE price_product_id IN ('.implode(',',$ids).')');
				$this->db->query();
			}
			$this->db->setQuery($insert.implode('),(',$values).')');
			$this->db->query();
		}
	}

	function _insertCategories(&$products){
		$values = array();
		$totalValid=0;
		$insert = 'INSERT IGNORE INTO '.hikashop_table('product_category').' (`category_id`,`product_id`,`ordering`) VALUES (';
		$ids = array();
		foreach($products as $product){
			if(empty($product->categories) && empty($product->hikashop_update)){
				if(@$product->product_type!='variant'){
					if(empty($this->template->categories)){
						$product->categories = array($this->mainProductCategory);
					}else{
						foreach($this->template->categories as $k => $id){
							static $orderings = array();
							if(!isset($orderings[(int)$id])){
								$this->db->setQuery('SELECT max(ordering) FROM '.hikashop_table('product_category').' WHERE category_id='.(int)$id);
								$orderings[(int)$id] = (int)$this->db->loadResult();
							}
							$orderings[(int)$id]++;
							$value = array((int)$id,$product->product_id,$orderings[(int)$id]);
							$values[] = implode(',',$value);
							$totalValid++;
							if( $totalValid%$this->perBatch == 0){
								$this->db->setQuery($insert.implode('),(',$values).')');
								$this->db->query();
								$totalValid=0;
								$values=array();
							}
						}
					}
				}
			}
			if(!empty($product->categories)){
				$ids[] = (int)$product->product_id;
				foreach($product->categories as $k => $id){
					$value = array((int)$id,(int)$product->product_id,(int)@$product->categories_ordering[$k]);
					$values[] = implode(',',$value);
					$totalValid++;
					if( $totalValid%$this->perBatch == 0){
						if(!empty($ids)){
							$this->db->setQuery('DELETE FROM '.hikashop_table('product_category').' WHERE product_id IN ('.implode(',',$ids).')');
							$this->db->query();
							$ids=array();
						}
						$this->db->setQuery($insert.implode('),(',$values).')');
						$this->db->query();
						$totalValid=0;
						$values=array();
					}
				}
			}
		}
		if(!empty($values)){
			if(!empty($ids)){
				$this->db->setQuery('DELETE FROM '.hikashop_table('product_category').' WHERE product_id IN ('.implode(',',$ids).')');
				$this->db->query();
			}
			$this->db->setQuery($insert.implode('),(',$values).')');
			$this->db->query();
		}
	}

	function _insertRelated(&$products,$type='related'){
		$values = array();
		$totalValid=0;
		$insert = 'INSERT IGNORE INTO '.hikashop_table('product_related').' (`product_related_id`,`product_related_type`,`product_id`,`product_related_ordering`) VALUES (';
		$ids=array();

		foreach($products as $product){
			if(!isset($product->$type) && empty($product->hikashop_update)){
				if(@$product->product_type!='variant' && !empty($this->template->$type)){
					$i = 0;
					foreach($this->template->$type as $id){
						$value = array((int)$id,$this->db->Quote($type),$product->product_id,$i);
						$values[] = implode(',',$value);
						$totalValid++;
						if( $totalValid && $totalValid%$this->perBatch == 0){
							$this->db->setQuery($insert.implode('),(',$values).')');
							$this->db->query();
							$totalValid=0;
							$values=array();
						}
						$i++;
					}
				}
			}elseif(isset($product->$type)&&is_array($product->$type)){
				$ids[] = (int)$product->product_id;
				$i = 0;
				foreach($product->$type as $k => $id){
					if(!empty($id)){
						$id = $this->_getRelated($id);
						$product->{$type}[$k] = $id;
						$value = array((int)$id,$this->db->Quote($type),$product->product_id,$i);
						$values[] = implode(',',$value);
						$totalValid++;
					}
					if( $totalValid && $totalValid%$this->perBatch == 0){
						if(!empty($ids)){
							$this->db->setQuery('DELETE FROM '.hikashop_table('product_related').' WHERE product_id IN ('.implode(',',$ids).') AND product_related_type='.$this->db->Quote($type));
							$this->db->query();
							$ids=array();
						}
						if(!empty($id)){
							$this->db->setQuery($insert.implode('),(',$values).')');
							$this->db->query();
						}
						$totalValid=0;
						$values=array();
					}
					$i++;
				}
			}
		}
		if(!empty($ids)){
			$this->db->setQuery('DELETE FROM '.hikashop_table('product_related').' WHERE product_id IN ('.implode(',',$ids).') AND product_related_type='.$this->db->Quote($type));
			$this->db->query();
		}
		if(count($values)){
			$this->db->setQuery($insert.implode('),(',$values).')');
			$this->db->query();
		}
	}

	function _insertVariants(&$products){

		$values = array();
		$totalValid=0;
		$insert = 'INSERT IGNORE INTO '.hikashop_table('variant').' (`variant_characteristic_id`,`variant_product_id`) VALUES (';
		$ids = array();
		foreach($products as $product){
			if(empty($product->variant_links)&&!empty($this->template->variant_links) && empty($product->hikashop_update)){
				$product->variant_links = $this->template->variant_links;
			}
			if(!empty($product->variant_links)){
				$ids[] = (int)$product->product_id;
				foreach($product->variant_links as $link){
					$value = array((int)$link,(int)$product->product_id);
					$values[] = implode(',',$value);
					$totalValid++;
					if( $totalValid%$this->perBatch == 0){
						if(!empty($ids)){
							$this->db->setQuery('DELETE FROM '.hikashop_table('variant').' WHERE variant_product_id IN ('.implode(',',$ids).')');
							$this->db->query();
							$ids=array();
						}
						$this->db->setQuery($insert.implode('),(',$values).')');
						$this->db->query();
						$totalValid=0;
						$values=array();
					}
				}
			}
		}
		if(!empty($values)){
			if(!empty($ids)){
				$this->db->setQuery('DELETE FROM '.hikashop_table('variant').' WHERE variant_product_id IN ('.implode(',',$ids).')');
				$this->db->query();
			}
			$this->db->setQuery($insert.implode('),(',$values).')');
			$this->db->query();
		}
	}

	function _insertTranslations(&$products){
		$value = array();
		$product_translation = false;
		$translations = array();

		foreach($products as $p) {
			if( !empty($p->translations) ) {
				$product_translation = true;
				$translation = reset($p->translations);
				foreach( get_object_vars($translation) as $key => $field){
					$value[] = $key;
				}
				$value[] = 'reference_id';
				break;
			}
		}

		if(!$product_translation) {
			if(empty($this->template->translations) || !empty($product->hikashop_update)) {
				return true;
			}
			$translations =& $this->template->translations;
			$translation = reset($translations);
			if(isset($translation->id)) unset($translation->id);
			foreach(get_object_vars($translation) as $key => $field){
				$value[] = $key;
			}
		}

		$ids = array();
		$values = array();
		$totalValid=0;

		$translationHelper = hikashop_get('helper.translation');
		if($translationHelper->isMulti(true,false)){
			$trans_table = 'jf_content';
			if($translationHelper->falang){
				$trans_table = 'falang_content';
			}

			$insert = 'INSERT IGNORE INTO '.hikashop_table($trans_table,false).' ('.implode(',',$value).') VALUES (';
			foreach($products as $product){
				if($product_translation) {
					unset($translations);
					$translations =& $product->translations;
				}
				if(empty($translations) || !is_array($translations)) continue;
				foreach($translations as $translation){
					$translation->reference_id = $product->product_id;
					if(isset($translation->id)) unset($translation->id);
					$value = array();
					foreach(get_object_vars($translation) as $field){
						$value[] = $this->db->Quote($field);
					}
					$values[] = implode(',',$value);
					$ids[] = 'language_id='.(int)$translation->language_id.' AND reference_id='.(int)$translation->reference_id.' AND reference_table='.$this->db->Quote($translation->reference_table).' AND reference_field='.$this->db->Quote($translation->reference_field);
					$totalValid++;
					if( $totalValid%$this->perBatch == 0){
						if(!empty($ids)){
							$this->db->setQuery('DELETE FROM '.hikashop_table($trans_table,false).' WHERE (' . implode(') OR (', $ids) . ')');
							$this->db->query();
							$ids=array();
						}
						$this->db->setQuery($insert.implode('),(',$values).')');
						$this->db->query();
						$totalValid=0;
						$values=array();
					}
				}
			}
			if(!empty($values)){
				if(!empty($ids)){
					$this->db->setQuery('DELETE FROM '.hikashop_table($trans_table,false).' WHERE (' . implode(') OR (', $ids) . ')');
					$this->db->query();
				}
				$this->db->setQuery($insert.implode('),(',$values).')');
				$this->db->query();
			}
		}
	}

	function _insertFiles(&$products,$type='files'){
		$db_type = 'product';
		if($type=='files'){
			$db_type='file';
		}
		$values = array();
		$totalValid=0;
		$ids=array();
		$insert = 'INSERT IGNORE INTO '.hikashop_table('file').' (`file_name`,`file_description`,`file_path`,`file_type`,`file_ref_id`,`file_ordering`,`file_free_download`,`file_limit`) VALUES (';
		foreach($products as $product){
			if(!isset($product->$type) && empty($product->hikashop_update)){
				if(@$product->product_type!='variant' && !empty($this->template->$type)){
					foreach($this->template->$type as $file){
						$value = array($this->db->Quote($file->file_name),$this->db->Quote($file->file_description),$this->db->Quote($file->file_path),$this->db->Quote($db_type),$product->product_id,$this->db->Quote($file->file_ordering),$this->db->Quote($file->file_free_download),$this->db->Quote($file->file_limit));
						$values[] = implode(',',$value);
						$totalValid++;
						if( $totalValid%$this->perBatch == 0){
							$this->db->setQuery($insert.implode('),(',$values).')');
							$this->db->query();
							$totalValid=0;
							$values=array();
						}
					}
				}
			}elseif(!empty($product->$type)){
				$ids[]=(int)$product->product_id;
				$ordering = 0;
				foreach($product->$type as $file){
					if(is_string($file)){
						$value = array($this->db->Quote(str_replace('_',' ',substr($file,0,strrpos($file,'.')))),$this->db->Quote(''),$this->db->Quote($file),$this->db->Quote($db_type),$product->product_id,$ordering,$this->db->Quote(''),$this->db->Quote(''));
					}else{
						$value = array($this->db->Quote($file->file_name),$this->db->Quote($file->file_description),$this->db->Quote($file->file_path),$this->db->Quote($db_type),$product->product_id,(int)(@$file->file_ordering?$file->file_ordering:$ordering),$this->db->Quote($file->file_free_download),$this->db->Quote($file->file_limit));
					}
					$ordering++;
					$values[] = implode(',',$value);
					$totalValid++;
					if( $totalValid%$this->perBatch == 0){
						if(!empty($ids)){
							$this->db->setQuery('DELETE FROM '.hikashop_table('file').' WHERE file_ref_id IN ('.implode(',',$ids).') AND file_type=\''.$db_type.'\'');
							$this->db->query();
							$ids = array();
						}
						$this->db->setQuery($insert.implode('),(',$values).')');
						$this->db->query();
						$totalValid=0;
						$values=array();
					}
				}

			}
		}
		if(!empty($values)){
			if(!empty($ids)){
				$this->db->setQuery('DELETE FROM '.hikashop_table('file').' WHERE file_ref_id IN ('.implode(',',$ids).') AND file_type=\''.$db_type.'\'');
				$this->db->query();
			}
			$this->db->setQuery($insert.implode('),(',$values).')');
			$this->db->query();
		}
	}


	function _separate(&$files){
		$separator='';
		foreach($this->listSeparators as $sep){
			if(preg_match('#(?!\\\\)'.$sep.'#',$files)){
				$separator = $sep;
				$files=str_replace('\\'.$separator,'#.#.#.#',$files);
				break;
			}
		}
		if(!empty($separator)){
			$files = explode($separator,$files);
		}else{
			$files = array($files);
		}
		foreach($files as $k => $v){
			$files[$k]=str_replace('#.#.#.#',$separator,$v);
		}
	}

	function _autoDetectHeader(){
		$app = JFactory::getApplication();
		$this->separator = ',';
		$this->header = str_replace("\xEF\xBB\xBF","",$this->header);
		foreach($this->listSeparators as $sep){
			if(strpos($this->header,$sep) !== false){
				$this->separator = $sep;
				break;
			}
		}
		$this->columns = explode($this->separator,$this->header);

		$this->translateColumns = array();

		if(!HIKASHOP_J30){
			$columnsTable = $this->db->getTableFields(hikashop_table('product'));
			$columns = reset($columnsTable);
		} else {
			$columns = $this->db->getTableColumns(hikashop_table('product'));
		}
		$columns['price_value']='price_value';
		$columns['price_value_with_tax']='price_value_with_tax';
		$columns['price_currency_id']='price_currency_id';
		$columns['price_min_quantity']='price_min_quantity';
		$columns['price_access']='price_access';
		$columns['files']='files';
		$columns['files_name']='files_name';
		$columns['files_description']='files_description';
		$columns['files_limit']='files_limit';
		$columns['files_ordering']='files_ordering';
		$columns['files_free_download']='files_free_download';
		$columns['images']='images';
		$columns['images_name']='images_name';
		$columns['images_description']='images_description';
		$columns['images_limit']='images_limit';
		$columns['images_ordering']='images_ordering';
		$columns['images_free_download']='images_free_download';
		$columns['parent_category']='parent_category';
		$columns['categories_namekey']='categories_namekey';
		$columns['categories_image']='categories_image';
		$columns['categories_ordering']='categories_ordering';
		$columns['categories']='categories';
		$columns['related']='related';
		$columns['options']='options';
		if(hikashop_level(2)){
			$columns['product_access']='product_access';
			$columns['product_group_after_purchase']='product_group_after_purchase';
		}
		foreach($this->columns as $i => $oneColumn){
			if(function_exists('mb_strtolower')){
				$this->columns[$i] = mb_strtolower(trim($oneColumn,'" '));
			}else{
				$this->columns[$i] = strtolower(trim($oneColumn,'" '));
			}
			$this->columns[$i] = strtolower(trim($oneColumn,'" '));
			if($this->columns[$i] == 'files_path') $this->columns[$i] = 'files';
			if($this->columns[$i] == 'images_path') $this->columns[$i] = 'images';
			foreach($this->columns as $k => $otherColumn){
				if($i != $k && $this->columns[$i] == strtolower($otherColumn)) {
					$app->enqueueMessage('The column "'.$this->columns[$i].'" is twice in your CSV. Only the second column data will be taken into account.','error');
				}
			}

			if( strpos($this->columns[$i],'|') !== false ) {
				$this->columns[$i] = str_replace('|','__tr__',$this->columns[$i]);
				$this->translateColumns[] = $this->columns[$i];
				$columns[$this->columns[$i]] = '';
			}

			if(!isset($columns[$this->columns[$i]])){
				if( isset($this->columnNamesConversionTable[$this->columns[$i]]) ){
					if(is_array($this->columnNamesConversionTable[$this->columns[$i]])){
						$this->columnNamesConversionTable[$this->columnNamesConversionTable[$this->columns[$i]]['name']]=$this->columnNamesConversionTable[$this->columns[$i]];
						$this->columns[$i]=$this->columnNamesConversionTable[$this->columns[$i]]['name'];
					}else{
						$this->columns[$i]=$this->columnNamesConversionTable[$this->columns[$i]];
					}
				}else{
					if(isset($this->characteristicsConversionTable[$this->columns[$i]])){
						$this->characteristicColumns[] = $this->columns[$i];
					}else{
						$possibilities = array_diff(array_keys($columns),array('product_id'));
						if(!empty($this->characteristics)){
							foreach($this->characteristics as $char){
								if(empty($char->characteristic_parent_id)){
									if(function_exists('mb_strtolower')){
										$possibilities[]=mb_strtolower(trim($char->characteristic_value,' "'));
									}else{
										$possibilities[]=strtolower(trim($char->characteristic_value,' "'));
									}

								}
							}
						}
						if($this->header_errors){
							$app->enqueueMessage(JText::sprintf('IMPORT_ERROR_FIELD',$this->columns[$i],implode(' | ',$possibilities)),'error');
						}
					}
				}
			}
		}

		$config = JFactory::getConfig();
		if(HIKASHOP_J30){
			$this->locale = strtolower($config->get('language'));
		}else{
			$this->locale = strtolower($config->getValue('config.language'));
		}
		$this->translateLanguages = array();
		$transHelper = hikashop_get('helper.translation');
		if($transHelper->isMulti(true,false)){
			$languages = $transHelper->loadLanguages();
			if(!empty($languages)){
				foreach($languages as $language) {
					$this->translateLanguages[ strtolower($language->code) ] = $language->id;
				}
			}
		}
		return true;
	}

	function _insertProducts(&$products){
		$this->_insertOneTypeOfProducts($products,'main');

		foreach($products as $k => $variant){
			if($variant->product_type!='main'){
				$parent_code = $variant->product_parent_id;
				if(is_numeric($parent_code)){
					foreach($products as $k2 => $main){
						if($variant->product_parent_id == @$main->product_id){
							$parent_code=$main->product_code;
						}
					}
				}
				if(!empty($this->codes[$parent_code])){
					$products[$k]->product_parent_id = @$this->codes[$parent_code]->product_id;
				}
				if(empty($products[$k]->product_parent_id)){
					unset($products[$k]->product_parent_id);
				}
			}
		}

		$this->_insertOneTypeOfProducts($products,'variant');

		$this->_insertVariants($products);
		$this->_insertPrices($products);
		$this->_insertFiles($products,'images');
		$this->_insertFiles($products,'files');
		$this->_insertCategories($products);
		$this->_insertRelated($products);
		$this->_insertRelated($products,'options');
		$this->_insertTranslations($products);
		$this->products =& $products;
	}

	function _insertOneTypeOfProducts(&$products,$type='main'){

		if(empty($products)) return true;

		$lines = array();
		$totalValid=0;
		$fields = array();
		$all_fields = $this->all_fields;
		if($type!='main'){
			$all_fields[]='product_parent_id';
		}

		$all_fields[]='product_id';

		foreach($this->columnsProductTable as $field){
			if(!in_array($field,$all_fields)){
				$all_fields[]=$field;
			}
		}

		foreach($all_fields as $field){
			$fields[]= '`'.$field.'`';
		}

		$fields = implode(', ',$fields);
		$insert = 'REPLACE INTO '.hikashop_table('product').' ('.$fields.') VALUES (';
		$codes = array();
		foreach($products as $product){
			if($product->product_type!=$type) continue;
			$codes[$product->product_code] = $this->db->Quote($product->product_code);
		}
		if(!empty($codes)){
			$query = 'SELECT * FROM '.hikashop_table('product'). ' WHERE product_code IN ('.implode(',',$codes).')';
			$this->db->setQuery($query);
			$already = $this->db->loadObjectList('product_id');
			if(!empty($already)){
				foreach($already as $code){
					$found = false;
					foreach($products as $k => $product){
						if($product->product_code==$code->product_code){
							$found = $k;
							break;
						}
					}

					if($found!==false){
						if($this->overwrite){
							if(!empty($products[$found]->product_type) && !empty($code->product_type) && $products[$found]->product_type==$code->product_type){
								$products[$found]->product_id = $code->product_id;
								$products[$found]->hikashop_update = true;
							}else{
								$app = JFactory::getApplication();
								$app->enqueueMessage('The product '.$products[$found]->product_code.' is of the type '. $products[$found]->product_type.' but it already exists in the database and is of the type '.$code->product_type.'. In order to avoid any problem the product insertion process has been skipped. Please correct its type before trying to reimport it.','error');
								unset($products[$found]);
							}

						}else{
							unset($products[$found]);
						}
					}
				}
			}

			$exist=0;
			if(!empty($codes)){
				foreach($products as $product){
					if($product->product_type!=$type || empty($codes[$product->product_code])) continue;
					$line = array();
					foreach($all_fields as $field){
						if(!isset($product->$field) && !empty($product->product_id) && isset($already[$product->product_id]) && is_object($already[$product->product_id])){
							$product->$field = $already[$product->product_id]->$field;
						}
						if($field=='product_id'){
							if(empty($product->$field)|| !is_numeric($product->$field)){
								$line[] = 'NULL';
							}else{
								$exist++;
								$line[] = $this->db->Quote(@$product->$field);
							}
						}else{
							if($field=='product_published' && !isset($product->$field) && $this->force_published){
								$product->product_published=1;
							}
							if($field=='product_quantity' && !isset($product->$field) && $this->force_published){
								$product->product_quantity=-1;
							}
							if(JRequest::getInt('update_product_quantity','0') && $field=='product_quantity' && $product->product_quantity != -1){
								$product->product_quantity += $already[$product->product_id]->$field;
							}
							$line[] = $this->db->Quote(@$product->$field);
						}
					}
					$lines[]=implode(',',$line);
					$totalValid++;
					if( $totalValid%$this->perBatch == 0){
						$this->db->setQuery($insert.implode('),(',$lines).')');
						$this->db->query();
						if($type=='main' || $this->countVariant){
							$this->totalInserted += count($lines);
						}
						$totalValid=0;
						$lines=array();
					}
				}
				if(!empty($lines)){
					$this->db->setQuery($insert.implode('),(',$lines).')');
					$this->db->query();
					if($type=='main' || $this->countVariant){
						$this->totalInserted += count($lines);
					}
				}

			}
			$this->totalInserted=$this->totalInserted-$exist;
			if(!empty($codes)){
				$query = 'SELECT product_code, product_id FROM '.hikashop_table('product'). ' WHERE product_code IN ('.implode(',',$codes).')';
				$this->db->setQuery($query);
				$newCodes = (array)$this->db->loadObjectList('product_code');
				foreach($newCodes as $k => $code){
					$this->codes[$k]=$code;
				}

				foreach($products as $k => $product){

					if($product->product_type==$type && !empty($this->codes[$product->product_code])){
						$products[$k]->product_id = @$this->codes[$product->product_code]->product_id;
						if($type=='variant'){
							$this->products_already_in_db[(int)@$products[$k]->product_parent_id]=(int)@$products[$k]->product_parent_id;
							$this->new_variants_in_db[(int)@$products[$k]->product_id]=(int)@$products[$k]->product_id;
						}
					}
				}
			}
		}
	}


	function getHtmlPage()
	{
		switch ($this->importName)
		{
			case 'openc':
				$buff = 'Opencart';
				break;
			case 'mijo':
				$buff = 'Mijoshop';
				break;
			case 'reds':
				$buff = 'Redshop';
				break;
			case 'vm':
				$buff = 'Virtuemart';
				break;
			default:
				$buff = '';
				break;
		}


		$imgFolder = HIKASHOP_IMAGES.'icons/icon-48-import.png';

		return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' .
			'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-gb" lang="en-gb" dir="ltr" id="minwidth" >' .
			'<head><meta http-equiv="content-type" content="text/html; charset=utf-8" /><title>HikaShop - '.$buff.' Import</title>' .
			'<script type="text/javascript">' . "\r\n" . 'var r = false; function import'.ucfirst($this->importName).'() { setTimeout( function() { if(r) { window.location = window.location+1; } }, 1000 ); }' . "\r\n" . '</script>' .
			'</head><body onload="import'.ucfirst($this->importName).'()">'.
			'<div style="width:auto; height:48px; margin:15px;"><div style="background-image:url('.$imgFolder.');width:48px;height:48px;float:left;"></div><h1 style="color:#3AABC6; font-size:1.4em; float:left;"><span style="color:#297F93"> HikaShop :</span> '.JText::sprintf('PRODUCTS_FROM_X',$buff).'</h1></div><br/>'.
			'<div style="margin-left:20px;">';
	}

	function getStartPage()
	{
		$buff = $this->importName;
		if ($buff == 'reds')
			$buff='redshop';
		return '<span style="color:#297F93; font-size:1.2em;text-decoration:underline;">Step 0</span><br/><br/>'.
			'Make a backup of your database.<br/>'.
			'When ready, click on <a '.$this->linkstyle.' href="'.hikashop_completeLink('import&task=import&importfrom='.$buff.'&'.$this->token.'=1&import=1').'">'.JText::_('HIKA_NEXT').'</a>, otherwise '.
			'<a'.$this->linkstyle.' href="'.hikashop_completeLink('import&task=show').'">'.JText::_('HIKA_BACK').'</a>.';
	}

	function proposeReImport()
	{
		$buff = $this->importName;
		if ($buff == 'reds')
			$buff='redshop';
		if( !isset($_GET['reimport']) )
		{
			echo '<p>You have already make an import. If you restart it, the import system will just import new elements</p>';
			echo '<p><span'.$this->bullstyle.'>&#9658;</span> <a'.$this->linkstyle.'href="'.hikashop_completeLink('import&task=import&importfrom='.$buff.'&'.$this->token.'=1&import=1&reimport=1').'">Import new elements</a></p>';
			return false;
		}

		$sql =  "UPDATE `#__hikashop_config` SET config_value=1 WHERE config_namekey = '".$this->importName."_import_state';";
		$this->db->setQuery($sql);
		$this->db->query();
		$this->refreshPage = true;
		echo '<p>The import will restart and import new elements...</p>';

		return true;
	}


	function importRebuildTree()
	{
		if( $this->db == null )
			return false;

		$categoryClass = hikashop_get('class.category');
		$query = 'SELECT category_namekey,category_left,category_right,category_depth,category_id,category_parent_id FROM `#__hikashop_category` ORDER BY category_left ASC';
		$this->db->setQuery($query);
		$categories = $this->db->loadObjectList();
		$root = null;
		$categoryClass->categories = array();
		foreach($categories as $cat){
			$categoryClass->categories[$cat->category_parent_id][]=$cat;
			if(empty($cat->category_parent_id)){
				$root = $cat;
			}
		}

		$categoryClass->rebuildTree($root,0,1);
	}


	function copyFile($dir, $fsrc, $dst, $debug = false){
		if ($debug){
			echo 'Source folder : '.$dir.'<br/>';
			echo 'File source name : '.$fsrc.'<br/>';
			echo 'From "'.$dir.$fsrc.'" to folder/file : "'.$dst.'"<br/>';
			echo '#####<br/>';
		}
		$src = $fsrc;
		if( file_exists($dir.$fsrc) )
			$src = $dir.$fsrc;
		else if( file_exists(HIKASHOP_ROOT.$fsrc) )
			$src = HIKASHOP_ROOT.$fsrc;

		if( file_exists($src) ){
			if( !file_exists($dst) ){
				$ret = JFile::copy($src, $dst);
				if( !$ret ){
					echo '<span '.$this->copywarning.'>The file "' . $src . '" could not be copied to "' . $dst . '"</span><br/>';
				}else{
					return true;
				}
			}
			else{
				echo '<span '.$this->copywarning.'>File already exists "' .$dst . '" ("' . $src . '")</span><br/>';
				return true;
			}
		}
		else{
			echo '<span '.$this->copywarning.'>File is not found "' . $dir.$fsrc . '"</span><br/>';
		}
		return false;
	}
}
