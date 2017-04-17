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
@include(HIKASHOP_ROOT . 'administrator/components/com_redshop/helpers/redshop.cfg.php');

class hikashopImportredsHelper extends hikashopImportHelper
{
	function __construct()
	{
		parent::__construct();
		$this->importName = 'reds';
		$this->copyImgDir = HIKASHOP_ROOT . 'components/com_redshop/assets/images/product';
		$this->copyCatImgDir = HIKASHOP_ROOT . 'components/com_redshop/assets/images/category';
		if (defined('PRODUCT_DOWNLOAD_ROOT'))
			$this->copyDownloadDir = PRODUCT_DOWNLOAD_ROOT;
		else
			$this->copyDownloadDir = HIKASHOP_ROOT . 'components/com_redshop/assets/download/product';
		$this->copyManufDir = HIKASHOP_ROOT . 'components/com_redshop/assets/images/manufacturer';
		jimport('joomla.filesystem.file');
	}

	function importFromRedshop()
	{
		@ob_clean();
		echo $this->getHtmlPage();

		$this->token = hikashop_getFormToken();
		flush();

		if( isset($_GET['import']) && $_GET['import'] == '1' )
		{
			$time = microtime(true);
			$processed = $this->doImport();
			if( $processed )
			{
				$elasped = microtime(true) - $time;

				if( !$this->refreshPage )
					echo '<p></br><a'.$this->linkstyle.'href="'.hikashop_completeLink('import&task=import&importfrom=redshop&'.$this->token.'=1&import=1&time='.time()).'">'.JText::_('HIKA_NEXT').'</a></p>';

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
			$sql =  "UPDATE `#__hikashop_config` SET config_value=(config_value+1) WHERE config_namekey = 'reds_import_state'; ";
			$this->db->setQuery($sql);
			$this->db->query();
			$sql = "UPDATE `#__hikashop_config` SET config_value=0 WHERE config_namekey = 'reds_import_current';";
			$this->db->setQuery($sql);
			$this->db->query();
		}
		else if( $current != $this->options->current )
		{
			$sql =  "UPDATE `#__hikashop_config` SET config_value=".$this->options->current." WHERE config_namekey = 'reds_import_current';";
			$this->db->setQuery($sql);
			$this->db->query();
		}

		return $ret;

	}

	function loadConfiguration()
	{
		$this->options = new stdClass();

		$data = array(
			'uploadfolder',
			'uploadsecurefolder',
			'main_currency',
			'reds_import_state',
			'reds_import_current',
			'reds_import_tax_id',
			'reds_import_main_cat_id',
			'reds_import_max_hk_cat',
			'reds_import_max_hk_prod',
			'reds_import_last_reds_cat',
			'reds_import_last_reds_prod',
			'reds_import_last_reds_user',
			'reds_import_last_reds_order',
			'reds_import_last_reds_pfile',
			'reds_import_last_reds_coupon',
			'reds_import_last_reds_discount',
			'reds_import_last_reds_discount_prod',
			'reds_import_last_reds_voucher',
			'reds_import_last_reds_taxrate',
			'reds_import_last_reds_taxclass',
			'reds_import_last_reds_manufacturer'
		);
		$this->db->setQuery('SELECT config_namekey, config_value FROM `#__hikashop_config` WHERE config_namekey IN ('."'".implode("','",$data)."'".');');

		$result = $this->db->loadObjectList();
		if (!empty($result))
		{
			foreach($result as $o)
			{
				if( substr($o->config_namekey, 0, 12) == 'reds_import_' )
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
			$this->options->last_reds_coupon = 0;
			$this->options->last_reds_discount = 0;
			$this->options->last_reds_discount_prod = 0;
			$this->options->last_reds_voucher = 0;
			$this->options->last_reds_pfile = 0;
			$this->options->last_reds_taxrate = 0;
			$this->options->last_reds_taxclass = 0;
			$this->options->last_reds_manufacturer = 0;

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


			$query='SHOW TABLES LIKE '.$this->db->Quote($this->db->getPrefix().substr(hikashop_table('reds_cat'),3));
			$this->db->setQuery($query);
			$table = $this->db->loadResult();
			if(!empty($table))
			{
				$this->db->setQuery("SELECT max(reds_id) as 'max' FROM `#__hikashop_reds_cat`;");
				$data = $this->db->loadObjectList();
				if( $data )
					$this->options->last_reds_cat = (int)($data[0]->max);
				else
					$this->options->last_reds_cat = 0;

				$this->db->setQuery("SELECT max(reds_id) as 'max' FROM `#__hikashop_reds_prod`;");
				$data = $this->db->loadObjectList();
				if( $data )
					$this->options->last_reds_prod = (int)($data[0]->max);
				else
					$this->options->last_reds_prod = 0;

				$this->db->setQuery("SELECT max(order_reds_id) as 'max' FROM `#__hikashop_order`;");
				$data = $this->db->loadObjectList();
				if( $data )
					$this->options->last_reds_order = (int)($data[0]->max);
				else
					$this->options->last_reds_order = 0;
			}
			else
			{
				$this->options->last_reds_cat = 0;
				$this->options->last_reds_prod = 0;
				$this->options->last_reds_order = 0;
			}

			$this->options->last_reds_user = 0;

			$sql = 'INSERT IGNORE INTO `#__hikashop_config` (`config_namekey`,`config_value`,`config_default`) VALUES '.
				"('reds_import_state',".$this->options->state.",".$this->options->state.")".
				",('reds_import_current',".$this->options->current.",".$this->options->current.")".
				",('reds_import_tax_id',".$this->options->tax_id.",".$this->options->tax_id.")".
				",('reds_import_main_cat_id',".$this->options->main_cat_id.",".$this->options->main_cat_id.")".
				",('reds_import_max_hk_cat',".$this->options->max_hk_cat.",".$this->options->max_hk_cat.")".
				",('reds_import_max_hk_prod',".$this->options->max_hk_prod.",".$this->options->max_hk_prod.")".
				",('reds_import_last_reds_cat',".$this->options->last_reds_cat.",".$this->options->last_reds_cat.")".
				",('reds_import_last_reds_prod',".$this->options->last_reds_prod.",".$this->options->last_reds_prod.")".
				",('reds_import_last_reds_user',".$this->options->last_reds_user.",".$this->options->last_reds_user.")".
				",('reds_import_last_reds_order',".$this->options->last_reds_order.",".$this->options->last_reds_order.")".
				",('reds_import_last_reds_pfile',".$this->options->last_reds_pfile.",".$this->options->last_reds_pfile.")".
				",('reds_import_last_reds_coupon',".$this->options->last_reds_coupon.",".$this->options->last_reds_coupon.")".
				",('reds_import_last_reds_discount',".$this->options->last_reds_discount.",".$this->options->last_reds_discount.")".
				",('reds_import_last_reds_discount_prod',".$this->options->last_reds_discount_prod.",".$this->options->last_reds_discount_prod.")".
				",('reds_import_last_reds_voucher',".$this->options->last_reds_voucher.",".$this->options->last_reds_voucher.")".
				",('reds_import_last_reds_taxrate',".$this->options->last_reds_taxrate.",".$this->options->last_reds_taxrate.")".
				",('reds_import_last_reds_taxclass',".$this->options->last_reds_taxclass.",".$this->options->last_reds_taxclass.")".
				",('reds_import_last_reds_manufacturer',".$this->options->last_reds_manufacturer.",".$this->options->last_reds_manufacturer.")".
				';';
			$this->db->setQuery($sql);
			$this->db->query();
		}
	}


	function createTables()
	{

		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 1 :</span> Initialization Tables</p>';
		$create = true;

		$query='SHOW TABLES LIKE '.$this->db->Quote($this->db->getPrefix().substr(hikashop_table('reds_cat'),3));
		$this->db->setQuery($query);
		$table = $this->db->loadResult();
		if (!empty($table))
			$create = false;

		if ($create)
		{
			$this->db->setQuery("CREATE TABLE IF NOT EXISTS `#__hikashop_reds_prod` (`reds_id` int(11) unsigned NOT NULL DEFAULT '0', `hk_id` int(11) unsigned NOT NULL DEFAULT '0', PRIMARY KEY (`reds_id`)) ENGINE=MyISAM");
			$this->db->query();
			$this->db->setQuery("CREATE TABLE IF NOT EXISTS `#__hikashop_reds_cat` (`reds_cat_id` INT(11) unsigned NOT NULL AUTO_INCREMENT, `reds_id` int(11) unsigned NOT NULL DEFAULT '0', `hk_id` int(11) unsigned NOT NULL DEFAULT '0', `category_type` varchar(255) NULL, PRIMARY KEY (`reds_cat_id`)) ENGINE=MyISAM");
			$this->db->query();

			$databaseHelper = hikashop_get('helper.database');
			$databaseHelper->addColumns('address','`address_reds_order_info_id` INT(11) NULL');
			$databaseHelper->addColumns('order','`order_reds_id` INT(11) NULL');
			$databaseHelper->addColumns('order','INDEX ( `order_reds_id` )');
			$databaseHelper->addColumns('taxation','`tax_reds_id` INT(11) NULL');

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
			'tax_namekey' => "CONCAT('REDS_TAX_', rstr.tax_rate_id)",
			'tax_rate' => 'rstr.tax_rate'
		);

		$sql = 'INSERT IGNORE INTO `#__hikashop_tax` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `#__redshop_tax_rate` AS rstr '.
			'WHERE rstr.tax_rate_id > ' . (int)$this->options->last_reds_taxrate;

		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported taxes: ' . $total . '</p>';


		$element = 'tax';
		$categoryClass = hikashop_get('class.category');
		$categoryClass->getMainElement($element);

		$data = array(
				'category_type' => "'tax'",
				'category_name' => "CONCAT('Tax imported (', rstg.tax_group_name,')')",
				'category_published' => 'rstg.published',
				'category_parent_id' => $element,
				'category_namekey' => " CONCAT('REDS_TAX_CATEGORY_', rstg.tax_group_id)"
		);

		$sql = 'INSERT IGNORE INTO `#__hikashop_category` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `#__redshop_tax_group` AS rstg ';
			'WHERE rstg.tax_group_id > ' . (int)$this->options->last_reds_taxclass;

		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported Taxes Categories: ' . $total . '</p>';

		if( $total > 0 ) {
			$this->options->max_hk_cat += $total;
			$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'reds_import_max_hk_cat'; ");
			$this->db->query();
			$this->importRebuildTree();
		}

		$data = array(
				'zone_namekey' => "case when hkz.zone_namekey IS NULL then '' else hkz.zone_namekey end",
				'category_namekey' => "CONCAT('REDS_TAX_CATEGORY_', rstg.tax_group_id)",
				'tax_namekey' => "CONCAT('REDS_TAX_', rstr.tax_rate_id)",
				'taxation_published' => '1',
				'taxation_type' => "''",
				'tax_reds_id' => 'rstg.tax_group_id'
			);

		$sql = 'INSERT IGNORE INTO `#__hikashop_taxation` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `#__redshop_tax_rate` AS rstr '.
			'INNER JOIN `#__redshop_tax_group` AS rstg ON rstr.tax_group_id = rstg.tax_group_id '.
			'LEFT JOIN `#__redshop_country` AS rsc ON rstr.tax_country = rsc.country_3_code ' . //Tocheck
			"LEFT JOIN `#__hikashop_zone` hkz ON rsc.country_3_code = hkz.zone_code_3 AND hkz.zone_type = 'country' ".
			'WHERE rstr.tax_rate_id > ' . (int)$this->options->last_reds_taxrate;

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

		$sql1 = 'SELECT * FROM `#__redshop_manufacturer` rsm '.
		'LEFT JOIN `#__redshop_media` rsme ON rsme.section_id = rsm.manufacturer_id AND rsme.media_section = \'manufacturer\' '.
		'LEFT JOIN `#__hikashop_reds_cat` hkmj ON rsm.manufacturer_id = hkmj.reds_id AND category_type=\'manufacturer\' '.
		'WHERE rsm.manufacturer_id > ' . (int)$this->options->last_reds_manufacturer;
		'ORDER BY rsm.manufacturer_id ASC;';

		$this->db->setQuery($sql1);
		$this->db->query();
		$datas = $this->db->loadObjectList();

		$sql2 = 'INSERT INTO `#__hikashop_category` (`category_id`,`category_parent_id`,`category_type`,`category_name`,`category_published`,'.
			'`category_namekey`,`category_description`,`category_menu`,`category_keywords`) VALUES ';
		$sql3 = 'INSERT INTO `#__hikashop_reds_cat` (`reds_id`,`hk_id`,`category_type`) VALUES ';
		$sql4 = 'INSERT INTO `#__hikashop_file` (`file_name`,`file_description`,`file_path`,`file_type`,`file_ref_id`) VALUES ';
		$doSql3 = false;
		$doSql4 = false;
		$i = $this->options->max_hk_cat + 1;
		$ids = array( 0 => $this->options->main_cat_id);
		$sep = '';
		$sep2 = '';
		$cpt=0;

		if( empty($datas) )
		{
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported manufacturers : 0</p>';
			return true;
		}
		else
		{
			foreach($datas as $data)
			{
				if( !empty($data->reds_id) )
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
				if( !empty($data->reds_id) )
					continue;

				$id = $ids[$data->manufacturer_id];

				$element = 'manufacturer';
				$categoryClass = hikashop_get('class.category');
				$categoryClass->getMainElement($element);

				$d = array(
					$id,
					$element,
					"'manufacturer'",
					$this->db->quote($data->manufacturer_name),
					'1',
					"CONCAT('REDS_MANUFAC_', ".$data->manufacturer_id .")",
					"'manufacturer imported from Redshop'",
					'0',
					$this->db->quote($data->metakey)
				);

				$sql2 .= $sep.'('.implode(',',$d).')';

				if( !empty($data->media_name))
				{
					$this->copyManufDir = str_replace('\\','/',rtrim(JPath::clean(html_entity_decode($this->copyManufDir)),DS.' ').DS);
					echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Copying categories images... </p>';
					$doSql4 = true;
					$sql4 .= $sep2."('".$data->media_name."','','".$data->media_name."','category',".$id.')';
					$sep2 = ',';
					$file_name = str_replace('\\','/',$data->media_name);
					if( strpos($file_name,'/') !== false ) {
						$file_name = substr($file_name, strrpos($file_name,'/'));
					}
					$this->copyFile($this->copyManufDir,$data->media_name, str_replace('//','/',str_replace('\\','/',$this->options->uploadfolder.$file_name)));
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
			$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'reds_import_max_hk_cat'; ");
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

		$this->db->setQuery("SELECT order_status_code, order_status_name FROM `#__redshop_order_status` WHERE order_status_name NOT IN ('".implode("','",$statuses)."');");
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
					$this->db->quote( strtolower($c->order_status_name) ),
					"'Order status imported from Redshop'",
					'1',
					$this->db->quote('status_reds_import_'.strtolower(str_replace(' ','_',$c->order_status_name))),
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

			if( $total > 0 ) {
				echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported order status categories : ' . $total . '</p>';
				$rebuild = true;
				$this->options->max_hk_cat += $total;
				$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'reds_import_max_hk_cat'; ");
				$this->db->query();
				$sql0 = '';
			}
			else
			{
				echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported order status categories : 0</p>';
			}
		}

		$sql1 = 'SELECT * FROM `#__redshop_category` rsc '.
			'LEFT JOIN `#__redshop_category_xref` rscx ON rsc.category_id = rscx.category_child_id '.
			'LEFT JOIN `#__hikashop_reds_cat` hkrs ON rsc.category_id = hkrs.reds_id AND category_type=\'category\' '.
			'WHERE rsc.category_id > '.$this->options->last_reds_cat.' '.
			'ORDER BY category_parent_id ASC, ordering ASC, category_id ASC;';

		$this->db->setQuery($sql1);
		$this->db->query();
		$datas = $this->db->loadObjectList();

		$sql2 = 'INSERT INTO `#__hikashop_category` (`category_id`,`category_parent_id`,`category_type`,`category_name`,`category_description`,`category_published`,'.
			'`category_ordering`,`category_namekey`,`category_created`,`category_access`,`category_menu`,`category_keywords`) VALUES ';
		$sql3 = 'INSERT INTO `#__hikashop_reds_cat` (`reds_id`,`hk_id`,`category_type`) VALUES ';
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
				if( !empty($data->reds_id) )
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
				if( !empty($data->reds_id) )
					continue;

				$id = $ids[$data->category_id];
				if(!empty($ids[$data->category_parent_id]))
					$pid = (int)$ids[$data->category_parent_id];
				else
					$pid = $ids[0];

				$element = new stdClass();
				$element->category_id = $id;
				$element->category_parent_id = $pid;
				$element->category_name = $data->category_name;
				$nameKey = $categoryClass->getNameKey($element);

				$d = array(
					$id,
					$pid,
					"'product'",
					$this->db->quote($data->category_name),
					$this->db->quote($data->category_description),
					$data->published,
					$data->ordering,
					$this->db->quote($nameKey),
					"'".$data->category_pdate."'",
					"'all'",
					'0',
					$this->db->quote($data->metakey)
				);

				$sql2 .= $sep.'('.implode(',',$d).')';

				if( !empty($data->category_full_image))
				{
					$this->copyCatImgDir = str_replace('\\','/',rtrim(JPath::clean(html_entity_decode($this->copyCatImgDir)),DS.' ').DS);
					echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Copying categories images... </p>';
					$doSql4 = true;
					$sql4 .= $sep2."('','','".$data->category_full_image."','category',".$id.')';
					$sep2 = ',';
					$file_name = str_replace('\\','/',$data->category_full_image);
					if( strpos($file_name,'/') !== false ) {
						$file_name = substr($file_name, strrpos($file_name,'/'));
					}
					$this->copyFile($this->copyCatImgDir,$data->category_full_image, str_replace('//','/',str_replace('\\','/',$this->options->uploadfolder.$file_name)));
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
			$this->db->setQuery("UPDATE `#__hikashop_config` SET config_value = ".$this->options->max_hk_cat." WHERE config_namekey = 'reds_import_max_hk_cat'; ");
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

		$this->db->setQuery('SELECT rsp.product_id, rsm.media_name FROM `#__redshop_product` rsp '.
						'LEFT JOIN `#__redshop_media` rsm ON rsp.product_id = rsm.section_id '.
						'LEFT JOIN `#__hikashop_reds_prod` hkprod ON rsp.product_id = hkprod.reds_id '.
						"WHERE rsp.product_id > ".$offset." AND hkprod.hk_id IS NULL AND (rsm.media_name IS NOT NULL) AND rsm.media_name <> '' AND rsm.media_section = 'product' ".
						'ORDER BY product_id ASC LIMIT '.$count.';'
		);

		$datas = $this->db->loadObjectList();
		$this->copyImgDir = str_replace('\\','/',rtrim(JPath::clean(html_entity_decode($this->copyImgDir)),DS.' ').DS);

		if (!empty($datas))
		{
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Copying products images... </p>';
			foreach($datas as $data) {
				if( !empty($data->media_name) ) {
					$file_name = str_replace('\\','/',$data->media_name);
					if( strpos($file_name,'/') !== false ) {
						$file_name = substr($file_name, strrpos($file_name,'/'));
					}
					$this->copyFile($this->copyImgDir,$data->media_name, $this->options->uploadfolder.$file_name);
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
			'product_name' => 'rsp.product_name',
			'product_description' => 'rsp.product_desc',
			'product_meta_description' => 'rsp.metadesc',
			'product_quantity' => 'case when quantity_selectbox_value IS NULL or quantity_selectbox_value < 0 then 0 else quantity_selectbox_value end',
			'product_code' => 'rsp.hika_sku',
			'product_published' => 'rsp.published',
			'product_hit' => 'rsp.visited',
			'product_created' => 'rsp.publish_date',
			'product_modified' => 'rsp.update_date',
			'product_sale_start' => 'rsp.product_availability_date',
			'product_tax_id' => 'hkc.category_id',
			'product_type' => "'main'",
			'product_url' => 'rsp.sef_url',
			'product_weight' => 'rsp.weight',
			'product_weight_unit' => "LOWER('".DEFAULT_WEIGHT_UNIT."')",
			'product_dimension_unit' => "LOWER('".DEFAULT_VOLUME_UNIT."')",
			'product_min_per_order' => 'rsp.min_order_product_quantity',
			'product_max_per_order' => 'rsp.max_order_product_quantity',
			'product_sales' => '0',
			'product_width' => 'rsp.product_width',
			'product_length' => 'rsp.product_length',
			'product_height' => 'rsp.product_height',
			'product_parent_id' => '0' //rsp.product_parent_id ?
		);

		$sql1 = 'INSERT IGNORE INTO `#__hikashop_product` (`'.implode('`,`',array_keys($data)).'`) '.
		'SELECT '.implode(',',$data).' FROM `#__redshop_product` AS rsp '.
		'LEFT JOIN `#__hikashop_taxation` hkt ON hkt.tax_reds_id = rsp.product_tax_id '.
		'LEFT JOIN `#__hikashop_category` hkc ON hkc.category_namekey = hkt.category_namekey '.
		'LEFT JOIN `#__hikashop_reds_prod` AS hkp ON rsp.product_id = hkp.reds_id '.
		'WHERE hkp.hk_id IS NULL ORDER BY rsp.product_id ASC;';

		$this->db->setQuery("SHOW COLUMNS FROM `#__redshop_product` LIKE 'hika_sku';");
		$data = $this->db->loadObjectList();
		if (empty($data))
		{
			$this->db->setQuery('ALTER TABLE `#__redshop_product` ADD COLUMN `hika_sku` VARCHAR(255) NOT NULL;');
			$this->db->query();
		}

		$this->db->setQuery("UPDATE `#__redshop_product` AS rsp SET rsp.hika_sku = CONCAT(rsp.product_name,'_',rsp.product_id) WHERE rsp.hika_sku='';");
		$this->db->query();

		$this->db->setQuery('SELECT hika_sku FROM `#__redshop_product` GROUP BY hika_sku HAVING COUNT(hika_sku)>1');
		$data = $this->db->loadObjectList();

		if (!empty($data))
		{
			foreach ($data as $d)
			{
				$this->db->setQuery("UPDATE `#__redshop_product` AS rsp SET rsp.hika_sku = CONCAT(rsp.hika_sku,'_',rsp.product_id) WHERE rsp.hika_sku = '".$d->hika_sku."';");
				$this->db->query();
			}
		}

		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> SKU generated: '.count($data).'</p>';

		$data = array(
			'reds_id' => 'rsp.product_id',
			'hk_id' => 'hkp.product_id'
		);

		$sql2 = 'INSERT IGNORE INTO `#__hikashop_reds_prod` (`'.implode('`,`',array_keys($data)).'`) '.
		'SELECT '.implode(',',$data).' FROM `#__redshop_product` AS rsp '.
		'INNER JOIN `#__hikashop_product` AS hkp ON CONVERT(rsp.hika_sku USING utf8) = CONVERT(hkp.product_code USING utf8) '.
		'LEFT JOIN `#__hikashop_reds_prod` hkrsp ON hkrsp.reds_id = rsp.product_id '.
		'WHERE hkrsp.hk_id IS NULL;';

		$sql3 = 'UPDATE `#__hikashop_product` AS hkp '.
			'INNER JOIN `#__redshop_product` AS rsp ON CONVERT(rsp.hika_sku USING utf8) = CONVERT(hkp.product_code USING utf8) '.
			'INNER JOIN `#__hikashop_reds_prod` hkrsp ON hkrsp.reds_id = rsp.product_parent_id '.
			'SET hkp.product_parent_id = hkrsp.hk_id;';

		$data = array(
			'file_name' => "''",
			'file_description' => "''",
			'file_path' => "SUBSTRING_INDEX(rsm.media_name,'/',-1)",
			'file_type' => "'product'",
			'file_ref_id' => 'hkprod.hk_id'
		);

		$sql4 = 'INSERT IGNORE INTO `#__hikashop_file` (`'.implode('`,`',array_keys($data)).'`) '.
		'SELECT '.implode(',',$data).' FROM `#__redshop_product` rsp '.
		'INNER JOIN `#__redshop_media` rsm ON rsp.product_id = rsm.section_id '.
		'INNER JOIN `#__hikashop_reds_prod` hkprod ON rsp.product_id = hkprod.reds_id '.
		'WHERE rsp.product_id > '.$this->options->last_reds_prod. ' AND (rsm.media_name IS NOT NULL) AND (rsm.media_name <>'." '') AND rsm.media_section = 'product' ;";

		$sql5 = 'UPDATE `#__hikashop_product` AS hkp '.
		'INNER JOIN `#__hikashop_reds_prod` AS hkrsp ON hkp.product_id = hkrsp.hk_id '.
		'INNER JOIN `#__redshop_product` AS rsp ON hkrsp.reds_id = rsp.product_id '.
		"INNER JOIN `#__hikashop_category` AS hkc ON hkc.category_type = 'manufacturer' AND rsp.manufacturer_id = hkc.category_menu ".
		'SET hkp.product_manufacturer_id = hkc.category_id '.
		'WHERE rsp.manufacturer_id > '.$this->options->last_reds_manufacturer.' OR rsp.product_id > '.$this->options->last_reds_prod.';';

		$this->db->setQuery($sql1);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Inserted products: ' . $total . '</p>';

		$this->db->setQuery($sql2);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Fallback links: ' . $total . '</p>';

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

		$ret = true;
		return $ret;
	}



	function importProductPrices()
	{
		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 6 :</span> Import Product Prices</p>';

		$ret = false;
		$cpt = 0;

		$data = array(
			'price_product_id' => 'hkprod.hk_Id',
			'price_value' => 'rsp.product_price',
			'price_currency_id' => "case when hkcur.currency_id IS NULL then '0' else hkcur.currency_id end",
			'price_min_quantity' => "case when rspp.price_quantity_start IS NULL then '0' else rspp.price_quantity_start end",
			'price_access' => "'all'"
		);

		$this->db->setQuery('INSERT IGNORE INTO #__hikashop_price (`'.implode('`,`',array_keys($data)).'`) '
				.'SELECT '.implode(',',$data).'FROM #__redshop_product rsp '
				.'INNER JOIN #__hikashop_reds_prod hkprod ON rsp.product_id = hkprod.reds_id '
				.'LEFT JOIN #__redshop_product_price rspp ON rsp.product_id = rspp.product_id '
				.'LEFT JOIN #__redshop_currency rsc ON rspp.product_currency = rsc.currency_id '
				.'LEFT JOIN #__hikashop_currency hkcur ON CONVERT(rsc.currency_code USING utf8) = CONVERT( hkcur.currency_code USING utf8) '
				.'WHERE rsp.product_id > ' . (int)$this->options->last_reds_prod
		);

		$ret = $this->db->query();
		$cpt = $this->db->getAffectedRows();

		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Prices imported : ' . $cpt .'</p>';

		return $ret;
	}


	function importProductCategory()
	{
		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 7 :</span> Import Product Category</p>';
		$ret = false;

		$data = array(
			'category_id' => 'hkrc.hk_id',
			'product_id' => 'hkrp.hk_id',
			'ordering' => 'rspcx.ordering',
		);

		$sql = 'INSERT IGNORE INTO `#__hikashop_product_category` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT ' . implode(',',$data).' FROM `#__redshop_product_category_xref` rspcx '.
			'INNER JOIN `#__hikashop_reds_cat` hkrc ON rspcx.category_id = hkrc.reds_id  AND category_type=\'category\''.
			'INNER JOIN `#__hikashop_reds_prod` hkrp ON rspcx.product_id = hkrp.reds_id '.
			'WHERE hkrp.reds_id > ' . (int)$this->options->last_reds_prod . ' OR hkrc.reds_id > ' . (int)$this->options->last_reds_cat;

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
				'SELECT rsui.user_id, rsui.user_email FROM `#__redshop_users_info` AS rsui '.
				'LEFT JOIN `#__hikashop_user` AS hkusr ON rsui.user_id = hkusr.user_cms_id '.
				'WHERE hkusr.user_cms_id IS NULL;';

		$data = array(
			'address_user_id' => 'hku.user_id',
			'address_firstname' => 'rsui.firstname',
			'address_lastname' => 'rsui.lastname',
			'address_company' => 'rsui.company_name',
			'address_street' => 'rsui.address',
			'address_post_code' => 'rsui.zipcode',
			'address_city' => 'rsui.city',
			'address_telephone' => 'rsui.phone',
			'address_state' => 'hkzsta.zone_namekey',
			'address_country' => 'hkzcou.zone_namekey',
			'address_vat' => "case when rsui.tax_exempt='0' then rsui.vat_number else '' end",
			'address_published' => '4'
		);


		$sql1 = 'INSERT IGNORE INTO `#__hikashop_address` (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM `#__redshop_users_info` AS rsui '.
				'INNER JOIN `#__redshop_country` rsc ON rsui.country_code = rsc.country_3_code '.
				'INNER JOIN `#__redshop_state` rss ON rsc.country_id = rss.country_id AND rsui.state_code = rss.state_2_code '.
				'INNER JOIN `#__hikashop_user` AS hku ON rsui.user_id = hku.user_cms_id '.
				'INNER JOIN `#__hikashop_zone` AS  hkzcou ON rsc.country_3_code = hkzcou.zone_code_3 AND hkzcou.zone_type=\'country\' '.
				'INNER JOIN `#__hikashop_zone_link` AS hkzl ON hkzcou.zone_namekey = hkzl.zone_parent_namekey '.
				'INNER JOIN `#__hikashop_zone` AS  hkzsta ON rss.state_2_code = hkzsta.zone_code_3 AND hkzsta.zone_type=\'state\' AND hkzsta.zone_namekey = hkzl.zone_child_namekey '.
				'WHERE rsui.user_id > '.$this->options->last_reds_user.' ORDER BY rsui.user_id ASC;';

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

	function importDiscount()
	{
		echo '<p '.$this->titlefont.'><span'.$this->titlestyle.'>Step 9 :</span> Import Discount</p>';
		$ret = false;

		$sql = "SELECT `config_value` FROM `#__hikashop_config` WHERE config_namekey = 'main_currency';";
		$this->db->setQuery($sql);
		$data = $this->db->loadObjectList();
		$main_currency = $data[0]->config_value;

		$data = array(
			'discount_type' => "'discount'",
			'discount_published' => 'published',
			'discount_code' => "CONCAT('REDS_DISCOUNT_', discount_id)", //Hum
			'discount_currency_id' => $main_currency,
			'discount_flat_amount' => "case when discount_type = '0' then discount_amount else 0 end",
			'discount_percent_amount' => "case when discount_type = '1' then discount_amount else 0 end",
			'discount_start' => 'start_date',
			'discount_end' => 'end_date',
			'discount_quota' => '0'
		);

		$sql1 = 'INSERT IGNORE INTO #__hikashop_discount (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM #__redshop_discount WHERE discount_id > ' . (int)$this->options->last_reds_discount;

		$data['discount_code'] = "CONCAT('REDS_DISCOUNTPROD_', discount_product_id)";

		$sql2 = 'INSERT IGNORE INTO #__hikashop_discount (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM #__redshop_discount_product WHERE discount_product_id > ' . (int)$this->options->last_reds_discount_prod;

		$data = array(
			'discount_type' => "'coupon'",
			'discount_published' => 'published',
			'discount_code' => 'coupon_code',
			'discount_currency_id' => $main_currency,
			'discount_flat_amount' => "case when percent_or_total = '0' then coupon_value else 0 end",
			'discount_percent_amount' => "case when percent_or_total = '1' then coupon_value else 0 end",
			'discount_start' => 'start_date',
			'discount_end' => 'end_date',
			'discount_quota' => 'coupon_left'
		);

		$sql3 = 'INSERT IGNORE INTO #__hikashop_discount (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM #__redshop_coupons WHERE coupon_id > ' . (int)$this->options->last_reds_coupon;

		$data = array(
			'discount_type' => "'coupon'",
			'discount_published' => 'rsv.published',
			'discount_code' => 'rsv.voucher_code',
			'discount_currency_id' => $main_currency,
			'discount_flat_amount' => "case when voucher_type = 'Percentage' then 0 else rsv.amount end",
			'discount_percent_amount' => "case when voucher_type = 'Percentage' then rsv.amount else 0 end",
			'discount_start' => 'rsv.start_date',
			'discount_end' => 'rsv.end_date',
			'discount_quota' => 'rsv.voucher_left',
			'discount_product_id' => 'rspv.product_id'
		);

		$sql4 = 'INSERT IGNORE INTO #__hikashop_discount (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM #__redshop_product_voucher rsv '.
				'LEFT JOIN #__redshop_product_voucher_xref AS rspv ON rsv.voucher_id = rspv.voucher_id '.
				'WHERE rsv.voucher_id > ' . (int)$this->options->last_reds_voucher;

		$this->db->setQuery($sql1);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Discount codes imported : ' . $total . '</p>';

		$this->db->setQuery($sql2);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Discount codes product imported : ' . $total . '</p>';

		$this->db->setQuery($sql3);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Coupons imported : ' . $total . '</p>';

		$this->db->setQuery($sql4);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Vouchers imported : ' . $total . '</p>';

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
				'order_number' => 'rso.order_number',
				'order_reds_id' => 'rso.order_id',
				'order_user_id' => 'hkusr.user_id',
				'order_status' => 'hkc.category_name',
				'order_discount_price' => 'rso.coupon_discount',
				'order_created' => 'rso.cdate',
				'order_modified' => 'rso.mdate',
				'order_ip' => 'rso.ip_address',
				'order_currency_id' => 'hkcur.currency_id',
				'order_shipping_price' => 'rso.order_shipping',
				'order_shipping_method' => "'Redshop import'",
				'order_shipping_id' => '1',
				'order_payment_id' => 0,
				'order_payment_method' => "'Redshop import'",
				'order_full_price' => 'rso.order_total',
				'order_partner_id' => 0,
				'order_partner_price' => 0,
				'order_partner_paid' => 0,
				'order_type' => "'sale'",
				'order_partner_currency_id' => 0,
				'order_shipping_tax' => 'rso.order_shipping_tax',
				'order_discount_tax' => 0
			);

		$sql1 = 'INSERT IGNORE INTO `#__hikashop_order` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `#__redshop_orders` AS rso '.
			'INNER JOIN `#__redshop_order_item` AS rsoi ON rso.order_id = rsoi.order_id '.
			'INNER JOIN `#__redshop_order_status` AS rsos ON rso.order_status = rsos.order_status_code '.
			'JOIN `#__hikashop_category` AS hkc ON rsos.order_status_name = hkc.category_name AND hkc.category_type = \'status\' '.
			'JOIN `#__hikashop_currency` AS hkcur ON CONVERT(rsoi.order_item_currency USING utf8) = CONVERT(hkcur.currency_code USING utf8) OR CONVERT(rsoi.order_item_currency USING utf8) = CONVERT(hkcur.currency_symbol USING utf8)'.
			'JOIN `#__hikashop_user` AS hkusr ON rso.user_id = hkusr.user_cms_id '.
			'WHERE rso.order_id > ' . (int)$this->options->last_reds_order . ' '.
			'GROUP BY rso.order_id '.
			'ORDER BY rso.order_id ASC;';

		$sql1_1 = 'UPDATE `#__hikashop_order` AS hko '.
				'INNER JOIN `#__redshop_coupons` AS rsc ON hko.order_reds_id = rsc.order_id '.
				'INNER JOIN `#__hikashop_discount` AS hkd ON hkd.discount_code = rsc.coupon_code '.
				'SET hko.order_discount_code = hkd.discount_code AND hko.order_discount_price = hkd.discount_flat_amount';

		$data = array(
			'address_user_id' => 'rsui.user_id',
			'address_firstname' => 'rsui.firstname',
			'address_lastname' => 'rsui.lastname',
			'address_company' => 'rsui.company_name',
			'address_street' => 'rsui.address',
			'address_post_code' => 'rsui.zipcode',
			'address_city' => 'rsui.city',
			'address_telephone' => 'rsui.phone',
			'address_state' => 'hkzsta.zone_namekey',
			'address_country' => 'hkzcou.zone_namekey',
			'address_published' => "case when rsui.address_type = 'BT' then 7 else 8 end",
			'address_vat' => "case when rsui.tax_exempt='0' then rsui.vat_number else '' end",
			'address_reds_order_info_id' => 'rsui.order_id'
		);

		$sql2_1 = 'INSERT IGNORE INTO `#__hikashop_address` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `#__redshop_order_users_info` AS rsui '.
			'INNER JOIN `#__redshop_country` rsc ON rsui.country_code = rsc.country_3_code '.
			'INNER JOIN `#__redshop_state` rss ON rsc.country_id = rss.country_id AND rsui.state_code = rss.state_2_code '.
			'INNER JOIN `#__hikashop_user` AS hku ON rsui.user_id = hku.user_cms_id '.
			'INNER JOIN `#__hikashop_zone` AS  hkzcou ON rsc.country_3_code = hkzcou.zone_code_3 AND hkzcou.zone_type=\'country\' '.
			'INNER JOIN `#__hikashop_zone_link` AS hkzl ON hkzcou.zone_namekey = hkzl.zone_parent_namekey '.
			'INNER JOIN `#__hikashop_zone` AS  hkzsta ON rss.state_2_code = hkzsta.zone_code_3 AND hkzsta.zone_type=\'state\' AND hkzsta.zone_namekey = hkzl.zone_child_namekey '.
			'WHERE rsui.order_id > '.$this->options->last_reds_order.' ORDER BY rsui.order_info_id ASC';

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
			'INNER JOIN `#__hikashop_address` AS a ON a.address_reds_order_info_id = o.order_reds_id '.
			'SET o.order_billing_address_id = a.address_id, o.order_shipping_address_id = a.address_id '.
			"WHERE o.order_billing_address_id = 0 AND address_published >= 7 ;";

		$sql4 = 'UPDATE `#__hikashop_order` AS o '.
			'INNER JOIN `#__hikashop_address` AS a ON a.address_reds_order_info_id = o.order_reds_id '.
			'SET o.order_shipping_address_id = a.address_id '.
			"WHERE o.order_shipping_address_id = 0 AND address_published >= 8 ;";

		$sql5 = 'UPDATE `#__hikashop_order` AS hko '.
			'JOIN `#__redshop_orders` AS rso ON hko.order_reds_id = rso.order_id '.
			"SET hko.order_payment_method = CONCAT('Redshop import: ', rso.payment_oprand) ".
			'WHERE hko.order_reds_id > ' . (int)$this->options->last_reds_order;

		$this->db->setQuery($sql1);
		$this->db->query();
		$total = $this->db->getAffectedRows();
		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Imported orders: ' . $total . '</p>';


		$this->db->setQuery("SHOW COLUMNS FROM #__redshop_coupons");
		$cols = $this->db->loadObjectList('Field');
		if (is_array($cols) && array_key_exists('order_id', $cols)){
			$this->db->setQuery($sql1_1);
			$this->db->query();
			$total = $this->db->getAffectedRows();
			echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Updating discount orders: ' . $total . '</p>';
		}

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
			'order_product_quantity' => 'rsoi.product_quantity',
			'order_product_name' => 'rsoi.order_item_name',
			'order_product_code' => 'rsp.hika_sku',
			'order_product_price' => 'rsoi.product_item_price',
			'order_product_tax' => "''",
			'order_product_options' => "''"
		);

		$sql = 'INSERT IGNORE INTO `#__hikashop_order_product` (`'.implode('`,`',array_keys($data)).'`) '.
				'SELECT '.implode(',',$data).' FROM `#__redshop_order_item` AS rsoi '.
				'INNER JOIN `#__redshop_product` rsp ON rsoi.product_id=rsp.product_id '.
				'INNER JOIN `#__hikashop_order` AS hko ON rsoi.order_id = hko.order_reds_id '.
				'INNER JOIN `#__hikashop_reds_prod` AS hkp ON rsoi.product_id = hkp.reds_id '.
				'WHERE rsoi.order_id > ' . (int)$this->options->last_reds_order . ';';

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
			$offset = $this->options->last_reds_pfile;

		$sql = 'SELECT download_id, file_name FROM `#__redshop_product_download` WHERE download_id > '.$offset.' ORDER BY download_id ASC LIMIT '.$count.';';
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
			'file_name' => 'rspd.file_name',
			'file_description' => "'File imported from Redshop'",
			'file_path' => "SUBSTRING_INDEX(SUBSTRING_INDEX(rspd.file_name, '/', -1), '\\\\', -1)",
			'file_type' => "'file'",
			'file_ref_id' => 'hkrsp.hk_id'
		);

		$sql = 'INSERT IGNORE INTO `#__hikashop_file` (`'.implode('`,`',array_keys($data)).'`) '.
		'SELECT '.implode(',',$data).' FROM `#__redshop_product_download` AS rspd '.
		'LEFT JOIN `#__hikashop_reds_prod` AS hkrsp ON rspd.product_id = hkrsp.reds_id '.
		'WHERE rspd.download_id > '.$this->options->last_reds_pfile;

		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();

		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Downloable files imported : ' . $total . '</p>';


		$data = array(
				'file_id' => 'hkf.file_id',
				'order_id' => 'hko.order_id',
				'download_number' => 'rspd.download_max'
			);

		$sql = 'INSERT IGNORE INTO `#__hikashop_download` (`'.implode('`,`',array_keys($data)).'`) '.
			'SELECT '.implode(',',$data).' FROM `#__redshop_product_download` AS rspd '.
			'INNER JOIN `#__hikashop_order` AS hko ON hko.order_reds_id = rspd.order_id '.
			'INNER JOIN `#__hikashop_reds_prod` AS hkp ON hkp.reds_id = rspd.product_id '.
			'INNER JOIN `#__hikashop_file` AS hkf ON ( CONVERT(hkf.file_name USING utf8) = CONVERT(rspd.file_name USING utf8) )'.
			"WHERE hkf.file_type = 'file' AND (hkp.hk_id = hkf.file_ref_id) AND (rspd.product_id > ".$this->options->last_reds_prod.' OR rspd.order_id > ' . (int)$this->options->last_reds_order . ');';

		$this->db->setQuery($sql);
		$this->db->query();
		$total = $this->db->getAffectedRows();

		echo '<p '.$this->pmarginstyle.'><span'.$this->bullstyle.'>&#149;</span> Downloable order files imported : ' . $total . '</p>';

		$ret = true;
		return $ret;
	}


	function finishImport()
	{
		$this->db->setQuery("SELECT max(category_id) as 'max' FROM `#__hikashop_category`;");
		$data = $this->db->loadObjectList();
		$this->options->max_hk_cat = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(product_id) as 'max' FROM `#__hikashop_product`;");
		$data = $this->db->loadObjectList();
		$this->options->max_hk_prod = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(reds_id) as 'max' FROM `#__hikashop_reds_cat`;");
		$data = $this->db->loadObjectList();
		$this->options->last_reds_cat = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(reds_id) as 'max' FROM `#__hikashop_reds_prod`;");
		$data = $this->db->loadObjectList();
		$this->options->last_reds_prod = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(user_id) as 'max' FROM `#__redshop_users_info`;");
		$data = $this->db->loadObjectList();
		$this->options->last_reds_user = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(order_reds_id) as 'max' FROM `#__hikashop_order`;");
		$data = $this->db->loadObjectList();
		$this->options->last_reds_order = (int)($data[0]->max);

		$this->db->setQuery("SELECT max(rsm.media_id) as 'max' FROM `#__redshop_media` rsm INNER JOIN `#__redshop_product` rsp ON rsp.product_id = rsm.section_id AND media_section = 'product';");
		$data = $this->db->loadObject();
		$this->options->last_reds_pfile = (int)($data->max);

		$this->db->setQuery("SELECT max(coupon_id) as 'max' FROM `#__redshop_coupons`;");
		$data = $this->db->loadObject();
		$this->options->last_reds_coupon = (int)($data->max);

		$this->db->setQuery("SELECT max(discount_id) as 'max' FROM `#__redshop_discount`;");
		$data = $this->db->loadObject();
		$this->options->last_reds_discount = (int)($data->max);

		$this->db->setQuery("SELECT max(discount_product_id) as 'max' FROM `#__redshop_discount_product`;");
		$data = $this->db->loadObject();
		$this->options->last_reds_discount_prod = (int)($data->max);

		$this->db->setQuery("SELECT max(voucher_id) as 'max' FROM `#__redshop_product_voucher`;");
		$data = $this->db->loadObject();
		$this->options->last_reds_voucher = (int)($data->max);

		$this->db->setQuery("SELECT max(tax_rate_id) as 'max' FROM `#__redshop_tax_rate`;");
		$data = $this->db->loadObject();
		$this->options->last_reds_taxrate = (int)($data->max);

		$this->db->setQuery("SELECT max(tax_group_id) as 'max' FROM `#__redshop_tax_group`;");
		$data = $this->db->loadObject();
		$this->options->last_reds_taxclass = (int)($data->max);

		$this->db->setQuery("SELECT max(manufacturer_id) as 'max' FROM `#__redshop_manufacturer`;");
		$data = $this->db->loadObjectList();
		$this->options->last_reds_manufacturer = (int)($data[0]->max);

		$this->options->state = (MAX_IMPORT_ID+1);
		$query = 'REPLACE INTO `#__hikashop_config` (`config_namekey`,`config_value`,`config_default`) VALUES '.
				"('reds_import_state',".$this->options->state.",".$this->options->state.")".
				",('reds_import_max_hk_cat',".$this->options->max_hk_cat.",".$this->options->max_hk_cat.")".
				",('reds_import_max_hk_prod',".$this->options->max_hk_prod.",".$this->options->max_hk_prod.")".
				",('reds_import_last_reds_cat',".$this->options->last_reds_cat.",".$this->options->last_reds_cat.")".
				",('reds_import_last_reds_prod',".$this->options->last_reds_prod.",".$this->options->last_reds_prod.")".
				",('reds_import_last_reds_user',".$this->options->last_reds_user.",".$this->options->last_reds_user.")".
				",('reds_import_last_reds_order',".$this->options->last_reds_order.",".$this->options->last_reds_order.")".
				",('reds_import_last_reds_pfile',".$this->options->last_reds_pfile.",".$this->options->last_reds_pfile.")".
				",('reds_import_last_reds_coupon',".$this->options->last_reds_coupon.",".$this->options->last_reds_coupon.")".
				",('reds_import_last_reds_discount',".$this->options->last_reds_discount.",".$this->options->last_reds_discount.")".
				",('reds_import_last_reds_discount_prod',".$this->options->last_reds_discount_prod.",".$this->options->last_reds_discount_prod.")".
				",('reds_import_last_reds_voucher',".$this->options->last_reds_voucher.",".$this->options->last_reds_voucher.")".
				",('reds_import_last_reds_taxrate',".$this->options->last_reds_taxrate.",".$this->options->last_reds_taxrate.")".
				",('reds_import_last_reds_taxclass',".$this->options->last_reds_taxclass.",".$this->options->last_reds_taxclass.")".
				",('reds_import_last_reds_manufacturer',".$this->options->last_reds_manufacturer.",".$this->options->last_reds_manufacturer.")".
				';';
		$this->db->setQuery($query);
		$this->db->query();

		echo '<p'.$this->titlefont.'>Import finished !</p>';

		$class = hikashop_get('class.plugins');

		$infos = $class->getByName('system','reds_redirect');
		if($infos)
		{
			$pkey = reset($class->pkeys);
			if(!empty($infos->$pkey))
			{
				if(version_compare(JVERSION,'1.6','<'))
					$url = JRoute::_('index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]='.$infos->$pkey);
				else
					$url = JRoute::_('index.php?option=com_plugins&view=plugin&layout=edit&extension_id='.$infos->$pkey);

				echo '<p>You can publish the <a'.$this->linkstyle.' href="'.$url.'">Redshop Fallback Redirect Plugin</a> so that your old Redshop links are automatically redirected to HikaShop pages and thus not loose the ranking of your content on search engines.</p>';
			}
		}
	}

}
