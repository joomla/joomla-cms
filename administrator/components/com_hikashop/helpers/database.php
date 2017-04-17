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
class hikashopDatabaseHelper {
	protected $db = null;
	public static $check_results = null;

	public function __construct() {
		$this->db = JFactory::getDBO();
	}

	function addColumns($table, $columns) {
		if(!is_array($columns))
			$columns = array($columns);

		$query = 'ALTER TABLE `'.hikashop_table($table).'` ADD '.implode(', ADD', $columns).';';
		$this->db->setQuery($query);
		$err = false;

		try {
			$this->db->query();
		}catch(Exception $e) {
			$err = true;
		}
		if(!$err)
			return true;

		if($err && count($columns) > 1) {

			foreach($columns as $col) {
				$query = 'ALTER TABLE `'.hikashop_table($table).'` ADD '.$col.';';

				$this->db->setQuery($query);
				$err = 0;

				try {
					$this->db->query();
				}catch(Exception $e) {
					$err++;
				}
			}

			if($err < count($columns))
				return true;
		}

		return false;
	}

	public function parseTableFile($filename, &$createTable, &$structure) {
		$queries = file_get_contents($filename);
		$tables = explode('CREATE TABLE IF NOT EXISTS', $queries);

		foreach($tables as $oneTable) {
			$fields = explode("\n\t", trim($oneTable));

			$tableNameTmp = substr($oneTable, strpos($oneTable, '`') + 1, strlen($oneTable) - 1);
			$tableName = substr($tableNameTmp, 0, strpos($tableNameTmp, '`'));
			if(empty($tableName))
				continue;

			foreach($fields as $oneField) {
				$oneField = trim($oneField);
				if(substr($oneField,0,1) != '`' || substr($oneField, strlen($oneField) - 1, strlen($oneField)) != ',')
					continue;

				if(empty($structure[$tableName]))
					$structure[$tableName] = array();

				$fieldNameTmp = substr($oneField,strpos($oneField,'`') + 1, strlen($oneField) - 1);
				$fieldName = substr($fieldNameTmp, 0, strpos($fieldNameTmp, '`'));
				$structure[$tableName][$fieldName] = trim($oneField, ',');
			}

			$createTable[$tableName] = 'CREATE TABLE IF NOT EXISTS ' . trim($oneTable);
		}
	}

