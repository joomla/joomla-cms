<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
class Com_DJImageSliderInstallerScript
{
	/*
	 * $parent is the class calling this method.
	 * $type is the type of change (install, update or discover_install, not uninstall).
	 * preflight runs before anything else and while the extracted files are in the uploaded temp folder.
	 * If preflight returns false, Joomla will abort the update and undo everything already done.
	 */
	function preflight( $type, $parent ) {
		
		if($type == 'update') { 

			$jversion = new JVersion();
			
			if(version_compare($this->getParam('version'), '2.0', 'lt')) {
				
				$db = JFactory::getDBO();
				$db->setQuery('SELECT extension_id FROM #__extensions WHERE name = "com_djimageslider"');
				$ext_id = $db->loadResult();
				// adding the schema version before update to 2.0+
				if($ext_id) {
					$db->setQuery("INSERT INTO #__schemas (extension_id, version_id) VALUES (".$ext_id.", '1.3')");
					$db->execute();
				}
			}
		}
	}
	
	function getParam( $name ) {
		$db = JFactory::getDbo();
		$db->setQuery('SELECT manifest_cache FROM #__extensions WHERE name = "com_djimageslider"');
		$manifest = json_decode( $db->loadResult(), true );
		return $manifest[ $name ];
	}
 
	/*
	 * sets parameter values in the component's row of the extension table
	 */
	function setParams($param_array) {
		if ( count($param_array) > 0 ) {
			// read the existing component value(s)
			$db = JFactory::getDbo();
			$db->setQuery('SELECT params FROM #__extensions WHERE name = "com_djimageslider"');
			$params = json_decode( $db->loadResult(), true );
			// add the new variable(s) to the existing one(s)
			foreach ( $param_array as $name => $value ) {
				$params[ (string) $name ] = (string) $value;
			}
			// store the combined new and existing values back as a JSON string
			$paramsString = json_encode( $params );
			$db->setQuery('UPDATE #__extensions SET params = ' .
				$db->quote( $paramsString ) .
				' WHERE name = "com_djimageslider"' );
				$db->execute();
		}
	}
}
