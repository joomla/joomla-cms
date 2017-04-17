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

include_once HIKASHOP_HELPER . 'import.php';
@include_once(HIKASHOP_ROOT . 'administrator/components/com_virtuemart/virtuemart.cfg.php');
ini_set('max_execution_time', 300);

class hikashopImportvm2Helper extends hikashopImportHelper
{
	var $vm_version = 2;
	var $vm_current_lng = '';
	var $sessionParams = '';
	var $vmprefix;

	function __construct(&$parent)
	{
		parent::__construct();
		$this->importName = 'vm';
		$this->sessionParams = HIKASHOP_COMPONENT.'vm';
		jimport('joomla.filesystem.file');
	}

	function importFromVM()
	{
		@ob_clean();
		echo $this->getHtmlPage();

		$this->token = hikashop_getFormToken();
		$app = JFactory::getApplication();
		flush();

		if( isset($_GET['import']) && $_GET['import'] == '1' )
		{
			$time = microtime(true);
			$this->vmprefix = $app->getUserState($this->sessionParams.'vmPrefix');
			$this->vm_current_lng = $app->getUserState($this->sessionParams.'language');
			$processed = $this->doImport();

			if($processed)
			{
				$elasped = microtime(true) - $time;

				if( !$this->refreshPage )
					echo '<p><a'.$this->linkstyle.' href="'.hikashop_completeLink('import&task=import&importfrom=vm&'.$this->token.'=1&import=1&time='.time()).'">'.JText::_('HIKA_NEXT').'</a></p>';

				echo '<p style="font-size:0.85em; color:#605F5D;">Elasped time: ' . round($elasped * 1000, 2) . 'ms</p>';
			}
			else
			{
				echo '<a'.$this->linkstyle.' href="'.hikashop_completeLink('import&task=show').'">'.JText::_('HIKA_BACK').'</a>';
			}
		}
		else
		{
			echo $this->getStartPage();
		}

		if( $this->refreshPage == true )
		{
			echo "<script type=\"text/javascript\">\r\nr = true; \r\n</script>";
		}
		echo '</body></html>';
		exit;
	}

	function getStartPage()
	{
		$app = JFactory::getApplication();

		$returnString = '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 0</span></p>';
		$continue=true;

		$this->vmprefix = $app->getUserStateFromRequest($this->sessionParams.'vmPrefix', 'vmPrefix', '', 'string' );
		if (empty($this->vmprefix))
			$this->vmprefix = $this->db->getPrefix();
		elseif(substr($this->vmprefix, -1, 1) != '_')
			$this->vmprefix .= '_';

		if(strpos($this->vmprefix, '__') !== false && $this->vmprefix != '#__')
			$this->vmprefix = str_replace('__', '_', $this->vmprefix);

		$app->setUserState($this->sessionParams.'vmPrefix',$this->vmprefix);

		$this->db->setQuery("SHOW TABLES LIKE '".$this->vmprefix."virtuemart_products'");
		$table = $this->db->loadObjectList();

		if (!$table)
		{
			$returnString .= '<p style="color:red; font-size:0.9em;">There is no table with the prefix \''.$this->vmprefix.'\' in your Joomla database.</p>';
			$continue = false;
		}

		$this->vm_current_lng = $app->getUserStateFromRequest($this->sessionParams.'language', 'language', '', 'string' );//JRequest::getString('language');
		$this->vm_current_lng = strtolower(str_replace('-','_',$this->vm_current_lng));
		$app->setUserState($this->sessionParams.'language',$this->vm_current_lng);
		$this->db->setQuery("SHOW TABLES LIKE '".$this->db->getPrefix()."virtuemart_products_".$this->vm_current_lng."'");
		$table = $this->db->loadObjectList();
		if (!$table)
		{
			$returnString .= '<p style="color:red; font-size:0.9em;"> There is no table corresponding to the language you selected ('.$this->vm_current_lng.') in your database. Please go back and select another language.</p>';
			$continue = false;
		}

		if ($continue)
		{
			$returnString = 'First, make a backup of your database.<br/>'.
			'When ready, click on <a '.$this->linkstyle.' href="'.hikashop_completeLink('import&task=import&importfrom=vm&'.$this->token.'=1&import=1').'">'.JText::_('HIKA_NEXT').'</a>, otherwise ';
		}
		$returnString .= '<a'.$this->linkstyle.' href="'.hikashop_completeLink('import&task=show').'">'.JText::_('HIKA_BACK').'</a>';
		return $returnString;

	}


	function doImport() {
		if( $this->db == null )
			return false;

		$this->loadConfiguration();
		$current = $this->options->current;

		$ret = true;
		$next = false;

		switch( $this->options->state ) {
			case 0:
				$next = $this->createTables();
				break;
			case 1:
				$next = $this->importTaxes();
				break;
			case 2:
				$next = $this->importManufacturers();
				break;
			case 3:
				$next = $this->importCategories();
				break;
			case 4:
				$next = $this->importProducts();
				break;
			case 5:
				$next = $this->importProductPrices();
				break;
			case 6:
				$next = $this->importProductCategory();
				break;
			case 7:
				$next = $this->importUsers();
				break;
			case 8:
				$next = $this->importDiscount();
				break;
			case 9:
				$next = $this->importOrders();
				break;
			case 10:
				$next = $this->importOrderItems();
				break;
			case 11:
				$next = $this->importDownloads();
				break;
			case 12:
				$next = $this->importReviews();
				break;
			case MAX_IMPORT_ID:
				$next = $this->finishImport();
				$ret = false;
				break;
			case MAX_IMPORT_ID+1:
				$next = false;
				$ret = $this->proposeReImport();
				break;
			default:
				$ret = false;
				break;
		}

		if( $ret && $next ) {
			$sql =  "UPDATE `#__hikashop_config` SET config_value=(config_value+1) WHERE config_namekey = 'vm_import_state'; ";
			$this->db->setQuery($sql);
			$this->db->query();
			$sql = "UPDATE `#__hikashop_config` SET config_value=0 WHERE config_namekey = 'vm_import_current';";
			$this->db->setQuery($sql);
			$this->db->query();
		} else if( $current != $this->options->current ) {
			$sql =  "UPDATE `#__hikashop_config` SET config_value=".$this->options->current." WHERE config_namekey = 'vm_import_current';";
			$this->db->setQuery($sql);
			$this->db->query();
		}

		return $ret;
	}

