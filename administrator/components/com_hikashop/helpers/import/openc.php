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
class hikashopImportopencHelper extends hikashopImportHelper
{
	var $hikaDatabaseName;
	var $opencDatabase;
	var $opencPrefix;
	var $opencRootPath;
	var $sessionParams;
	var $importcurrencies;

	function __construct()
	{
		parent::__construct();
		$this->importName = 'openc';
		$this->copyImgDir = '';
		$this->copyCatImgDir = '';
		$this->copyDownloadDir = '';
		$this->copyManufDir = '';
		$this->sessionParams = HIKASHOP_COMPONENT.'openc';
		jimport('joomla.filesystem.file');
	}

	function importFromOpenc()
	{
		@ob_clean();
		echo $this->getHtmlPage();

		$this->token = hikashop_getFormToken();
		flush();

		if( isset($_GET['import']) && $_GET['import'] == '1' )
		{
			$app = JFactory::getApplication();
			$config = JFactory::getConfig();
			$this->opencDatabase = $app->getUserState($this->sessionParams.'dbName');
			$this->opencRootPath = $app->getUserState($this->sessionParams.'rootPath');
			$this->opencPrefix = $app->getUserState($this->sessionParams.'prefix');

			if(HIKASHOP_J30)
				$this->hikaDatabaseName = $config->get('db');
			else
				$this->hikaDatabaseName = $config->getValue('db');
			$this->importcurrencies = $app->getUserState($this->sessionParams.'importcurrencies');

			if (substr($this->opencRootPath, -1)!='/')
				$this->opencRootPath = $this->opencRootPath.'/';

			@include_once($this->opencRootPath . 'admin/config.php');

			$time = microtime(true);
			$processed = $this->doImport();

			if( $processed )
			{
				$elasped = microtime(true) - $time;

				if( !$this->refreshPage )
					echo '<p></br><a'.$this->linkstyle.'href="'.hikashop_completeLink('import&task=import&importfrom=openc&'.$this->token.'=1&import=1&time='.time()).'">'.JText::_('HIKA_NEXT').'</a></p>';

				echo '<p style="font-size:0.85em; color:#605F5D;">Elasped time: ' . round($elasped * 1000, 2) . 'ms</p>';
			}
			else
			{
				echo '<a'.$this->linkstyle.'href="'.hikashop_completeLink('import&task=show').'">'.JText::_('HIKA_BACK').'</a>';
			}
		}
		else
		{
			echo $this-> getStartPage();
		}

		if( $this->refreshPage )
		{
			echo "<script type=\"text/javascript\">\r\nr = true;\r\n</script>";
		}
		echo '</body></html>';
		exit;
	}



	function getStartPage()
	{
		$app = JFactory::getApplication();
		$database = JFactory::getDBO();

		$returnString = '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 0</span></p>';
		$continue = true;


		$this->opencDatabase = $app->getUserStateFromRequest($this->sessionParams.'dbName', 'opencDbName', '', 'string' ); //getUserStateFromRequest( 'keyFromSession', 'keyFromRequest', '', 'typeKey' ) - JRequest::getString()
		$this->opencRootPath = $app->getUserStateFromRequest($this->sessionParams.'rootPath', 'opencRootPath', '', 'string' );
		$this->opencPrefix = $app->getUserStateFromRequest($this->sessionParams.'prefix', 'opencPrefix', '', 'string' );
		$config = JFactory::getConfig();
		if(HIKASHOP_J30)
			$this->hikaDatabaseName = $config->get('db');
		else
			$this->hikaDatabaseName = $config->getValue('db');
		$this->importcurrencies = $app->getUserStateFromRequest($this->sessionParams.'importcurrencies', 'import_currencies', '', 'string' );

		if (empty($this->opencDatabase))
		{
			$returnString .= '<p style="color:red">Please specify a name for your Opencart Database.</p>';
		}
		elseif (empty($this->opencRootPath))
		{
			$returnString .= '<p style="color:red">Please specify a root path for your Opencart website.</p>';
		}
		else
		{
			$query = 'SHOW TABLES FROM '.$this->opencDatabase.' LIKE '.$database->Quote(substr(hikashop_table($this->opencPrefix.'product',false),3)).';';
			try
			{
				$database->setQuery($query);
				$table = $database->loadResult();
			}
			catch(Exception $e)
			{
				$returnString .= '<p style="color:red">Error with the openning of the `'.$this->opencDatabase.'` database.</br><span style="font-size:0.75em">Mysql Error :'.$e.'</span></p>';
				$continue = false;
			}
			if ($continue)
			{
				if (empty($table))
					$returnString .= '<p style="color:red">Opencart has not been found in the database you specified : '.$this->opencDatabase.'</p>';
				else
					$returnString .= 'The import will now start from the database `'.$this->opencDatabase.'`.</br>First, make a backup of your databases.<br/>'.
									'When ready, click on <a '.$this->linkstyle.' href="'.hikashop_completeLink('import&task=import&importfrom=openc&'.$this->token.'=1&import=1').'">'.JText::_('HIKA_NEXT').'</a>, otherwise ';
			}
		}
		$returnString .= '<a'.$this->linkstyle.' href="'.hikashop_completeLink('import&task=show').'">'.JText::_('HIKA_BACK').'</a>';
		return $returnString;
	}





