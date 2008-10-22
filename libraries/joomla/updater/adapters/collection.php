<?php
/**
 * Collection Update Adapter
 * Handles retrieving updates from collections
 */
 
defined('JPATH_BASE') or die();
 
jimport('joomla.updater.updateadapter');
 
/**
 * Collection Update Adapater Class
 * @since 1.6
 */
class JUpdaterCollection extends JUpdateAdapter {
	
	var $base;
	var $parent = Array(0); 
	var $pop_parent = 0;
	var $update_sites;
	var $updates;
	
	/**
     * Gets the reference to the current direct parent
     *
     * @return object
     */
    function _getStackLocation()
    {
            /*$return = '';
            
            foreach($this->_stack as $stack) {
                    $return .= $stack.'->';
            }

            return rtrim($return, '->');*/
            return implode('->', $this->_stack);
    }
    
    function _getParent() {
    	return end($this->parent);
    }
	
	function _startElement($parser, $name, $attrs = Array()) {
		array_push($this->_stack, $name);
		$tag = $this->_getStackLocation();
		// reset the data
		eval('$this->'. $tag .'->_data = "";');
		//echo 'Opened: '; print_r($this->_stack); echo '<br />';
		//print_r($attrs); echo '<br />';
		switch($name) {
			case 'CATEGORY':
				if(isset($attrs['REF'])) {
					$this->update_sites[] = Array('type'=>'collection','location'=>$attrs['REF'],'updatesiteid'=>$this->_updatesiteid);
					//echo 'Found new update collection: '. $attrs['NAME'] .'<br />';
				} else {
					// This item will have children, so prepare to attach them
					$this->pop_parent = 1;
				}
				break;
			case 'EXTENSION':
				$update =& JTable::getInstance('update');
				$update->updatesiteid = $this->_updatesiteid;
				foreach($this->_updatecols AS $col) {
					// reset the values if it doesn't exist
					if(!array_key_exists($col, $attrs)) {
						$attrs[$col] = '';
						if($col == 'CLIENT') {
							$attrs[$col] = 'site';
						}
					}
				}
				//echo '<br /><br />';
				$client = JApplicationHelper::getClientInfo($attrs['CLIENT'],1);
				$attrs['CLIENT_ID'] = $client->id;
				// lower case all of the fields
				foreach($attrs as $key=>$attr) {
					$values[strtolower($key)] = $attr;
				}
				
				// only add the update if it is on the same platform and release as we are
				$ver = new JVersion();
				$filter =& JFilterInput::getInstance();
				$product = strtolower($filter->clean($ver->PRODUCT, 'cmd')); // lower case and remove the exclamation mark
				// set defaults, the extension file should clarify in case but it may be only available in one version
				// this allows an update site to specify a targetplatform
				// targetplatformversion can be a regexp, so 1.[56] would be valid for an extension that supports 1.5 and 1.6
				// Note: whilst the version is a regexp here, the targetplatform is not (new extension per platform)
				//       Additionally, the version is a regexp here and it may also be in an extension file if the extension is
				//       compatible against multiple versions of the same platform (e.g. a library)
				if(!isset($values['targetplatform'])) $values['targetplatform'] = $product; // set this to ourself as a default
				if(!isset($values['targetplatformversion'])) $values['targetplatformversion'] = $ver->RELEASE; // set this to ourself as a default
				// validate that we can install the extension 
				if($product == $values['targetplatform'] && preg_match('/'.$values['targetplatformversion'].'/',$ver->RELEASE)) {				
					$update->bind($values);
					$this->updates[] = $update;
				}
				break;
		}
	}
	
	function _endElement($parser, $name) {
		$lastcell = array_pop($this->_stack);
		//echo 'Closed: ' . $lastcell .'; Stack: '. print_r($this->_stack,1) .'<br /><br />';
		//echo 'Closed: '; print_r($this->_stack); echo '<br /><br />';
		switch($name) {
			case 'CATEGORY':
				if($this->pop_parent) {
					$this->pop_parent = 0;
					array_pop($this->parent);
				}
				break;
		}
	}
	
	/*// we don't care about char data in collection because there should be none
	function _characterData($parser, $data) {
		$tag = $this->_getStackLocation();
		eval('$obj =& $this->'. $tag .'->_data;');
		$obj .= $data;
	}*/
	
	function findUpdate($options) {
		$url = $options['location'];
		$this->_updatesiteid = $options['updatesiteid'];
		//echo '<p>Find update for collection run on <a href="'. $url .'">'. $url .'</a></p>';
		if(substr($url, -4) != '.xml') {
			if(substr($url, -1) != '/') {
				$url .= '/';
			}
			$url .= 'update.xml';
		}
		
		$this->base = new stdClass();
		$this->update_sites = Array();
		$this->updates = Array();
		$dbo =& $this->parent->getDBO();
		
		if (!($fp = @fopen($url, "r"))) {
			// TODO: Add a 'mark bad' setting here somehow
		    JError::raiseWarning('101', JText::_('Update') .'::'. JText::_('Collection') .': '. JText::_('Could not open').' '. $url);
		    return false;
		}
		
		$this->xml_parser = xml_parser_create('');
		xml_set_object($this->xml_parser, $this);
		xml_set_element_handler($this->xml_parser, '_startElement', '_endElement');
		//xml_set_character_data_handler($this->xml_parser, '_characterData');
	
		while ($data = fread($fp, 8192)) {
		    if (!xml_parse($this->xml_parser, $data, feof($fp))) {
		        die(sprintf("XML error: %s at line %d",
		                    xml_error_string(xml_get_error_code($this->xml_parser)),
		                    xml_get_current_line_number($this->xml_parser)));
		    }
		}
		// TODO: Decrement the bad counter if non-zero
		/*if(count($this->update_sites)) {
			return $this->update_sites;
		} else return true;*/
		return Array('update_sites'=>$this->update_sites,'updates'=>$this->updates);
	}
}