	function loadConfiguration()
	{
		if( $this->db == null )
			return false;

		$this->loadVmConfigs();

		$data = array(
			'uploadfolder',
			'uploadsecurefolder',
			'main_currency',
			'vm_import_state',
			'vm_import_current',
			'vm_import_tax_id',
			'vm_import_main_cat_id',
			'vm_import_max_hk_cat',
			'vm_import_max_hk_prod',
			'vm_import_last_vm_cat',
			'vm_import_last_vm_prod',
			'vm_import_last_vm_user',
			'vm_import_last_vm_order',
			'vm_import_last_vm_pfile',
			'vm_import_last_vm_coupon',
			'vm_import_last_vm_taxrate',
			'vm_import_last_vm_manufacturer',
			'vm_import_last_vm_review'
		);
		$this->db->setQuery('SELECT config_namekey, config_value FROM `#__hikashop_config` WHERE config_namekey IN ('."'".implode("','",$data)."'".');');
		$options = $this->db->loadObjectList();

		$this->options = new stdClass();
		if (!empty($options))
		{
			foreach($options as $o) {
				if( substr($o->config_namekey, 0, 10) == 'vm_import_' ) {
					$nk = substr($o->config_namekey, 10);
				} else {
					$nk = $o->config_namekey;
				}

				$this->options->$nk = $o->config_value;
			}
		}

		$this->options->uploadfolder = rtrim(JPath::clean(html_entity_decode($this->options->uploadfolder)),DS.' ').DS;
		if(!preg_match('#^([A-Z]:)?/.*#',$this->options->uploadfolder)){
			if(!$this->options->uploadfolder[0]=='/' || !is_dir($this->options->uploadfolder)){
				$this->options->uploadfolder = JPath::clean(HIKASHOP_ROOT.DS.trim($this->options->uploadfolder,DS.' ').DS);
			}
		}

		$this->options->uploadsecurefolder = rtrim(JPath::clean(html_entity_decode($this->options->uploadsecurefolder)),DS.' ').DS;
		if(!preg_match('#^([A-Z]:)?/.*#',$this->options->uploadsecurefolder)){
			if(!$this->options->uploadsecurefolder[0]=='/' || !is_dir($this->options->uploadsecurefolder)){
				$this->options->uploadsecurefolder = JPath::clean(HIKASHOP_ROOT.DS.trim($this->options->uploadsecurefolder,DS.' ').DS);
			}
		}

		if( !isset($this->options->state) ) {
			$this->options->state = 0;
			$this->options->current = 0;
			$this->options->tax_id = 0;
			$this->options->last_vm_coupon = 0;
			$this->options->last_vm_pfile = 0;
			$this->options->last_vm_taxrate = 0;
			$this->options->last_vm_manufacturer = 0;
			$this->options->last_vm_review = 0;

			$element = 'product';
			$categoryClass = hikashop_get('class.category');
			$categoryClass->getMainElement($element);
			$this->options->main_cat_id = $element;

			$this->db->setQuery("SELECT max(category_id) as 'max' FROM `#__hikashop_category`;");
			$data = $this->db->loadObjectList();
			$this->options->max_hk_cat = (int)($data[0]->max);

			$this->db->setQuery("SELECT max(product_id) as 'max' FROM `#__hikashop_product`;");
			$data = $this->db->loadObjectList();
			$this->options->max_hk_prod = (int)($data[0]->max);

			$query='SHOW TABLES LIKE '.$this->db->Quote($this->db->getPrefix().substr(hikashop_table('vm_cat'),3));
			$this->db->setQuery($query);
			$table = $this->db->loadResult();
			if(!empty($table)){
				$this->db->setQuery("SELECT max(vm_id) as 'max' FROM `#__hikashop_vm_cat`;");
				$data = $this->db->loadObjectList();
				if( $data ) {
					$this->options->last_vm_cat = (int)($data[0]->max);
				} else {
					$this->options->last_vm_cat = 0;
				}

				$this->db->setQuery("SELECT max(vm_id) as 'max' FROM `#__hikashop_vm_prod`;");
				$data = $this->db->loadObjectList();
				if( $data ) {
					$this->options->last_vm_prod = (int)($data[0]->max);
				} else {
					$this->options->last_vm_prod = 0;
				}
				$this->db->setQuery("SELECT max(order_vm_id) as 'max' FROM `#__hikashop_order`;");
				$data = $this->db->loadObjectList();
				$this->options->last_vm_order = (int)($data[0]->max);
			}else{
				$this->options->last_vm_cat = 0;
				$this->options->last_vm_prod = 0;
				$this->options->last_vm_order = 0;
			}

			$this->options->last_vm_user = 0;

			$sql = 'INSERT IGNORE INTO `#__hikashop_config` (`config_namekey`,`config_value`,`config_default`) VALUES '.
				"('vm_import_state',".$this->options->state.",".$this->options->state.")".
				",('vm_import_current',".$this->options->current.",".$this->options->current.")".
				",('vm_import_tax_id',".$this->options->tax_id.",".$this->options->tax_id.")".
				",('vm_import_main_cat_id',".$this->options->main_cat_id.",".$this->options->main_cat_id.")".
				",('vm_import_max_hk_cat',".$this->options->max_hk_cat.",".$this->options->max_hk_cat.")".
				",('vm_import_max_hk_prod',".$this->options->max_hk_prod.",".$this->options->max_hk_prod.")".
				",('vm_import_last_vm_cat',".$this->options->last_vm_cat.",".$this->options->last_vm_cat.")".
				",('vm_import_last_vm_prod',".$this->options->last_vm_prod.",".$this->options->last_vm_prod.")".
				",('vm_import_last_vm_user',".$this->options->last_vm_user.",".$this->options->last_vm_user.")".
				",('vm_import_last_vm_order',".$this->options->last_vm_order.",".$this->options->last_vm_order.")".
				",('vm_import_last_vm_pfile',".$this->options->last_vm_pfile.",".$this->options->last_vm_pfile.")".
				",('vm_import_last_vm_coupon',".$this->options->last_vm_coupon.",".$this->options->last_vm_coupon.")".
				",('vm_import_last_vm_taxrate',".$this->options->last_vm_taxrate.",".$this->options->last_vm_taxrate.")".
				",('vm_import_last_vm_manufacturer',".$this->options->last_vm_manufacturer.",".$this->options->last_vm_manufacturer.")".
				",('vm_import_last_vm_review',".$this->options->last_vm_review.",".$this->options->last_vm_review.")".
				';';
			$this->db->setQuery($sql);
			$this->db->query();
		}
	}

	function loadVmConfigs()
	{
		$configstring = '';

		$this->db->setQuery('SELECT config FROM `'.$this->vmprefix.'virtuemart_configs`;');
		$data = $this->db->loadObjectList();
		$configstring = $data[0]->config;
		$paths = $this->parseConfig($configstring);
		foreach ($paths as $key => $value)
		{
			switch ($key)
			{
				case 'media_category_path' :
					$this->copyCatImgDir = HIKASHOP_ROOT.$value;
					break;
				case 'media_product_path' :
					$this->copyImgDir = HIKASHOP_ROOT.$value;
					$this->copyImgValue = $value;
					break;
				case 'media_manufacturer_path' :
					$this->copyManufDir = HIKASHOP_ROOT.$value;
					break;
				default :
					break;
			}
		}
	}

	function parseConfig($string)
	{
		$arraypath = array(
			'media_category_path',
			'media_product_path',
			'media_manufacturer_path'
		);
		$paths =array();

		$firstparse = explode('|', $string);
		foreach ($firstparse as $fp)
		{
			$secondparse = explode('=', $fp);
			if (in_array($secondparse[0],$arraypath))
			{
				$thirdparse = explode('"', $secondparse[1]);
				$paths[$secondparse[0]] = $thirdparse[1];
			}
		}
		return $paths;
	}

	function finishImport()
	{
		if( $this->db == null )
			return false;

		$this->db->setQuery("SELECT max(category_id) as 'max' FROM `#__hikashop_category`;");
		$data = $this->db->loadObjectList();
		$this->options->max_hk_cat = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(product_id) as 'max' FROM `#__hikashop_product`;");
		$data = $this->db->loadObjectList();
		$this->options->max_hk_prod = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(vm_id) as 'max' FROM `#__hikashop_vm_cat`;");
		$data = $this->db->loadObjectList();
		$this->options->last_vm_cat = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(vm_id) as 'max' FROM `#__hikashop_vm_prod`;");
		$data = $this->db->loadObjectList();
		$this->options->last_vm_prod = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(virtuemart_user_id) as 'max' FROM `".$this->vmprefix."virtuemart_userinfos`;");
		$data = $this->db->loadObjectList();
		$this->options->last_vm_user = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(order_vm_id) as 'max' FROM `#__hikashop_order`;");
		$data = $this->db->loadObjectList();
		$this->options->last_vm_order = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(vmpm.virtuemart_media_id) as 'max' FROM `".$this->vmprefix."virtuemart_products` vmp INNER JOIN `".$this->vmprefix."virtuemart_product_medias` vmpm ON vmp.virtuemart_product_id = vmpm.virtuemart_product_id INNER JOIN `".$this->vmprefix."virtuemart_medias` vmm ON vmpm.virtuemart_media_id = vmm.virtuemart_media_id;");
		$data = $this->db->loadObject();
		$this->options->last_vm_pfile = (int)($data->max);

		$this->db->setQuery("SELECT max(virtuemart_coupon_id) as 'max' FROM `".$this->vmprefix."virtuemart_coupons`;");
		$data = $this->db->loadObject();
		$this->options->last_vm_coupon = (int)($data->max);

		$this->db->setQuery("SELECT max(virtuemart_calc_id) as 'max' FROM `".$this->vmprefix."virtuemart_calcs`;");
		$data = $this->db->loadObject();
		$this->options->last_vm_taxrate = (int)($data->max);

		$this->db->setQuery("SELECT max(virtuemart_manufacturer_id) as 'max' FROM `".$this->vmprefix."virtuemart_manufacturers`;");
		$data = $this->db->loadObjectList();
		$this->options->last_vm_manufacturer = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(virtuemart_rating_review_id) as 'max' FROM `".$this->vmprefix."virtuemart_rating_reviews`;");
		$data = $this->db->loadObject();
		$this->options->last_vm_review = (int)($data->max);


		$this->options->state = (MAX_IMPORT_ID+1);
		$query = 'REPLACE INTO `#__hikashop_config` (`config_namekey`,`config_value`,`config_default`) VALUES '.
				"('vm_import_state',".$this->options->state.",".$this->options->state.")".
				",('vm_import_max_hk_cat',".$this->options->max_hk_cat.",".$this->options->max_hk_cat.")".
				",('vm_import_max_hk_prod',".$this->options->max_hk_prod.",".$this->options->max_hk_prod.")".
				",('vm_import_last_vm_cat',".$this->options->last_vm_cat.",".$this->options->last_vm_cat.")".
				",('vm_import_last_vm_prod',".$this->options->last_vm_prod.",".$this->options->last_vm_prod.")".
				",('vm_import_last_vm_user',".$this->options->last_vm_user.",".$this->options->last_vm_user.")".
				",('vm_import_last_vm_order',".$this->options->last_vm_order.",".$this->options->last_vm_order.")".
				",('vm_import_last_vm_pfile',".$this->options->last_vm_pfile.",".$this->options->last_vm_pfile.")".
				",('vm_import_last_vm_coupon',".$this->options->last_vm_coupon.",".$this->options->last_vm_coupon.")".
				",('vm_import_last_vm_taxrate',".$this->options->last_vm_taxrate.",".$this->options->last_vm_taxrate.")".
				",('vm_import_last_vm_manufacturer',".$this->options->last_vm_manufacturer.",".$this->options->last_vm_manufacturer.")".
				",('vm_import_last_vm_review',".$this->options->last_vm_review.",".$this->options->last_vm_review.")".
				';';
		$this->db->setQuery($query);
		$this->db->query();

		echo '<p'.$this->titlefont.'>Import finished !</p>';
		$class = hikashop_get('class.plugins');
		$infos = $class->getByName('system','vm_redirect');
		if($infos){
			$pkey = reset($class->pkeys);
			if(!empty($infos->$pkey)){
				if(version_compare(JVERSION,'1.6','<')){
					$url = JRoute::_('index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]='.$infos->$pkey);
				}else{
					$url = JRoute::_('index.php?option=com_plugins&view=plugin&layout=edit&extension_id='.$infos->$pkey);
				}
				echo '<p>You can publish the <a'.$this->linkstyle.' href="'.$url.'">VirtueMart Fallback Redirect Plugin</a> so that your old VirtueMart links are automatically redirected to HikaShop pages and thus not loose the ranking of your content on search engines.</p>';
			}
		}
	}

