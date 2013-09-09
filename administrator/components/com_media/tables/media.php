<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Jtable class
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.2
 */
class MediaTableMedia extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   MediaTableMedia &$db  JDatabaseDriver  connector object.
	 *
	 * @since   3.0
	 */
	var $content_id = null;
	var $title = null;
	var $alias = null;
    var $body = null ;
	var $state  = null;
	var $checked_out_time  = null;
	var $checked_out  = null;
	var $params  = null;
	var $metadata  = null;
	var $created_user_id  = null;
	var $created_by_alias  = null;
	var $created_time  = null;
	var $modified_time  = null;
	var $language  = null;
	var $publish_up  = null;
	var $publish_down  = null;
	var $content_item_id  = null;
	var $asset_id  = null;
	var $images  = null;
	var $urls  = null;
	var $metakey  = null;
	var $metadesc  = null;
	var $catid  = null;
	var $xreference  = null;

	public function __construct(&$db)
	{
		$this->_observers = new JObserverUpdater($this); JObserverMapper::attachAllObservers($this);
		parent::__construct('#__ucm_media', 'content_id', $db);
	}

	protected function _getAssetTitle()
	{
		return $this->title;
	}


	public function bind($array, $ignore = '')
	{
		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules'])) {
			$rules = new JRules($array['rules']);
			$this->setRules($rules);
		}
		return parent::bind($array, $ignore);
	}

	/**
	 * Redefined asset name, as we support action control
	 */
	protected function _getAssetName() {
		$k = $this->_tbl_key;
		return 'com_media.media.'.(int) $this->$k;
	}

}
