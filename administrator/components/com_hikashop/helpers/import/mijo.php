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
@include(HIKASHOP_ROOT . 'components/com_mijoshop/opencart/admin/config.php');

class hikashopImportmijoHelper extends hikashopImportHelper
{
	var $importcurrencies;

	function __construct()
	{
		parent::__construct();
		$this->importName = 'mijo';
		jimport('joomla.filesystem.file');
	}

	function importFromMijo()
	{
		@ob_clean();
		echo $this->getHtmlPage();

		$this->token = hikashop_getFormToken();
		flush();

		if( isset($_GET['import']) && $_GET['import'] == '1' )
		{
			$this->importcurrencies = JRequest::getInt('importcurrencies');
			$time = microtime(true);
			$processed = $this->doImport();
			if( $processed )
			{
				$elasped = microtime(true) - $time;

				if( !$this->refreshPage )
					echo '<p></br><a'.$this->linkstyle.'href="'.hikashop_completeLink('import&task=import&importfrom=mijo&'.$this->token.'=1&import=1&importcurrencies='.$this->importcurrencies.'&time='.time()).'">'.JText::_('HIKA_NEXT').'</a></p>';

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
		$this->importcurrencies = JRequest::getInt('import_currencies');
		return '<span style="color:#297F93; font-size:1.2em;text-decoration:underline;">Step 0</span><br/><br/>'.
			'Make a backup of your database.<br/>'.
			'When ready, click on <a '.$this->linkstyle.' href="'.hikashop_completeLink('import&task=import&importfrom=mijo&'.$this->token.'=1&import=1&importcurrencies='.$this->importcurrencies).'">'.JText::_('HIKA_NEXT').'</a>, otherwise '.
			'<a'.$this->linkstyle.' href="'.hikashop_completeLink('import&task=show').'">'.JText::_('HIKA_BACK').'</a>.';
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
			$sql =  "UPDATE `#__hikashop_config` SET config_value=(config_value+1) WHERE config_namekey = 'mijo_import_state'; ";
			$this->db->setQuery($sql);
			$this->db->query();
			$sql = "UPDATE `#__hikashop_config` SET config_value=0 WHERE config_namekey = 'mijo_import_current';";
			$this->db->setQuery($sql);
			$this->db->query();
		}
		else if( $current != $this->options->current )
		{
			$sql =  "UPDATE `#__hikashop_config` SET config_value=".$this->options->current." WHERE config_namekey = 'mijo_import_current';";
			$this->db->setQuery($sql);
			$this->db->query();
		}

		return $ret;

	}

	function loadConfiguration()
	{
		$this->options = null;

		if (defined('DIR_IMAGE')) {
			if(strpos(DIR_IMAGE, HIKASHOP_ROOT) === false)
				$this->copyImgDir = HIKASHOP_ROOT.DIR_IMAGE;
			else
				$this->copyImgDir = DIR_IMAGE;
		} else
			$this->copyImgDir = HIKASHOP_ROOT.'components/com_mijoshop/opencart/image/';

		if (defined('DIR_IMAGE')) {
			if(strpos(DIR_IMAGE, HIKASHOP_ROOT) === false)
				$this->copyCatImgDir = HIKASHOP_ROOT.DIR_IMAGE;
			else
				$this->copyCatImgDir = DIR_IMAGE;
		} else
			$this->copyCatImgDir = HIKASHOP_ROOT.'components/com_mijoshop/opencart/image/';

		if (defined('DIR_DOWNLOAD')) {
			if(strpos(DIR_DOWNLOAD, HIKASHOP_ROOT) === false)
				$this->copyDownloadDir = HIKASHOP_ROOT.DIR_DOWNLOAD;
			else
				$this->copyDownloadDir = DIR_DOWNLOAD;
		} else
			$this->copyDownloadDir = HIKASHOP_ROOT.'components/com_mijoshop/opencart/download/';

		$data = array(
			'uploadfolder',
			'uploadsecurefolder',
			'main_currency',
			'mijo_import_state',
			'mijo_import_current',
			'mijo_import_tax_id',
			'mijo_import_main_cat_id',
			'mijo_import_max_hk_cat',
			'mijo_import_max_hk_prod',
			'mijo_import_last_mijo_cat',
			'mijo_import_last_mijo_prod',
			'mijo_import_last_mijo_user',
			'mijo_import_last_mijo_order',
			'mijo_import_last_mijo_pfile',
			'mijo_import_last_mijo_coupon',
			'mijo_import_last_mijo_voucher',
			'mijo_import_last_mijo_taxrate',
			'mijo_import_last_mijo_taxclass',
			'mijo_import_last_mijo_manufacturer'
		);
		$this->db->setQuery('SELECT config_namekey, config_value FROM `#__hikashop_config` WHERE config_namekey IN ('."'".implode("','",$data)."'".');');

		$result = $this->db->loadObjectList();
		if (!empty($result))
		{
			foreach($result as $o)
			{
				if( substr($o->config_namekey, 0, 12) == 'mijo_import_' )
					$nk = substr($o->config_namekey, 12);
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
			$this->options->last_mijo_coupon = 0;
			$this->options->last_mijo_voucher = 0;
			$this->options->last_mijo_pfile = 0;
			$this->options->last_mijo_taxrate = 0;
			$this->options->last_mijo_taxclass = 0;
			$this->options->last_mijo_manufacturer = 0;

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


			$query='SHOW TABLES LIKE '.$this->db->Quote($this->db->getPrefix().substr(hikashop_table('mijo_cat'),3));
			$this->db->setQuery($query);
			$table = $this->db->loadResult();
			if(!empty($table))
			{
				$this->db->setQuery("SELECT max(mijo_id) as 'max' FROM `#__hikashop_mijo_cat`;");
				$data = $this->db->loadObjectList();
				if( $data )
					$this->options->last_mijo_cat = (int)($data[0]->max);
				else
					$this->options->last_mijo_cat = 0;

				$this->db->setQuery("SELECT max(mijo_id) as 'max' FROM `#__hikashop_mijo_prod`;");
				$data = $this->db->loadObjectList();
				if( $data )
					$this->options->last_mijo_prod = (int)($data[0]->max);
				else
					$this->options->last_mijo_prod = 0;

				$this->db->setQuery("SELECT max(order_mijo_id) as 'max' FROM `#__hikashop_order`;");
				$data = $this->db->loadObjectList();
				if( $data )
					$this->options->last_mijo_order = (int)($data[0]->max);
				else
					$this->options->last_mijo_order = 0;
			}
			else
			{
				$this->options->last_mijo_cat = 0;
				$this->options->last_mijo_prod = 0;
				$this->options->last_mijo_order = 0;
			}

			$this->options->last_mijo_user = 0;

			$sql = 'INSERT IGNORE INTO `#__hikashop_config` (`config_namekey`,`config_value`,`config_default`) VALUES '.
				"('mijo_import_state',".$this->options->state.",".$this->options->state.")".
				",('mijo_import_current',".$this->options->current.",".$this->options->current.")".
				",('mijo_import_tax_id',".$this->options->tax_id.",".$this->options->tax_id.")".
				",('mijo_import_main_cat_id',".$this->options->main_cat_id.",".$this->options->main_cat_id.")".
				",('mijo_import_max_hk_cat',".$this->options->max_hk_cat.",".$this->options->max_hk_cat.")".
				",('mijo_import_max_hk_prod',".$this->options->max_hk_prod.",".$this->options->max_hk_prod.")".
				",('mijo_import_last_mijo_cat',".$this->options->last_mijo_cat.",".$this->options->last_mijo_cat.")".
				",('mijo_import_last_mijo_prod',".$this->options->last_mijo_prod.",".$this->options->last_mijo_prod.")".
				",('mijo_import_last_mijo_user',".$this->options->last_mijo_user.",".$this->options->last_mijo_user.")".
				",('mijo_import_last_mijo_order',".$this->options->last_mijo_order.",".$this->options->last_mijo_order.")".
				",('mijo_import_last_mijo_pfile',".$this->options->last_mijo_pfile.",".$this->options->last_mijo_pfile.")".
				",('mijo_import_last_mijo_coupon',".$this->options->last_mijo_coupon.",".$this->options->last_mijo_coupon.")".
				",('mijo_import_last_mijo_voucher',".$this->options->last_mijo_voucher.",".$this->options->last_mijo_voucher.")".
				",('mijo_import_last_mijo_taxrate',".$this->options->last_mijo_taxrate.",".$this->options->last_mijo_taxrate.")".
				",('mijo_import_last_mijo_taxclass',".$this->options->last_mijo_taxclass.",".$this->options->last_mijo_taxclass.")".
				",('mijo_import_last_mijo_manufacturer',".$this->options->last_mijo_manufacturer.",".$this->options->last_mijo_manufacturer.")".
				';';
			$this->db->setQuery($sql);
			$this->db->query();
		}
	}


	function createTables()
	{

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 1 :</span> Initialization Tables</p>';
		$create = true;

		$query='SHOW TABLES LIKE '.$this->db->Quote($this->db->getPrefix().substr(hikashop_table('mijo_cat'),3));
		$this->db->setQuery($query);
		$table = $this->db->loadResult();
		if (!empty($table))
			$create = false;

		if ($create)
		{
			$this->db->setQuery("CREATE TABLE IF NOT EXISTS `#__hikashop_mijo_prod` (`mijo_id` int(11) unsigned NOT NULL DEFAULT '0', `hk_id` int(11) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`mijo_id`)) ENGINE=MyISAM");
			$this->db->query();
			$this->db->setQuery("CREATE TABLE IF NOT EXISTS `#__hikashop_mijo_cat` (`mijo_cat_id` INT(11) unsigned NOT NULL AUTO_INCREMENT, `mijo_id` int(11) unsigned NOT NULL DEFAULT '0', `hk_id` int(11) unsigned NOT NULL DEFAULT '0', `category_type` varchar(255) NULL, PRIMARY KEY (`mijo_cat_id`)) ENGINE=MyISAM");
			$this->db->query();

			$databaseHelper = hikashop_get('helper.database');
			$databaseHelper->addColumns('address','`address_mijo_order_info_id` INT(11) NULL');
			$databaseHelper->addColumns('order','`order_mijo_id` INT(11) NULL');
			$databaseHelper->addColumns('order','INDEX ( `order_mijo_id` )');
			$databaseHelper->addColumns('taxation','`tax_mijo_id` INT(11) NULL');

			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> All table created</p>';

		}
		else
		{
			echo '<p>Tables have been already created.</p>';
			$this->refreshPage = true; //toComment ?
		}

		return true;
	}


	function importTaxes()
	{
		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 2 :</span> Import Taxes<p>';
		$ret = false;

		$data = array(
			'tax_namekey' => "CONCAT('MIJO_TAX_', mjt.tax_rate_id)",
			'tax_rate' => 'mjt.rate'
		);

		$sql = 'INSERT IGNORE INTO `#__hikashop_tax` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `#__mijoshop_tax_rate` AS mjt '.
			'WHERE mjt.tax_rate_id > ' . (int)$this->options->last_mijo_taxrate;

		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported taxes: ' . $total . '</p>';


		$element = 'tax';
		$categoryClass = hikashop_get('class.category');
		$categoryClass->getMainElement($element);

		$data = array(
				'category_type' => "'tax'",
				'category_name' => "CONCAT('Tax imported (', mtc.title,')')",
				'category_published' => '1',
				'category_parent_id' => $element,
				'category_namekey' => " CONCAT('MIJO_TAX_CATEGORY_', mtc.tax_class_id)" //"case when hkz.zone_id IS NULL then CONCAT('MIJO_TAX_', mjtr.tax_rate_id,'_0') else CONCAT('MIJO_TAX_', mjtr.tax_rate_id,'_',hkz.zone_id) end",
			);

		$sql = 'INSERT IGNORE INTO `#__hikashop_category` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `#__mijoshop_tax_class` AS mtc ';
			'WHERE mtc.tax_class_id > ' . (int)$this->options->last_mijo_taxclass;

		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported Taxes Categories: ' . $total . '</p>';

		if( $total > 0 ) {
			$this->options->max_hk_cat += $total;
			$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'mijo_import_max_hk_cat'; ");
			$this->db->query();
			$this->importRebuildTree();
		}


		$data = array(
				'zone_namekey' => "case when hkz.zone_namekey IS NULL then '' else hkz.zone_namekey end",
				'category_namekey' => "CONCAT('MIJO_TAX_CATEGORY_', mjtc.tax_class_id)", //"case when hkz.zone_id IS NULL then CONCAT('MIJO_TAX_', mjtr.tax_rate_id,'_0') else CONCAT('MIJO_TAX_', mjtr.tax_rate_id,'_',hkz.zone_id) end",
				'tax_namekey' => "CONCAT('MIJO_TAX_', mjtra.tax_rate_id)",
				'taxation_published' => '1',
				'taxation_type' => "''",
				'tax_mijo_id' => 'mjtc.tax_class_id' //'mjtra.tax_rate_id' See import product
			);

		$sql = 'INSERT IGNORE INTO `#__hikashop_taxation` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `#__mijoshop_tax_class` AS mjtc '.
			'INNER JOIN `#__mijoshop_tax_rule` AS mjtr ON mjtc.tax_class_id = mjtr.tax_class_id '.
			'INNER JOIN `#__mijoshop_tax_rate` AS mjtra ON mjtr.tax_rate_id = mjtra.tax_rate_id '.
			'LEFT JOIN `#__mijoshop_zone_to_geo_zone` AS mjz ON mjtra.geo_zone_id = mjz.geo_zone_id '.
			'LEFT JOIN `#__mijoshop_country` AS mjc ON mjz.country_id = mjc.country_id ' .
			"LEFT JOIN `#__hikashop_zone` hkz ON mjc.iso_code_3 = hkz.zone_code_3 AND hkz.zone_type = 'country' ".
			'WHERE mjtra.tax_rate_id > ' . (int)$this->options->last_mijo_taxrate;

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

		$sql1 = 'SELECT * FROM `#__mijoshop_manufacturer` mjm '.
		'LEFT JOIN `#__hikashop_mijo_cat` hkmj ON mjm.manufacturer_id = hkmj.mijo_id  AND category_type=\'manufacturer\' '.
		'WHERE mjm.manufacturer_id > ' . (int)$this->options->last_mijo_manufacturer;
		'ORDER BY mjm.manufacturer_id ASC;';

		$this->db->setQuery($sql1);
		$this->db->query();
		$datas = $this->db->loadObjectList();

		$sql2 = 'INSERT INTO `#__hikashop_category` (`category_id`,`category_parent_id`,`category_type`,`category_name`,`category_published`,'.
			'`category_namekey`,`category_description`,`category_menu`) VALUES ';
		$sql3 = 'INSERT INTO `#__hikashop_mijo_cat` (`mijo_id`,`hk_id`,`category_type`) VALUES ';
		$sql4 = 'INSERT INTO `#__hikashop_file` (`file_name`,`file_description`,`file_path`,`file_type`,`file_ref_id`) VALUES ';
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
				if( !empty($data->mijo_id) )
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
				if( !empty($data->mijo_id) )
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
					"CONCAT('MIJO_MANUFAC_', ".$data->manufacturer_id .")",
					"'manufacturer imported from Mijoshop'",
					'0'
				);

				$sql2 .= $sep.'('.implode(',',$d).')';

				if( !empty($data->image))
				{
					$this->copyCatImgDir = str_replace('\\','/',rtrim(JPath::clean(html_entity_decode($this->copyCatImgDir)),DS.' ').DS);
					if (!$echo)
					{
						echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Copying manufacturers images... </p>';
						$echo=true;
					}
					$doSql4 = true;
					$sql4 .= $sep2."(".$this->db->quote($data->name).",'',".$this->db->quote($data->image).",'category',".$id.')'; //type = category / manufacturer ?
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
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported Manufacturers : ' . $total . '</p>';
		}
		else
		{
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported Manufacturers : 0</p>';
		}

		if( isset($total) && $total > 0)
		{
			$rebuild = true;
			$this->options->max_hk_cat += $total + 1;
			$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'mijo_import_max_hk_cat'; ");
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
		$echo=false;

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

		$this->db->setQuery("SELECT order_status_id, name FROM `#__mijoshop_order_status` WHERE name NOT IN ('".implode("','",$statuses)."','canceled');");
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
					$this->db->quote( strtolower($c->name) ),
					"'Order status imported from Mijoshop'",
					'1',
					$this->db->quote('status_mijo_import_'.strtolower(str_replace(' ','_',$c->name))),
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
				$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'mijo_import_max_hk_cat'; ");
				$this->db->query();
				$sql0 = '';
			}
			else
			{
				echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported order status categories : 0</p>';
			}
		}


		$sql1 = 'SELECT * FROM `#__mijoshop_category` mjc '.
		'INNER JOIN `#__mijoshop_category_description` mjcd ON mjc.category_id = mjcd.category_id '.
		'LEFT JOIN `#__hikashop_mijo_cat` hkmj ON mjc.category_id = hkmj.mijo_id AND category_type=\'category\' '.
		'WHERE mjc.category_id > '.$this->options->last_mijo_cat.' '.
		'ORDER BY mjc.parent_id ASC, mjc.category_id ASC;';

		$this->db->setQuery($sql1);
		$this->db->query();
		$datas = $this->db->loadObjectList();

		$sql2 = 'INSERT INTO `#__hikashop_category` (`category_id`,`category_parent_id`,`category_type`,`category_name`,`category_description`,`category_published`,'.
			'`category_ordering`,`category_namekey`,`category_created`,`category_modified`,`category_access`,`category_menu`) VALUES ';
		$sql3 = 'INSERT INTO `#__hikashop_mijo_cat` (`mijo_id`,`hk_id`,`category_type`) VALUES ';
		$sql4 = 'INSERT INTO `#__hikashop_file` (`file_name`,`file_description`,`file_path`,`file_type`,`file_ref_id`) VALUES ';
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
				if( !empty($data->mijo_id) )
				{
					$ids[(int)$data->category_id] = $data->hk_id;
				}
				else
				{
					$doSql3 = true;
					$ids[(int)$data->category_id] = $i;
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
				if( !empty($data->mijo_id) )
					continue;

				$id = (int)$ids[(int)$data->category_id];
				if(!empty($ids[$data->parent_id]))
					$pid = (int)$ids[$data->parent_id];
				else
					$pid = $ids[0];

				$element = new stdClass();
				$element->category_id = $id;
				$element->category_parent_id = $pid;
				$element->category_name = $data->name;
				$nameKey = $categoryClass->getNameKey($element);
				if(!is_numeric($data->date_added)) $data->date_added = strtotime($data->date_added);
				if(!is_numeric($data->date_modified)) $data->date_modified = strtotime($data->date_modified);
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

				if( !empty($data->image))
				{
					$this->copyCatImgDir = str_replace('\\','/',rtrim(JPath::clean(html_entity_decode($this->copyCatImgDir)),DS.' ').DS);
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
			$this->options->max_hk_cat += $total + 1;
			$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'mijo_import_max_hk_cat'; ");
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

		$query = 'SELECT * FROM `#__mijoshop_product`;';
		$this->db->setQuery($query);
		$el = $this->db->loadObject();
		$main_image = false;
		if(isset($el->image)){
			$main_image = true;


			$query = 'SELECT mjp.product_id, mjp.image FROM `#__mijoshop_product` mjp '.
				'LEFT JOIN `#__hikashop_mijo_prod` hkprod ON mjp.product_id = hkprod.mijo_id '.
				'WHERE mjp.product_id > '.$offset.' AND hkprod.hk_id IS NULL AND (mjp.image IS NOT NULL) AND mjp.image <> \'\' '.
				'ORDER BY product_id ASC LIMIT '.$count.';';
			$this->db->setQuery($query);

			$datas = $this->db->loadObjectList();
			$this->copyImgDir = str_replace('\\','/',rtrim(JPath::clean(html_entity_decode($this->copyImgDir)),DS.' ').DS);

			if (!empty($datas))
			{
				echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Copying products images... </p>';
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

		}

		$query = 'SELECT mjp.product_id, mjpi.image FROM `#__mijoshop_product` mjp '.
				'LEFT JOIN `#__mijoshop_product_image` mjpi ON mjp.product_id = mjpi.product_id '.
				'LEFT JOIN `#__hikashop_mijo_prod` hkprod ON mjp.product_id = hkprod.mijo_id '.
				'WHERE mjp.product_id > '.$offset.' AND hkprod.hk_id IS NULL AND (mjpi.image IS NOT NULL) AND mjpi.image <> \'\' '.
				'ORDER BY product_id ASC LIMIT '.$count.';';
		$this->db->setQuery($query);

		$datas = $this->db->loadObjectList();
		$this->copyImgDir = str_replace('\\','/',rtrim(JPath::clean(html_entity_decode($this->copyImgDir)),DS.' ').DS);

		if (!empty($datas))
		{
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Copying products images... </p>';
			foreach($datas as $data) {
				if( !empty($data->image) ) {
					$file_name = str_replace('\\','/',$data->image);
					if( strpos($file_name,'/') !== false ) {
						$file_name = substr($file_name, strrpos($file_name,'/'));
					}
					$this->copyFile($this->copyImgDir,$data->image, $this->options->uploadfolder.$file_name);
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
			'product_name' => 'mjpd.name',
			'product_description' => 'mjpd.description',
			'product_quantity' => 'case when mjp.quantity IS NULL or mjp.quantity < 0 then 0 else mjp.quantity end',
			'product_code' => 'mjp.hika_sku',
			'product_published' => 'mjp.status',
			'product_hit' => 'mjp.viewed',
			'product_created' => 'mjp.date_added',
			'product_modified' => 'mjp.date_modified',
			'product_sale_start' => 'mjp.date_available',
			'product_tax_id' => 'hkc.category_id',
			'product_type' => "'main'",
			'product_url' => "''",
			'product_weight' => 'mjp.weight',
			'product_weight_unit' => "LOWER(mjwcd.unit)",
			'product_dimension_unit' => "LOWER(mjlcd.unit)",
			'product_min_per_order' => 'mjp.minimum',
			'product_sales' => '0',
			'product_width' => 'mjp.width',
			'product_length' => 'mjp.length',
			'product_height' => 'mjp.height',
			'product_parent_id' => '0'
		);

		$sql1 = 'INSERT IGNORE INTO `#__hikashop_product` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `#__mijoshop_product` AS mjp '.
			'INNER JOIN `#__mijoshop_weight_class_description` mjwcd ON mjp.weight_class_id = mjwcd.weight_class_id '.
			'INNER JOIN `#__mijoshop_length_class_description` mjlcd ON mjp.length_class_id = mjlcd.length_class_id '.
			'INNER JOIN `#__mijoshop_product_description` mjpd ON mjp.product_id = mjpd.product_id '.
			'LEFT JOIN `#__hikashop_taxation` hkt ON hkt.tax_mijo_id = mjp.tax_class_id '.
			'LEFT JOIN `#__hikashop_category` hkc ON hkc.category_namekey = hkt.category_namekey '.
			'LEFT JOIN `#__hikashop_mijo_prod` AS hkp ON mjp.product_id = hkp.mijo_id '.
			'WHERE hkp.hk_id IS NULL ORDER BY mjp.product_id ASC;';

		$this->db->setQuery("SHOW COLUMNS FROM `#__mijoshop_product` LIKE 'hika_sku';");
		$data = $this->db->loadObjectList();
		if (empty($data))
		{
			$this->db->setQuery('ALTER TABLE `#__mijoshop_product` ADD COLUMN `hika_sku` VARCHAR(255) NOT NULL;');
			$this->db->query();
		}

		$this->db->setQuery('UPDATE `#__mijoshop_product` AS mjp SET mjp.hika_sku = mjp.sku;');
		$this->db->query();
		$this->db->setQuery("UPDATE `#__mijoshop_product` AS mjp SET mjp.hika_sku = CONCAT(mjp.model,'_',mjp.product_id) WHERE mjp.hika_sku='';");
		$this->db->query();

		$this->db->setQuery('SELECT hika_sku FROM `#__mijoshop_product` GROUP BY hika_sku HAVING COUNT(hika_sku)>1');
		$data = $this->db->loadObjectList();

		if (!empty($data))
		{
			foreach ($data as $d)
			{
				$this->db->setQuery("UPDATE `#__mijoshop_product` AS mjp SET mjp.hika_sku = CONCAT(mjp.hika_sku,'_',mjp.product_id) WHERE mjp.hika_sku = '".$d->hika_sku."';");
				$this->db->query();
			}
		}

		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> SKU generated: '.count($data).'</p>';

		$data = array(
			'mijo_id' => 'mjp.product_id',
			'hk_id' => 'hkp.product_id'
		);

		$sql2 = 'INSERT IGNORE INTO `#__hikashop_mijo_prod` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `#__mijoshop_product` AS mjp '.
			'INNER JOIN `#__hikashop_product` AS hkp ON CONVERT(mjp.hika_sku USING utf8) = CONVERT(hkp.product_code USING utf8) '.
			'LEFT JOIN `#__hikashop_mijo_prod` hkmjp ON hkmjp.mijo_id = mjp.product_id '.
			'WHERE hkmjp.hk_id IS NULL;';

		$data = array(
			'file_name' => "''",
			'file_description' => "''",
			'file_path' => "SUBSTRING_INDEX(mjp.image,'/',-1)",
			'file_type' => "'product'",
			'file_ref_id' => 'hkmjp.hk_id'
		);


		$sql40 = 'INSERT IGNORE INTO `#__hikashop_file` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `#__mijoshop_product` AS mjp '.
			'INNER JOIN `#__hikashop_mijo_prod` AS hkmjp ON mjp.product_id = hkmjp.mijo_id '.
			'WHERE mjp.product_id > '.$this->options->last_mijo_prod. ' AND (mjp.image IS NOT NULL) AND (mjp.image <>'." '');";


		$data = array(
			'file_name' => "''",
			'file_description' => "''",
			'file_path' => "SUBSTRING_INDEX(mjpi.image,'/',-1)",
			'file_type' => "'product'",
			'file_ref_id' => 'hkmjp.hk_id'
		);


		$sql4 = 'INSERT IGNORE INTO `#__hikashop_file` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `#__mijoshop_product` AS mjp '.
			'INNER JOIN `#__mijoshop_product_image` mjpi ON mjp.product_id = mjpi.product_id '.
			'INNER JOIN `#__hikashop_mijo_prod` AS hkmjp ON mjp.product_id = hkmjp.mijo_id '.
			'WHERE mjp.product_id > '.$this->options->last_mijo_prod. ' AND (mjpi.image IS NOT NULL) AND (mjpi.image <>'." '');";

		$sql5 = 'UPDATE `#__hikashop_product` AS hkp '.
			'INNER JOIN `#__hikashop_mijo_prod` AS hkmjp ON hkp.product_id = hkmjp.hk_id '.
			'INNER JOIN `#__mijoshop_product` AS mjp ON hkmjp.mijo_id = mjp.product_id '.
			"INNER JOIN `#__hikashop_category` AS hkc ON hkc.category_type = 'manufacturer' AND mjp.manufacturer_id = hkc.category_menu ".
			'SET hkp.product_manufacturer_id = hkc.category_id '.
			'WHERE mjp.manufacturer_id > '.$this->options->last_mijo_manufacturer.' OR mjp.product_id > '.$this->options->last_mijo_prod.';';

		$this->db->setQuery($sql1);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Inserted products: ' . $total . '</p>';

		$this->db->setQuery($sql2);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Fallback links: ' . $total . '</p>';

		if($main_image){
			$this->db->setQuery($sql40);
			$this->db->query();
			$total = $this->db->getAffectedRows();
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Inserted products files: ' . $total . '</p>';
		}

		$this->db->setQuery($sql4);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Inserted products images: ' . $total . '</p>';

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

		$query = 'SELECT hkcur.currency_id FROM `#__mijoshop_currency` mjc '.
			'INNER JOIN `#__hikashop_currency` hkcur ON CONVERT(mjc.code USING utf8) = CONVERT(hkcur.currency_code USING utf8) '.
			'WHERE mjc.value = ' . $this->db->Quote('1.0') . ';';
		$this->db->setQuery($query);

		$data = $this->db->loadObjectList();

		if (!empty($data))
		{
			$query = 'INSERT IGNORE INTO `#__hikashop_price` (`price_product_id`,`price_value`,`price_currency_id`,`price_min_quantity`,`price_access`) '.
				'SELECT hkprod.hk_Id, mjp.price, ' . $this->db->Quote($data[0]->currency_id) . ', ' . $this->db->Quote('0') . ', ' . $this->db->Quote('all') . ' '.
				'FROM `#__mijoshop_product` mjp INNER JOIN `#__hikashop_mijo_prod` hkprod ON mjp.product_id = hkprod.mijo_id '.
				'WHERE mjp.product_id > ' . (int)$this->options->last_mijo_prod;
			$this->db->setQuery($query);
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
			'category_id' => 'hmjc.hk_id',
			'product_id' => 'hmjp.hk_id',
			'ordering' => '0',
		);

		$sql = 'INSERT IGNORE INTO `#__hikashop_product_category` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `#__mijoshop_product_to_category` mjpc '.
			'INNER JOIN #__hikashop_mijo_cat hmjc ON mjpc.category_id = hmjc.mijo_id AND category_type=\'category\' '.
			'INNER JOIN #__hikashop_mijo_prod hmjp ON mjpc.product_id = hmjp.mijo_id '.
			'WHERE hmjp.mijo_id > ' . (int)$this->options->last_mijo_prod . ' OR hmjc.mijo_id > ' . (int)$this->options->last_mijo_cat;

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

		$sql0 = 'INSERT IGNORE INTO `#__hikashop_user` (`user_cms_id`,`user_email`) '.
			'SELECT u.id, u.email FROM `#__mijoshop_customer` mjc '.
			'INNER JOIN `#__mijoshop_juser_ocustomer_map` mjju ON mjju.ocustomer_id = mjc.customer_id '.
			'INNER JOIN `#__users` u ON mjju.juser_id = u.id '.
			'LEFT JOIN `#__hikashop_user` AS hkusr ON mjju.juser_id = hkusr.user_cms_id '.
			'WHERE hkusr.user_cms_id IS NULL;';

		$data = array(
			'address_user_id' => 'hku.user_id',
			'address_title' => "'Mr'",
			'address_firstname' => 'mja.firstname',
			'address_lastname' => 'mja.lastname',
			'address_company' => 'mja.company',
			'address_street' => 'CONCAT(mja.address_1,\' \',mja.address_2)',
			'address_post_code' => 'mja.postcode',
			'address_city' => 'mja.city',
			'address_telephone' => 'mjcu.telephone',
			'address_fax' => 'mjcu.fax',
			'address_state' => 'hkzsta.zone_namekey',
			'address_country' => 'hkzcou.zone_namekey',
			'address_published' => 4
		);

		$sql1 = 'INSERT IGNORE INTO `#__hikashop_address` (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM `#__mijoshop_customer` AS mjcu '.
				'INNER JOIN `#__mijoshop_address` AS mja ON mjcu.customer_id = mja.customer_id '.
				'INNER JOIN `#__mijoshop_juser_ocustomer_map` mjju ON mja.customer_id = mjju.ocustomer_id '.
				'INNER JOIN `#__users` u ON mjju.juser_id = u.id '.
				'INNER JOIN `#__hikashop_user` AS hku ON mjju.juser_id = hku.user_cms_id '.
				'INNER JOIN `#__mijoshop_country` AS mjc ON mja.country_id = mjc.country_id '.
				'INNER JOIN `#__mijoshop_zone` AS mjz ON mja.zone_id = mjz.zone_id '.
				'LEFT JOIN `#__hikashop_zone` AS  hkzcou ON mjc.iso_code_3 = hkzcou.zone_code_3 AND hkzcou.zone_type=\'country\' '.
				'INNER JOIN `#__hikashop_zone_link` AS hkzl ON hkzcou.zone_namekey = hkzl.zone_parent_namekey '.
				'INNER JOIN `#__hikashop_zone` AS  hkzsta ON mjz.code = hkzsta.zone_code_3 AND hkzsta.zone_type=\'state\' AND hkzsta.zone_namekey = hkzl.zone_child_namekey '.
				'WHERE mjcu.customer_id > '.$this->options->last_mijo_user.' ORDER BY mja.customer_id ASC';

		$sql2 = 'UPDATE `#__hikashop_address` AS a '.
				'JOIN `#__hikashop_zone` AS hkz ON (a.address_country = hkz.zone_code_3 AND hkz.zone_type = "country") '.
				'SET address_country = hkz.zone_namekey, address_published = 3 WHERE address_published = 4;';

		$sql3 = 'UPDATE `#__hikashop_address` AS a '.
				'JOIN `#__hikashop_zone_link` AS zl ON (a.address_country = zl.zone_parent_namekey) '.
				'JOIN `#__hikashop_zone` AS hks ON (hks.zone_namekey = zl.zone_child_namekey AND hks.zone_type = "state" AND hks.zone_code_3 = a.address_state) '.
				'SET address_state = hks.zone_namekey, address_published = 2 WHERE address_published = 3;';

		$sql4 = 'UPDATE `#__hikashop_address` AS a SET a.address_country = \'\' WHERE address_published > 3;';
		$sql5 = 'UPDATE `#__hikashop_address` AS a SET a.address_state = \'\' WHERE address_published > 2;';
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
		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 10 :</span> Import Orders</p>';

		$ret = false;
		$offset = $this->options->current;
		$count = 100;
		$total = 0;

		$vat_cols = "''";

		$data = array(
			'order_number' => 'mjo.order_id',
			'order_mijo_id' => 'mjo.order_id',
			'order_user_id' => 'hkusr.user_id',
			'order_status' => 'hkc.category_name',
			'order_created' => 'mjo.date_added',
			'order_ip' => 'mjo.ip',
			'order_currency_id' => 'hkcur.currency_id',
			'order_shipping_price' => "''", //?
			'order_shipping_method' => 'mjo.shipping_method',
			'order_shipping_id' => '1',
			'order_payment_id' => 0,
			'order_payment_method' => 'mjo.payment_method',
			'order_full_price' => 'mjot.value',
			'order_modified' => 'mjo.date_modified',
			'order_partner_id' => 0,
			'order_partner_price' => 0,
			'order_partner_paid' => 0,
			'order_type' => "'sale'",
			'order_partner_currency_id' => 0,
			'order_shipping_tax' => "''", //?
			'order_discount_tax' => 0
		);

		$sql1 = 'INSERT IGNORE INTO `#__hikashop_order` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `#__mijoshop_order` AS mjo '.
			'INNER JOIN `#__mijoshop_order_product` mjop ON mjop.order_id = mjo.order_id '.
			'INNER JOIN `#__mijoshop_order_status` AS mjos ON mjo.order_status_id = mjos.order_status_id '.
			'INNER JOIN `#__hikashop_category` AS hkc ON mjos.name = hkc.category_name AND hkc.category_type = \'status\' '.
			'INNER JOIN `#__hikashop_currency` AS hkcur ON CONVERT(mjo.currency_code USING utf8) = CONVERT(hkcur.currency_code USING utf8) '.
			'INNER JOIN `#__mijoshop_order_total` mjot ON mjo.order_id = mjot.order_id AND code=\'total\' '.
			'INNER JOIN `#__mijoshop_juser_ocustomer_map` mjju ON mjo.customer_id = mjju.ocustomer_id '.
			'INNER JOIN `#__users` u ON mjju.juser_id = u.id '.
			'INNER JOIN `#__hikashop_user` AS hkusr ON mjju.juser_id = hkusr.user_cms_id '.
			'WHERE mjo.order_id > ' . (int)$this->options->last_mijo_order . ' '.
			'GROUP BY mjo.order_id '.
			'ORDER BY mjo.order_id ASC;';

		$sql1_1 = 'UPDATE `#__hikashop_order` AS hko '.
			'INNER JOIN `#__mijoshop_voucher` AS mjv ON hko.order_mijo_id = mjv.order_id '.
			'INNER JOIN `#__hikashop_discount` AS hkd ON hkd.discount_code = mjv.code '.
			'SET hko.order_discount_code = hkd.discount_code AND hko.order_discount_price = hkd.discount_flat_amount';

		$data = array(
			'address_user_id' => 'mjo.customer_id',
			'address_firstname' => 'mjo.payment_firstname',
			'address_lastname' => 'mjo.payment_lastname',
			'address_company' => 'mjo.payment_company',
			'address_street' => 'CONCAT(mjo.payment_address_1,\' \',mjo.payment_address_2)',
			'address_post_code' => 'mjo.payment_postcode',
			'address_city' => 'mjo.payment_city ',
			'address_telephone' => 'mjo.telephone',
			'address_fax' => 'mjo.fax',
			'address_state' => 'hkzsta.zone_namekey',
			'address_country' => 'hkzcou.zone_namekey',
			'address_published' => '7',
			'address_vat' => $vat_cols,
			'address_mijo_order_info_id' => 'mjo.order_id' //8
		);

		$sql2_1 = 'INSERT IGNORE INTO `#__hikashop_address` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `#__mijoshop_order` AS mjo '.
			'INNER JOIN `#__mijoshop_country` AS mjc ON mjo.payment_country_id = mjc.country_id '.
			'INNER JOIN `#__mijoshop_zone` AS mjz ON mjo.payment_zone_id = mjz.zone_id '.
			'INNER JOIN `#__hikashop_zone` AS  hkzcou ON mjc.iso_code_3 = hkzcou.zone_code_3 AND hkzcou.zone_type=\'country\' '.
			'INNER JOIN `#__hikashop_zone_link` AS hkzl ON hkzcou.zone_namekey = hkzl.zone_parent_namekey '.
			'INNER JOIN `#__hikashop_zone` AS  hkzsta ON mjz.code = hkzsta.zone_code_3 AND hkzsta.zone_type=\'state\' AND hkzsta.zone_namekey = hkzl.zone_child_namekey '.
			'WHERE mjo.order_id > ' . (int)$this->options->last_mijo_order;

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
			'INNER JOIN `#__hikashop_address` AS a ON a.address_mijo_order_info_id = o.order_mijo_id '.
			'SET o.order_billing_address_id = a.address_id, o.order_shipping_address_id = a.address_id '.
			'WHERE o.order_billing_address_id = 0 AND address_published >= 7 ;';

		$sql4 = 'UPDATE `#__hikashop_order` AS o '.
			'INNER JOIN `#__hikashop_address` AS a ON a.address_mijo_order_info_id = o.order_mijo_id '.
			'SET o.order_shipping_address_id = a.address_id '.
			'WHERE o.order_shipping_address_id = 0 AND address_published >= 8 ;';

		$sql5 = 'UPDATE `#__hikashop_order` AS hko '.
			'JOIN `#__mijoshop_order` AS mjo ON hko.order_mijo_id = mjo.order_id '.
			'SET hko.order_payment_method = CONCAT(' . $this->db->Quote('mijo import: ') . ', mjo.payment_method) '.
			'WHERE hko.order_mijo_id > ' . (int)$this->options->last_mijo_order;

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
			'order_product_quantity' => 'mjop.quantity',
			'order_product_name' => 'mjop.name',
			'order_product_code' => 'mjp.hika_sku',
			'order_product_price' => 'mjop.price',
			'order_product_tax' => 'mjop.tax',
			'order_product_options' => "''"
		);

		$sql = 'INSERT IGNORE INTO `#__hikashop_order_product` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `#__mijoshop_order_product` AS mjop '.
			'INNER JOIN `#__mijoshop_product` mjp ON mjop.product_id=mjp.product_id '.
			'INNER JOIN `#__hikashop_order` AS hko ON mjop.order_id = hko.order_mijo_id '.
			'INNER JOIN `#__hikashop_mijo_prod` AS hkp ON hkp.mijo_id = mjop.product_id '.
			'WHERE mjop.order_id > ' . (int)$this->options->last_mijo_order . ';';

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
			$offset = $this->options->last_mijo_pfile;

		$sql = "SELECT `config_value` FROM `#__hikashop_config` WHERE config_namekey = 'download_number_limit';";
		$this->db->setQuery($sql);
		$data = $this->db->loadObjectList();
		$dl_limit = $data[0]->config_value;

		$sql = 'SELECT mjd.download_id, mjd.filename FROM `#__mijoshop_download` AS mjd WHERE mjd.download_id > '.$offset.' ORDER BY mjd.download_id ASC LIMIT '.$count.';'; //Why no Mask FFS
		$this->db->setQuery($sql);
		$data = $this->db->loadObjectList();
		$max = 0;

		if (!empty($data))
		{
			$this->copyDownloadDir = str_replace('\\','/',rtrim(JPath::clean(html_entity_decode($this->copyDownloadDir)),DS.' ').DS);
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
			'file_name' => 'mjdd.name',
			'file_description' => "''",
			'file_path' => "SUBSTRING_INDEX(mjd.filename ,'/',-1)", //Why no filename ?
			'file_type' => "'file'",
			'file_ref_id' => 'hkmjp.hk_id'
		);

		$sql = 'INSERT IGNORE INTO `#__hikashop_file` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `#__mijoshop_download` AS mjd '.
			'INNER JOIN `#__mijoshop_download_description` mjdd ON mjd.download_id = mjdd.download_id '.
			'LEFT JOIN `#__mijoshop_product_to_download` mjpd ON mjd.download_id = mjpd.download_id '.
			'LEFT JOIN `#__hikashop_mijo_prod` AS hkmjp ON mjpd.product_id = hkmjp.mijo_id '.
			'WHERE mjd.download_id > '.$this->options->last_mijo_pfile;

		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();

		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Downloable files imported : ' . $total . '</p>';

		$data = array(
			'file_id' => 'hkf.file_id',
			'order_id' => 'hko.order_id',
			'download_number' => '(' . $dl_limit . '- mjd.remaining)' //$dl_limit ?
		);

		$sql = 'INSERT IGNORE INTO `#__hikashop_download` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `#__mijoshop_download` AS mjd '.
			'INNER JOIN `#__mijoshop_download_description` mjdd ON mjd.download_id = mjdd.download_id '.
			'INNER JOIN `#__hikashop_file` AS hkf ON ( CONVERT(hkf.file_name USING utf8) = CONVERT(mjdd.name USING utf8) )'.
			'INNER JOIN `#__mijoshop_product_to_download` AS mjpd ON mjd.download_id = mjpd.download_id '.
			'INNER JOIN `#__mijoshop_order_product` AS mjop ON mjpd.product_id = mjop.product_id '.
			'INNER JOIN `#__hikashop_order` AS hko ON hko.order_mijo_id = mjop.order_id '.
			'WHERE mjd.download_id > '.$this->options->last_mijo_pfile;

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

		$sql = 'INSERT IGNORE INTO #__hikashop_discount (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM #__mijoshop_coupon WHERE coupon_id > ' . (int)$this->options->last_mijo_coupon;

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

		$sql = 'INSERT IGNORE INTO #__hikashop_discount (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM #__mijoshop_voucher WHERE voucher_id > ' . (int)$this->options->last_mijo_voucher;

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

		$query = 'UPDATE `#__hikashop_currency` AS hkcur '.
			'INNER JOIN `#__mijoshop_currency` mjc ON CONVERT(mjc.code USING utf8) = CONVERT( hkcur.currency_code USING utf8) '.
			'SET hkcur.currency_rate = mjc.value';
		$this->db->setQuery($query);

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

		$this->db->setQuery("SELECT max(product_id) as 'max' FROM `#__hikashop_product`;");
		$data = $this->db->loadObjectList();
		$this->options->max_hk_prod = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(mijo_id) as 'max' FROM `#__hikashop_mijo_cat`;");
		$data = $this->db->loadObjectList();
		$this->options->last_mijo_cat = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(mijo_id) as 'max' FROM `#__hikashop_mijo_prod`;");
		$data = $this->db->loadObjectList();
		$this->options->last_mijo_prod = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(customer_id) as 'max' FROM `#__mijoshop_customer`;");
		$data = $this->db->loadObjectList();
		$this->options->last_mijo_user = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(order_mijo_id) as 'max' FROM `#__hikashop_order`;");
		$data = $this->db->loadObjectList();
		$this->options->last_mijo_order = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(mjpi.product_image_id) as 'max' FROM `#__mijoshop_product_image` mjpi INNER JOIN `#__mijoshop_product` mjp ON mjpi.product_id = mjp.product_id");
		$data = $this->db->loadObject();
		$this->options->last_mijo_pfile = (int)($data->max);

		$this->db->setQuery("SELECT max(coupon_id) as 'max' FROM `#__mijoshop_coupon`;");
		$data = $this->db->loadObject();
		$this->options->last_mijo_coupon = (int)($data->max);

		$this->db->setQuery("SELECT max(voucher_id) as 'max' FROM `#__mijoshop_voucher`;");
		$data = $this->db->loadObject();
		$this->options->last_mijo_voucher = (int)($data->max);

		$this->db->setQuery("SELECT max(tax_rate_id) as 'max' FROM `#__mijoshop_tax_rate`;");
		$data = $this->db->loadObject();
		$this->options->last_mijo_taxrate = (int)($data->max);

		$this->db->setQuery("SELECT max(tax_class_id) as 'max' FROM `#__mijoshop_tax_class`;");
		$data = $this->db->loadObject();
		$this->options->last_mijo_taxclass = (int)($data->max);

		$this->db->setQuery("SELECT max(manufacturer_id) as 'max' FROM `#__mijoshop_manufacturer`;");
		$data = $this->db->loadObjectList();
		$this->options->last_mijo_manufacturer = (int)($data[0]->max);

		$this->options->state = (MAX_IMPORT_ID+1);
		$query = 'REPLACE INTO `#__hikashop_config` (`config_namekey`,`config_value`,`config_default`) VALUES '.
				"('mijo_import_state',".$this->options->state.",".$this->options->state.")".
				",('mijo_import_max_hk_cat',".$this->options->max_hk_cat.",".$this->options->max_hk_cat.")".
				",('mijo_import_max_hk_prod',".$this->options->max_hk_prod.",".$this->options->max_hk_prod.")".
				",('mijo_import_last_mijo_cat',".$this->options->last_mijo_cat.",".$this->options->last_mijo_cat.")".
				",('mijo_import_last_mijo_prod',".$this->options->last_mijo_prod.",".$this->options->last_mijo_prod.")".
				",('mijo_import_last_mijo_user',".$this->options->last_mijo_user.",".$this->options->last_mijo_user.")".
				",('mijo_import_last_mijo_order',".$this->options->last_mijo_order.",".$this->options->last_mijo_order.")".
				",('mijo_import_last_mijo_pfile',".$this->options->last_mijo_pfile.",".$this->options->last_mijo_pfile.")".
				",('mijo_import_last_mijo_coupon',".$this->options->last_mijo_coupon.",".$this->options->last_mijo_coupon.")".
				",('mijo_import_last_mijo_voucher',".$this->options->last_mijo_voucher.",".$this->options->last_mijo_voucher.")".
				",('mijo_import_last_mijo_taxrate',".$this->options->last_mijo_taxrate.",".$this->options->last_mijo_taxrate.")".
				",('mijo_import_last_mijo_taxclass',".$this->options->last_mijo_taxclass.",".$this->options->last_mijo_taxclass.")".
				",('mijo_import_last_mijo_manufacturer',".$this->options->last_mijo_manufacturer.",".$this->options->last_mijo_manufacturer.")".
				';';
		$this->db->setQuery($query);
		$this->db->query();

		echo '<p'.$this->titlefont.'>Import finished !</p>';

		$class = hikashop_get('class.plugins');

		$infos = $class->getByName('system','mijo_redirect');
		if($infos)
		{
			$pkey = reset($class->pkeys);
			if(!empty($infos->$pkey))
			{
				if(version_compare(JVERSION,'1.6','<'))
					$url = JRoute::_('index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]='.$infos->$pkey);
				else
					$url = JRoute::_('index.php?option=com_plugins&view=plugin&layout=edit&extension_id='.$infos->$pkey);

				echo '<p>You can publish the <a'.$this->linkstyle.' href="'.$url.'">Mijoshop Fallback Redirect Plugin</a> so that your old Mijoshop links are automatically redirected to HikaShop pages and thus not loose the ranking of your content on search engines.</p>';
			}
		}
	}

}