	function createTables()
	{
		if( $this->db == null )
			return false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 1 :</span> Initialization Tables</p>';

		$query='SHOW TABLES LIKE '.$this->db->Quote($this->db->getPrefix().substr(hikashop_table('vm_cat'),3));
		$this->db->setQuery($query);
		$table = $this->db->loadResult();

		if( empty($table) )
		{
			$this->db->setQuery("CREATE TABLE IF NOT EXISTS `#__hikashop_vm_prod` (`vm_id` int(10) unsigned NOT NULL DEFAULT '0', `hk_id` int(11) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`vm_id`)) ENGINE=MyISAM");
			$this->db->query();
			$this->db->setQuery("CREATE TABLE IF NOT EXISTS `#__hikashop_vm_cat` (`vm_id` int(10) unsigned NOT NULL DEFAULT '0', `hk_id` int(11) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`vm_id`)) ENGINE=MyISAM");
			$this->db->query();

			$databaseHelper = hikashop_get('helper.database');
			$databaseHelper->addColumns('address','`address_vm_order_info_id` INT(11) NULL');
			$databaseHelper->addColumns('order','`order_vm_id` INT(11) NULL');
			$databaseHelper->addColumns('order','INDEX ( `order_vm_id` )');
			$databaseHelper->addColumns('taxation','`tax_vm_id` INT(11) NULL');

			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> All table created</p>';
		}
		else
		{
			echo '<p>Tables have been already created.</p>';
		}

		return true;
	}

	function importTaxes()
	{
		if( $this->db == null )
			return false;

		$ret = false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 2 :</span> Import Taxes<p>';


		$buffTable=$this->vmprefix."virtuemart_calcs";
		$data = array(
			'tax_namekey' => "CONCAT('VM_TAX_', vmtr.virtuemart_calc_id)",
			'tax_rate' => 'vmtr.calc_value'
		);
		$sql = 'INSERT IGNORE INTO `#__hikashop_tax` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `'.$buffTable.'` AS vmtr '.
			'WHERE vmtr.virtuemart_calc_id > ' . (int)$this->options->last_vm_taxrate;

		$this->db->setQuery($sql);
		$this->db->query();

		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported taxes: ' . $total . '</p>';

		$element = 'tax';
		$categoryClass = hikashop_get('class.category');
		$categoryClass->getMainElement($element);

		$data = array(
			'category_type' => "'tax'",
			'category_name' => "case when vmcs.country_name IS NULL then 'Tax imported (no country)' else CONCAT('Tax imported (', vmcs.country_name,')') end",
			'category_published' => '1',
			'category_parent_id' => $element,
			'category_namekey' => "case when hkz.zone_id IS NULL then CONCAT('VM_TAX_', vmtr.virtuemart_calc_id,'_0') else CONCAT('VM_TAX_', vmtr.virtuemart_calc_id,'_',hkz.zone_id) end",
		);
		$sql = 'INSERT IGNORE INTO `#__hikashop_category` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `'.$this->vmprefix.'virtuemart_calcs` vmtr '.
			"LEFT JOIN `".$this->vmprefix."virtuemart_calc_countries` vmcc ON vmtr.virtuemart_calc_id = vmcc.virtuemart_calc_id " .
			"LEFT JOIN `".$this->vmprefix."virtuemart_countries` vmcs ON vmcc.virtuemart_country_id = vmcs.virtuemart_country_id ".
			"LEFT JOIN `#__hikashop_zone` hkz ON vmcs.country_3_code = hkz.zone_code_3 AND hkz.zone_type = 'country' ".
			"WHERE vmtr.virtuemart_calc_id >" . $this->options->last_vm_taxrate;

		$this->db->setQuery($sql);
		$this->db->query();

		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported Taxes Categories: ' . $total . '</p>';

		if( $total > 0 ) {
			$this->options->max_hk_cat += $total;
			$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'vm_import_max_hk_cat'; ");
			$this->db->query();
			$this->importRebuildTree();
		}

		$data = array(
			'zone_namekey' => "case when hkz.zone_namekey IS NULL then '' else hkz.zone_namekey end",
			'category_namekey' => "case when hkz.zone_id IS NULL then CONCAT('VM_TAX_', vmtr.virtuemart_calc_id,'_0') else  CONCAT('VM_TAX_', vmtr.virtuemart_calc_id,'_',hkz.zone_id) end",
			'tax_namekey' => "CONCAT('VM_TAX_', vmtr.virtuemart_calc_id)",
			'taxation_published' => '1',
			'taxation_type' => "''",
			'tax_vm_id' => 'vmtr.virtuemart_calc_id'
		);
		$sql = 'INSERT IGNORE INTO `#__hikashop_taxation` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `'.$this->vmprefix.'virtuemart_calcs` vmtr '.
			"LEFT JOIN `".$this->vmprefix."virtuemart_calc_countries` vmcc ON vmtr.virtuemart_calc_id = vmcc.virtuemart_calc_id " .
			"LEFT JOIN `".$this->vmprefix."virtuemart_countries` vmcs ON vmcc.virtuemart_country_id = vmcs.virtuemart_country_id ".
			"LEFT JOIN `".$this->vmprefix."hikashop_zone` hkz ON vmcs.country_3_code = hkz.zone_code_3 AND hkz.zone_type = 'country' ".
			"WHERE vmtr.virtuemart_calc_id >" . $this->options->last_vm_taxrate;

		$this->db->setQuery($sql);
		$this->db->query();

		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported Taxations: ' . $total . '</p>';

		$ret = true;
		return $ret;
	}

	function importManufacturers() {
		if( $this->db == null )
			return false;
		$ret = false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 3 :</span> Import Manufacturers</p>';

		$element = 'manufacturer';
		$categoryClass = hikashop_get('class.category');
		$categoryClass->getMainElement($element);

		$buffTable=$this->vmprefix."virtuemart_manufacturers_".$this->vm_current_lng;

		$data = array(
			'category_type' => "'manufacturer'",
			'category_name' => "vmm.mf_name ",
			'category_published' => '1',
			'category_parent_id' => $element,
			'category_namekey' => "CONCAT('VM_MANUFAC_', vmm.virtuemart_manufacturer_id )",
			'category_description' => 'vmm.mf_desc',
			'category_menu' => 'vmm.virtuemart_manufacturer_id'
		);

		$sql = 'INSERT IGNORE INTO `#__hikashop_category` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `'.$buffTable.'` vmm '.
			'WHERE vmm.virtuemart_manufacturer_id > ' . (int)$this->options->last_vm_manufacturer;

		$this->db->setQuery($sql);
		$this->db->query();

		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported Manufacturers : ' . $total . '</p>';

		if( $total > 0 )
		{
			$this->options->max_hk_cat += $total;
			$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'vm_import_max_hk_cat'; ");
			$this->db->query();
			$this->importRebuildTree();
		}
		$ret = true;
		return $ret;
	}