	function doImport()
	{
		if( $this->db == null )
			return false;

		$this->loadConfiguration();

		$current = $this->options->current;
		$ret = true;
		$next = false;

		switch($this->options->state)
		{
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

		if( $ret && $next )
		{
			$sql =  "UPDATE `#__hikashop_config` SET config_value=(config_value+1) WHERE config_namekey = 'openc_import_state'; ";
			$this->db->setQuery($sql);
			$this->db->query();
			$sql = "UPDATE `#__hikashop_config` SET config_value=0 WHERE config_namekey = 'openc_import_current';";
			$this->db->setQuery($sql);
			$this->db->query();
		}
		else if( $current != $this->options->current )
		{
			$sql =  "UPDATE `#__hikashop_config` SET config_value=".$this->options->current." WHERE config_namekey = 'openc_import_current';";
			$this->db->setQuery($sql);
			$this->db->query();
		}

		return $ret;

	}

	function loadConfiguration()
	{
		$this->options = null;
		$data = array(
			'uploadfolder',
			'uploadsecurefolder',
			'main_currency',
			'openc_import_state',
			'openc_import_current',
			'openc_import_tax_id',
			'openc_import_main_cat_id',
			'openc_import_max_hk_cat',
			'openc_import_max_hk_prod',
			'openc_import_last_openc_cat',
			'openc_import_last_openc_prod',
			'openc_import_last_openc_user',
			'openc_import_last_openc_order',
			'openc_import_last_openc_pfile',
			'openc_import_last_openc_coupon',
			'openc_import_last_openc_voucher',
			'openc_import_last_openc_taxrate',
			'openc_import_last_openc_taxclass',
			'openc_import_last_openc_manufacturer',
			'openc_import_max_joomla_user'
		);
		$this->db->setQuery('SELECT config_namekey, config_value FROM `#__hikashop_config` WHERE config_namekey IN ('."'".implode("','",$data)."'".');');
		$result = $this->db->loadObjectList();

		if (!empty($result))
		{
			foreach($result as $o)
			{
				if( substr($o->config_namekey, 0, 13) == 'openc_import_' )
					$nk = substr($o->config_namekey, 13);
				else
					$nk = $o->config_namekey;
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

		if( !isset($this->options->state) )
		{
			$this->options->state = 0;
			$this->options->current = 0;
			$this->options->tax_id = 0;
			$this->options->last_openc_coupon = 0;
			$this->options->last_openc_voucher = 0;
			$this->options->last_openc_pfile = 0;
			$this->options->last_openc_taxrate = 0;
			$this->options->last_openc_taxclass = 0;
			$this->options->last_openc_manufacturer = 0;

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

			$this->db->setQuery("SELECT max(id) as 'max' FROM `#__users`;");
			$data = $this->db->loadObjectList();
			$this->options->max_joomla_user = (int)($data[0]->max);

			$query='SHOW TABLES LIKE '.$this->db->Quote($this->db->getPrefix().substr(hikashop_table('openc_cat'),3));
			$this->db->setQuery($query);
			$table = $this->db->loadResult();
			if(!empty($table))
			{
				$this->db->setQuery("SELECT max(openc_id) as 'max' FROM `#__hikashop_openc_cat`;");
				$data = $this->db->loadObjectList();
				if( $data )
					$this->options->last_openc_cat = (int)($data[0]->max);
				else
					$this->options->last_openc_cat = 0;

				$this->db->setQuery("SELECT max(openc_id) as 'max' FROM `#__hikashop_openc_prod`;");
				$data = $this->db->loadObjectList();
				if( $data )
					$this->options->last_openc_prod = (int)($data[0]->max);
				else
					$this->options->last_openc_prod = 0;

				$this->db->setQuery("SELECT max(order_openc_id) as 'max' FROM `#__hikashop_order`;");
				$data = $this->db->loadObjectList();
				if( $data )
					$this->options->last_openc_order = (int)($data[0]->max);
				else
					$this->options->last_openc_order = 0;
			}
			else
			{
				$this->options->last_openc_cat = 0;
				$this->options->last_openc_prod = 0;
				$this->options->last_openc_order = 0;
			}

			$this->options->last_openc_user = 0;

			$sql = 'INSERT IGNORE INTO `#__hikashop_config` (`config_namekey`,`config_value`,`config_default`) VALUES '.
				"('openc_import_state',".$this->options->state.",".$this->options->state.")".
				",('openc_import_current',".$this->options->current.",".$this->options->current.")".
				",('openc_import_tax_id',".$this->options->tax_id.",".$this->options->tax_id.")".
				",('openc_import_main_cat_id',".$this->options->main_cat_id.",".$this->options->main_cat_id.")".
				",('openc_import_max_hk_cat',".$this->options->max_hk_cat.",".$this->options->max_hk_cat.")".
				",('openc_import_max_joomla_user',".$this->options->max_joomla_user.",".$this->options->max_joomla_user.")".
				",('openc_import_max_hk_prod',".$this->options->max_hk_prod.",".$this->options->max_hk_prod.")".
				",('openc_import_last_openc_cat',".$this->options->last_openc_cat.",".$this->options->last_openc_cat.")".
				",('openc_import_last_openc_prod',".$this->options->last_openc_prod.",".$this->options->last_openc_prod.")".
				",('openc_import_last_openc_user',".$this->options->last_openc_user.",".$this->options->last_openc_user.")".
				",('openc_import_last_openc_order',".$this->options->last_openc_order.",".$this->options->last_openc_order.")".
				",('openc_import_last_openc_pfile',".$this->options->last_openc_pfile.",".$this->options->last_openc_pfile.")".
				",('openc_import_last_openc_coupon',".$this->options->last_openc_coupon.",".$this->options->last_openc_coupon.")".
				",('openc_import_last_openc_voucher',".$this->options->last_openc_voucher.",".$this->options->last_openc_voucher.")".
				",('openc_import_last_openc_taxrate',".$this->options->last_openc_taxrate.",".$this->options->last_openc_taxrate.")".
				",('openc_import_last_openc_taxclass',".$this->options->last_openc_taxclass.",".$this->options->last_openc_taxclass.")".
				",('openc_import_last_openc_manufacturer',".$this->options->last_openc_manufacturer.",".$this->options->last_openc_manufacturer.")".
				';';
			$this->db->setQuery($sql);
			$this->db->query();
		}
	}


	function createTables()
	{
		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 1 :</span> Initialization Tables</p>';
		$create = true;

		$query='SHOW TABLES LIKE '.$this->db->Quote($this->db->getPrefix().substr(hikashop_table('openc_cat'),3));
		$this->db->setQuery($query);
		$table = $this->db->loadResult();
		if (!empty($table))
			$create = false;

		if ($create)
		{
			$this->db->setQuery("CREATE TABLE IF NOT EXISTS `#__hikashop_openc_prod` (`openc_id` int(11) unsigned NOT NULL DEFAULT '0', `hk_id` int(11) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`openc_id`)) ENGINE=MyISAM");
			$this->db->query();
			$this->db->setQuery("CREATE TABLE IF NOT EXISTS `#__hikashop_openc_cat` (`openc_cat_id` INT(11) unsigned NOT NULL AUTO_INCREMENT, `openc_id` int(11) unsigned NOT NULL DEFAULT '0', `hk_id` int(11) unsigned NOT NULL DEFAULT '0', `category_type` varchar(255) NULL, PRIMARY KEY (`openc_cat_id`)) ENGINE=MyISAM");
			$this->db->query();
			$this->db->setQuery("CREATE TABLE IF NOT EXISTS `#__hikashop_openc_user` (`openc_user_id` int(11) unsigned NOT NULL DEFAULT '0', `hk_user_cms_id` int(11) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`openc_user_id`)) ENGINE=MyISAM");
			$this->db->query();
			$this->db->setQuery("CREATE TABLE IF NOT EXISTS `#__hikashop_openc_customer` (`openc_customer_id` int(11) unsigned NOT NULL DEFAULT '0', `hk_customer_cms_id` int(11) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`openc_customer_id`)) ENGINE=MyISAM");
			$this->db->query();

			$databaseHelper = hikashop_get('helper.database');
			$databaseHelper->addColumns('address','`address_openc_order_info_id` INT(11) NULL');
			$databaseHelper->addColumns('order','`order_openc_id` INT(11) NULL');
			$databaseHelper->addColumns('order','INDEX ( `order_openc_id` )');
			$databaseHelper->addColumns('taxation','`tax_openc_id` INT(11) NULL');

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
		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 2 :</span> Import Taxes<p>';
		$ret = false;

		$data = array(
			'tax_namekey' => "CONCAT('OPENC_TAX_', octr.tax_rate_id)",
			'tax_rate' => 'octr.rate'
		);

		$sql = 'INSERT IGNORE INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_tax` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'tax_rate` AS octr '.
			'WHERE octr.tax_rate_id > ' . (int)$this->options->last_openc_taxrate;

		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported taxes: ' . $total . '</p>';


		$element = 'tax';
		$categoryClass = hikashop_get('class.category');
		$categoryClass->getMainElement($element);

		$data = array(
				'category_type' => "'tax'",
				'category_name' => "CONCAT('Tax imported (', octc.title,')')",
				'category_published' => '1',
				'category_parent_id' => $element,
				'category_namekey' => " CONCAT('OPENC_TAX_CATEGORY_', octc.tax_class_id)"
			);

		$sql = 'INSERT IGNORE INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_category` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'tax_class` AS octc ';
			'WHERE octc.tax_class_id > ' . (int)$this->options->last_openc_taxclass;

		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported Taxes Categories: ' . $total . '</p>';

		if( $total > 0 ) {
			$this->options->max_hk_cat += $total;
			$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'openc_import_max_hk_cat'; ");
			$this->db->query();
			$this->importRebuildTree();
		}


		$data = array(
				'zone_namekey' => "case when hkz.zone_namekey IS NULL then '' else hkz.zone_namekey end",
				'category_namekey' => "CONCAT('OPENC_TAX_CATEGORY_', octc.tax_class_id)", //"case when hkz.zone_id IS NULL then CONCAT('openc_TAX_', octr.tax_rate_id,'_0') else CONCAT('openc_TAX_', octr.tax_rate_id,'_',hkz.zone_id) end",
				'tax_namekey' => "CONCAT('OPENC_TAX_', octra.tax_rate_id)",
				'taxation_published' => '1',
				'taxation_type' => "''",
				'tax_openc_id' => 'octc.tax_class_id' //'octra.tax_rate_id' See import product
			);

		$sql = 'INSERT IGNORE INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_taxation` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'tax_class` AS octc '.
			'INNER JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'tax_rule` AS octr ON octc.tax_class_id = octr.tax_class_id '.
			'INNER JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'tax_rate` AS octra ON octr.tax_rate_id = octra.tax_rate_id '.
			'LEFT JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'zone_to_geo_zone` AS ocz ON octra.geo_zone_id = ocz.geo_zone_id '.
			'LEFT JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'country` AS occ ON ocz.country_id = occ.country_id ' .
			"LEFT JOIN `".$this->hikaDatabaseName."`.`#__hikashop_zone` hkz ON occ.iso_code_3 = hkz.zone_code_3 AND hkz.zone_type = 'country' ".
			'WHERE octra.tax_rate_id > ' . (int)$this->options->last_openc_taxrate;

		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported Taxations: ' . $total . '</p>';

		$ret = true;
		return $ret;
	}


	function importManufacturers()
	{
		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 3 :</span> Import Manufacturers</p>';
		$ret = false;

		$count = 100;
		$rebuild = false;

		$sql1 = 'SELECT * FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'manufacturer` ocm '.
		'LEFT JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_openc_cat` hkoc ON ocm.manufacturer_id = hkoc.openc_id  AND category_type=\'manufacturer\' '.
		'WHERE ocm.manufacturer_id > ' . (int)$this->options->last_openc_manufacturer;
		'ORDER BY ocm.manufacturer_id ASC;';

		$this->db->setQuery($sql1);
		$this->db->query();
		$datas = $this->db->loadObjectList();

		$sql2 = 'INSERT INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_category` (`category_id`,`category_parent_id`,`category_type`,`category_name`,`category_published`,'.
			'`category_namekey`,`category_description`,`category_menu`) VALUES ';
		$sql3 = 'INSERT INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_openc_cat` (`openc_id`,`hk_id`,`category_type`) VALUES ';
		$sql4 = 'INSERT INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_file` (`file_name`,`file_description`,`file_path`,`file_type`,`file_ref_id`) VALUES ';
		$doSql3 = false;
		$doSql4 = false;
		$i = $this->options->max_hk_cat + 1;
		$ids = array( 0 => $this->options->main_cat_id);
		$sep = '';
		$sep2 = '';
		$cpt=0;
		$echo=false;

		if( empty($datas) )
		{
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported manufacturers : 0</p>';
			return true;
		}
		else
		{
			foreach($datas as $data)
			{
				if( !empty($data->openc_id) )
				{
					$ids[$data->manufacturer_id] = $data->hk_id;
				}
				else
				{
					$doSql3 = true;
					$ids[$data->manufacturer_id] = $i;
					$sql3 .= $sep.'('.$data->manufacturer_id.','.$i.',\'manufacturer\')';
					$i++;
					$sep = ',';
				}
				$cpt++;
				if( $cpt >= $count )
					break;
			}

			$sql3 .= ';';

			$cpt = 0;
			$sep = '';

			foreach($datas as $data)
			{
				if( !empty($data->openc_id) )
					continue;

				$id = $ids[$data->manufacturer_id];

				$element = 'manufacturer';
				$categoryClass = hikashop_get('class.category');
				$categoryClass->getMainElement($element);

				$d = array(
					$id,
					$element,
					"'manufacturer'",
					$this->db->quote($data->name),
					'1',
					"CONCAT('OPENC_MANUFAC_', ".$data->manufacturer_id .")",
					"'manufacturer imported from Opencart'",
					'0'
				);

				$sql2 .= $sep.'('.implode(',',$d).')';

				if( !empty($data->image) && !empty($this->opencRootPath))
				{
					if (defined('DIR_IMAGE'))
						$this->copyManufDir = str_replace('\\','/',rtrim(JPath::clean(html_entity_decode(DIR_IMAGE)),DS.' ').DS);
					else
						$this->copyManufDir = $this->opencRootPath.'image/';
					if (!$echo)
					{
						echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Copying manufacturers images... </p>';
						$echo=true;
					}
					$doSql4 = true;
					$sql4 .= $sep2."(".$this->db->quote($data->name).",'',".$this->db->quote($data->image).",'category',".$id.')';
					$sep2 = ',';
					$file_name = str_replace('\\','/',$data->image);
					if( strpos($file_name,'/') !== false ) {
						$file_name = substr($file_name, strrpos($file_name,'/'));
					}
					$this->copyFile($this->copyManufDir,$data->image, str_replace('//','/',str_replace('\\','/',$this->options->uploadfolder.$file_name)));
				}
				$sep = ',';

				$cpt++;
				if( $cpt >= $count )
					break;
			}
		}
		if($cpt > 0)
		{
			$sql2 .= ';';
			$sql4 .= ';';
			$this->db->setQuery($sql2);
			$this->db->query();
			$total = $this->db->getAffectedRows();
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported Manufacturers : ' . $total . '</p>';
		}
		else
		{
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported Manufacturers : 0</p>';
		}

		if( isset($total) && $total > 0)
		{
			$rebuild = true;
			$this->options->max_hk_cat += $total;
			$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'openc_import_max_hk_cat'; ");
			$this->db->query();
		}

		if ($doSql3)
		{
			$this->db->setQuery($sql3);
			$this->db->query();
			$total = $this->db->getAffectedRows();
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Fallback links : ' . $total . '</p>';
		}

		if($doSql4)
		{
			$this->db->setQuery($sql4);
			$this->db->query();
			$total = $this->db->getAffectedRows();
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Manufacturers files : ' . $total . '</p>';
		}

		if( $rebuild )
			$this->importRebuildTree();

		if( $cpt < $count )
			$ret = true;

		return $ret;
	}


	function importCategories()
	{
		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 4 :</span> Import General Categories</p>';

		jimport('joomla.filesystem.file');
		$categoryClass = hikashop_get('class.category');

		$rebuild = false;
		$ret = false;
		$offset = 0;
		$count = 100;
		$cpt = 0;
		$sep = '';
		$sep2 = '';
		$echo = false;

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

		$this->db->setQuery("SELECT order_status_id, name FROM `".$this->opencDatabase."`.`".$this->opencPrefix."order_status` WHERE name NOT IN ('".implode("','",$statuses)."','canceled');");
		$data = $this->db->loadObjectList();

		if( count($data) > 0 )
		{
			$sql0 = 'INSERT IGNORE INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_category` (`category_id`,`category_parent_id`,`category_type`,`category_name`,`category_description`,`category_published`,'.
				'`category_namekey`,`category_access`,`category_menu`,`category_keywords`) VALUES ';

			$id = $this->options->max_hk_cat + 1;
			$sep = '';
			foreach($data as $c) {
				$d = array(
					$id++,
					$status_category,
					"'status'",
					$this->db->quote( strtolower($c->name) ),
					"'Order status imported from Opencart'",
					'1',
					$this->db->quote('status_openc_import_'.strtolower(str_replace(' ','_',$c->name))),
					"'all'",
					'0',
					$this->db->quote( $c->order_status_id )
				);
				$sql0 .= $sep.'('.implode(',',$d).')';
				$sep = ',';
			}

			$this->db->setQuery($sql0);
			$this->db->query();
			$total = $this->db->getAffectedRows();

			if( $total > 0 ) {
				echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported order status categories : ' . $total . '</p>';
				$rebuild = true;
				$this->options->max_hk_cat += $total;
				$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'openc_import_max_hk_cat'; ");
				$this->db->query();
				$sql0 = '';
			}
			else
			{
				echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported order status categories : 0</p>';
			}
		}


		$sql1 = 'SELECT * FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'category` occ '.
		'INNER JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'category_description` occd ON occ.category_id = occd.category_id '.
		'LEFT JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_openc_cat` hkoc ON occ.category_id = hkoc.openc_id AND category_type=\'category\' '.
		'WHERE occ.category_id > '.$this->options->last_openc_cat.' '.
		'ORDER BY occ.parent_id ASC, occ.category_id ASC;';

		$this->db->setQuery($sql1);
		$this->db->query();
		$datas = $this->db->loadObjectList();

		$sql2 = 'INSERT INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_category` (`category_id`,`category_parent_id`,`category_type`,`category_name`,`category_description`,`category_published`,'.
			'`category_ordering`,`category_namekey`,`category_created`,`category_modified`,`category_access`,`category_menu`) VALUES ';
		$sql3 = 'INSERT INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_openc_cat` (`openc_id`,`hk_id`,`category_type`) VALUES ';
		$sql4 = 'INSERT INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_file` (`file_name`,`file_description`,`file_path`,`file_type`,`file_ref_id`) VALUES ';
		$doSql3 = false;
		$doSql4 = false;
		$i = $this->options->max_hk_cat + 1;
		$ids = array( 0 => $this->options->main_cat_id);
		$sep = '';

		if( empty($datas) )
		{
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported category : 0</p>';
			if( $rebuild )
				$this->importRebuildTree();
			return true;
		}
		else
		{
			foreach($datas as $data)
			{
				if( !empty($data->openc_id) )
				{
					$ids[$data->category_id] = $data->hk_id;
				}
				else
				{
					$doSql3 = true;
					$ids[$data->category_id] = $i;
					$sql3 .= $sep.'('.$data->category_id.','.$i.',\'category\')';
					$i++;
					$sep = ',';
				}
				$cpt++;
				if( $cpt >= $count )
					break;
			}
			$sql3 .= ';';

			$cpt = 0;
			$sep = '';

			foreach($datas as $data)
			{
				if( !empty($data->openc_id) )
					continue;

				$id = $ids[$data->category_id];
				if(!empty($ids[$data->parent_id]))
					$pid = (int)$ids[$data->parent_id];
				else
					$pid = $ids[0];

				$element = new stdClass();
				$element->category_id = $id;
				$element->category_parent_id = $pid;
				$element->category_name = $data->name;
				$nameKey = $categoryClass->getNameKey($element);

				$d = array(
					$id,
					$pid,
					"'product'",
					$this->db->quote($data->name),
					$this->db->quote($data->description),
					'1',
					$data->sort_order,
					$this->db->quote($nameKey),
					"'".$data->date_added."'",
					"'".$data->date_modified."'",
					"'all'",
					'0'
				);

				$sql2 .= $sep.'('.implode(',',$d).')';

				if( !empty($data->image) && !empty($this->opencRootPath))
				{
					if (defined('DIR_IMAGE'))
						$this->copyCatImgDir = str_replace('\\','/',rtrim(JPath::clean(html_entity_decode(DIR_IMAGE)),DS.' ').DS);
					else
						$this->copyCatImgDir = $this->opencRootPath.'image/';
					if (!$echo)
					{
						echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Copying categories images... </p>';
						$echo=true;
					}
					$doSql4 = true;
					$sql4 .= $sep2."('','','".$data->image."','category',".$id.')';
					$sep2 = ',';
					$file_name = str_replace('\\','/',$data->image);
					if( strpos($file_name,'/') !== false ) {
						$file_name = substr($file_name, strrpos($file_name,'/'));
					}
					$this->copyFile($this->copyCatImgDir,$data->image, str_replace('//','/',str_replace('\\','/',$this->options->uploadfolder.$file_name)));
				}
				$sep = ',';

				$cpt++;
				if( $cpt >= $count )
					break;
			}
		}


		if($cpt > 0)
		{
			$sql2 .= ';';
			$sql4 .= ';';
			$this->db->setQuery($sql2);
			$this->db->query();
			$total = $this->db->getAffectedRows();
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported Categories : ' . $total . '</p>';
		}
		else
		{
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported category : 0</p>';
		}

		if( isset($total) && $total > 0)
		{
			$rebuild = true;
			$this->options->max_hk_cat += $total;
			$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'openc_import_max_hk_cat'; ");
			$this->db->query();
		}

		if ($doSql3)
		{
			$this->db->setQuery($sql3);
			$this->db->query();
			$total = $this->db->getAffectedRows();
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Fallback links : ' . $total . '</p>';
		}

		if($doSql4)
		{
			$this->db->setQuery($sql4);
			$this->db->query();
			$total = $this->db->getAffectedRows();
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Categories files : ' . $total . '</p>';
		}

		if( $rebuild )
			$this->importRebuildTree();

		if( $cpt < $count )
			$ret = true;

		return $ret;
	}


	function importProducts()
	{
		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 5 :</span> Import Products</p>';

		$ret = false;
		$count = 100;
		$offset = $this->options->current;
		$max = 0;

		jimport('joomla.filesystem.file');
		$categoryClass = hikashop_get('class.category');

		$this->db->setQuery('SELECT ocp.product_id, ocpi.image FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'product` ocp '.
						'LEFT JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'product_image` ocpi ON ocp.product_id = ocpi.product_id '.
						'LEFT JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_openc_prod` hkprod ON ocp.product_id = hkprod.openc_id '.
						"WHERE ocp.product_id > ".(int)$offset." AND hkprod.hk_id IS NULL AND (ocp.image IS NOT NULL) AND ocp.image <> '' ".
						'ORDER BY product_id ASC LIMIT '.$count.';'
		);

		$datas = $this->db->loadObjectList();

		if (!empty($datas) && !empty($this->opencRootPath))
		{
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Copying products images... </p>';
			if (defined('DIR_IMAGE'))
				$this->copyImgDir = str_replace('\\','/',rtrim(JPath::clean(html_entity_decode(DIR_IMAGE)),DS.' ').DS);
			else
				$this->copyImgDir = $this->opencRootPath.'image/';

			foreach($datas as $data) {
				if( !empty($data->image) ) {
					$file_name = str_replace('\\','/',$data->image);
					if( strpos($file_name,'/') !== false ) {
						$file_name = substr($file_name, strrpos($file_name,'/'));
					}
					$this->copyFile($this->copyImgDir,$data->image, $this->options->uploadfolder.$file_name);
					$max = $data->product_id;
				}
			}
		}


		if( $max > 0 )
		{
			echo '<p>Copying files...(last proccessed product id: ' . $max . ')</p>';
			$this->options->current = $max;
			$this->refreshPage = true;
			return $ret;
		}

		$data = array(
			'product_name' => 'ocpd.name',
			'product_description' => 'ocpd.description',
			'product_quantity' => 'case when ocp.quantity IS NULL or ocp.quantity < 0 then 0 else ocp.quantity end',
			'product_code' => 'ocp.hika_sku',
			'product_published' => 'ocp.status',
			'product_hit' => 'ocp.viewed',
			'product_created' => 'ocp.date_added',
			'product_modified' => 'ocp.date_modified',
			'product_sale_start' => 'ocp.date_available',
			'product_tax_id' => 'hkc.category_id',
			'product_type' => "'main'",
			'product_url' => "''",
			'product_weight' => 'ocp.weight',
			'product_weight_unit' => "LOWER(ocwcd.unit)",
			'product_dimension_unit' => "LOWER(oclcd.unit)",
			'product_min_per_order' => 'ocp.minimum',
			'product_sales' => '0',
			'product_width' => 'ocp.width',
			'product_length' => 'ocp.length',
			'product_height' => 'ocp.height',
			'product_parent_id' => '0'
		);

		$sql1 = 'INSERT IGNORE INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_product` (`'.implode('`,`',array_keys($data)).'`) '.
		'SELECT '.implode(',',$data).' FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'product` AS ocp '.
		'INNER JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'weight_class_description` ocwcd ON ocp.weight_class_id = ocwcd.weight_class_id '.
		'INNER JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'length_class_description` oclcd ON ocp.length_class_id = oclcd.length_class_id '.
		'INNER JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'product_description` ocpd ON ocp.product_id = ocpd.product_id '.
		'LEFT JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_taxation` hkt ON hkt.tax_openc_id = ocp.tax_class_id '.
		'LEFT JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_category` hkc ON hkc.category_namekey = hkt.category_namekey '.
		'LEFT JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_openc_prod` AS hkp ON ocp.product_id = hkp.openc_id '.
		'WHERE hkp.hk_id IS NULL ORDER BY ocp.product_id ASC;';

		$this->db->setQuery("SHOW COLUMNS FROM `".$this->opencDatabase."`.`'.$this->opencPrefix.'product` LIKE 'hika_sku';");
		$data = $this->db->loadObjectList();
		if (empty($data))
		{
			$this->db->setQuery('ALTER TABLE `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'product` ADD COLUMN `hika_sku` VARCHAR(255) NOT NULL;');
			$this->db->query();
		}

		$this->db->setQuery('UPDATE `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'product` AS ocp SET ocp.hika_sku = ocp.sku;');
		$this->db->query();
		$this->db->setQuery("UPDATE `".$this->opencDatabase."`.`".$this->opencPrefix."product` AS ocp SET ocp.hika_sku = CONCAT(ocp.model,'_',ocp.product_id) WHERE ocp.hika_sku='';");
		$this->db->query();

		$this->db->setQuery('SELECT hika_sku FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'product` GROUP BY hika_sku HAVING COUNT(hika_sku)>1');
		$data = $this->db->loadObjectList();

		if (!empty($data))
		{
			foreach ($data as $d)
			{
				$this->db->setQuery("UPDATE `oc_product` AS ocp SET ocp.hika_sku = CONCAT(ocp.hika_sku,'_',ocp.product_id) WHERE ocp.hika_sku = '".$d->hika_sku."';");
				$this->db->query();
			}
		}

		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> SKU generated: '.count($data).'</p>';

		$data = array(
			'openc_id' => 'ocp.product_id',
			'hk_id' => 'hkp.product_id'
		);

		$sql2 = 'INSERT IGNORE INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_openc_prod` (`'.implode('`,`',array_keys($data)).'`) '.
		'SELECT '.implode(',',$data).' FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'product` AS ocp '.
		'INNER JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_product` AS hkp ON CONVERT(ocp.hika_sku USING utf8) = CONVERT(hkp.product_code USING utf8) '.
		'LEFT JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_openc_prod` hkocp ON hkocp.openc_id = ocp.product_id '.
		'WHERE hkocp.hk_id IS NULL;';


		$data = array(
			'file_name' => "''",
			'file_description' => "''",
			'file_path' => "SUBSTRING_INDEX(ocpi.image,'/',-1)",
			'file_type' => "'product'",
			'file_ref_id' => 'hkocp.hk_id'
		);


		$sql4 = 'INSERT IGNORE INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_file` (`'.implode('`,`',array_keys($data)).'`) '.
		'SELECT '.implode(',',$data).' FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'product` AS ocp '.
		'INNER JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'product_image` ocpi ON ocp.product_id = ocpi.product_id '.
		'INNER JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_openc_prod` AS hkocp ON ocp.product_id = hkocp.openc_id '.
		'WHERE ocp.product_id > '.(int)$this->options->last_openc_prod. ' AND (ocpi.image IS NOT NULL) AND (ocpi.image <>'." '');";

		$sql5 = 'UPDATE `'.$this->hikaDatabaseName.'`.`#__hikashop_product` AS hkp '.
		'INNER JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_openc_prod` AS hkocp ON hkp.product_id = hkocp.hk_id '.
		'INNER JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'product` AS ocp ON hkocp.openc_id = ocp.product_id '.
		"INNER JOIN `".$this->hikaDatabaseName."`.`#__hikashop_category` AS hkc ON hkc.category_type = 'manufacturer' AND ocp.manufacturer_id = hkc.category_menu ".
		'SET hkp.product_manufacturer_id = hkc.category_id '.
		'WHERE ocp.manufacturer_id > '.(int)$this->options->last_openc_manufacturer.' OR ocp.product_id > '.$this->options->last_openc_prod.';';

		$this->db->setQuery($sql1);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Inserted products: ' . $total . '</p>';

		$this->db->setQuery($sql2);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Fallback links: ' . $total . '</p>';

		$this->db->setQuery($sql4);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Inserted products files: ' . $total . '</p>';

		$this->db->setQuery($sql5);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Updating products manufacturers: ' . $total . '</p>';

		$ret = true;
		return $ret;
	}


	function importVariant()
	{

	}


	function importProductPrices()
	{
		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 6 :</span> Import Product Prices</p>';

		$ret = false;
		$cpt = 0;

		$this->db->setQuery('SELECT hkcur.currency_id FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'currency` occ '.
						'INNER JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_currency` hkcur ON CONVERT(occ.code USING utf8) = CONVERT( hkcur.currency_code USING utf8) '.
						"WHERE occ.value = '1.0';");

		$data = $this->db->loadObjectList();

		if (!empty($data))
		{
			$this->db->setQuery('INSERT IGNORE INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_price` (`price_product_id`,`price_value`,`price_currency_id`,`price_min_quantity`,`price_access`) '
					."SELECT hkprod.hk_Id, ocp.price, '".$data[0]->currency_id."', '0', 'all' "
					.'FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'product` ocp INNER JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_openc_prod` hkprod ON ocp.product_id = hkprod.openc_id '
					.'WHERE ocp.product_id > ' . (int)$this->options->last_openc_prod
			);
		}
		else
		{
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Prices imported : 0</p>';
		}

		$ret = $this->db->query();
		$cpt = $this->db->getAffectedRows();

		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Prices imported : ' . $cpt .'</p>';

		if ($this->importcurrencies)
			$this->importCurrencies();

		return $ret;
	}


	function importProductCategory()
	{
		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 7 :</span> Import Product Category</p>';
		$ret = false;

		$data = array(
			'category_id' => 'hocc.hk_id',
			'product_id' => 'hocp.hk_id',
			'ordering' => '0',
		);

		$sql = 'INSERT IGNORE INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_product_category` (`'.implode('`,`',array_keys($data)).'`) '.
		'SELECT ' . implode(',',$data).' FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'product_to_category` ocpc '.
		'INNER JOIN #__hikashop_openc_cat hocc ON ocpc.category_id = hocc.openc_id AND category_type=\'category\' '.
		'INNER JOIN #__hikashop_openc_prod hocp ON ocpc.product_id = hocp.openc_id '.
		'WHERE hocp.openc_id > ' . (int)$this->options->last_openc_prod . ' OR hocc.openc_id > ' . $this->options->last_openc_cat;

		$this->db->setQuery($sql);
		$ret = $this->db->query();

		$total = $this->db->getAffectedRows();
		$this->importRebuildTree();

		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Inserted products categories: ' . $total . '</p>';

		return $ret;

	}

	function importUsers()
	{
		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 8 :</span> Import Users</p>';
		$ret = false;

		$sqla = 'SELECT ocu.* FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'user` ocu '.
		'LEFT JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_openc_user` hkocu ON ocu.user_id = hkocu.openc_user_id '.
		'WHERE hkocu.openc_user_id IS NULL '.
		'ORDER BY ocu.user_id ASC;';

		$this->db->setQuery($sqla);
		$this->db->query();
		$datas = $this->db->loadObjectList();
		$i = $this->options->max_joomla_user + 1;

		if( empty($datas) )
		{
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported user to Joomla : 0</p>';
		}
		else
		{
			$total = $this->importUsersToJoomla($datas,$i,false);
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported users to Joomla : ' . $total . '</p>';
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Fallback links (users) : ' . $total . '</p>';
		}


		$sqla = 'SELECT occu.* FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'customer` occu '.
		'LEFT JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_openc_customer` hkoccu ON occu.customer_id = hkoccu.openc_customer_id '.
		'WHERE hkoccu.openc_customer_id IS NULL '.
		'ORDER BY occu.customer_id ASC;';

		$this->db->setQuery($sqla);
		$this->db->query();
		$datas = $this->db->loadObjectList();

		if( empty($datas) )
		{
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported customer to Joomla : 0</p>';
		}
		else
		{
			$total = $this->importUsersToJoomla($datas,$i,true);
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported customers to Joomla : ' . $total . '</p>';
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Fallback links (customers) : ' . $total . '</p>';
		}

		$sql0 = 'INSERT IGNORE INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_user` (`user_cms_id`,`user_email`) '.
				'SELECT hkoccu.hk_customer_cms_id, occ.email FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'customer` occ '.
				'INNER JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_openc_customer` hkoccu ON hkoccu.openc_customer_id = occ.customer_id '.
				'LEFT JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_user` AS hkusr ON hkoccu.hk_customer_cms_id = hkusr.user_cms_id '.
				'WHERE hkusr.user_cms_id IS NULL;';


		$data = array(
			'address_user_id' => 'hku.user_id',
			'address_title' => "'Mr'",
			'address_firstname' => 'oca.firstname',
			'address_lastname' => 'oca.lastname',
			'address_company' => 'oca.company',
			'address_street' => 'CONCAT(oca.address_1,\' \',oca.address_2)',
			'address_post_code' => 'oca.postcode',
			'address_city' => 'oca.city',
			'address_telephone' => 'occu.telephone',
			'address_fax' => 'occu.fax',
			'address_state' => 'hkzsta.zone_namekey',
			'address_country' => 'hkzcou.zone_namekey',
			'address_published' => 4
		);

		$sql1 = 'INSERT IGNORE INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_address` (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'customer` AS occu '.
				'INNER JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'address` AS oca ON occu.customer_id = oca.customer_id '.
				'INNER JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_openc_customer` hkoccu ON oca.customer_id = hkoccu.openc_customer_id '.
				'INNER JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_user` AS hku ON hkoccu.hk_customer_cms_id = hku.user_cms_id '.
				'INNER JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'country` AS occ ON oca.country_id = occ.country_id '.
				'INNER JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'zone` AS ocz ON oca.zone_id = ocz.zone_id '.
				'LEFT JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_zone` AS  hkzcou ON occ.iso_code_3 = hkzcou.zone_code_3 AND hkzcou.zone_type=\'country\' '.
				'INNER JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_zone_link` AS hkzl ON hkzcou.zone_namekey = hkzl.zone_parent_namekey '.
				'INNER JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_zone` AS  hkzsta ON ocz.code = hkzsta.zone_code_3 AND hkzsta.zone_type=\'state\' AND hkzsta.zone_namekey = hkzl.zone_child_namekey '.
				'WHERE occu.customer_id > '.(int)$this->options->last_openc_user.' ORDER BY oca.customer_id ASC';


		$this->db->setQuery($sql0);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported users to Hikashop : ' . $total . '</p>';

		$this->db->setQuery($sql1);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported addresses : ' . $total . '</p>';



		$ret = true;
		return $ret;
	}


	function importUsersToJoomla($datas,&$i,$customer)
	{
		$cpt = 0;
		foreach($datas as $data)
		{
			if ($customer)
				$data->username = $data->email;
			$this->db->setQuery("SELECT * FROM `#__users` WHERE username = ".$this->db->quote($data->username).";");
			$this->db->query();
			$result = $this->db->loadObjectList();
			if (!empty($result))
			{
				echo '<p><span'.$this->bullstyle.'>&#9658;</span> The user <strong>'.$data->username.'</strong> won\'t be imported because an user with the same name already exists on Joomla.</p>';
			}
			else
			{
				$sqlb = 'INSERT IGNORE INTO `#__users` (id, name, username, email, password, block, sendEmail, registerDate, params) VALUES ';
				$sqlc = 'INSERT IGNORE INTO `#__user_usergroup_map` (user_id, group_id) VALUES ';
				if ($customer)
				{
					$sqld = 'INSERT IGNORE INTO `#__hikashop_openc_customer` (openc_customer_id, hk_customer_cms_id) VALUES ';
					$block = !$data->approved;
				}
				else
				{
					$sqld = 'INSERT IGNORE INTO `#__hikashop_openc_user` (openc_user_id, hk_user_cms_id) VALUES ';
					$block = 0;
				}
				$d = array(
					$i,
					$this->db->quote($data->lastname),
					$this->db->quote($data->username),
					$this->db->quote($data->email),
					"CONCAT(".$this->db->quote(@$data->password).",':',".$this->db->quote(@$data->salt).")",
					$this->db->quote($block),
					'0',
					$this->db->quote($data->date_added),
					"'{}'",
				);

				$sqlb .= '('.implode(',',$d).');';

				if (isset($data->password)) //Don't insert guest
				{
					$this->db->setQuery($sqlb);
					$this->db->query();
				}

				if (!$customer)
					$data->user_group_id == 1 ? $group = 8 : $group = 2;
				else
					$group = 2;

				$sqlc .= '('.$i.','.$group.')';

				if (isset($data->password)) //Don't insert guest
				{
					$this->db->setQuery($sqlc);
					$this->db->query();
				}

				if ($customer)
					$sqld .= '('.$data->customer_id.','.$i.')';
				else
					$sqld .= '('.$data->user_id.','.$i.')';

				$this->db->setQuery($sqld);
				$this->db->query();

				$i++;
				$cpt++;
			}
		}
		return $cpt;
	}


	function importOrders()
	{
		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 10 :</span> Import Orders</p>';

		$ret = false;
		$offset = $this->options->current;
		$count = 100;
		$total = 0;

		$vat_cols = "''";

		$data = array(
			'order_number' => 'oco.order_id',
			'order_openc_id' => 'oco.order_id',
			'order_user_id' => 'hkusr.user_id',
			'order_status' => 'hkc.category_name',
			'order_created' => 'oco.date_added',
			'order_ip' => 'oco.ip',
			'order_currency_id' => 'hkcur.currency_id',
			'order_shipping_price' => "''", //?
			'order_shipping_method' => 'oco.shipping_method',
			'order_shipping_id' => '1',
			'order_payment_id' => 0,
			'order_payment_method' => 'oco.payment_method',
			'order_full_price' => 'ocot.value',
			'order_modified' => 'oco.date_modified',
			'order_partner_id' => 0,
			'order_partner_price' => 0,
			'order_partner_paid' => 0,
			'order_type' => "'sale'",
			'order_partner_currency_id' => 0,
			'order_shipping_tax' => "''", //?
			'order_discount_tax' => 0
		);

		$sql1 = 'INSERT IGNORE INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_order` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'order` AS oco '.
			'INNER JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'order_product` ocop ON ocop.order_id = oco.order_id '.
			'INNER JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_category` AS hkc ON oco.order_status_id = hkc.category_keywords AND hkc.category_type = \'status\' '.
			'INNER JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_currency` AS hkcur ON CONVERT(oco.currency_code USING utf8) = CONVERT(hkcur.currency_code USING utf8) '.
			'INNER JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'order_total` ocot ON oco.order_id = ocot.order_id AND code=\'total\' '.
			'INNER JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_openc_customer` hkoccu ON oco.customer_id = hkoccu.openc_customer_id '.
			'INNER JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_user` AS hkusr ON hkoccu.hk_customer_cms_id = hkusr.user_cms_id '.
			'WHERE oco.order_id > ' . (int)$this->options->last_openc_order . ' '.
			'GROUP BY oco.order_id '.
			'ORDER BY oco.order_id ASC;';

		$sql1_1 = 'UPDATE `'.$this->hikaDatabaseName.'`.`#__hikashop_order` AS hko '.
				'INNER JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'voucher` AS ocv ON hko.order_openc_id = ocv.order_id '.
				'INNER JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_discount` AS hkd ON hkd.discount_code = ocv.code '.
				'SET hko.order_discount_code = hkd.discount_code AND hko.order_discount_price = hkd.discount_flat_amount';

		$data = array(
			'address_user_id' => 'oco.customer_id',
			'address_firstname' => 'oco.payment_firstname',
			'address_lastname' => 'oco.payment_lastname',
			'address_company' => 'oco.payment_company',
			'address_street' => 'CONCAT(oco.payment_address_1,\' \',oco.payment_address_2)',
			'address_post_code' => 'oco.payment_postcode',
			'address_city' => 'oco.payment_city ',
			'address_telephone' => 'oco.telephone',
			'address_fax' => 'oco.fax',
			'address_state' => 'hkzsta.zone_namekey',
			'address_country' => 'hkzcou.zone_namekey',
			'address_published' => '7',
			'address_vat' => $vat_cols,
			'address_openc_order_info_id' => 'oco.order_id' //8
		);

		$sql2_1 = 'INSERT IGNORE INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_address` (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'order` AS oco '.
				'INNER JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'country` AS occ ON oco.payment_country_id = occ.country_id '.
				'INNER JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'zone` AS ocz ON oco.payment_zone_id = ocz.zone_id '.
				'LEFT JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_zone` AS  hkzcou ON occ.iso_code_3 = hkzcou.zone_code_3 AND hkzcou.zone_type=\'country\' '.
				'INNER JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_zone_link` AS hkzl ON hkzcou.zone_namekey = hkzl.zone_parent_namekey '.
				'INNER JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_zone` AS  hkzsta ON ocz.code = hkzsta.zone_code_3 AND hkzsta.zone_type=\'state\' AND hkzsta.zone_namekey = hkzl.zone_child_namekey '.
				'WHERE oco.order_id > ' . (int)$this->options->last_openc_order;

		$sql2_2 = 'UPDATE `#__hikashop_address` AS a '.
				'JOIN `#__hikashop_zone` AS hkz ON (a.address_country = hkz.zone_code_3 AND hkz.zone_type = "country") '.
				'SET address_country = hkz.zone_namekey, address_published = 6 WHERE address_published >= 7;';

		$sql2_3 = 'UPDATE `#__hikashop_address` AS a '.
				'JOIN `#__hikashop_zone_link` AS zl ON (a.address_country = zl.zone_parent_namekey) '.
				'JOIN `#__hikashop_zone` AS hks ON (hks.zone_namekey = zl.zone_child_namekey AND hks.zone_type = "state" AND hks.zone_code_3 = a.address_state) '.
				'SET address_state = hks.zone_namekey, address_published = 5 WHERE address_published = 6;';

		$sql2_4 = 'UPDATE `#__hikashop_address` AS a '.
				'SET address_published = 0 WHERE address_published > 4;';


		$sql3 = 'UPDATE `#__hikashop_order` AS o '.
			'INNER JOIN `#__hikashop_address` AS a ON a.address_openc_order_info_id = o.order_openc_id '.
			'SET o.order_billing_address_id = a.address_id, o.order_shipping_address_id = a.address_id '.
			"WHERE o.order_billing_address_id = 0 AND address_published >= 7 ;";

		$sql4 = 'UPDATE `#__hikashop_order` AS o '.
			'INNER JOIN `#__hikashop_address` AS a ON a.address_openc_order_info_id = o.order_openc_id '.
			'SET o.order_shipping_address_id = a.address_id '.
			"WHERE o.order_shipping_address_id = 0 AND address_published >= 8 ;";

		$sql5 = 'UPDATE `'.$this->hikaDatabaseName.'`.`#__hikashop_order` AS hko '.
			'JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'order` AS oco ON hko.order_openc_id = oco.order_id '.
			"SET hko.order_payment_method = CONCAT('openc import: ', oco.payment_method) ".
			'WHERE hko.order_openc_id > ' . (int)$this->options->last_openc_order;

		$this->db->setQuery($sql1);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported orders: ' . $total . '</p>';

		$this->db->setQuery($sql1_1);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Updating discount orders: ' . $total . '</p>';

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
		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 11 :</span> Import Order Items</p>';
		$ret = false;

		$offset = $this->options->current;
		$count = 100;

		$data = array(
			'order_id' => 'hko.order_id',
			'product_id' => 'hkp.hk_id',
			'order_product_quantity' => 'ocop.quantity',
			'order_product_name' => 'ocop.name',
			'order_product_code' => 'ocp.hika_sku',
			'order_product_price' => 'ocop.price',
			'order_product_tax' => 'ocop.tax',
			'order_product_options' => "''"
		);

		$sql = 'INSERT IGNORE INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_order_product` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'order_product` AS ocop '.
			'INNER JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'product` ocp ON ocop.product_id=ocp.product_id '.
			'INNER JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_order` AS hko ON ocop.order_id = hko.order_openc_id '.
			'INNER JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_openc_prod` AS hkp ON hkp.openc_id = ocop.product_id '.
			'WHERE ocop.order_id > ' . (int)$this->options->last_openc_order . ';';

		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();

		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Orders Items : '. $total .'</p>';
		$ret = true;

		return $ret;
	}


	function importDownloads()
	{
		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 12 :</span> Import Downloads</p>';
		$ret = false;

		jimport('joomla.filesystem.file');
		$categoryClass = hikashop_get('class.category');

		$count = 100;
		$offset = $this->options->current;
		if( $offset == 0 )
			$offset = $this->options->last_openc_pfile;

		$sql = "SELECT `config_value` FROM `#__hikashop_config` WHERE config_namekey = 'download_number_limit';";
		$this->db->setQuery($sql);
		$data = $this->db->loadObjectList();
		$dl_limit = $data[0]->config_value;

		$sql = 'SELECT ocd.download_id, ocd.filename FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'download` AS ocd WHERE ocd.download_id > '.$offset.' ORDER BY ocd.download_id ASC LIMIT '.$count.';';
		$this->db->setQuery($sql);
		$data = $this->db->loadObjectList();
		$max = 0;

		if (!empty($data)  && !empty($this->opencRootPath))
		{
			if ( defined('DIR_DOWNLOAD') )
				$this->copyDownloadDir = str_replace('\\','/',rtrim(JPath::clean(html_entity_decode(DIR_DOWNLOAD)),DS.' ').DS);
			else
				$this->copyDownloadDir = 'C:/wamp/www/Opencart_156/download/';

			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Copying downloads files... </p>';

			foreach($data as $c)
			{
				$file_name = end(explode('/',str_replace('\\','/',$c->filename)));
				if( strpos($file_name,'/') !== false ) {
					$file_name = substr($file_name, strrpos($file_name,'/'));
				}
				$dstFolder = $this->options->uploadfolder; //secure ?
				$this->copyFile($this->copyDownloadDir, $c->filename, $dstFolder.$file_name);
				$max = $c->download_id;
			}
			if( $max > 0 )
			{
				echo '<p>Copying files...<br/>(Last processed file id: ' . $max . '</p>';
				$this->options->current = $max;
				$this->refreshPage = true;
				return $ret;
			}
		}

		$data = array(
			'file_name' => 'ocdd.name',
			'file_description' => "''",
			'file_path' => "SUBSTRING_INDEX(ocd.filename ,'/',-1)", //Why no filename ?
			'file_type' => "'file'",
			'file_ref_id' => 'hkocp.hk_id'
		);

		$sql = 'INSERT IGNORE INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_file` (`'.implode('`,`',array_keys($data)).'`) '.
		'SELECT '.implode(',',$data).' FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'download` AS ocd '.
		'INNER JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'download_description` ocdd ON ocd.download_id = ocdd.download_id '.
		'LEFT JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'product_to_download` ocpd ON ocd.download_id = ocpd.download_id '.
		'LEFT JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_openc_prod` AS hkocp ON ocpd.product_id = hkocp.openc_id '.
		'WHERE ocd.download_id > '.(int)$this->options->last_openc_pfile;

		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();

		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Downloable files imported : ' . $total . '</p>';

		$data = array(
			'file_id' => 'hkf.file_id',
			'order_id' => 'hko.order_id',
			'download_number' => '(' . $dl_limit . '- ocd.remaining)' //$dl_limit ?
		);

		$sql = 'INSERT IGNORE INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_download` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'download` AS ocd '.
			'INNER JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'download_description` ocdd ON ocd.download_id = ocdd.download_id '.
			'INNER JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_file` AS hkf ON ( CONVERT(hkf.file_name USING utf8) = CONVERT(ocdd.name USING utf8) )'.
			'INNER JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'product_to_download` AS ocpd ON ocd.download_id = ocpd.download_id '.
			'INNER JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'order_product` AS ocop ON ocpd.product_id = ocop.product_id '.
			'INNER JOIN `'.$this->hikaDatabaseName.'`.`#__hikashop_order` AS hko ON hko.order_openc_id = ocop.order_id '.
			'WHERE ocd.download_id > '.(int)$this->options->last_openc_pfile;

		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();

		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Downloable order files imported : ' . $total . '</p>';

		$ret = true;
		return $ret;
	}


	function importDiscount()
	{
		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 9 :</span> Import Discount</p>';
		$ret = false;

		$sql = "SELECT `config_value` FROM `#__hikashop_config` WHERE config_namekey = 'main_currency';";
		$this->db->setQuery($sql);
		$data = $this->db->loadObjectList();
		$main_currency = $data[0]->config_value;

		$data = array(
			'discount_type' => "'coupon'",
			'discount_start' => 'date_start',
			'discount_end' => 'date_end',
			'discount_quota' => 'uses_total',
			'discount_quota_per_user' => 'uses_customer',
			'discount_published' => 'status',
			'discount_code' => 'code',
			'discount_currency_id' => $main_currency,
			'discount_flat_amount' => "case when type = 'F' then discount else 0 end",
			'discount_percent_amount' => "case when type = 'P' then discount else 0 end",
			'discount_quota' => '0'
		);

		$sql = 'INSERT IGNORE INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_discount` (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'coupon` WHERE coupon_id > ' . $this->options->last_openc_coupon;

		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Discount codes/coupons imported : ' . $total . '</p>';


		$data = array(
			'discount_type' => "'coupon'",
			'discount_code' => 'code',
			'discount_currency_id' => $main_currency,
			'discount_flat_amount' => 'amount',
			'discount_percent_amount' => '0',
			'discount_published' => 'status',
		);

		$sql = 'INSERT IGNORE INTO `'.$this->hikaDatabaseName.'`.`#__hikashop_discount` (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'voucher` WHERE voucher_id > ' . $this->options->last_openc_voucher;

		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Vouchers imported : ' . $total . '</p>';

		$ret = true;
		return $ret;
	}


	function importCurrencies()
	{
		if( $this->db == null )
			return false;

		$this->db->setQuery('UPDATE `'.$this->hikaDatabaseName.'`.`#__hikashop_currency` AS hkcur '.
						'INNER JOIN `'.$this->opencDatabase.'`.`'.$this->opencPrefix.'currency` occ ON CONVERT(occ.code USING utf8) = CONVERT( hkcur.currency_code USING utf8) '.
						'SET hkcur.currency_rate = occ.value'
		);

		$ret = $this->db->query();
		$cpt = $this->db->getAffectedRows();

		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Currencies values imported : ' . $cpt .'</p>';

		return true;
	}

	function finishImport()
	{
		$this->db->setQuery("SELECT max(category_id) as 'max' FROM `#__hikashop_category`;");
		$data = $this->db->loadObjectList();
		$this->options->max_hk_cat = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(id) as 'max' FROM `#__users`;");
		$data = $this->db->loadObjectList();
		$this->options->max_joomla_user = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(product_id) as 'max' FROM `#__hikashop_product`;");
		$data = $this->db->loadObjectList();
		$this->options->max_hk_prod = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(openc_id) as 'max' FROM `#__hikashop_openc_cat`;");
		$data = $this->db->loadObjectList();
		$this->options->last_openc_cat = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(openc_id) as 'max' FROM `#__hikashop_openc_prod`;");
		$data = $this->db->loadObjectList();
		$this->options->last_openc_prod = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(customer_id) as 'max' FROM `".$this->opencDatabase."`.`".$this->opencPrefix."customer`;");
		$data = $this->db->loadObjectList();
		$this->options->last_openc_user = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(order_openc_id) as 'max' FROM `#__hikashop_order`;");
		$data = $this->db->loadObjectList();
		$this->options->last_openc_order = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(ocpi.product_image_id) as 'max' FROM `".$this->opencDatabase."`.`".$this->opencPrefix."product_image` ocpi INNER JOIN `".$this->opencDatabase."`.`".$this->opencPrefix."product` ocp ON ocpi.product_id = ocp.product_id");
		$data = $this->db->loadObject();
		$this->options->last_openc_pfile = (int)($data->max);

		$this->db->setQuery("SELECT max(coupon_id) as 'max' FROM `".$this->opencDatabase."`.`".$this->opencPrefix."coupon`;");
		$data = $this->db->loadObject();
		$this->options->last_openc_coupon = (int)($data->max);

		$this->db->setQuery("SELECT max(voucher_id) as 'max' FROM `".$this->opencDatabase."`.`".$this->opencPrefix."voucher`;");
		$data = $this->db->loadObject();
		$this->options->last_openc_voucher = (int)($data->max);

		$this->db->setQuery("SELECT max(tax_rate_id) as 'max' FROM `".$this->opencDatabase."`.`".$this->opencPrefix."tax_rate`;");
		$data = $this->db->loadObject();
		$this->options->last_openc_taxrate = (int)($data->max);

		$this->db->setQuery("SELECT max(tax_class_id) as 'max' FROM `".$this->opencDatabase."`.`".$this->opencPrefix."tax_class`;");
		$data = $this->db->loadObject();
		$this->options->last_openc_taxclass = (int)($data->max);

		$this->db->setQuery("SELECT max(manufacturer_id) as 'max' FROM `".$this->opencDatabase."`.`".$this->opencPrefix."manufacturer`;");
		$data = $this->db->loadObjectList();
		$this->options->last_openc_manufacturer = (int)($data[0]->max);

		$this->options->state = (MAX_IMPORT_ID+1);
		$query = 'REPLACE INTO `#__hikashop_config` (`config_namekey`,`config_value`,`config_default`) VALUES '.
				"('openc_import_state',".$this->options->state.",".$this->options->state.")".
				",('openc_import_max_hk_cat',".$this->options->max_hk_cat.",".$this->options->max_hk_cat.")".
				",('openc_import_max_joomla_user',".$this->options->max_joomla_user.",".$this->options->max_joomla_user.")".
				",('openc_import_max_hk_prod',".$this->options->max_hk_prod.",".$this->options->max_hk_prod.")".
				",('openc_import_last_openc_cat',".$this->options->last_openc_cat.",".$this->options->last_openc_cat.")".
				",('openc_import_last_openc_prod',".$this->options->last_openc_prod.",".$this->options->last_openc_prod.")".
				",('openc_import_last_openc_user',".$this->options->last_openc_user.",".$this->options->last_openc_user.")".
				",('openc_import_last_openc_order',".$this->options->last_openc_order.",".$this->options->last_openc_order.")".
				",('openc_import_last_openc_pfile',".$this->options->last_openc_pfile.",".$this->options->last_openc_pfile.")".
				",('openc_import_last_openc_coupon',".$this->options->last_openc_coupon.",".$this->options->last_openc_coupon.")".
				",('openc_import_last_openc_voucher',".$this->options->last_openc_voucher.",".$this->options->last_openc_voucher.")".
				",('openc_import_last_openc_taxrate',".$this->options->last_openc_taxrate.",".$this->options->last_openc_taxrate.")".
				",('openc_import_last_openc_taxclass',".$this->options->last_openc_taxclass.",".$this->options->last_openc_taxclass.")".
				",('openc_import_last_openc_manufacturer',".$this->options->last_openc_manufacturer.",".$this->options->last_openc_manufacturer.")".
				';';
		$this->db->setQuery($query);
		$this->db->query();

		echo '<p'.$this->titlefont.'>Import finished !</p>';

		$class = hikashop_get('class.plugins');

	}

}
