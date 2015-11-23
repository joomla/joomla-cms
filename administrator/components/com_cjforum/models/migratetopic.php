<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();
require_once JPATH_ADMINISTRATOR . '/components/com_cjforum/models/topic.php';

class CjForumModelMigrateTopic extends CjForumModelTopic
{
	protected $text_prefix = 'COM_CJFORUM';

	public $typeAlias = 'com_cjforum.topic';
	
	protected $_item = null;
	
	public function __construct($config)
	{
		parent::__construct($config);
	}

	public function getTable ($type = 'MigrateTopic', $prefix = 'CjForumTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
}