	function importCategories()
	{
		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 4 :</span> Import General Categories</p>';

		if( $this->db == null )
			return false;

		jimport('joomla.filesystem.file');
		$categoryClass = hikashop_get('class.category');

		$rebuild = false;
		$ret = false;
		$offset = $this->options->current;
		$count = 100;
		$max = 0;

		$statuses = array(
			'P' => 'created',
			'C' => 'confirmed',
			'X' => 'cancelled',
			'R'=> 'refunded' ,
			'S' => 'shipped'
		);
		$this->db->setQuery("SELECT category_keywords, category_parent_id FROM `#__hikashop_category` WHERE category_type = 'status' AND category_name = 'confirmed'");
		$data = $this->db->loadObject();
		$status_category = $data->category_parent_id;
		if( $data->category_keywords != 'C' ) {
			foreach($statuses as $k => $v) {
				$this->db->setQuery("UPDATE `#__hikashop_category` SET category_keywords = '".$k."' WHERE category_type = 'status' AND category_name = '".$v."'; ");
				$this->db->query();
			}
		}

		$this->db->setQuery("SELECT order_status_code, order_status_name, order_status_description FROM `".$this->vmprefix."virtuemart_orderstates` WHERE order_status_name NOT IN ('".implode("','",$statuses)."');");
		$data = $this->db->loadObjectList();

		if( count($data) > 0 )
		{
			$sql0 = 'INSERT IGNORE INTO `#__hikashop_category` (`category_id`,`category_parent_id`,`category_type`,`category_name`,`category_description`,`category_published`,'.
				'`category_namekey`,`category_access`,`category_menu`,`category_keywords`) VALUES ';

			$id = $this->options->max_hk_cat + 1;
			$sep = '';
			foreach($data as $c) {

				$d = array(
					$id++,
					$status_category,
					"'status'",
					$this->db->quote(strtolower(JText::_($c->order_status_name))),
					$this->db->quote( $c->order_status_description ),
					'1',
					$this->db->quote('status_vm_import_'.strtolower(str_replace(' ','_',JText::_($c->order_status_name)))),
					"'all'",
					'0',
					$this->db->quote( $c->order_status_code )
				);

				$sql0 .= $sep.'('.implode(',',$d).')';
				$sep = ',';
			}

			$this->db->setQuery($sql0);
			$this->db->query();
			$total = $this->db->getAffectedRows();

			if( $total > 0 )
			{
				echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported order status categories : ' . $total . '</p>';
				$rebuild = true;

				$this->options->max_hk_cat = $id;
				$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'vm_import_max_hk_cat'; ");
				$this->db->query();
				$sql0 = '';
			}
			else
			{
				echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported order status categories : 0</p>';
			}

		}

		$buffTable=$this->vmprefix."virtuemart_categories_".$this->vm_current_lng;

		$this->db->setQuery('SELECT *, vmc.virtuemart_category_id as vmid, hkvm.vm_id as vm_cat_id, hkvmp.vm_id as vm_parent_id FROM `'.$this->vmprefix.'virtuemart_categories` vmc '.
				"LEFT JOIN `".$buffTable."` vmceg ON vmc.virtuemart_category_id = vmceg.virtuemart_category_id ".
				"LEFT JOIN `".$this->vmprefix."virtuemart_category_medias` vmcm ON vmceg.virtuemart_category_id = vmcm.virtuemart_category_id ".
				"LEFT JOIN `".$this->vmprefix."virtuemart_medias` vmm ON vmcm.virtuemart_media_id = vmm.virtuemart_media_id ".
				'LEFT JOIN `'.$this->vmprefix.'virtuemart_category_categories` vmcc ON vmceg.virtuemart_category_id = vmcc.category_child_id '.
				'LEFT JOIN `#__hikashop_vm_cat` hkvm ON vmc.virtuemart_category_id = hkvm.vm_id '.
				'LEFT JOIN `#__hikashop_vm_cat` hkvmp ON vmcc.category_parent_id = hkvmp.vm_id '.
				'ORDER BY category_parent_id ASC, vmc.ordering ASC, vmc.virtuemart_category_id ASC LIMIT '.(int)$offset.', '.(int)$count.';');
		$data = $this->db->loadObjectList();

		$max = $offset + count($data);

		$total = count($data);
		if( $total == 0 ) {
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported category : 0</p>';
			if( $rebuild )
				$this->importRebuildTree();
			return true;
		}


		$sql0 = 'INSERT IGNORE INTO `#__hikashop_category` (`category_id`,`category_parent_id`,`category_type`,`category_name`,`category_description`,`category_published`,'.
			'`category_ordering`,`category_namekey`,`category_created`,`category_modified`,`category_access`,`category_menu`) VALUES ';
		$sql1 = 'INSERT IGNORE INTO `#__hikashop_vm_cat` (`vm_id`,`hk_id`) VALUES ';
		$sql2 = 'INSERT INTO `#__hikashop_file` (`file_name`,`file_description`,`file_path`,`file_type`,`file_ref_id`) VALUES ';
		$doSql2 = false;
		$doSql1 = false;

		$i = $this->options->max_hk_cat + 1;
		$ids = array( 0 => $this->options->main_cat_id);
		$sep = '';

		foreach($data as $c)
		{
			if( !empty($c->vm_cat_id) )
			{
				$ids[(int)$c->vmid] = $c->hk_id;
			}
			else
			{
				$doSql1 = true;

				$ids[(int)$c->vmid] = $i;
				$sql1 .= $sep.'('.(int)$c->vmid.','.$i.')';

				$i++;

				$sep = ',';
			}
		}
		$sql1 .= ';';

		$sep = '';
		$sep2 = '';
		$doQuery = false;

		foreach($data as $c)
		{
			if( !empty($c->vm_cat_id) )
				continue;

			$doQuery = true;
			$id = $ids[(int)$c->vmid];

			if(!empty($ids[(int)$c->category_parent_id]))
				$pid = (int)$ids[(int)$c->category_parent_id];
			else if(!empty($c->vm_parent_id))
				$pid = (int)$c->vm_parent_id;
			else
				$pid = $ids[0];

			$element = new stdClass();
			$element->category_id = $id;
			$element->category_parent_id = $pid; //See also category_parent_id
			$element->category_name = $c->category_name;
			$nameKey = $categoryClass->getNameKey($element);

			$d = array(
				$id,
				$pid,
				"'product'",
				$this->db->quote($c->category_name),
				$this->db->quote($c->category_description),
				'1',
				(int)@$c->ordering,
				$this->db->quote($nameKey),
				$this->db->quote(strtotime($c->created_on)),
				$this->db->quote(strtotime($c->modified_on)),
				"'all'",
				'0'
			);

			$sql0 .= $sep.'('.implode(',',$d).')';

			$result = false;
			if(!empty($c->file_meta) ) {
				$file_name = str_replace('\\','/',$c->file_meta);
				if( strpos($file_name,'/') !== false ) {
					$file_name = substr($file_name, strrpos($file_name,'/'));
				}
				$result = $this->copyFile($this->copyCatImgDir,$c->file_meta, $this->options->uploadfolder.$file_name);
				if($result){
					$sql2 .= $sep2."('','',".$this->db->Quote($file_name).",'category',".$id.')';
					$sep2 = ',';
					$max = $c->virtuemart_category_id;
				}
			}
			if(!$result && !empty($c->file_url)){
				$file_name = str_replace('\\','/',$c->file_url);
				if( strpos($file_name,'/') !== false ) {
					$file_name = substr($file_name, strrpos($file_name,'/'));
				}
				$result = $this->copyFile($this->copyCatImgDir,$c->file_url, $this->options->uploadfolder.$file_name);

				if($result) {
					$sql2 .= $sep2."('','',".$this->db->Quote($file_name).",'category',".$id.')';
					$sep2 = ',';
					$max = $c->virtuemart_category_id;
				} else if(!empty($c->file_meta)) {
					$file_name = str_replace('\\','/',$c->file_meta);
					if( strpos($file_name,'/') !== false ) {
						$file_name = substr($file_name, strrpos($file_name,'/'));
					}
					$sql2 .= $sep2."('','',".$this->db->Quote($file_name).",'category',".$id.')';
					$sep2 = ',';
					$max = $c->virtuemart_category_id;
				}
			}
			if($result)
				$doSql2 = true;

			$sep = ',';
		}

		$sql0 .= ';';

		if ($doQuery)
		{
			$this->db->setQuery($sql0);
			$this->db->query();
			$total = $this->db->getAffectedRows();
		}
		else
			$total = 0;
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported Categories : ' . $total . '</p>';


		if( isset($total) && $total > 0)
		{
			$rebuild = true;
			$this->options->max_hk_cat += $total + 1;
			$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'vm_import_max_hk_cat'; ");
			$this->db->query();
		}

		if ($doSql1)
		{
			$this->db->setQuery($sql1);
			$this->db->query();
			$total = $this->db->getAffectedRows();
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Fallback links : ' . $total . '</p>';
		}
		else
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Fallback links : 0</p>';

		if( $doSql2 )
		{
			$sql2 .= ';';
			$this->db->setQuery($sql2);
			$this->db->query();
			$total = $this->db->getAffectedRows();
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Categories files : ' . $total . '</p>';
		}
		else
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Categories files : 0</p>';


		if( $rebuild )
			$this->importRebuildTree();
		$this->options->current = $max;

		if( $max > 0 ) {
			echo '<p>Copying files...(last proccessed product id: ' . $max . ')</p>';
			$this->options->current = $max;
			$this->refreshPage = true;
			return $ret;
		}

		$ret = true;
		return $ret;
	}


