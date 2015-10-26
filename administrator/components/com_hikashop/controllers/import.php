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

class ImportController extends hikashopController
{

	var $type='import';
	var $helperImport;
	var $db;

	function __construct()
	{
		parent::__construct();
		$this->db = JFactory::getDBO();
		$this->modify[]='import';
		$this->registerDefaultTask('show');
		$this->helper = hikashop_get('helper.import');
	}

	function import()
	{
		JRequest::checkToken('request') || die( 'Invalid Token' );
		$function = JRequest::getCmd('importfrom');
		$this->helper->addTemplate(JRequest::getInt('template_product',0));

		switch($function){
			case 'file':
				$this->_file();
				break;
			case 'textarea':
				$this->_textarea();
				break;
			case 'folder':
				if(hikashop_level(2)){
					$this->_folder();
				}else{
					$app =& JFactory::getApplication();
					$app->enqueueMessage(Text::_('ONLY_FROM_HIKASHOP_BUSINESS'),'error');
				}
				break;
			case 'vm':
				$query = 'SHOW TABLES LIKE '.$this->db->Quote($this->db->getPrefix().substr(hikashop_table('virtuemart_products',false),3));
				$this->db->setQuery($query);
				$table = $this->db->loadResult();
				if (empty($table))
				{
					$query='SHOW TABLES LIKE '.$this->db->Quote($this->db->getPrefix().substr(hikashop_table('vm_product',false),3));
					$this->db->setQuery($query);
					$table = $this->db->loadResult();
					if (empty($table))
					{
						$app =& JFactory::getApplication();
						$app->enqueueMessage('VirtueMart has not been found in the database','error');
					}
					else
					{
						$this->helperImport = hikashop_get('helper.import-vm1', $this);
						$this->_vm();
					}
				}
				else
				{
					$this->helperImport = hikashop_get('helper.import-vm2', $this);
					$this->_vm();
				}
				break;
			case 'mijo':
				$this->helperImport = hikashop_get('helper.import-mijo',$this);
				$query='SHOW TABLES LIKE '.$this->db->Quote($this->db->getPrefix().substr(hikashop_table('mijoshop_product',false),3));
				$this->db->setQuery($query);
				$table = $this->db->loadResult();
				if (empty($table))
				{
					$app =& JFactory::getApplication();
					$app->enqueueMessage('Mijoshop has not been found in the database','error');
				}
				else
				{
					$this->_mijo();
				}
				break;
			case 'redshop':
				$this->helperImport = hikashop_get('helper.import-reds',$this);
				$query='SHOW TABLES LIKE '.$this->db->Quote($this->db->getPrefix().substr(hikashop_table('redshop_product',false),3));
				$this->db->setQuery($query);
				$table = $this->db->loadResult();
				if (empty($table))
				{
					$app =& JFactory::getApplication();
					$app->enqueueMessage('Redshop has not been found in the database','error');
				}
				else
				{
					$this->_redshop();
				}
				break;
			case 'openc':
				$this->helperImport = hikashop_get('helper.import-openc',$this);
				$this->_opencart();
				break;
			default:
				$plugin = hikashop_import('hikashop',$function);
				if($plugin)
					$plugin->onImportRun();
				break;
		}
		return $this->show();
	}

	function _textarea(){
		$content = JRequest::getVar('textareaentries','','','string',JREQUEST_ALLOWRAW);
		$this->helper->overwrite = JRequest::getInt('textarea_update_products');
		$this->helper->createCategories = JRequest::getInt('textarea_create_categories');
		$this->helper->force_published = JRequest::getInt('textarea_force_publish');
		return $this->helper->handleContent($content);
	}

	function _folder(){
		$type = JRequest::getCmd('importfolderfrom');
		$delete = JRequest::getInt('delete_files_automatically');
		$uploadFolder = JRequest::getVar($type.'_folder','');
		return $this->helper->importFromFolder($type,$delete,$uploadFolder);
	}

	function _file(){
		$importFile =  JRequest::getVar( 'importfile', array(), 'files','array');
		$this->helper->overwrite = JRequest::getInt('file_update_products');
		$this->helper->createCategories = JRequest::getInt('file_create_categories');
		$this->helper->force_published = JRequest::getInt('file_force_publish');
		return $this->helper->importFromFile($importFile);
	}


	function _vm() {
		return $this->helperImport->importFromVM();
	}

	function _mijo() {
		return $this->helperImport->importFromMijo();
	}

	function _redshop() {
		return $this->helperImport->importFromRedshop();
	}

	function _opencart() {
		return $this->helperImport->importFromOpenc();
	}

}