	public function checkdb() {
		$createTable = array();
		$structure = array();
		$this->parseTableFile(HIKASHOP_BACK . 'tables.sql', $createTable, $structure);

		try{
			$this->db->setQuery("SELECT * FROM #__hikashop_field");
			$custom_fields = $this->db->loadObjectList();
		} catch(Exception $e) {
			$custom_fields = array();
			$msg = $e->getMessage();
		}

		$ret = array();
		ob_start();

		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onHikashopBeforeCheckDB', array(&$createTable, &$custom_fields, &$structure, &$this));

		$html = ob_get_clean();
		if(!empty($html))
			$ret[] = $html;

		if(!empty($custom_fields)){
			foreach($custom_fields as $custom_field) {
				if(@$custom_field->field_type == 'customtext')
					continue;
				if(substr($custom_field->field_table, 0, 4) == 'plg.')
					continue;

				switch($custom_field->field_table) {
					case 'contact':
						break;
					case 'item':
						$table = '#__hikashop_cart_product';
						if(!isset($structure[$table][$custom_field->field_namekey]))
							$structure[$table][$custom_field->field_namekey] = '`'.$custom_field->field_namekey.'` TEXT NULL';
						$table = '#__hikashop_order_product';
						if(!isset($structure[$table][$custom_field->field_namekey]))
							$structure[$table][$custom_field->field_namekey] = '`'.$custom_field->field_namekey.'` TEXT NULL';
						break;
					default:
						$table = '#__hikashop_'.$custom_field->field_table;
						if(!isset($structure[$table][$custom_field->field_namekey]))
							$structure[$table][$custom_field->field_namekey] = '`'.$custom_field->field_namekey.'` TEXT NULL';
						break;
				}
			}
		}

		$tableName = array_keys($structure);
		$structureDB = array();

		foreach($tableName as $oneTableName) {
			$msg = '';
			$fields2 = null;
			try{
				$this->db->setQuery('SHOW COLUMNS FROM ' . $oneTableName);
				$fields2 = $this->db->loadObjectList();
			} catch(Exception $e) {
				$fields2 = null;
				$msg = $e->getMessage();
			}

			$table_name = str_replace('#__', '', $oneTableName);

			if($fields2 == null) {
				if(empty($msg))
					$msg = substr(strip_tags($this->db->getErrorMsg()), 0, 200);

				$ret[] = array(
					'info',
					sprintf('Could not load columns from the table "%s" : %s', $table_name, $msg)
				);

				$msg = '';
				try {
					$this->db->setQuery($createTable[$oneTableName]);
					$isError = $this->db->query();
				} catch(Exception $e) {
					$isError = null;
					$msg = $e->getMessage();
				}

				if($isError == null) {
					if(empty($msg))
						$msg = substr(strip_tags($this->db->getErrorMsg()), 0, 200);
					$ret[] = array(
						'error',
						sprintf('Could not create the table "%s"', $table_name)
					);
					$ret[] = array('error_msg', $msg);
				} else {
					$ret[] = array(
						'success',
						sprintf('Problem solved - table "%s" created', $table_name)
					);
				}
			}

			if(!empty($fields2)) {
				foreach($fields2 as $oneField) {
					if(empty($structureDB[$oneTableName]))
						$structureDB[$oneTableName] = array();

					$structureDB[$oneTableName][$oneField->Field] = $oneField->Field;
				}
			}

		}

		foreach($tableName as $oneTableName) {
			$t = array();
			if(!empty($structureDB[$oneTableName]))
				$t = $structureDB[$oneTableName];

			$resultCompare[$oneTableName] = array_diff(array_keys($structure[$oneTableName]), $t);

			$table_name = str_replace('#__', '', $oneTableName);

			if(empty($resultCompare[$oneTableName])) {
				$ret[] = array(
					'success',
					sprintf('Table "%s" checked', $table_name)
				);
				continue;
			}

			foreach($resultCompare[$oneTableName] as $oneField) {
				$ret[] = array(
					'info',
					sprintf('Field "%s" missing in %s', $oneField, $table_name)
				);

				$msg = '';
				try{
					$this->db->setQuery('ALTER TABLE ' . $oneTableName . ' ADD ' . $structure[$oneTableName][$oneField]);
					$isError = $this->db->query();
				} catch(Exception $e) {
					$isError = null;
					$msg = $e->getMessage();
				}

				if($isError == null) {
					if(empty($msg))
						$msg = substr(strip_tags($this->db->getErrorMsg()), 0, 200);

					$ret[] = array(
						'error',
						sprintf('Could not add the field "%s" in the table "%s"', $oneField, $table_name)
					);
					$ret[] = array('error_msg', $msg);
				} else {
					$ret[] = array(
						'success',
						sprintf('Field "%s" added in the table "%s"', $oneField, $table_name)
					);
				}
			}
		}


		$query = 'SELECT count(p.product_id) as result FROM `#__hikashop_product` AS p ' .
				' LEFT JOIN `#__hikashop_product_category` AS pc ON p.product_id = pc.product_id ' .
				' WHERE p.product_type = ' . $this->db->Quote('main') . ' AND pc.category_id IS NULL;';
		try {
			$this->db->setQuery($query);
			$result = $this->db->loadResult();
		} catch(Exception $e) {
			$result = -1;
		}

		if($result > 0) {
			$ret[] = array(
				'info',
				sprintf('Found %d product(s) without category', $result)
			);

			$product_category_id = 'product';
			$categoryClass = hikashop_get('class.category');
			$categoryClass->getMainElement($product_category_id);

			$query = 'INSERT INTO `#__hikashop_product_category` (category_id, product_id, ordering) ' .
					' SELECT '.$product_category_id.', p.product_id, 1 FROM `#__hikashop_product` AS p ' .
					' LEFT JOIN `#__hikashop_product_category` AS pc ON p.product_id = pc.product_id ' .
					' WHERE p.product_type = ' . $this->db->Quote('main') . ' AND pc.category_id IS NULL;';

			$msg = '';
			try {
				$this->db->setQuery($query);
				$isError = $this->db->query();
			} catch(Exception $e) {
				$isError = null;
				$msg = $e->getMessage();
			}

			if($isError == null) {
				if(empty($msg))
					$msg = substr(strip_tags($this->db->getErrorMsg()), 0, 200);

				$ret[] = array(
					'error',
					'Could not retrieve the missing products'
				);
				$ret[] = array('error_msg', $msg);
			} else {
				$ret[] = array(
					'success',
					sprintf('Add %d product(s) in the main product category', $result)
				);
			}
		} else if($result < 0) {
			$ret[] = array(
				'error',
				'Could not check for missing products'
			);
		} else {
			$ret[] = array(
				'success',
				'Product categories checked'
			);
		}

		$query = 'SELECT characteristic_id FROM `#__hikashop_characteristic`;';
		try {
			$this->db->setQuery($query);
			if(!HIKASHOP_J25){
				$characteristic_ids = $this->db->loadResultArray();
			} else {
				$characteristic_ids = $this->db->loadColumn();
			}
			$where = '';
			if(is_array($characteristic_ids) && count($characteristic_ids)){
				$where = ' WHERE variant_characteristic_id NOT IN('.implode(',',$characteristic_ids).')';
			}
			$query = 'DELETE FROM `#__hikashop_variant`'.$where.';';
			$this->db->setQuery($query);
			$result = $this->db->query();
			if($result){
				$ret[] = array(
					'success',
					'Variants orphan links cleaned'
				);
			}
		} catch(Exception $e) {
			$ret[] = array(
				'error',
				$e->getMessage()
			);
		}

		$dispatcher->trigger('onHikashopAfterCheckDB', array(&$ret));

		$config = hikashop_config();
		$cfgVersion = $config->get( 'version');
		$manifestVersion  =  $this->getVersion_NumberOnly();
		if ( version_compare( $manifestVersion, $cfgVersion) > 0) {
			$query = "UPDATE `#__hikashop_config` SET `config_value` = ".$this->db->Quote($manifestVersion)." WHERE config_namekey = 'version'";
			$this->db->setQuery($query);
			$this->db->query();
		}

		self::$check_results = $ret;
		return $ret;
	}