	function importProducts() {
		if( $this->db == null )
			return false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 5 :</span> Import Products</p>';

		jimport('joomla.filesystem.file');
		$categoryClass = hikashop_get('class.category');

		$ret = false;
		$count = 100;
		$offset = $this->options->current;
		$max = 0;

		$this->db->setQuery(
			'SELECT vmp.virtuemart_product_id, vmm.file_meta, vmm.file_url '.
			'FROM `'.$this->vmprefix.'virtuemart_products` vmp '.
			"INNER JOIN `".$this->vmprefix."virtuemart_product_medias` vmpm ON vmp.virtuemart_product_id = vmpm.virtuemart_product_id ".
			"INNER JOIN `".$this->vmprefix."virtuemart_medias` vmm ON vmpm.virtuemart_media_id = vmm.virtuemart_media_id ".
			'LEFT JOIN `#__hikashop_vm_prod` hkprod ON vmp.virtuemart_product_id = hkprod.vm_id '.
			"WHERE vmp.virtuemart_product_id > ".$offset." AND hkprod.hk_id IS NULL AND ((vmm.file_meta IS NOT NULL AND vmm.file_meta <> '') OR vmm.file_url <> '' )".
			'ORDER BY vmp.virtuemart_product_id ASC LIMIT '.$count.';'
		);

		$data = $this->db->loadObjectList();

		$meta_files = array();
		if (!empty($data))
		{
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Copying products images... </p>';
			foreach($data as $c) {
				$result = false;
				if( !empty($c->file_meta) ) {
					$file_name = str_replace('\\','/',$c->file_meta);
					if( strpos($file_name,'/') !== false ) {
						$file_name = substr($file_name, strrpos($file_name,'/'));
					}
					$result = $this->copyFile($this->copyImgDir,$c->file_meta, $this->options->uploadfolder.$file_name);
					if($result){
						$meta_files[] = $this->db->Quote($c->file_meta);
					}
				}
				if(!$result && !empty($c->file_url)){
					$file_name = str_replace('\\','/',$c->file_url);
					if( strpos($file_name,'/') !== false ) {
						$file_name = substr($file_name, strrpos($file_name,'/'));
					}
					if(strpos($this->copyImgValue, trim($c->file_url, '/')) === 0)
						$c->file_url = trim(substr(trim($c->file_url, '/'), strlen($this->copyImgValue)), '/');
					$result = $this->copyFile($this->copyImgDir,$c->file_url, $this->options->uploadfolder.$file_name);
					if(!$result && !empty($c->file_meta)){ //if we couldn't copy the image from the file_url, we still add the image name from file_meta and then the user can copy the files manually
						$meta_files[] = $this->db->Quote($c->file_meta);
					}
				}
			}
		}
		if(empty($meta_files) || !count($meta_files)){
			$meta_files = "''";
		}else{
			$meta_files = implode(',',$meta_files);
		}

		$buffTable=$this->vmprefix."virtuemart_products_".$this->vm_current_lng;

		$data = array(
			'product_name' => 'vmpeg.product_name',
			'product_description' => "CONCAT(vmpeg.product_s_desc,'<hr id=\"system-readmore\"/>',vmpeg.product_desc)",
			'product_quantity' => 'case when vmp.product_in_stock IS NULL or vmp.product_in_stock < 0 then 0 else vmp.product_in_stock end',
			'product_code' => 'case when vmp.product_sku IS NULL or vmp.product_sku="" then MD5(vmp.virtuemart_product_id) else vmp.product_sku end',
			'product_published' => "vmp.published",
			'product_hit' => '0',
			'product_created' => "UNIX_TIMESTAMP(vmp.created_on)",
			'product_modified' => 'UNIX_TIMESTAMP(vmp.modified_on)',
			'product_sale_start' => 'UNIX_TIMESTAMP(vmp.product_available_date)',
			'product_tax_id' => 'hkc.category_id',
			'product_type' => "'main'",
			'product_url' => 'vmp.product_url',
			'product_weight' => 'vmp.product_weight',
			'product_weight_unit' => "LOWER(vmp.product_weight_uom)",
			'product_dimension_unit' => "LOWER(vmp.product_lwh_uom)",
			'product_sales' => 'vmp.product_sales',
			'product_width' => 'vmp.product_width',
			'product_length' => 'vmp.product_length',
			'product_height' => 'vmp.product_height',
		);

		$sql1 = 'INSERT IGNORE INTO `#__hikashop_product` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'virtuemart_products` AS vmp '.
			'INNER JOIN `'.$buffTable.'` vmpeg ON vmp.virtuemart_product_id = vmpeg.virtuemart_product_id '.
			'LEFT JOIN `'.$this->vmprefix.'virtuemart_product_prices` vmpp ON vmpeg.virtuemart_product_id = vmpp.virtuemart_product_id '.
			'LEFT JOIN `#__hikashop_taxation` hkt ON hkt.tax_vm_id = vmpp.product_tax_id '.
			'LEFT JOIN `#__hikashop_category` hkc ON hkc.category_namekey = hkt.category_namekey '.
			'LEFT JOIN `#__hikashop_vm_prod` AS hkp ON vmp.virtuemart_product_id = hkp.vm_id '.
			'WHERE hkp.hk_id IS NULL AND vmp.virtuemart_product_id > '.$offset.' '.
			'GROUP BY vmp.virtuemart_product_id '.
			'ORDER BY vmp.virtuemart_product_id ASC LIMIT '.$count.';';

		$data = array(
			'vm_id' => 'vmp.virtuemart_product_id',
			'hk_id' => 'hkp.product_id'
		);

		$sql2 = 'INSERT IGNORE INTO `#__hikashop_vm_prod` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'virtuemart_products` AS vmp '.
			'INNER JOIN `#__hikashop_product` AS hkp ON CONVERT(vmp.product_sku USING utf8) = CONVERT(hkp.product_code USING utf8) '.
			'LEFT JOIN `#__hikashop_vm_prod` hkvm ON vmp.virtuemart_product_id = hkvm.vm_id '.
			'WHERE hkvm.hk_id IS NULL AND vmp.virtuemart_product_id > '.$offset.' '.
			'ORDER BY vmp.virtuemart_product_id ASC LIMIT '.$count.';';

		$sql3 = 'UPDATE `#__hikashop_product` AS hkp '.
			'INNER JOIN `'.$this->vmprefix.'virtuemart_products` AS vmp ON CONVERT(vmp.product_sku USING utf8) = CONVERT(hkp.product_code USING utf8) '.
			'INNER JOIN `#__hikashop_vm_prod` AS hkvm ON vmp.product_parent_id = hkvm.vm_id '.
			'SET hkp.product_parent_id = hkvm.hk_id;';

		$data = array(
			'file_name' => "''",
			'file_description' => "''",
			'file_path' => "case when vmm.file_meta IN (".$meta_files.") then SUBSTRING_INDEX(vmm.file_meta,'/',-1) else SUBSTRING_INDEX(vmm.file_url,'/',-1) end",
			'file_type' => "'product'",
			'file_ref_id' => 'hkvm.hk_id'
		);

		$sql4 = 'INSERT IGNORE INTO `#__hikashop_file` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'virtuemart_products` AS vmp '.
			"INNER JOIN `#__hikashop_vm_prod` AS hkvm ON vmp.virtuemart_product_id = hkvm.vm_id ".
			"INNER JOIN `".$this->vmprefix."virtuemart_product_medias` vmpm ON hkvm.vm_id = vmpm.virtuemart_product_id ".
			"INNER JOIN `".$this->vmprefix."virtuemart_medias` vmm ON vmpm.virtuemart_media_id = vmm.virtuemart_media_id ".
			'WHERE vmp.virtuemart_product_id > '.$this->options->last_vm_prod." AND (vmm.file_meta <> '' OR vmm.file_url <> '') ";

		$sql5 = 'UPDATE `#__hikashop_product` AS hkp '.
			'INNER JOIN `#__hikashop_vm_prod` AS hkvm ON hkp.product_id = hkvm.hk_id '.
			'INNER JOIN `'.$this->vmprefix.'virtuemart_product_manufacturers` AS vmpm ON vmpm.virtuemart_product_id = hkvm.vm_id '.
			"INNER JOIN `#__hikashop_category` AS hkc ON hkc.category_type = 'manufacturer' AND vmpm.virtuemart_manufacturer_id = hkc.category_menu ".
			'SET hkp.product_manufacturer_id = hkc.category_id '.
			'WHERE vmpm.virtuemart_manufacturer_id > '.$this->options->last_vm_manufacturer.' OR vmpm.virtuemart_product_id > '.$this->options->last_vm_prod.';';

		$this->db->setQuery($sql1);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Inserted products: ' . $total . '</p>';

		$this->db->setQuery($sql2);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Fallback links: ' . $total . '</p>';

		$this->db->setQuery('SELECT MAX(vm_id) FROM `#__hikashop_vm_prod`');
		$this->db->query();
		$max = (int)$this->db->loadResult();

		$this->db->setQuery($sql3);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Updating products for parent links: ' . $total . '</p>';

		$this->db->setQuery($sql4);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Inserted products files: ' . $total . '</p>';

		$this->db->setQuery($sql5);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Updating products manufacturers: ' . $total . '</p>';

		if( $max > 0 && $max > $offset) {
			echo '<p>Copying  files...(last proccessed product id: ' . $max . ')</p>';
			$this->options->current = $max;
			$this->refreshPage = true;
			return $ret;
		}

		$ret = true;
		return $ret;
	}

