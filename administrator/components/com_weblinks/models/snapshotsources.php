<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * Weblinks Component Snapshots
 *
 * Read XML snapshots file
 *
 * @package		Joomla.Administrator
 * @subpackage	Weblinks
 * @since		1.6
 */
class WeblinksModelSnapshotSources extends JModel
{
	/**
	 * Snapshot data
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct(array('name' => 'snapshotsources'));
	}

	/**
	 * Method to get a currency
	 */
	function &getData()
	{
		// Load the rates data
		if (!$this->_loadData())
			$this->_initData();

		return $this->_data;
	}

	/**
	 * Method to load snapshots data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 */
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (!$this->_data)
		{
			$options = array('lite' => '1');
			$xmlDoc = & JFactory::getXMLparser('simple', $options);
			if ($xmlDoc == false) {
				$this->setError(JText::_('Error: Cannot create XML doc'));
				return false;
			}

			$xml = $xmlDoc->loadFile(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_weblinks'.DS.'models'.DS.'snapshotsources.xml');
			if (!$xml) {
				$this->setError(JText::_('Error: Cannot load document'));
				return false;
			}
			die;
			$config = &$xmlDoc->document;
			if ($config->name != 'config') {
				$this->setError(JText::_('Error: Root incorrect'));
				return false;
			}

			$sitesNodes = &$config->children();
			foreach($sitesNodes as $siteNode)
			{
				if ($sitesNode->name != 'sites') {
					$this->setError(JText::_('Error: Sites incorrect'));
					return false;
				}

				$sites = array();
				$siteNodes = & $sitesNode->children();
				foreach ($siteNodes as $siteNode) {
					if ($siteNode->name != 'site') {
						$this->setError(JText::_('Error: Invalid site format'));
						return false;
					}
					$siteInfo = & $siteNode->attributes;
					$site = new stdClass();
					$site->name = $siteInfo['name'];
					$siteItems = & $siteNode->children;
					foreach ($siteItems as $siteItem) {
						switch ($siteItem->name) {
							case 'url':
								$site->url = $siteItem->data();
								break;
							case 'website':
								$site->website = $siteItem->data();
								break;
							case 'website-url':
								$site->website_url = $siteItem->data();
								break;
							case 'pic':
								$site->pic = $siteItem->data();
								break;
							case 'submit':
								$site->submit = $siteItem->data();
								break;
							case 'valid':
								$site->valid = $siteItem->data();
								break;
							default:
								$this->setError(JText::_('Error: Invalid site parameter'));
								return false;
								break;
						}
					}
					$sites[] = $site;
				}

				$this->_data = $sites;
				return (boolean) $this->_data;
			}
			return true;
		}
	}
	/**
	 * Method to initialise the currency data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 */
	function _initData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$this->_data = null;
			return (boolean) $this->_data;
		}
		return true;
	}
}