	public function getVersion()
	{
		 static $instance;

		 if ( isset( $instance)) { return $instance; }

		 jimport( 'joomla.application.helper');
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		 $version = "unknown";

		 $folder  = dirname(dirname(__FILE__));
		 $pattern = '^'
					 . substr( basename( dirname(dirname(__FILE__)),'.php'), 4)
					 . '.*'
					 . '\.xml$'
					 ;
		$xmlFilesInDir = JFolder::files( $folder, $pattern);
		if ( !empty( $xmlFilesInDir)) {
			foreach ($xmlFilesInDir as $xmlfile) {
				if ($data = JApplicationHelper::parseXMLInstallFile($folder.DS.$xmlfile)) {
					if ( isset( $found_version)) {
						if ( version_compare( $data['version'], $found_version) >= 0) {
							$found_version = $data['version'];
						}
					}
					else {
						$found_version = $data['version'];
					}
				}
			}
			if ( !empty( $found_version)) {
				$version = $found_version;
			}
		}
		else {
			$filename = dirname(dirname(__FILE__)) .DS. substr( basename( dirname(dirname(__FILE__)),'.php'), 4).'.xml';
			if (file_exists($filename) && $data = JApplicationHelper::parseXMLInstallFile($filename)) {
				if (isset($data['version']) && !empty($data['version'])) {
					$version = $data['version'];
				}
			}
		}

		$instance = $version;
		return $instance;
	}

	public function getVersion_NumberOnly( $verString=null)
	{
		 if ( empty( $verString)) {
				$verString = $this->getVersion();
		 }

		 if ( preg_match( '#[A-Za-z0-9\.\s]+#i', $verString, $match)) {
				$result = $match[0];
		 }
		 else {
				$parts = explode( '-', $verString);
				$result = $parts[0];
		 }

		 $result = str_replace( ' ', '.', trim( $result));
		 return $result;
	}

	public function getCheckResults() {
		return self::$check_results;
	}
}