	function importProductPrices()
	{
		if( $this->db == null )
			return false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 6 :</span> Import Product Prices</p>';

		$ret = false;
		$cpt = 0;

		$this->db->setQuery('INSERT IGNORE INTO #__hikashop_price (`price_product_id`,`price_value`,`price_currency_id`,`price_min_quantity`,`price_access`) '
				.'SELECT hkprod.hk_Id, product_price, hkcur.currency_id, price_quantity_start, \'all\' '
				.'FROM '.$this->vmprefix.'virtuemart_product_prices vmpp '
				.'INNER JOIN #__hikashop_vm_prod hkprod ON vmpp.virtuemart_product_id = hkprod.vm_id '
				.'INNER JOIN '.$this->vmprefix.'virtuemart_currencies vmc ON vmpp.product_currency = vmc.virtuemart_currency_id '
				.'INNER JOIN #__hikashop_currency hkcur ON CONVERT(vmc.currency_code_3 USING utf8) = CONVERT( hkcur.currency_code USING utf8) '
				.'WHERE vmpp.virtuemart_product_id > ' . (int)$this->options->last_vm_prod
		);

		$ret = $this->db->query();
		$cpt = $this->db->getAffectedRows();

		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Prices imported : ' . $cpt .'</p>';
		return $ret;
	}


	function importProductCategory()
	{
		if( $this->db == null )
			return false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 7 :</span> Import Product Category</p>';

		$data = array(
			'category_id' => 'vmc.hk_id',
			'product_id' => 'vmp.hk_id',
			'ordering' => '`product_list`',
		);

		$data['ordering'] = '`ordering`';
		$sql = 'INSERT IGNORE INTO `#__hikashop_product_category` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `'.$this->vmprefix.'virtuemart_product_categories` vmpc '.
			'INNER JOIN #__hikashop_vm_cat vmc ON vmpc.virtuemart_category_id = vmc.vm_id '.
			'INNER JOIN #__hikashop_vm_prod vmp ON vmpc.virtuemart_product_id = vmp.vm_id '.
			'WHERE vmp.vm_id > ' . (int)$this->options->last_vm_prod . ' OR vmc.vm_id > ' . (int)$this->options->last_vm_cat;

		$this->db->setQuery($sql);
		$this->db->query();

		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Inserted products categories: ' . $total . '</p>';
		return true;
	}


	function importUsers()
	{
		if( $this->db == null )
			return false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 8 :</span> Import Users</p>';

		$ret = false;

		$sql0 = 'INSERT IGNORE INTO `#__hikashop_user` (`user_cms_id`,`user_email`) '.
				'SELECT u.id, u.email FROM `'.$this->vmprefix.'virtuemart_userinfos` vmui INNER JOIN `#__users` u ON vmui.virtuemart_user_id = u.id '. //?
				'LEFT JOIN `#__hikashop_user` AS hkusr ON vmui.virtuemart_user_id = hkusr.user_cms_id '.
				'WHERE hkusr.user_cms_id IS NULL;';

		$data = array(
			'address_user_id' => 'hku.user_id',
			'address_firstname' => 'vmui.first_name',
			'address_middle_name' => 'vmui.middle_name',
			'address_lastname' => 'vmui.last_name',
			'address_company' => 'vmui.company',
			'address_street' => 'CONCAT(vmui.address_1,\' \',vmui.address_2)',
			'address_post_code' => 'vmui.zip',
			'address_city' => 'vmui.city',
			'address_telephone' => 'vmui.phone_1',
			'address_telephone2' => 'vmui.phone_2',
			'address_fax' => 'vmui.fax',
			'address_state' => 'vmui.state',
			'address_country' => 'vmui.country',
			'address_published' => 4
		);

		$data['address_state'] = 'vms.state_3_code';
		$data['address_country'] = 'vmc.country_3_code';
		$sql1 = 'INSERT IGNORE INTO `#__hikashop_address` (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'virtuemart_userinfos` AS vmui '.
				"INNER JOIN `".$this->vmprefix."virtuemart_states` vms ON vmui.virtuemart_state_id = vms.virtuemart_state_id ".
				"INNER JOIN `".$this->vmprefix."virtuemart_countries` vmc ON vmui.virtuemart_country_id = vmc.virtuemart_country_id ".
				'INNER JOIN `#__hikashop_user` AS hku ON vmui.virtuemart_user_id = hku.user_cms_id '.
				'WHERE vmui.virtuemart_user_id > '.$this->options->last_vm_user.' ORDER BY vmui.virtuemart_user_id ASC;';

		$sql2 = 'UPDATE `#__hikashop_address` AS a '.
				'JOIN `#__hikashop_zone` AS hkz ON (a.address_country = hkz.zone_code_3 AND hkz.zone_type = "country") '.
				'SET address_country = hkz.zone_namekey, address_published = 3 WHERE address_published = 4;';

		$sql3 = 'UPDATE `#__hikashop_address` AS a '.
				'JOIN `#__hikashop_zone_link` AS zl ON (a.address_country = zl.zone_parent_namekey) '.
				'JOIN `#__hikashop_zone` AS hks ON (hks.zone_namekey = zl.zone_child_namekey AND hks.zone_type = "state" AND hks.zone_code_3 = a.address_state) '.
				'SET address_state = hks.zone_namekey, address_published = 2 WHERE address_published = 3;';

		$sql4 = "UPDATE `#__hikashop_address` AS a SET a.address_country = '' WHERE address_published > 3;";
		$sql5 = "UPDATE `#__hikashop_address` AS a SET a.address_state = '' WHERE address_published > 2;";
		$sql6 = 'UPDATE `#__hikashop_address` AS a SET a.address_published = 1 WHERE address_published > 1;';

		$this->db->setQuery($sql0);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported Users: ' . $total . '</p>';

		$this->db->setQuery($sql1);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported addresses: ' . $total . '</p>';

		$this->db->setQuery($sql2);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported addresses countries: ' . $total . '</p>';

		$this->db->setQuery($sql3);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported addresses states: ' . $total . '</p>';

		$this->db->setQuery($sql4);
		$this->db->query();
		$this->db->setQuery($sql5);
		$this->db->query();
		$this->db->setQuery($sql6);
		$this->db->query();

		$ret = true;

		return $ret;
	}

