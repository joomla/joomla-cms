<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Weblinks
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * Weblinks Component Snapshots
 *
 * Read XML snapshots file
 *
 * @package		Joomla
 * @subpackage	Weblinks
 * @since 1.6
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
			$xmlDoc = & JFactory::getXMLparser('dom', $options);
			if ( $xmlDoc == false ) {
				$this->setError(JText::_('Error: Cannot create XML doc'));
				return false;
			}

			$content = $xmlDoc->getTextFromFile(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_weblinks'.DS.'models'.DS.'snapshotsources.xml');
			if (!$content) {
				$this->setError(JText::_('Error: Cannot load document'));
				return false;
			}

			$status = $xmlDoc->parseXML($content);
			if ( $status == false ) {
				$this->setError(JText::_('Error: Cannot parse XML doc'));
				return false;
			}

			$config = &$xmlDoc->documentElement;
			if ($config->nodeName != 'config') {
				$this->setError(JText::_('Error: Root incorrect'));
				return false;
			}

			$sitesNode = &$config->firstChild;
			if ($sitesNode->nodeName != 'sites') {
				$this->setError(JText::_('Error: Sites incorrect'));
				return false;
			}

			$sites = array();
			$siteNode = & $sitesNode->firstChild;
			while ($siteNode) {
				if ($siteNode->nodeName != 'site') {
					$this->setError(JText::_('Error: Invalid site format'));
					return false;
				}
				$siteInfo = & $siteNode->attributes;
				$site = new stdClass();
				$site->name = $siteInfo['name'];
				$siteItem = & $siteNode->firstChild;
				while ($siteItem) {
					switch ($siteItem->nodeName) {
						case 'url':
							$site->url = $siteItem->firstChild->nodeValue;
							break;
						case 'website':
							$site->website = $siteItem->firstChild->nodeValue;
							break;
						case 'website-url':
							$site->website_url = $siteItem->firstChild->nodeValue;
							break;
						case 'pic':
							$site->pic = $siteItem->firstChild->nodeValue;
							break;
						case 'submit':
							$site->submit = $siteItem->firstChild->nodeValue;
							break;
						case 'valid':
							$site->valid = $siteItem->firstChild->nodeValue;
							break;
						default:
							$this->setError(JText::_('Error: Invalid site parameter'));
							return false;
							break;
					}
					$siteItem = & $siteItem->nextSibling;
				}
				$sites[] = $site;
				$siteNode = & $siteNode->nextSibling;
			}

			$this->_data = $sites;
			return (boolean) $this->_data;
		}
		return true;
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