<?php
/**
 * Installer Dependency Checking
 * Builds a tree to determine the dependency
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Dependency Checker
 * @since 1.6
 */
class JDependency {
	/**
	 * Resolve
	 */
	public function resolve() {
		
	}
	
	/**
	 * Validates a set of packages can install
	 */
	public function validate($packages) {
		foreach($packages as $package) {
			
		}
	}
	
	private function _buildTree() {
		$dbo = JFactory::getDBO();
		$dbo->setQuery('SELECT type, element, folder, manifest_cache, client_id FROM #__extensions WHERE state > 0');
		$results = $dbo->loadObjectList();
		$rc = count($results); $i = 0;
		do {
			$result = $result[$i];
			if(!$result->manifest_cache) {
				$path_opt = JDependency::getPathType($result->type, $result->element, $result->folder, $result->client_id);
				$result->manifest_cache = serialize(JApplicationHelper::parseXMLInstallFile(JApplicationHelper::getPath($path_opt['variable'], $path_opt['user_option'])));
			}
			$i++;
		} while($i < $rc);
	}

	/**
	 * Get the current platform
	 * @return array name of the platform and version
	 */
	public static function getCurrentPlatform() {
		$version = new JVersion();
		$filter =& JFilterInput::getInstance();
		$name = strtolower($filter->clean($version->PRODUCT, 'cmd'));
		return Array('name'=>$name, 'version'=>$version->getShortVersion());
	}
	
	/**
	 * Returns path information for a given set of variables
	 * Used in tandem with JApplicationHelper::getPath
	 */
	public static function getPathType($type, $element, $folder, $client) {
		switch($type) {
			case 'plugin':
				return Array('variable'=>'plg_xml', 'path_opt'=>$folder.'/'.$element);
				break;
			case 'component':
				return Array('variable'=>'com_xml', 'path_opt'=>$element);
				break;
			case 'module':
				return Array('variable'=>'mod'.intval($client).'_xml', 'path_opt'=>$element);
				break;
		}
	}
}