	function importOrders()
	{
		if( $this->db == null )
			return false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 10 :</span> Import Orders</p>';

		$ret = false;
		$offset = $this->options->current;
		$count = 100;
		$total = 0;
		$guest = 0;

		$this->db->setQuery("SELECT name FROM `".$this->vmprefix."virtuemart_userfields` WHERE name = 'tax_exemption_number' AND published = 1");

		$vat_cols = $this->db->loadObjectList();
		if( isset($vat_cols) && $vat_cols !== null && is_array($vat_cols) && count($vat_cols)>0)
			$vat_cols = 'vmui.' . $vat_cols[0]->name;
		else
			$vat_cols = "''";

		$data = array(
			'order_number' => 'vmo.order_number',
			'order_vm_id' => 'vmo.virtuemart_order_id',
			'order_user_id' => 'hkusr.user_id',
			'order_status' => 'hkc.category_name',
			'order_discount_code' => 'vmo.coupon_code',
			'order_discount_price' => 'vmo.coupon_discount',
			'order_created' => 'UNIX_TIMESTAMP(vmo.created_on)',
			'order_ip' => 'vmo.ip_address',
			'order_currency_id' => 'hkcur.currency_id',
			'order_shipping_price' => 'vmo.order_shipment',
			'order_shipping_method' => "'vm import'",
			'order_shipping_id' => '1',
			'order_payment_id' => 0,
			'order_payment_method' => "'vm import'",
			'order_full_price' => 'vmo.order_total',
			'order_modified' => 'UNIX_TIMESTAMP(vmo.modified_on)',
			'order_partner_id' => 0,
			'order_partner_price' => 0,
			'order_partner_paid' => 0,
			'order_type' => "'sale'",
			'order_partner_currency_id' => 0,
			'order_shipping_tax' => 'vmo.order_shipment_tax',
			'order_discount_tax' => 0
		);

		$sql1 = 'INSERT IGNORE INTO `#__hikashop_order` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'virtuemart_orders` AS vmo '.
			'INNER JOIN `'.$this->vmprefix.'virtuemart_currencies` vmc ON vmo.order_currency = vmc.virtuemart_currency_id '.
			'INNER JOIN `#__hikashop_currency` hkcur ON CONVERT(vmc.currency_code_3 USING utf8) = CONVERT( hkcur.currency_code USING utf8) '. //needed ?
			'LEFT JOIN `'.$this->vmprefix.'virtuemart_orderstates` AS vmos ON vmo.order_status = vmos.order_status_code '.
			'LEFT JOIN `#__hikashop_category` AS hkc ON vmos.order_status_name = hkc.category_name AND hkc.category_type = \'status\' '. //No U founded
			'INNER JOIN `#__hikashop_user` AS hkusr ON vmo.virtuemart_user_id = hkusr.user_cms_id '.
			'WHERE vmo.virtuemart_order_id > ' . (int)$this->options->last_vm_order . ' '.
			'GROUP BY vmo.virtuemart_order_id '.
			'ORDER BY vmo.virtuemart_order_id ASC;';

		$data = array(
			'address_user_id' => 'vmui.virtuemart_user_id',
			'address_firstname' => 'vmui.first_name',
			'address_middle_name' => 'vmui.middle_name',
			'address_lastname' => 'vmui.last_name',
			'address_company' => 'vmui.company',
			'address_street' => "CONCAT(vmui.address_1,' ',vmui.address_2)",
			'address_post_code' => 'vmui.zip',
			'address_city' => 'vmui.city',
			'address_telephone' => 'vmui.phone_1',
			'address_telephone2' => 'vmui.phone_2',
			'address_fax' => 'vmui.fax',
			'address_state' => 'vms.state_3_code',
			'address_country' => 'vmc.country_3_code',
			'address_published' => "case when vmui.address_type = 'BT' then 7 else 8 end",
			'address_vat' => $vat_cols,
			'address_vm_order_info_id' => 'vmui.virtuemart_order_id'
		);

		$sql2_1 = 'INSERT IGNORE INTO `#__hikashop_address` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'virtuemart_order_userinfos` AS vmui '.
			"INNER JOIN `".$this->vmprefix."virtuemart_states` vms ON vmui.virtuemart_state_id = vms.virtuemart_state_id ".
			"INNER JOIN `".$this->vmprefix."virtuemart_countries` vmc ON vmui.virtuemart_country_id = vmc.virtuemart_country_id ".
			'WHERE vmui.virtuemart_order_id > '.$this->options->last_vm_order.' ORDER BY vmui.virtuemart_order_userinfo_id ASC';

		$sql2_2 = 'UPDATE `#__hikashop_address` AS a '.
				'JOIN `#__hikashop_zone` AS hkz ON (a.address_country = hkz.zone_code_3 AND hkz.zone_type = "country") '.
				'SET address_country = hkz.zone_namekey, address_published = 6 WHERE address_published >= 7;';

		$sql2_3 = 'UPDATE `#__hikashop_address` AS a '. // todo
				'JOIN `#__hikashop_zone_link` AS zl ON (a.address_country = zl.zone_parent_namekey) '.
				'JOIN `#__hikashop_zone` AS hks ON (hks.zone_namekey = zl.zone_child_namekey AND hks.zone_type = "state" AND hks.zone_code_3 = a.address_state) '.
				'SET address_state = hks.zone_namekey, address_published = 5 WHERE address_published = 6;';

		$sql2_4 = 'UPDATE `#__hikashop_address` AS a '.
				'SET address_published = 0 WHERE address_published > 4;';

		$sql3 = 'UPDATE `#__hikashop_order` AS o '.
			'INNER JOIN `#__hikashop_address` AS a ON a.address_vm_order_info_id = o.order_vm_id '.
			'SET o.order_billing_address_id = a.address_id, o.order_shipping_address_id = a.address_id '.
			"WHERE o.order_billing_address_id = 0 AND address_published >= 7 ;";

		$sql4 = 'UPDATE `#__hikashop_order` AS o '.
			'INNER JOIN `#__hikashop_address` AS a ON a.address_vm_order_info_id = o.order_vm_id '.
			'SET o.order_shipping_address_id = a.address_id '.
			"WHERE o.order_shipping_address_id = 0 AND address_published >= 8 ;";

		$buffTable=$this->vmprefix."virtuemart_paymentmethods_".$this->vm_current_lng;

		$sql5 = 'UPDATE `#__hikashop_order` AS a '.
				'JOIN `'.$this->vmprefix.'virtuemart_orders` AS vmo ON a.order_vm_id = vmo.virtuemart_order_id '.
				'JOIN `'.$buffTable.'` AS vmp ON vmo.virtuemart_paymentmethod_id = vmp.virtuemart_paymentmethod_id '.
				"SET a.order_payment_method = CONCAT('vm import: ', vmp.payment_name) ".
				'WHERE a.order_vm_id > ' . (int)$this->options->last_vm_order;

		$this->db->setQuery('SET SQL_BIG_SELECTS=1');
		$this->db->query();

		$this->db->setQuery($sql1);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported orders: ' . $total . ' (including '.$guest.' guests)</p>';

		$this->db->setQuery($sql2_1);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported orders addresses: ' . $total . '</p>';

		$this->db->setQuery($sql3);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Updating billing addresses: ' . $total . '</p>';

		$this->db->setQuery($sql4);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Updating shipping addresses: ' . $total . '</p>';

		$this->db->setQuery($sql5);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Updating order payments: ' . $total . '</p>';

		$this->db->setQuery($sql2_2);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Updating orders: ' . $total;
		$this->db->setQuery($sql2_3);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '/' . $total;
		$this->db->setQuery($sql2_4);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '/' . $total . '</p>';

		$ret = true;

		return $ret;
	}

	function importOrderItems()
	{
		if( $this->db == null )
			return false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 11 :</span> Import Order Items</p>';

		$ret = false;
		$offset = $this->options->current;
		$count = 100;

		$data = array(
			'order_id' => 'hko.order_id',
			'product_id' => 'hkp.hk_id',
			'order_product_quantity' => 'vmoi.product_quantity',
			'order_product_name' => 'vmoi.order_item_name',
			'order_product_code' => 'vmoi.order_item_sku',
			'order_product_price' => 'vmoi.product_item_price',
			'order_product_tax' => '(vmoi.product_final_price - vmoi.product_item_price)',
			'order_product_options' => "''"
		);

		$sql = 'INSERT IGNORE INTO `#__hikashop_order_product` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'virtuemart_order_items` AS vmoi '.
			'INNER JOIN `#__hikashop_order` AS hko ON vmoi.virtuemart_order_id = hko.order_vm_id '.
			'INNER JOIN `#__hikashop_vm_prod` AS hkp ON vmoi.virtuemart_product_id = hkp.vm_id '.
			'WHERE vmoi.virtuemart_order_id > ' . (int)$this->options->last_vm_order . ';';

		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();

		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Orders Items imported : '. $total .'</p>';
		$ret = true;

		return $ret;
	}

