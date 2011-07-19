<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Updater
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.updater.updateadapter');

/**
 * Collection Update Adapter Class
 * @since   11.1
 */
class JUpdaterCollection extends JUpdateAdapter {
	/**
	 * @var object Root of the tree
	 */
	protected $base;

	/**
	 * @var array Tree of objects
	 */
	protected $parent = Array(0);

	/**
	 * @var boolean Used to control if an item has a child or not
	 */
	protected $pop_parent = 0;

	/**
	 * @var array A list of discovered update sites
	 */
	protected $update_sites;

	/**
	 * @var array A list of discovered updates
	 */
	protected $updates;

	/**
	 * Gets the reference to the current direct parent
	 *
	 * @return  object
	 * @since   11.1
	 */
	protected function _getStackLocation()
	{

		return implode('->', $this->_stack);
	}

	/**
	 * Get the parent tag
	 * @return  string   parent
	 */
	protected function _getParent()
	{
		return end($this->parent);
	}

	/**
	 * Opening an XML element
	 * @param   object parser object
	 * @param   string name of element that is opened
	 * @param   array array of attributes for the element
	 *
	 */
	public function _startElement($parser, $name, $attrs = Array())
	{
		array_push($this->_stack, $name);
		$tag = $this->_getStackLocation();
		// Reset the data
		eval('$this->'. $tag .'->_data = "";');
		switch($name)
		{
			case 'CATEGORY':
				if(isset($attrs['REF']))
				{
					$this->update_sites[] = Array('type'=>'collection','location'=>$attrs['REF'],'update_site_id'=>$this->_update_site_id);
				} else
				{
					// This item will have children, so prepare to attach them
					$this->pop_parent = 1;
				}
				break;
			case 'EXTENSION':
				$update = JTable::getInstance('update');
				$update->set('update_site_id', $this->_update_site_id);
				foreach($this->_updatecols AS $col)
				{
					// Reset the values if it doesn't exist
					if(!array_key_exists($col, $attrs))
					{
						$attrs[$col] = '';
						if($col == 'CLIENT')
						{
							$attrs[$col] = 'site';
						}
					}
				}
				$client = JApplicationHelper::getClientInfo($attrs['CLIENT'],1);
				$attrs['CLIENT_ID'] = $client->id;
				// Lower case all of the fields
				foreach($attrs as $key=>$attr)
				{
					$values[strtolower($key)] = $attr;
				}

				// Only add the update if it is on the same platform and release as we are
				$ver = new JVersion;
				$product = strtolower(JFilterInput::getInstance()->clean($ver->PRODUCT, 'cmd')); // lower case and remove the exclamation mark
				// Set defaults, the extension file should clarify in case but it may be only available in one version
				// This allows an update site to specify a targetplatform
				// targetplatformversion can be a regexp, so 1.[56] would be valid for an extension that supports 1.5 and 1.6
				// Note: Whilst the version is a regexp here, the targetplatform is not (new extension per platform)
				//		Additionally, the version is a regexp here and it may also be in an extension file if the extension is
				//		compatible against multiple versions of the same platform (e.g. a library)
				if(!isset($values['targetplatform'])) $values['targetplatform'] = $product; // set this to ourself as a default
				if(!isset($values['targetplatformversion'])) $values['targetplatformversion'] = $ver->RELEASE; // set this to ourself as a default
				// validate that we can install the extension
				if($product == $values['targetplatform'] && preg_match('/'.$values['targetplatformversion'].'/',$ver->RELEASE))
				{
					$update->bind($values);
					$this->updates[] = $update;
				}
				break;
		}
	}

	/**
	 * Closing an XML element
	 * Note: This is a protected function though has to be exposed externally as a callback
	 * @param   object parser object
	 * @param   string name of the element closing
	 */
	protected function _endElement($parser, $name)
	{
		$lastcell = array_pop($this->_stack);
		switch($name)
		{
			case 'CATEGORY':
				if($this->pop_parent)
				{
					$this->pop_parent = 0;
					array_pop($this->parent);
				}
				break;
		}
	}

	// Note: we don't care about char data in collection because there should be none


	/*
	 * Find an update
	 * @param   array options to use; update_site_id: the unique ID of the update site to look at
	 *
	 * @return  array    update_sites and updates discovered
	 */
	public function findUpdate($options)
	{
		$url = $options['location'];
		$this->_update_site_id = $options['update_site_id'];
		if(substr($url, -4) != '.xml')
		{
			if(substr($url, -1) != '/') {
				$url .= '/';
			}
			$url .= 'update.xml';
		}

		$this->base = new stdClass;
		$this->update_sites = Array();
		$this->updates = Array();
		$dbo = $this->parent->getDBO();

		if (!($fp = @fopen($url, "r")))
		{
			$query = $dbo->getQuery(true);
			$query->update('#__update_sites');
			$query->set('enabled = 0');
			$query->where('update_site_id = '. $this->_update_site_id);
			$dbo->setQuery($query);
			$dbo->Query();
			JError::raiseWarning('101', JText::sprintf('JLIB_UPDATER_ERROR_COLLECTION_OPEN_URL', $url));
			return false;
		}

		$this->xml_parser = xml_parser_create('');
		xml_set_object($this->xml_parser, $this);
		xml_set_element_handler($this->xml_parser, '_startElement', '_endElement');

		while ($data = fread($fp, 8192))
		{
			if (!xml_parse($this->xml_parser, $data, feof($fp)))
			{
				die(sprintf("XML error: %s at line %d",
							xml_error_string(xml_get_error_code($this->xml_parser)),
							xml_get_current_line_number($this->xml_parser)));
			}
		}
		// TODO: Decrement the bad counter if non-zero
		return Array('update_sites'=>$this->update_sites,'updates'=>$this->updates);
	}
}
