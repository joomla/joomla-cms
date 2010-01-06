<?php

defined('JPATH_BASE') or die();

jimport('joomla.updater.updateadapter');

class JUpdaterExtension extends JUpdateAdapter
{
	protected function _startElement($parser, $name, $attrs = Array()) {
		array_push($this->_stack, $name);
		$tag = $this->_getStackLocation();
		// reset the data
		eval('$this->'. $tag .'->_data = "";');
		//echo 'Opened: '; print_r($this->_stack); echo '<br />';
		//print_r($attrs); echo '<br />';
		switch($name) {
			case 'UPDATE':
				$this->current_update =& JTable::getInstance('update');
				$this->current_update->update_site_id = $this->_update_site_id;
				$this->current_update->detailsurl = $this->_url;
				break;
			case 'UPDATES': // don't do anything
				break;
			default:
				if(in_array($name, $this->_updatecols)) {
					$name = strtolower($name);
					$this->current_update->$name = '';
				}
				if($name == 'TARGETPLATFORM') {
					$this->current_update->targetplatform = $attrs;
				}
				break;
		}
	}

	protected function _endElement($parser, $name)
	{
		array_pop($this->_stack);
		//echo 'Closing: '. $name .'<br />';
		switch($name) {
			case 'UPDATE':
				$ver = new JVersion();
				$product = strtolower(JFilterInput::clean($ver->PRODUCT, 'cmd')); // lower case and remove the exclamation mark
				// check that the product matches and that the version matches (optionally a regexp)
				if($product == $this->current_update->targetplatform['NAME'] && preg_match('/'.$this->current_update->targetplatform['VERSION'].'/',$ver->RELEASE)) {
					// Target platform isn't a valid field in the update table so unset it to prevent J! from trying to store it
					unset($this->current_update->targetplatform);
					if(isset($this->latest)) {
						if(version_compare($this->current_update->version, $this->latest->version, '>') == 1) {
							$this->latest = $this->current_update;
						}
					} else {
						$this->latest = $this->current_update;
					}
				}
				break;
			case 'UPDATES':
				// :D
				break;
		}
	}

	protected function _characterData($parser, $data)
	{
		$tag = $this->_getLastTag();
		//if(!isset($this->$tag->_data)) $this->$tag->_data = '';
		//$this->$tag->_data .= $data;
		if(in_array($tag, $this->_updatecols)) {
			$tag = strtolower($tag);
			$this->current_update->$tag .= $data;
		}
	}

	public function findUpdate($options)
	{
		$url = $options['location'];
		$this->_url =& $url;
		$this->_update_site_id = $options['update_site_id'];
		//echo '<p>Find update for extension run on <a href="'. $url .'">'. $url .'</a></p>';
		if(substr($url, -4) != '.xml') {
			if(substr($url, -1) != '/') {
				$url .= '/';
			}
			$url .= 'extension.xml';
		}


		$dbo =& $this->parent->getDBO();

		if (!($fp = @fopen($url, "r"))) {
			// TODO: Add a 'mark bad' setting here somehow
		    JError::raiseWarning('101', JText::_('Update') .'::'. JText::_('Extension') .': '. JText::_('Could not open').' '. $url);
		    return false;
		}

		$this->xml_parser = xml_parser_create('');
		xml_set_object($this->xml_parser, $this);
		xml_set_element_handler($this->xml_parser, '_startElement', '_endElement');
		xml_set_character_data_handler($this->xml_parser, '_characterData');

		while ($data = fread($fp, 8192)) {
		    if (!xml_parse($this->xml_parser, $data, feof($fp))) {
		        die(sprintf("XML error: %s at line %d",
		                    xml_error_string(xml_get_error_code($this->xml_parser)),
		                    xml_get_current_line_number($this->xml_parser)));
		    }
		}
		xml_parser_free($this->xml_parser);
		if(isset($this->latest)) {
			$updates = Array($this->latest);
		} else {
			$updates = Array();
		}
		return Array('update_sites'=>Array(),'updates'=>$updates);
	}
}