	function importDownloads()
	{
		if( $this->db == null )
			return false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 12 :</span> Import Downloads</p>';

		jimport('joomla.filesystem.file');
		$categoryClass = hikashop_get('class.category');
		$app = JFactory::getApplication();

		$ret = false;
		$count = 100;
		$offset = $this->options->current;

		if( $offset == 0 )
		{
			$offset = $app->getUserState($this->sessionParams.'last_vm_pfile');
			if (!$offset)
				$offset = $this->options->last_vm_pfile;
		}

		$sql = "SELECT `config_value` FROM `#__hikashop_config` WHERE config_namekey = 'download_number_limit';";
		$this->db->setQuery($sql);
		$data = $this->db->loadObjectList();
		$dl_limit = $data[0]->config_value;

		$sql = 'SELECT vmm.virtuemart_media_id, vmm.file_meta, vmm.file_is_product_image, vmm.file_is_downloadable '.
		'FROM `'.$this->vmprefix.'virtuemart_products` vmp ' .
		'INNER JOIN `'.$this->vmprefix.'virtuemart_product_medias` AS vmpm ON vmp.virtuemart_product_id = vmpm.virtuemart_product_id ' .
		'INNER JOIN `'.$this->vmprefix.'virtuemart_medias` vmm ON vmpm.virtuemart_media_id = vmm.virtuemart_media_id ' .
		'WHERE vmm.virtuemart_media_id > '.$offset.' AND vmm.file_is_downloadable = 1'.
		' ORDER BY vmm.virtuemart_media_id ASC LIMIT '.$count.';';

		$this->db->setQuery($sql);
		$data = $this->db->loadObjectList();
		$max = 0;
		$meta_files = array();
		foreach($data as $c) {
			$result = false;
			if( !empty($c->file_meta) ) {
				$file_name = str_replace('\\','/',$c->file_meta);
				if( strpos($file_name,'/') !== false ) {
					$file_name = substr($file_name, strrpos($file_name,'/'));
				}
				$dstFolder = $this->options->uploadsecurefolder;
				if($c->file_is_product_image){
					$dstFolder = $this->options->uploadfolder;
				}
				$result = $this->copyFile($this->copyImgDir,$c->file_meta, $dstFolder.$file_name);
				if($result){
					$meta_files[] = $this->db->Quote($c->file_meta);
				}
				$max = $c->virtuemart_media_id;
			}
			if(!$result && !empty($c->file_url)){
				$file_name = str_replace('\\','/',$c->file_url);
				if( strpos($file_name,'/') !== false ) {
					$file_name = substr($file_name, strrpos($file_name,'/'));
				}
				$dstFolder = $this->options->uploadsecurefolder;
				if($c->file_is_product_image){
					$dstFolder = $this->options->uploadfolder;
				}
				$result = $this->copyFile($this->copyImgDir,$c->file_url, $dstFolder.$file_name);
				if(!$result && !empty($c->file_meta)){
					$meta_files[] = $this->db->Quote($c->file_meta);
				}
				$max = $c->virtuemart_media_id;
			}
		}
		if(empty($meta_files) || !count($meta_files)){
			$meta_files = "''";
		}else{
			$meta_files = implode(',',$meta_files);
		}
		$app->setUserState($this->sessionParams.'last_vm_pfile',$max);

		if( $max > 0 ) {
			echo '<p>Copying files...<br/>(Last processed file id: ' . $max . ')</p>';
			$this->options->current = $max;
			$this->refreshPage = true;
			return $ret;
		}

		$data = array(
			'file_name' => 'vmm.file_title',
			'file_description' => 'vmm.file_description',
			'file_path' => "case when vmm.file_meta IN (".$meta_files.") then SUBSTRING_INDEX(SUBSTRING_INDEX(vmm.file_meta, '/', -1), '\\\\', -1) else SUBSTRING_INDEX(SUBSTRING_INDEX(vmm.file_url, '/', -1), '\\\\', -1) end",
			'file_type' => "case when vmm.file_is_product_image = 1 then 'product' else 'file' end",
			'file_ref_id' => 'hkp.hk_id'
		);
		$sql = 'INSERT IGNORE INTO `#__hikashop_file` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `'.$this->vmprefix.'virtuemart_products` vmp '.
			"INNER JOIN `".$this->vmprefix."virtuemart_product_medias` AS vmpm ON vmp.virtuemart_product_id = vmpm.virtuemart_product_id ".
			"INNER JOIN `".$this->vmprefix."virtuemart_medias` vmm ON vmpm.virtuemart_media_id = vmm.virtuemart_media_id " .
			'INNER JOIN `#__hikashop_vm_prod` AS hkp ON vmm.virtuemart_media_id = hkp.vm_id '.
			'WHERE vmm.virtuemart_media_id > '.$this->options->last_vm_pfile.';';

		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Downloable files imported : ' . $total . '</p>';


		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Downloable order files imported : 0</p>';
		return true;
	}

	function importDiscount()
	{
		if( $this->db == null )
			return false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 9 :</span> Import Discount</p>';

		$sql = "SELECT `config_value` FROM `#__hikashop_config` WHERE config_namekey = 'main_currency';";
		$this->db->setQuery($sql);
		$data = $this->db->loadObjectList();
		$main_currency = $data[0]->config_value;

		$data = array(
			'discount_type' => "'coupon'", //coupon or discount
			'discount_published' => '1',
			'discount_code' => '`coupon_code`',
			'discount_currency_id' => $main_currency,
			'discount_flat_amount' => "case when percent_or_total = 'total' then coupon_value else 0 end",
			'discount_percent_amount' => "case when percent_or_total = 'percent' then coupon_value else 0 end",
			'discount_quota' => "case when coupon_type = 'gift' then 1 else 0 end"
		);

		$sql = 'INSERT IGNORE INTO #__hikashop_discount (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM '.$this->vmprefix.'virtuemart_coupons WHERE virtuemart_coupon_id > ' . (int)$this->options->last_vm_coupon;
		$this->db->setQuery($sql);
		$this->db->query();

		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Discount codes / coupons imported : ' . $total . '</p>';

		$data = array(
			'discount_type' => "'discount'",
			'discount_published' => '1',
			'discount_code' => "CONCAT('discount_', vmp.product_sku)",
			'discount_currency_id' => $main_currency,
			'discount_flat_amount' => "case when vmc.percent_or_total = 'total' then vmc.coupon_value else 0 end",
			'discount_percent_amount' => "case when vmc.percent_or_total = 'percent' then vmc.coupon_value else 0 end",
			'discount_quota' => "''",
			'discount_product_id' => 'hkp.hk_id',
			'discount_category_id' => '0',
			'discount_start' => "UNIX_TIMESTAMP(vmc.coupon_start_date)",
			'discount_end' => "UNIX_TIMESTAMP(vmc.coupon_expiry_date)"
		);

		$sql = 'INSERT IGNORE INTO #__hikashop_discount (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM '.$this->vmprefix.'virtuemart_products vmp '.
			'INNER JOIN `'.$this->vmprefix.'virtuemart_product_prices` vmpp ON vmp.virtuemart_product_id = vmpp.virtuemart_product_id '.
			'INNER JOIN `'.$this->vmprefix.'virtuemart_coupons` vmc ON vmpp.product_discount_id = vmc.virtuemart_coupon_id '.
			'INNER JOIN `#__hikashop_vm_prod` AS hkp ON hkp.vm_id = vmp.virtuemart_product_id '.
			'WHERE vmp.virtuemart_product_id > ' . (int)$this->options->last_vm_prod;

		$this->db->setQuery($sql);
		$this->db->query();

		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Discount product imported : ' . $total . '</p>';

		$ret = true;

		return $ret;
	}

	function importReviews()
	{
		if( $this->db == null )
			return false;

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 13 :</span> Import Product reviews</p>';

		$ret = false;
		$offset = $this->options->current;
		$count = 100;

		$data = array(
			'vote_ref_id' => 'hkvp.hk_id',
			'vote_type' => "'product'",
			'vote_user_id' => 'hkusr.user_id',
			'vote_rating' => 'vrv.vote',
			'vote_comment' => 'vrr.comment',
			'vote_useful' => '0', //review_ok ?
			'vote_pseudo' => 'u.username',
			'vote_ip' => 'vrr.lastip',
			'vote_email' => 'u.email',
			'vote_date' => 'UNIX_TIMESTAMP(vrv.created_on)'
		);

		$sql = 'INSERT IGNORE INTO `#__hikashop_vote` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `#__virtuemart_rating_reviews` AS vrr '.
			'INNER JOIN `#__virtuemart_rating_votes` AS vrv ON vrr.virtuemart_rating_review_id = vrv.virtuemart_rating_vote_id '. //join with double primary keys Zzzz
			'INNER JOIN `#__hikashop_vm_prod` AS hkvp ON vrr.virtuemart_product_id = hkvp.vm_id '.
			'INNER JOIN `#__hikashop_user` AS hkusr ON vrr.created_by = hkusr.user_cms_id '.
			'INNER JOIN `#__users` AS u ON hkusr.user_cms_id = u.id '.
			'WHERE vrr.virtuemart_rating_review_id > ' . (int)$this->options->last_vm_review . ';';

		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();

		$sql = 'SELECT hkvp.hk_id as hkid, vrv.vote as vmvote FROM `#__virtuemart_rating_votes` AS vrv '.
			'INNER JOIN `#__hikashop_vm_prod` AS hkvp ON vrv.virtuemart_product_id = hkvp.vm_id '.
			'WHERE vrv.virtuemart_rating_vote_id > ' . (int)$this->options->last_vm_review . ' '.
			'ORDER BY hkvp.hk_id; ';

		$this->db->setQuery($sql);
		$this->db->query();
		$data = $this->db->loadObjectList();

		$continue = false;
		$idmain = $sum = $divide = $nbentries = 0;

		foreach($data as $d)
		{
			if (!$continue)
				$idmain = $d->hkid;

			$sum += $d->vmvote;
			$divide++;

			if ($idmain==$d->hkid)
			{
				$continue = true;
				continue;
			}
			else
			{
				$average = $sum / $divide;
				$sql = 'UPDATE `#__hikashop_product` SET `product_average_score` = '.$average.', `product_total_vote` = '.$divide.' WHERE product_id = '.$d->hkid;
				$this->db->query();
				$nbentries += $this->db->getAffectedRows();
				$sum = $divide = 0;
				$continue = false;
			}
		}

		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Products reviews imported : '. $total .'</p>';
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Products average scores updated : '. $nbentries .'</p>';

		$ret = true;
		return $ret;
	}
}
